# -*- coding: utf-8 -*-
"""
SmartGov Market Documentation - Appendices A to H & Verification Summary
"""

APPENDICES = {
    "appendix_a": {
        "title": "APPENDIX A: DATABASE SCHEMAS & TABLES SPECIFICATION",
        "content": """The SmartGov Market relational database ('smartgov_market') comprises 25 normalized tables structured with InnoDB engine and utf8mb4 encoding:

1. `roles`: Role definitions (id, name, display_name, created_at).
2. `departments`: Municipal inspection departments (id, department_name, code, description, is_active, created_at).
3. `users`: User authentication accounts (id, role_id, full_name, email, phone, password_hash, address, municipality, district, profile_image, is_verified, is_active, last_login, created_at, updated_at).
4. `addresses`: Customer shipping addresses (id, user_id, address_line, municipality, district, latitude, longitude, is_default, created_at).
5. `vendors`: Registered businesses (id, user_id, business_name, business_type, pan_vat, registration_no, address, municipality, district, latitude, longitude, status, created_at, updated_at).
6. `vendor_applications`: Application workflow tracking (id, application_no, vendor_id, department_id, assigned_officer_id, status, officer_remarks, submitted_at, reviewed_at, approved_at).
7. `vendor_documents`: Uploaded compliance files (id, application_id, document_type, file_name, file_path, file_size, verification_status, officer_remarks, uploaded_at).
8. `licenses`: Digital business licenses (id, license_no, vendor_id, application_id, issuing_authority, issue_date, expiry_date, status, renewal_status, renewal_requested_at, qr_code_path, created_at).
9. `government_services`: Online municipal services (id, department_id, service_name, description, requirements, processing_time, fee, status, created_at).
10. `service_applications`: Citizen service requests (id, application_no, service_id, user_id, assigned_officer_id, status, remarks, document_path, submitted_at, updated_at).
11. `service_application_documents`: Citizen service file attachments (id, application_id, document_type, file_name, file_path, file_size, uploaded_at).
12. `categories`: Product categories (id, name, slug, description, image_path, is_active, created_at).
13. `products`: Vendor product catalog (id, vendor_id, category_id, name, slug, description, price, stock_quantity, image_path, status, created_at, updated_at).
14. `product_images`: Additional product images (id, product_id, image_path, is_primary).
15. `cart`: Active customer shopping carts (id, user_id, created_at, updated_at).
16. `cart_items`: Cart itemized records (id, cart_id, product_id, quantity, price).
17. `orders`: Customer orders (id, order_no, customer_id, customer_name, customer_phone, total_amount, shipping_address, municipality, district, payment_method, payment_status, status, created_at, updated_at).
18. `order_items`: Order line items (id, order_id, product_id, vendor_id, quantity, price, subtotal).
19. `payments`: Payment transactions (id, order_id, transaction_id, payment_method, amount, status, payment_date).
20. `complaints`: Public grievances (id, complaint_no, user_id, category, related_type, related_id, priority, description, location_address, latitude, longitude, department_id, assigned_officer_id, status, resolution, created_at, updated_at).
21. `complaint_attachments`: Complaint evidentiary files (id, complaint_id, file_path, file_name).
22. `reviews`: Verified product reviews (id, product_id, user_id, order_id, rating, comment, created_at).
23. `notifications`: User dashboard notifications (id, user_id, title, message, link, is_read, created_at).
24. `audit_logs`: Security audit logs (id, user_id, action, entity, entity_id, description, ip_address, created_at).
25. `password_resets`: Password recovery tokens (id, user_id, token, expires_at, used, created_at)."""
    },
    "appendix_b": {
        "title": "APPENDIX B: KEY IMPLEMENTATION SOURCE CODE LISTINGS",
        "content": """Selected mission-critical source code excerpts illustrating architectural patterns:

Excerpt B.1: Multi-Port Resilient Database Connection (config/database.php)
-----------------------------------------------------------------------------
function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $portsToTry = [DB_PORT, '3306'];
        $connected = false;
        $lastException = null;
        foreach (array_unique($portsToTry) as $port) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 3,
                ];
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                $connected = true;
                break;
            } catch (PDOException $e) {
                $lastException = $e;
            }
        }
        if (!$connected) {
            die("Database Connection Failed: Unable to connect to SmartGov Market database.");
        }
        ensureSchemaUpgrades($pdo);
    }
    return $pdo;
}

Excerpt B.2: Atomic Order Placement Transaction (api/place_order.php)
-----------------------------------------------------------------------------
$db->beginTransaction();
try {
    $order_no = generateCode('ORD', 'orders', 'order_no');
    $isCod = ($payment_method === 'Cash on Delivery');
    $payStatus = 'Pending';
    $ordStatus = $isCod ? 'Processing' : 'Pending Payment';

    $oInsert = $db->prepare("INSERT INTO orders (order_no, customer_id, customer_name, customer_phone, total_amount, shipping_address, municipality, district, payment_method, payment_status, status, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $oInsert->execute([$order_no, $user_id, $customer_name, $customer_phone, $total_amount, $shipping_address, $municipality, $district, $payment_method, $payStatus, $ordStatus]);
    $order_id = $db->lastInsertId();

    $oiInsert = $db->prepare("INSERT INTO order_items (order_id, product_id, vendor_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $stockUpdate = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");

    foreach ($cartItems as $item) {
        $subtotal = $item['db_price'] * $item['quantity'];
        $oiInsert->execute([$order_id, $item['product_id'], $item['vendor_id'], $item['quantity'], $item['db_price'], $subtotal]);
        $stockUpdate->execute([$item['quantity'], $item['product_id']]);
    }

    if ($isCod) {
        processOrderPayment($order_id, $payment_method, $total_amount);
    }

    $clearCart = $db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $clearCart->execute([$cart_id]);

    $db->commit();
    logAudit('Order Placed', 'Order', $order_no, "Customer ID {$user_id} placed order {$order_no}");
} catch (Exception $e) {
    $db->rollBack();
    throw $e;
}

Excerpt B.3: Digital License Issuance & QR Generation (includes/license_generator.php)
-----------------------------------------------------------------------------
function issueDigitalLicense($vendor_id, $application_id = null, $issuing_authority = 'Department of Commerce & Industry') {
    $db = getDBConnection();
    $license_no = generateCode('LIC', 'licenses', 'license_no');
    $issue_date = date('Y-m-d');
    $expiry_date = date('Y-m-d', strtotime('+1 year'));
    $qr_code_url = BASE_URL . "verify.php?license_no=" . urlencode($license_no);

    $insertStmt = $db->prepare("INSERT INTO licenses (license_no, vendor_id, application_id, issuing_authority, issue_date, expiry_date, status, qr_code_path, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, 'VALID', ?, NOW())");
    $insertStmt->execute([$license_no, $vendor_id, $application_id, $issuing_authority, $issue_date, $expiry_date, $qr_code_url]);
    $license_id = $db->lastInsertId();

    $updateVendor = $db->prepare("UPDATE vendors SET status = 'Verified' WHERE id = ?");
    $updateVendor->execute([$vendor_id]);

    return [
        'id' => $license_id,
        'license_no' => $license_no,
        'vendor_id' => $vendor_id,
        'qr_code_url' => $qr_code_url
    ];
}"""
    },
    "appendix_c": {
        "title": "APPENDIX C: LOCAL INSTALLATION & SETUP GUIDE",
        "content": """Step-by-step instructions for running SmartGov Market locally:

Prerequisites:
1. XAMPP (Apache 2.4 + MariaDB/MySQL 10.4+ and PHP 8.0+)
2. Git or archive extract utility.
3. Modern Web Browser (Chrome, Firefox, or Edge).

Installation Steps:
Step 1: Place Project in XAMPP Document Root
Copy or clone the repository folder into your XAMPP htdocs directory:
  Path: C:\\xampp\\htdocs\\SmartGov-Market

Step 2: Start Apache and MySQL Services
Open the XAMPP Control Panel. Start 'Apache' and 'MySQL' modules. Ensure MySQL is running on either port 3306 or 3307.

Step 3: Initialize Database & Seed Data
Open a command terminal (PowerShell or Command Prompt) and run the automated database setup script:
  cd C:\\xampp\\htdocs\\SmartGov-Market
  & "C:\\xampp\\php\\php.exe" database/init_db.php
This script creates the `smartgov_market` database, executes `schema.sql`, applies migrations, and seeds test accounts, departments, categories, products, orders, and complaints.

Step 4: Launch and Access the Application
- Access via standard Apache URL:
  http://localhost/SmartGov-Market/
- Or launch PHP built-in server:
  & "C:\\xampp\\php\\php.exe" -S localhost:8000
  Then open: http://localhost:8000/

Step 5: Pre-Configured Demo Credentials (Password: password123 for all)
- System Administrator: admin@smartgov.gov.np
- Municipal Government Officer: officer@smartgov.gov.np
- Approved Local Vendor: vendor@localcrafts.np
- Pending Vendor Application: pending.vendor@freshfarm.np
- Citizen / Customer: customer@gmail.com
- Public QR Verification: http://localhost:8000/verify.php?license_no=LIC-2026-000101"""
    },
    "appendix_d": {
        "title": "APPENDIX D: CITIZEN / CUSTOMER USER MANUAL",
        "content": """Step-by-step user guide for Citizens and Consumers:

1. Account Registration:
   - Click 'Sign In / Register' on the top navigation bar.
   - Click 'Create New Citizen Account'.
   - Fill in full name, contact email, mobile phone, and delivery address. Click 'Register'.
2. Browsing and Purchasing Verified Goods:
   - Click 'Marketplace' in the navigation bar.
   - Browse products by category or use the search bar to locate items.
   - Click on any product to view details, vendor operating coordinates, and verified customer reviews.
   - Click 'Add to Cart' to add desired items.
3. Checkout & Payment:
   - Open 'Cart' and click 'Proceed to Checkout'.
   - Confirm or update your delivery address (municipality, district, street).
   - Select payment method: 'Cash on Delivery' or digital wallet ('eSewa', 'Khalti', 'ConnectIPS').
   - Click 'Place Order'. If a digital wallet was selected, you will be redirected to the demo payment portal. Enter any demo mobile number (e.g. 9841000000) and confirm payment.
4. Tracking Orders:
   - Navigate to 'My Account' -> 'Order History'.
   - Click on any order to view delivery status and transaction receipts.
5. Applying for Municipal Services:
   - Click 'Services' in the navigation bar.
   - Review service requirements and click 'Apply Now'.
   - Fill in details, attach required documents, and submit. Track progress under 'My Applications'.
6. Lodging Public Grievances:
   - Navigate to 'My Account' -> 'Complaints'.
   - Click 'Lodge New Complaint'. Select category, priority, and describe the issue.
   - Click the interactive map to pin the incident location and attach photo evidence.
   - Submit the grievance and monitor investigation updates from municipal officers."""
    },
    "appendix_e": {
        "title": "APPENDIX E: SYSTEM ADMINISTRATOR MANUAL",
        "content": """Operational guide for System Administrators:

1. Accessing Administration Console:
   - Login using administrator credentials (admin@smartgov.gov.np).
   - Navigate to 'admin/dashboard.php'.
2. Real-Time Analytics & Monitoring:
   - Inspect live KPI counters: Total Users, Approved vs. Pending Vendors, Marketplace Sales, and Public Complaints.
   - Analyze dynamic Chart.js visualizations for vendor distributions and product categories.
3. Managing Users and Roles:
   - Navigate to 'admin/users.php'. View registered citizens, vendors, and officers. Activate or deactivate accounts.
4. Moderating Vendors & Digital Licenses:
   - Open 'admin/vendors.php' or 'admin/licenses.php'. Review vendor statuses. Suspend or revoke operating licenses if violations occur.
5. Product Catalog Moderation:
   - Open 'admin/products.php'. Toggle visibility of individual merchant products or disable prohibited items.
6. Inspecting Security Audit Logs:
   - Open 'admin/audit-logs.php' to audit timestamped actions, affected database entities, and client IP addresses.
7. Generating CSV Reports:
   - Navigate to 'admin/reports.php'. Click 'Export Vendors CSV', 'Export Orders CSV', or 'Export Complaints CSV' to stream structured data files."""
    },
    "appendix_f": {
        "title": "APPENDIX F: VENDOR PORTAL MANUAL",
        "content": """Operational guide for Local Merchants and Vendors:

1. Vendor Registration & Application:
   - Register a vendor account via 'vendor/register.php'.
   - Complete the multi-step onboarding form in 'vendor/application.php': input enterprise name, trade category, PAN/VAT number, and pin your physical workshop on the Leaflet GIS map.
   - Upload clear scanned copies of your PAN/VAT certificate, citizenship card, and municipal permit (PDF or JPG under 5MB).
   - Submit the application and await municipal officer review.
2. Viewing and Printing Your Digital Business License:
   - Once approved, access 'vendor/license.php'.
   - View your official digital license (LIC-YYYY-XXXXXX), valid dates, and live QR code.
   - Click 'Print Official License' to generate a physical certificate for shop display.
3. Adding and Managing Products:
   - Navigate to 'vendor/products.php' and click 'Add New Product'.
   - Fill in product title, category, retail price (NPR), stock quantity, and description.
   - Upload a product photo and click 'Publish Product'.
4. Order Fulfillment:
   - When a customer orders your product, you receive an in-app alert.
   - Navigate to 'vendor/orders.php'. Review the delivery address and items.
   - As you package and deliver items, update fulfillment status to 'Shipped' and 'Delivered'.
5. Requesting License Renewal:
   - When your annual license nears expiration, click 'Request License Renewal' in 'vendor/license.php' to trigger officer review."""
    },
    "appendix_g": {
        "title": "APPENDIX G: COMPREHENSIVE TEST CASE RECORDS",
        "content": """Detailed test execution logs covering all 22 functional test scenarios are documented in Chapter 7, Section 7.7. All 22 test cases were executed against the local test database with 20 confirmed passes and 2 partial implementations (simulated payment gateway and internal in-app notifications)."""
    },
    "appendix_h": {
        "title": "APPENDIX H: USER INTERFACE LAYOUT REFERENCE",
        "content": """Complete visual specifications and descriptions for all 22 primary application interfaces are documented in Chapter 6, Figures 6.1 through 6.22."""
    }
}

VERIFICATION_SUMMARY = {
    "modules_documented": 14,
    "database_tables": 25,
    "user_roles": 4,
    "diagrams_created": 8,
    "test_cases": 22,
    "features_confirmed": [
        "User Registration & BCRYPT Authentication",
        "Session Fixation & 7200s Inactivity Timeout",
        "Multi-Step Vendor Onboarding & Legal Document Uploads",
        "Officer Inspection Queue & Document Verification Audit",
        "Automated Digital License Issuance (LIC-YYYY-XXXXXX)",
        "Public QR Code License Verification (verify.php)",
        "Verified Merchant Marketplace Product Catalog & Search",
        "Vendor Product CRUD, Pricing & Stock Inventory Management",
        "Interactive Customer Shopping Cart & AJAX Updates",
        "Atomic Multi-Table Checkout with Stock Reservation",
        "Cash on Delivery (COD) Order Fulfillment Lifecycle",
        "Simulated Digital Gateway Integration (eSewa/Khalti/ConnectIPS)",
        "Customer Order Tracking Timeline & Payment Ledger",
        "Verified Purchaser Product Review Restriction (Delivered check)",
        "Municipal Digital Services Catalog & Citizen Applications",
        "Citizen Grievance Redressal with Photo Evidence & Department Dispatch",
        "Interactive Leaflet.js / OpenStreetMap GIS Geocoding",
        "Administrator Real-Time Metric Counters & Chart.js Visualizations",
        "Comprehensive Immutable Security Audit Logging (audit_logs)",
        "One-Click CSV Report Generation for Vendors, Orders, Complaints",
        "Dynamic Client-Side Bilingual Localization (English & Nepali)",
        "Resilient Multi-Port Database Connection (3307/3306) & Auto Schema Upgrades"
    ],
    "features_partially_implemented": [
        "Payment Gateway Integration: Implemented as an academic simulated gateway (demo-payment.php) capturing demo transactions and atomic order confirmations, but does not debit real bank accounts.",
        "External Notification Dispatch: In-app dashboard notifications are fully functional; external SMS and SMTP email dispatches require carrier API keys."
    ],
    "features_future_enhancements": [
        "Production Payment Gateway Webhooks (Live eSewa, Khalti, Fonepay, Card Gateway)",
        "Automated AI / OCR Document Verification for PAN and National ID cards",
        "Cross-Platform Mobile Applications (Flutter / React Native)",
        "Municipal Inspection GIS Route Optimization",
        "Blockchain Distributed Ledger Anchoring for License Audits",
        "Progressive Web App (PWA) Offline Catalog & License Caching"
    ],
    "important_issues_discovered": [
        "MariaDB Service Port Collisions: In local XAMPP setups, port 3307 is frequently assigned due to existing MySQL installations. Handled gracefully via multi-port fallback in config/database.php.",
        "Schema Migrations: The base schema was augmented dynamically with password_resets and service_application_documents tables via ensureSchemaUpgrades() in config/database.php."
    ]
}
