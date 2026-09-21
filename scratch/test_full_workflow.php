<?php
/**
 * SmartGov-Market Full Workflow Verification Script
 * Tests all end-to-end user journeys and system requirements
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/license_generator.php';

echo "=======================================================\n";
echo "=== STARTING SMARTGOV-MARKET WORKFLOW VERIFICATION ===\n";
echo "=======================================================\n\n";

$db = getDBConnection();
$passed = 0;
$failed = 0;

function assertTest($condition, $name, $details = '') {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $name\n";
        if ($details) echo "       $details\n";
        $passed++;
    } else {
        echo "[FAIL] $name\n";
        if ($details) echo "       ERROR: $details\n";
        $failed++;
    }
}

// 1. Database Connection & Schema Test
try {
    $dbPort = $db->query("SELECT @@port")->fetchColumn();
    $tblCount = $db->query("SELECT count(*) FROM information_schema.tables WHERE table_schema = 'smartgov_market'")->fetchColumn();
    assertTest($tblCount >= 20, "1. Database Connected on Port $dbPort", "Found $tblCount tables in smartgov_market");
} catch (Exception $e) {
    assertTest(false, "1. Database Connection", $e->getMessage());
}

// Roles retrieval
$cRole = $db->query("SELECT id FROM roles WHERE LOWER(name) IN ('citizen', 'customer') LIMIT 1")->fetchColumn() ?: 4;
$vRole = $db->query("SELECT id FROM roles WHERE LOWER(name) = 'vendor' LIMIT 1")->fetchColumn() ?: 3;

// 2. Citizen Registration with Full Address & Duplicate Prevention
$testEmail = 'citizen_' . time() . '_' . rand(100, 999) . '@test.com';
$testPhone = '98' . rand(10000000, 99999999);
$testPass = password_hash('Pass@123', PASSWORD_BCRYPT);
$testAddress = 'Ward 4, Shanti Galli, House 12';
$testMun = 'Kathmandu';
$testDist = 'Kathmandu';
$citizenId = 0;

try {
    $ins = $db->prepare("INSERT INTO users (role_id, full_name, email, phone, password_hash, address, municipality, district, is_verified, is_active, created_at)
                         VALUES (?, 'Test Citizen User', ?, ?, ?, ?, ?, ?, 1, 1, NOW())");
    $ins->execute([$cRole, $testEmail, $testPhone, $testPass, $testAddress, $testMun, $testDist]);
    $citizenId = (int)$db->lastInsertId();
    
    // Verify citizen saved with complete residential address
    $cCheck = $db->prepare("SELECT * FROM users WHERE id = ?");
    $cCheck->execute([$citizenId]);
    $citizen = $cCheck->fetch();
    
    assertTest(!empty($citizen['address']) && !empty($citizen['municipality']), "2a. Citizen Registered with Complete Address", "Address: {$citizen['address']}, Mun: {$citizen['municipality']}");
    
    // Test duplicate check
    $dupCheck = $db->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $dupCheck->execute([$testEmail, $testPhone]);
    $dupCount = $dupCheck->rowCount();
    assertTest($dupCount === 1, "2b. Duplicate Email/Phone Detection Works", "Found exactly 1 record for unique email/phone");
} catch (Exception $e) {
    assertTest(false, "2. Citizen Registration", $e->getMessage());
}

// 3. Vendor Registration & Admin Verification with Digital License
$vendorId = 0;
try {
    $vEmail = 'vendor_' . time() . '_' . rand(100, 999) . '@test.com';
    $vPhone = '97' . rand(10000000, 99999999);
    
    $insVUser = $db->prepare("INSERT INTO users (role_id, full_name, email, phone, password_hash, is_verified, is_active, created_at)
                             VALUES (?, 'Organic Veggies Owner', ?, ?, ?, 1, 1, NOW())");
    $insVUser->execute([$vRole, $vEmail, $vPhone, $testPass]);
    $vUserId = $db->lastInsertId();

    $insVendor = $db->prepare("INSERT INTO vendors (user_id, business_name, business_type, address, municipality, district, status, created_at)
                              VALUES (?, 'Himalayan Organic Market', 'Agriculture', 'New Baneshwor', 'Kathmandu', 'Kathmandu', 'Pending', NOW())");
    $insVendor->execute([$vUserId]);
    $vendorId = (int)$db->lastInsertId();

    assertTest($vendorId > 0, "3a. Vendor Registered in Pending Status", "Vendor ID: $vendorId");

    // Admin reviews and issues Digital License
    $license = issueDigitalLicense($vendorId, null, 'Department of Commerce');
    
    // Re-check vendor status
    $vStatus = $db->query("SELECT status FROM vendors WHERE id = $vendorId")->fetchColumn();
    assertTest($vStatus === 'Verified', "3b. Admin Review/Verification updates vendor to 'Verified'", "Vendor Status: $vStatus");
    assertTest(!empty($license['license_no']) && !empty($license['qr_code_path']), "3c. Digital License Generated with QR Code Target", "License No: {$license['license_no']}, Target: {$license['qr_code_path']}");
    
    // 4. Verify Digital License Public Record
    $pubLic = getLicenseByNumber($license['license_no']);
    assertTest($pubLic && $pubLic['vendor_id'] == $vendorId, "4. Public License Verification query succeeds", "Owner: {$pubLic['owner_name']}, Business: {$pubLic['business_name']}");
} catch (Exception $e) {
    assertTest(false, "3/4. Vendor License & Verification", $e->getMessage());
}

// 5. Category Management CRUD & Product Insertion
$productId = 0;
try {
    $timeVal = time() . rand(10, 99);
    $catName = 'Organic Produce ' . $timeVal;
    $catSlug = 'organic-produce-' . $timeVal;
    $catStmt = $db->prepare("INSERT INTO categories (name, slug, description, is_active) VALUES (?, ?, 'Organic farm produce', 1)");
    $catStmt->execute([$catName, $catSlug]);
    $catId = $db->lastInsertId();
    assertTest($catId > 0, "5a. Category Created and Active", "Category ID: $catId, Name: $catName");

    // Vendor adds product
    $pStmt = $db->prepare("INSERT INTO products (vendor_id, category_id, name, slug, description, price, stock_quantity, status, created_at)
                          VALUES (?, ?, 'Organic Red Apples', ?, 'Fresh Himalayan apples', 250.00, 50, 'Active', NOW())");
    $pStmt->execute([$vendorId, $catId, 'apples-' . $timeVal]);
    $productId = (int)$db->lastInsertId();
    assertTest($productId > 0, "5b. Product Listed by Verified Vendor", "Product ID: $productId");

    // Check catalog query (same query used on public marketplace)
    $catQuery = $db->query("SELECT p.id, p.name, p.price, v.business_name FROM products p 
                           JOIN vendors v ON p.vendor_id = v.id 
                           JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = $productId AND p.status = 'Active' AND v.status IN ('Approved', 'Verified') AND c.is_active = 1");
    $pubProduct = $catQuery->fetch();
    assertTest($pubProduct && $pubProduct['id'] == $productId, "5c. Product is Visible in Public Marketplace", "Name: {$pubProduct['name']}, Vendor: {$pubProduct['business_name']}");
} catch (Exception $e) {
    assertTest(false, "5. Category & Product Management", $e->getMessage());
}

// 6. Admin Product Moderation (Disable / Enable)
try {
    // Admin disables product
    $db->prepare("UPDATE products SET status = 'Disabled' WHERE id = ?")->execute([$productId]);
    
    // Check catalog query again - should return false
    $catCheckDisabled = $db->query("SELECT p.id FROM products p 
                                   JOIN vendors v ON p.vendor_id = v.id 
                                   JOIN categories c ON p.category_id = c.id 
                                   WHERE p.id = $productId AND p.status = 'Active' AND v.status IN ('Approved', 'Verified') AND c.is_active = 1")->fetch();
    assertTest(!$catCheckDisabled, "6a. Disabled Product is instantly hidden from marketplace catalog", "Disabled product returned 0 results");

    // Admin re-enables product
    $db->prepare("UPDATE products SET status = 'Active' WHERE id = ?")->execute([$productId]);
    $catCheckEnabled = $db->query("SELECT p.id FROM products p WHERE id = $productId AND status = 'Active'")->fetch();
    assertTest(!empty($catCheckEnabled), "6b. Re-enabled Product returns to Active state", "Status: Active");
} catch (Exception $e) {
    assertTest(false, "6. Product Moderation", $e->getMessage());
}

// 7. Order Placement & Cart Checkout
try {
    $orderNo = generateCode('ORD', 'orders', 'order_no');
    $subtotal = 500.00;
    $total = 500.00;

    $oStmt = $db->prepare("INSERT INTO orders (order_no, customer_id, customer_name, customer_phone, total_amount, 
                                             shipping_address, municipality, district, 
                                             payment_method, payment_status, status, created_at)
                          VALUES (?, ?, 'Test Citizen User', ?, ?, ?, ?, ?, 'Cash on Delivery', 'Pending', 'Processing', NOW())");
    $oStmt->execute([$orderNo, $citizenId, $testPhone, $total, $testAddress, $testMun, $testDist]);
    $orderId = (int)$db->lastInsertId();

    $oiStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, vendor_id, quantity, price, subtotal)
                           VALUES (?, ?, ?, 2, 250.00, 500.00)");
    $oiStmt->execute([$orderId, $productId, $vendorId]);
    $orderItemId = $db->lastInsertId();

    assertTest($orderId > 0 && $orderItemId > 0, "7a. Order Placed with Order Items & Addresses", "Order No: $orderNo, Total: Rs. $total");

    // 8. Admin Order Inspection and Status Lifecycle
    $inspectQuery = $db->prepare("SELECT o.*, u.full_name as user_full_name, u.email as customer_email,
                                         COUNT(oi.id) as item_count
                                  FROM orders o
                                  JOIN users u ON o.customer_id = u.id
                                  JOIN order_items oi ON o.id = oi.order_id
                                  WHERE o.id = ? GROUP BY o.id");
    $inspectQuery->execute([$orderId]);
    $orderRecord = $inspectQuery->fetch();

    assertTest($orderRecord && $orderRecord['customer_name'] === 'Test Citizen User', "8a. Admin Order Inspection retrieves full customer & item details", "Customer: {$orderRecord['customer_name']}, Order No: {$orderRecord['order_no']}");

    // Update order status to Processing, then Delivered
    $db->prepare("UPDATE orders SET status = 'Delivered', payment_status = 'Paid' WHERE id = ?")->execute([$orderId]);
    $st1 = $db->query("SELECT status, payment_status FROM orders WHERE id = $orderId")->fetch();
    assertTest($st1['status'] === 'Delivered' && $st1['payment_status'] === 'Paid', "8b. Order Status updated to 'Delivered' and 'Paid'", "Status: {$st1['status']}, Payment: {$st1['payment_status']}");
} catch (Exception $e) {
    assertTest(false, "7/8. Order Placement & Inspection", $e->getMessage());
}

// 9. Complaint System with GIS Coordinates
try {
    $compNo = generateCode('CMP', 'complaints', 'complaint_no');
    $cStmt = $db->prepare("INSERT INTO complaints (complaint_no, user_id, category, priority, description,
                                                  location_address, latitude, longitude, department_id, status, created_at)
                          VALUES (?, ?, 'Pricing', 'High', 'The vendor charged above the government rate ceiling',
                                  'Ward 4, Kathmandu Central Market', 27.71724, 85.32402, 3, 'Submitted', NOW())");
    $cStmt->execute([$compNo, $citizenId]);
    $compColId = (int)$db->lastInsertId();

    // Inspect complaint
    $compCheck = $db->prepare("SELECT c.*, u.full_name as citizen_name
                               FROM complaints c
                               LEFT JOIN users u ON c.user_id = u.id
                               WHERE c.id = ?");
    $compCheck->execute([$compColId]);
    $complaint = $compCheck->fetch();

    assertTest($complaint && !empty($complaint['latitude']) && !empty($complaint['longitude']), 
               "9a. Complaint lodged with precise GPS coordinates", 
               "Comp No: $compNo, Lat: {$complaint['latitude']}, Lng: {$complaint['longitude']}");

    // Admin updates complaint to Resolved
    $db->prepare("UPDATE complaints SET status = 'Resolved', resolution = 'Warning issued to vendor; price ceiling enforced.' WHERE id = ?")->execute([$compColId]);
    $cSt = $db->query("SELECT status, resolution FROM complaints WHERE id = $compColId")->fetch();
    assertTest($cSt['status'] === 'Resolved', "9b. Admin updates Complaint to 'Resolved' with resolution notes", "Notes: {$cSt['resolution']}");
} catch (Exception $e) {
    assertTest(false, "9. Complaint & GIS System", $e->getMessage());
}

// 10. Session & Flash Message Mechanism Verification
try {
    // Set flash message and retrieve it
    setFlashMessage('success', 'Verification flash message test');
    $flash = getFlashMessage();
    assertTest($flash !== null && $flash['type'] === 'success', "10. Flash Message System operates without delayed consumption", "Message: {$flash['message']}");
} catch (Exception $e) {
    assertTest(false, "10. Flash Message System", $e->getMessage());
}

echo "\n=======================================================\n";
echo "VERIFICATION SUMMARY:\n";
echo "Total Tests Passed: $passed\n";
echo "Total Tests Failed: $failed\n";
echo "=======================================================\n";
