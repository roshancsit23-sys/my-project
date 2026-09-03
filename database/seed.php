<?php
// SmartGov Market - Database Seeder Script
// File: database/seed.php

require_once __DIR__ . '/../config/database.php';

try {
    $db = getDBConnection();
    
    echo "Seeding SmartGov Market Database...\n";
    
    // 1. Roles
    $roles = [
        ['id' => 1, 'name' => 'admin', 'display_name' => 'Administrator'],
        ['id' => 2, 'name' => 'officer', 'display_name' => 'Government Officer'],
        ['id' => 3, 'name' => 'vendor', 'display_name' => 'Local Vendor'],
        ['id' => 4, 'name' => 'customer', 'display_name' => 'Citizen / Customer'],
    ];
    $stmt = $db->prepare("INSERT INTO roles (id, name, display_name) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), display_name=VALUES(display_name)");
    foreach ($roles as $r) {
        $stmt->execute([$r['id'], $r['name'], $r['display_name']]);
    }
    echo "[✓] Roles seeded.\n";
    
    // 2. Departments
    $departments = [
        ['id' => 1, 'department_name' => 'Department of Commerce & Industry', 'code' => 'DCI', 'description' => 'Business regulation, vendor licensing, and local commerce compliance.'],
        ['id' => 2, 'department_name' => 'Department of Agriculture & Food Safety', 'code' => 'DAFS', 'description' => 'Agri-products inspection, organic certification, and food security.'],
        ['id' => 3, 'department_name' => 'Consumer Protection Authority', 'code' => 'CPA', 'description' => 'Fair trade monitoring, customer grievance management, and price control.'],
    ];
    $stmt = $db->prepare("INSERT INTO departments (id, department_name, code, description) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE department_name=VALUES(department_name)");
    foreach ($departments as $d) {
        $stmt->execute([$d['id'], $d['department_name'], $d['code'], $d['description']]);
    }
    echo "[✓] Departments seeded.\n";
    
    // 3. Demo Users
    $passwordHash = password_hash('password123', PASSWORD_BCRYPT);
    
    $users = [
        [1, 1, 'Super Administrator', 'admin@smartgov.gov.np', '9800000001', $passwordHash, 'Gov Secretariat, Singha Durbar', 'Kathmandu Metro', 'Kathmandu'],
        [2, 2, 'Officer Ramesh Shrestha', 'officer@smartgov.gov.np', '9800000002', $passwordHash, 'Municipal Licensing Bureau', 'Kathmandu Metro', 'Kathmandu'],
        [3, 3, 'Himalayan Organic Farms (Approved Vendor)', 'vendor@localcrafts.np', '9800000003', $passwordHash, 'Farm House 42, Nagarkot Road', 'Bhaktapur Municipality', 'Bhaktapur'],
        [4, 3, 'Annapurna Handicrafts (Pending Vendor)', 'pending.vendor@freshfarm.np', '9800000004', $passwordHash, 'Lakeside Ward 6', 'Pokhara Metropolitan', 'Kaski'],
        [5, 4, 'Sita Sharma (Citizen Customer)', 'customer@gmail.com', '9800000005', $passwordHash, 'House 12, Baneshwor', 'Kathmandu Metro', 'Kathmandu'],
    ];
    
    $stmt = $db->prepare("INSERT INTO users (id, role_id, full_name, email, phone, password_hash, address, municipality, district, is_verified, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 1) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), password_hash=VALUES(password_hash)");
    foreach ($users as $u) {
        $stmt->execute($u);
    }
    echo "[✓] Users seeded.\n";
    
    // 4. Vendors & Applications
    // Vendor 1: Approved Vendor
    $stmt = $db->prepare("INSERT INTO vendors (id, user_id, business_name, business_type, address, municipality, district, latitude, longitude, status) VALUES (1, 3, 'Himalayan Organic Farms', 'Agriculture & Food', 'Nagarkot Road 42', 'Bhaktapur Municipality', 'Bhaktapur', 27.6710, 85.4298, 'Approved') ON DUPLICATE KEY UPDATE status='Approved'");
    $stmt->execute();
    
    $stmt = $db->prepare("INSERT INTO vendor_applications (id, application_no, vendor_id, department_id, assigned_officer_id, status, officer_remarks, submitted_at, reviewed_at, approved_at) VALUES (1, 'VND-2026-000101', 1, 2, 2, 'Approved', 'All agricultural clearance certificates verified. Approved for digital business license.', NOW(), NOW(), NOW()) ON DUPLICATE KEY UPDATE status='Approved'");
    $stmt->execute();
    
    // License for Approved Vendor
    $stmt = $db->prepare("INSERT INTO licenses (id, license_no, vendor_id, application_id, issuing_authority, issue_date, expiry_date, status) VALUES (1, 'LIC-2026-000101', 1, 1, 'Department of Agriculture & Food Safety', '2026-01-01', '2027-01-01', 'VALID') ON DUPLICATE KEY UPDATE status='VALID'");
    $stmt->execute();

    // Vendor 2: Pending Vendor
    $stmt = $db->prepare("INSERT INTO vendors (id, user_id, business_name, business_type, address, municipality, district, latitude, longitude, status) VALUES (2, 4, 'Annapurna Handicrafts', 'Handicrafts & Souvenirs', 'Lakeside Ward 6', 'Pokhara Metropolitan', 'Kaski', 28.2096, 83.9856, 'Pending') ON DUPLICATE KEY UPDATE status='Pending'");
    $stmt->execute();
    
    $stmt = $db->prepare("INSERT INTO vendor_applications (id, application_no, vendor_id, department_id, assigned_officer_id, status, officer_remarks, submitted_at) VALUES (2, 'VND-2026-000102', 2, 1, 2, 'Submitted', 'Application received. Pending officer document verification.', NOW()) ON DUPLICATE KEY UPDATE status='Submitted'");
    $stmt->execute();

    // Sample Document for Pending Vendor
    $stmt = $db->prepare("INSERT INTO vendor_documents (id, application_id, document_type, file_name, file_path, file_size, verification_status) VALUES (1, 2, 'Tax Registration (PAN/VAT)', 'pan_certificate.pdf', 'uploads/documents/sample_pan.pdf', 102400, 'Pending') ON DUPLICATE KEY UPDATE verification_status='Pending'");
    $stmt->execute();
    
    echo "[✓] Vendors, Applications, Documents & License seeded.\n";
    
    // 5. Product Categories
    $categories = [
        [1, 'Organic Food & Agro', 'organic-food-agro', 'Fresh local fruits, vegetables, honey, and tea direct from mountain farms.', 'cat-food.jpg'],
        [2, 'Handicrafts & Arts', 'handicrafts-arts', 'Authentic handmade wooden crafts, pashmina shawls, and thangka paintings.', 'cat-crafts.jpg'],
        [3, 'Clothing & Textile', 'clothing-textile', 'Traditional Dhaka garments, woolens, and eco-friendly hemp bags.', 'cat-clothing.jpg'],
        [4, 'Herbal & Wellness', 'herbal-wellness', 'Ayurvedic medicines, essential oils, and natural skincare products.', 'cat-wellness.jpg'],
    ];
    $stmt = $db->prepare("INSERT INTO categories (id, name, slug, description, image_path) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name)");
    foreach ($categories as $c) {
        $stmt->execute($c);
    }
    echo "[✓] Categories seeded.\n";
    
    // 6. Products
    $products = [
        [1, 1, 1, 'Pure Mustang Organic Honey (500g)', 'pure-mustang-organic-honey-500g', 'Unfiltered, 100% natural raw mountain honey harvested from high-altitude mustard blossoms.', 850.00, 45, 'honey.jpg', 'Active'],
        [2, 1, 1, 'Ilam Orthodox Green Tea (250g)', 'ilam-orthodox-green-tea-250g', 'Hand-picked premium loose leaf green tea grown in the eastern hills of Ilam.', 450.00, 100, 'tea.jpg', 'Active'],
        [3, 1, 2, 'Hand-Carved Wooden Peacock Window', 'hand-carved-wooden-peacock-window', 'Exquisite traditional Newari wood carving souvenir crafted by veteran artisans of Bhaktapur.', 3500.00, 8, 'window.jpg', 'Active'],
        [4, 1, 3, 'Authentic Himalayan Pashmina Stole', 'authentic-himalayan-pashmina-stole', 'Ultra-soft 100% cashmere pashmina shawl woven with intricate traditional borders.', 2800.00, 15, 'pashmina.jpg', 'Active'],
    ];
    $stmt = $db->prepare("INSERT INTO products (id, vendor_id, category_id, name, slug, description, price, stock_quantity, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), price=VALUES(price)");
    foreach ($products as $p) {
        $stmt->execute($p);
    }
    echo "[✓] Products seeded.\n";
    
    // 7. Government Services
    $services = [
        [1, 1, 'Local Business License Issuance', 'Official registration and digital licensing for local retail, agricultural, and craft vendors.', 'PAN/VAT certificate, Citizenship copy, Business address proof', '3 Business Days', 500.00],
        [2, 2, 'Organic Food & Quality Certification', 'Government inspection and organic quality endorsement seal for food producers.', 'Sample testing report, Land ownership/lease agreement', '5 Business Days', 1200.00],
        [3, 3, 'Fair Trade & Market Grievance Submission', 'Official complaint lodging system for price gouging, fake goods, or vendor misconduct.', 'Receipt or transaction details, Photograph evidence', '2 Business Days', 0.00],
    ];
    $stmt = $db->prepare("INSERT INTO government_services (id, department_id, service_name, description, requirements, processing_time, fee) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE service_name=VALUES(service_name)");
    foreach ($services as $s) {
        $stmt->execute($s);
    }
    echo "[✓] Government Services seeded.\n";
    
    // 8. Sample Order & Order Items
    $stmt = $db->prepare("INSERT INTO orders (id, order_no, customer_id, total_amount, shipping_address, municipality, district, payment_method, payment_status, status) VALUES (1, 'ORD-2026-000501', 5, 1300.00, 'House 12, Baneshwor', 'Kathmandu Metro', 'Kathmandu', 'Cash on Delivery', 'Paid', 'Delivered') ON DUPLICATE KEY UPDATE status='Delivered'");
    $stmt->execute();
    
    $stmt = $db->prepare("INSERT INTO order_items (id, order_id, product_id, vendor_id, quantity, price, subtotal) VALUES (1, 1, 1, 1, 1, 850.00, 850.00), (2, 1, 2, 1, 1, 450.00, 450.00) ON DUPLICATE KEY UPDATE subtotal=VALUES(subtotal)");
    $stmt->execute();

    $stmt = $db->prepare("INSERT INTO payments (id, order_id, transaction_id, payment_method, amount, status) VALUES (1, 1, 'TXN-COD-998877', 'Cash on Delivery', 1300.00, 'Completed') ON DUPLICATE KEY UPDATE status='Completed'");
    $stmt->execute();
    
    // 9. Sample Delivered Product Review
    $stmt = $db->prepare("INSERT INTO reviews (id, product_id, user_id, order_id, rating, comment) VALUES (1, 1, 5, 1, 5, 'The Mustang honey is 100% pure and delicious! Fast delivery by the verified local vendor.') ON DUPLICATE KEY UPDATE rating=5");
    $stmt->execute();
    echo "[✓] Sample Order, Payment & Review seeded.\n";
    
    // 10. Sample Complaint
    $stmt = $db->prepare("INSERT INTO complaints (id, complaint_no, user_id, category, related_type, related_id, priority, description, location_address, latitude, longitude, department_id, assigned_officer_id, status) VALUES (1, 'CMP-2026-000801', 5, 'Delivery', 'Order', 1, 'Medium', 'Delay in package delivery confirmation.', 'Baneshwor Height', 27.6915, 85.3420, 3, 2, 'Assigned') ON DUPLICATE KEY UPDATE status='Assigned'");
    $stmt->execute();
    echo "[✓] Sample Complaint seeded.\n";
    
    // 11. Initial Notifications
    $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, link) VALUES 
        (3, 'Business License Approved!', 'Congratulations! Your business application VND-2026-000101 has been approved. License LIC-2026-000101 is now active.', 'vendor/license.php'),
        (5, 'Order Delivered Successfully', 'Your order ORD-2026-000501 has been delivered by Himalayan Organic Farms. Please rate your experience.', 'order-details.php?id=1'),
        (2, 'New Vendor Application Received', 'Application VND-2026-000102 from Annapurna Handicrafts requires document inspection.', 'officer/application-details.php?id=2')");
    $stmt->execute();
    echo "[✓] Initial Notifications seeded.\n";
    
    echo "\nDatabase seeding completed successfully!\n";
    
} catch (Exception $e) {
    die("Seeding Error: " . $e->getMessage() . "\n");
}
