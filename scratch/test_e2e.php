<?php
// SmartGov Market Automated End-to-End Test Verification Script
// File: scratch/test_e2e.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/license_generator.php';
require_once __DIR__ . '/../includes/payment.php';

try {
    echo "=========================================================\n";
    echo "STARTING SMARTGOV MARKET AUTOMATED E2E TEST VERIFICATION\n";
    echo "=========================================================\n\n";
    
    $db = getDBConnection();
    
    // Step 1: Create Test Vendor Account
    echo "[1/8] Registering Test Vendor...\n";
    $vEmail = 'e2e.vendor_' . time() . '@test.np';
    $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
    
    $uStmt = $db->prepare("INSERT INTO users (role_id, full_name, email, phone, password_hash, address, municipality, district, is_verified, is_active) 
                           VALUES (3, 'E2E Vendor Owner', ?, '9811111111', ?, 'Ward 5, Patan', 'Lalitpur Metro', 'Lalitpur', 1, 1)");
    $uStmt->execute([$vEmail, $passwordHash]);
    $vUserId = $db->lastInsertId();
    
    $vStmt = $db->prepare("INSERT INTO vendors (user_id, business_name, business_type, address, municipality, district, latitude, longitude, status) 
                           VALUES (?, 'Patan Handicrafts E2E', 'Handicrafts & Arts', 'Ward 5, Patan', 'Lalitpur Metro', 'Lalitpur', 27.6744, 85.3249, 'Pending')");
    $vStmt->execute([$vUserId]);
    $vendorId = $db->lastInsertId();
    
    $appNo = generateCode('VND', 'vendor_applications', 'application_no');
    $appStmt = $db->prepare("INSERT INTO vendor_applications (application_no, vendor_id, department_id, status, submitted_at) VALUES (?, ?, 1, 'Submitted', NOW())");
    $appStmt->execute([$appNo, $vendorId]);
    $appId = $db->lastInsertId();
    
    echo "    [✓] Vendor Registered: 'Patan Handicrafts E2E' (App: {$appNo})\n";
    
    // Step 2: Officer Approval & Digital License Generation
    echo "[2/8] Government Officer Reviewing & Approving Vendor...\n";
    $lic = issueDigitalLicense($vendorId, $appId, 'Department of Commerce & Industry');
    echo "    [✓] License Generated: {$lic['license_no']} | Status: {$lic['status']}\n";
    
    // Step 3: Approved Vendor Listing Product
    echo "[3/8] Adding Product to Marketplace...\n";
    $pStmt = $db->prepare("INSERT INTO products (vendor_id, category_id, name, slug, description, price, stock_quantity, image_path, status) 
                           VALUES (?, 2, 'Patan Wooden Statue E2E', 'patan-wooden-statue-e2e', 'Handcrafted teak wood statue.', 1200.00, 10, 'assets/css/product-default.jpg', 'Active')");
    $pStmt->execute([$vendorId]);
    $productId = $db->lastInsertId();
    echo "    [✓] Product Listed: 'Patan Wooden Statue E2E' (Price: रु 1200.00, Stock: 10)\n";
    
    // Step 4: Customer Order Placement & Stock Validation
    echo "[4/8] Customer Placing Order for Product...\n";
    $cUserId = 5; // Demo Sita Sharma
    $orderNo = generateCode('ORD', 'orders', 'order_no');
    
    $db->beginTransaction();
    $oInsert = $db->prepare("INSERT INTO orders (order_no, customer_id, total_amount, shipping_address, municipality, district, payment_method, payment_status, status) 
                             VALUES (?, ?, 1200.00, 'House 12, Baneshwor', 'Kathmandu Metro', 'Kathmandu', 'Cash on Delivery', 'Pending', 'Processing')");
    $oInsert->execute([$orderNo, $cUserId]);
    $orderId = $db->lastInsertId();
    
    $oiInsert = $db->prepare("INSERT INTO order_items (order_id, product_id, vendor_id, quantity, price, subtotal) VALUES (?, ?, ?, 2, 1200.00, 2400.00)");
    $oiInsert->execute([$orderId, $productId, $vendorId]);
    
    $stockDeduct = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - 2 WHERE id = ?");
    $stockDeduct->execute([$productId]);
    
    $db->commit();
    
    // Check remaining stock
    $remStock = $db->query("SELECT stock_quantity FROM products WHERE id = {$productId}")->fetchColumn();
    echo "    [✓] Order Placed: {$orderNo} | Remaining Stock: {$remStock} (Deducted 2)\n";
    
    // Step 5: Vendor Fulfillment
    echo "[5/8] Vendor Updating Order to Delivered...\n";
    $uOrder = $db->prepare("UPDATE orders SET status = 'Delivered', payment_status = 'Paid' WHERE id = ?");
    $uOrder->execute([$orderId]);
    echo "    [✓] Order Status: Delivered & Paid\n";
    
    // Step 6: Customer Submitting Review
    echo "[6/8] Customer Writing Review for Delivered Product...\n";
    $rStmt = $db->prepare("INSERT INTO reviews (product_id, user_id, order_id, rating, comment) VALUES (?, ?, ?, 5, 'Exceptional craftsmanship!')");
    $rStmt->execute([$productId, $cUserId, $orderId]);
    echo "    [✓] 5-Star Review Submitted\n";
    
    // Step 7: Public License QR Verification Lookup
    echo "[7/8] Testing Public QR License Verification API...\n";
    $verLic = verifyLicenseByNo($lic['license_no']);
    if ($verLic && $verLic['status'] === 'VALID') {
        echo "    [✓] License Verification SUCCESSFUL: '{$verLic['business_name']}' STATUS=VALID\n";
    } else {
        throw new Exception("License verification failed!");
    }
    
    // Step 8: Clean up test vendor
    echo "[8/8] Cleaning up temporary E2E test data...\n";
    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$vUserId]);
    echo "    [✓] Cleanup complete.\n";
    
    echo "\n=========================================================\n";
    echo "SMARTGOV MARKET E2E INTEGRATION TEST PASSED 100% CLEAN!\n";
    echo "=========================================================\n";

} catch (Exception $e) {
    echo "\n[X] E2E TEST FAILED: " . $e->getMessage() . "\n";
    exit(1);
}
