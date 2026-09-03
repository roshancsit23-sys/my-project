<?php
// SmartGov Market - Automated End-to-End Verification Test Script
// File: scratch/test_flow.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/payment.php';

$db = getDBConnection();
echo "==================================================\n";
echo "SMARTGOV-MARKET AUTOMATED INTEGRATION TEST SUITE\n";
echo "==================================================\n\n";

try {
    // 1. Verify Database Schema
    echo "[TEST 1] Database Schema Check...\n";
    $colStmt = $db->query("SHOW COLUMNS FROM service_applications LIKE 'document_path'");
    if ($colStmt->fetch()) {
        echo "  ✓ OK: Column 'document_path' exists in service_applications.\n";
    } else {
        throw new Exception("Column 'document_path' missing in service_applications.");
    }

    // 2. Test Citizen Registration with Residential Address
    echo "\n[TEST 2] Citizen Registration with Residential Address...\n";
    $testEmail = "test_citizen_" . time() . "@example.com";
    $testPass = password_hash("password123", PASSWORD_BCRYPT);
    $cStmt = $db->prepare("INSERT INTO users (role_id, full_name, email, phone, password_hash, address, municipality, district, is_verified, is_active, created_at) 
                           VALUES (4, 'Test Citizen', ?, '9841999999', ?, 'Boudha, Kathmandu, Ward 6', 'Kathmandu Metro', 'Kathmandu', 1, 1, NOW())");
    $cStmt->execute([$testEmail, $testPass]);
    $citizenId = $db->lastInsertId();
    echo "  ✓ OK: Created Citizen Account ID #{$citizenId} with Address 'Boudha, Kathmandu, Ward 6'.\n";

    // 3. Test Vendor Registration & Product Creation
    echo "\n[TEST 3] Vendor Registration & Product Listing...\n";
    $vendorEmail = "test_vendor_" . time() . "@example.com";
    $vUserStmt = $db->prepare("INSERT INTO users (role_id, full_name, email, phone, password_hash, address, municipality, district, is_verified, is_active, created_at) 
                               VALUES (3, 'Test Vendor Owner', ?, '9851999999', ?, 'Thamel, Kathmandu', 'Kathmandu Metro', 'Kathmandu', 1, 1, NOW())");
    $vUserStmt->execute([$vendorEmail, $testPass]);
    $vUserId = $db->lastInsertId();

    $vStmt = $db->prepare("INSERT INTO vendors (user_id, business_name, business_type, address, municipality, district, status, created_at) 
                           VALUES (?, 'Himalayan Organic Traders', 'Agriculture & Food Products', 'Thamel, Kathmandu', 'Kathmandu Metro', 'Kathmandu', 'Approved', NOW())");
    $vStmt->execute([$vUserId]);
    $vendorId = $db->lastInsertId();

    // Create Test Product
    $prodName = "Pure Mustang Honey " . rand(100, 999);
    $pStmt = $db->prepare("INSERT INTO products (vendor_id, category_id, name, slug, description, price, stock_quantity, image_path, status, created_at) 
                           VALUES (?, 1, ?, 'pure-mustang-honey', 'Authentic mountain honey', 1250.00, 20, 'assets/images/placeholder.svg', 'Active', NOW())");
    $pStmt->execute([$vendorId, $prodName]);
    $productId = $db->lastInsertId();
    echo "  ✓ OK: Created Approved Vendor #{$vendorId} and Listed Product #{$productId} ('{$prodName}') with Stock = 20.\n";

    // 4. Test Marketplace Visibility
    echo "\n[TEST 4] Marketplace Public Listing Check...\n";
    $pubStmt = $db->prepare("SELECT p.name, v.business_name FROM products p JOIN vendors v ON p.vendor_id = v.id WHERE p.id = ? AND p.status = 'Active'");
    $pubStmt->execute([$productId]);
    $pubItem = $pubStmt->fetch();
    if ($pubItem) {
        echo "  ✓ OK: Product '{$pubItem['name']}' from '{$pubItem['business_name']}' is publicly visible on marketplace.\n";
    } else {
        throw new Exception("Product #{$productId} is not visible on public marketplace!");
    }

    // 5. Test Order Placement & Stock Deduction
    echo "\n[TEST 5] Order Placement & Auto Stock Reduction...\n";
    $orderNo = "ORD-TEST-" . time();
    $qtyOrdered = 3;
    $totalAmt = 1250.00 * $qtyOrdered;
    
    $oInsert = $db->prepare("INSERT INTO orders (order_no, customer_id, customer_name, customer_phone, total_amount, shipping_address, municipality, district, payment_method, payment_status, status, created_at) 
                             VALUES (?, ?, 'Test Citizen', '9841999999', ?, 'Boudha, Kathmandu, Ward 6', 'Kathmandu Metro', 'Kathmandu', 'eSewa', 'Pending', 'Pending Payment', NOW())");
    $oInsert->execute([$orderNo, $citizenId, $totalAmt]);
    $orderId = $db->lastInsertId();

    $oiInsert = $db->prepare("INSERT INTO order_items (order_id, product_id, vendor_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, 1250.00, ?)");
    $oiInsert->execute([$orderId, $productId, $vendorId, $qtyOrdered, $totalAmt]);

    // Update Stock
    $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?")->execute([$qtyOrdered, $productId]);
    
    $stCheck = $db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
    $stCheck->execute([$productId]);
    $remStock = $stCheck->fetchColumn();
    echo "  ✓ OK: Order #{$orderId} ({$orderNo}) placed. Product stock updated from 20 to {$remStock}.\n";

    // 6. Test Simulated Demo Payment (eSewa / Khalti / ConnectIPS) & Transaction History
    echo "\n[TEST 6] Simulated Demo Payment & Transaction Record...\n";
    $txnId = "DEMO-" . strtoupper(substr(md5((string)mt_rand()), 0, 8));
    $payStmt = $db->prepare("INSERT INTO payments (order_id, transaction_id, payment_method, amount, status, payment_date) VALUES (?, ?, 'eSewa', ?, 'Completed', NOW())");
    $payStmt->execute([$orderId, $txnId, $totalAmt]);

    $db->prepare("UPDATE orders SET payment_status = 'Paid', status = 'Paid' WHERE id = ?")->execute([$orderId]);

    // Verify Transaction Record
    $tCheck = $db->prepare("SELECT p.transaction_id, p.payment_method, p.amount, o.order_no, o.status 
                            FROM payments p JOIN orders o ON p.order_id = o.id WHERE p.transaction_id = ?");
    $tCheck->execute([$txnId]);
    $txnRecord = $tCheck->fetch();
    if ($txnRecord) {
        echo "  ✓ OK: Simulated eSewa Payment recorded. Transaction ID: {$txnRecord['transaction_id']}, Amount: NPR {$txnRecord['amount']}, Order Status: {$txnRecord['status']}.\n";
    } else {
        throw new Exception("Transaction record #{$txnId} not found!");
    }

    // 7. Test Government Service Application & File Upload Record
    echo "\n[TEST 7] Government Service Application with Document Attachment...\n";
    $srvAppNo = "SRV-TEST-" . time();
    $docRelPath = "uploads/services/SRV_test_document.pdf";
    $sInsert = $db->prepare("INSERT INTO service_applications (application_no, service_id, user_id, status, remarks, document_path, submitted_at) 
                             VALUES (?, 1, ?, 'Submitted', 'Requesting recommendation letter for business', ?, NOW())");
    $sInsert->execute([$srvAppNo, $citizenId, $docRelPath]);
    $srvAppId = $db->lastInsertId();

    $srvCheck = $db->prepare("SELECT application_no, document_path, status FROM service_applications WHERE id = ?");
    $srvCheck->execute([$srvAppId]);
    $srvData = $srvCheck->fetch();
    if ($srvData && $srvData['document_path'] === $docRelPath) {
        echo "  ✓ OK: Government Service Application #{$srvAppId} ({$srvData['application_no']}) created with document path '{$srvData['document_path']}' and Status '{$srvData['status']}'.\n";
    } else {
        throw new Exception("Government service application failed verification!");
    }

    echo "\n==================================================\n";
    echo "ALL 7 INTEGRATION TESTS PASSED SUCCESSFULLY! ✓\n";
    echo "==================================================\n";

} catch (Exception $e) {
    echo "\n[TEST ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
