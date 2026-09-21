# -*- coding: utf-8 -*-
"""
SmartGov Market Documentation - Chapters 4 & 5
System Design and System Implementation
"""

CHAPTER_4 = {
    "title": "CHAPTER 4: SYSTEM DESIGN",
    "sections": [
        {
            "num": "4.1",
            "title": "System Architecture",
            "content": """SmartGov Market employs a 3-Tier Layered Web Architecture optimized for security, performance, and procedural-modular clarity in PHP. The three tiers are organized as follows:

1. Presentation Tier (Client Layer): Executed within the client's web browser. It renders HTML5 semantic markup, Bootstrap 5 responsive styling, custom glassmorphic CSS, Font Awesome 6 icons, interactive Leaflet.js maps, dynamic Chart.js canvases, and client-side JavaScript for bilingual localization (i18n.js) and DOM manipulation.
2. Application / Business Logic Tier (Server Layer): Executed by the Apache HTTP server running PHP 8.2+. This tier contains core business logic, middleware routing, session validation, Role-Based Access Control (RBAC), cryptographic token generators, file upload processors, atomic transaction controllers, and notification dispatchers.
3. Data Tier (Database Layer): Managed by the MariaDB / MySQL 8.0+ relational database engine. It enforces referential integrity through foreign key constraints, stores normalized tabular data across 25 schema tables, executes atomic transactions (ACID compliance), and maintains system audit logs."""
        },
        {
            "num": "4.2",
            "title": "Overall System Architecture Diagram",
            "content": """The overall architectural interaction between platform users, presentation interfaces, application controllers, and database entities is structured as follows:

+-----------------------------------------------------------------------------------+
|                             CLIENT / PRESENTATION LAYER                           |
|  [Citizen / Customer]      [Local Vendor]      [Gov Officer]      [Administrator] |
|           |                       |                  |                   |        |
|           +-----------------------+------------------+-------------------+        |
|                                   |                                               |
|       Bootstrap 5 UI | Glassmorphism CSS | Leaflet GIS | Chart.js | i18n.js       |
+-----------------------------------|-----------------------------------------------+
                                    | HTTP / HTTPS Requests (GET / POST / AJAX)
+-----------------------------------|-----------------------------------------------+
|                       APPLICATION & BUSINESS LOGIC LAYER                          |
|  [Authentication & RBAC Middleware: auth.php / security.php (BCRYPT / CSRF)]     |
|                                                                                   |
|  +---------------------------+  +-------------------------+  +-----------------+  |
|  |   E-GOVERNANCE MODULES    |  |   E-COMMERCE MODULES    |  | ADMINISTRATION  |  |
|  | - Vendor Reg & KYC Docs   |  | - Product Catalog & CRUD|  | - User Mgmt     |  |
|  | - Officer Document Review |  | - Cart & Atomic Checkout|  | - Vendor Status |  |
|  | - License Issuance & QR   |  | - Order Processing (COD)|  | - Product Review|  |
|  | - Public Verification API |  | - Simulated Payment GW  |  | - Audit Trail   |  |
|  | - Municipal Services Dir  |  | - Order Tracking System |  | - CSV Exports   |  |
|  | - Grievance Redressal GIS |  | - Verified Reviews      |  | - Analytics     |  |
|  +---------------------------+  +-------------------------+  +-----------------+  |
|                                                                                   |
|  [Helper Engines: license_generator.php | payment.php | notifications.php]       |
+-----------------------------------|-----------------------------------------------+
                                    | PDO Prepared Statements (Port 3307 / 3306)
+-----------------------------------|-----------------------------------------------+
|                           DATA MANAGEMENT LAYER (MariaDB)                         |
|  Normalized Relational Database (`smartgov_market`) - 25 Tables                   |
|  - Users, Roles, Departments, Addresses, Vendors, Vendor Applications, Documents  |
|  - Licenses, Gov Services, Service Applications, Categories, Products, Images     |
|  - Carts, Cart Items, Orders, Order Items, Payments, Complaints, Reviews, Audits |
+-----------------------------------------------------------------------------------+"""
        },
        {
            "num": "4.3",
            "title": "User Roles and Permissions Matrix",
            "content": """The platform strictly enforces Role-Based Access Control (RBAC) across four authenticated user roles and public unauthenticated visitors. The permissions matrix below outlines access rights across major functional modules:"""
        },
        {
            "num": "4.4",
            "title": "Use Case Diagram",
            "content": """The functional capabilities exposed to the respective system actors are illustrated in the Use Case model:

                             SMARTGOV MARKET SYSTEM
+----------------------------------------------------------------------------------+
|                                                                                  |
|   (Browse Products & Search) <------------------ [Citizen / Customer]            |
|   (Manage Shopping Cart) <---------------------- [Citizen / Customer]            |
|   (Checkout & Select Payment) <----------------- [Citizen / Customer]            |
|   (Track Order Status) <------------------------ [Citizen / Customer]            |
|   (Post Verified Review) <---------------------- [Citizen / Customer]            |
|   (Apply for Municipal Service) <--------------- [Citizen / Customer]            |
|   (Lodge Complaint with GIS Pin) <-------------- [Citizen / Customer]            |
|                                                                                  |
|   (Register Vendor Profile) <------------------- [Local Vendor]                  |
|   (Upload PAN/VAT & Permits) <------------------ [Local Vendor]                  |
|   (View & Print Digital License) <-------------- [Local Vendor]                  |
|   (Request License Renewal) <------------------- [Local Vendor]                  |
|   (Manage Products & Stock) <------------------- [Local Vendor]                  |
|   (Fulfill Customer Orders) <------------------- [Local Vendor]                  |
|                                                                                  |
|   (Review Vendor Applications) <---------------- [Government Officer]            |
|   (Inspect & Verify Documents) <---------------- [Government Officer]            |
|   (Approve & Issue Digital License) <----------- [Government Officer]            |
|   (Investigate Public Grievances) <------------- [Government Officer]            |
|   (Process Municipal Applications) <------------ [Government Officer]            |
|                                                                                  |
|   (Manage All System Users) <------------------- [System Administrator]          |
|   (Moderate Vendors & Products) <--------------- [System Administrator]          |
|   (View Real-Time Chart Analytics) <------------ [System Administrator]          |
|   (Inspect Security Audit Logs) <--------------- [System Administrator]          |
|   (Export Analytical CSV Reports) <------------- [System Administrator]          |
|                                                                                  |
|   (Scan QR & Verify License Status) <----------- [Public / Unregistered Visitor] |
|                                                                                  |
+----------------------------------------------------------------------------------+"""
        },
        {
            "num": "4.5",
            "title": "Use Case Descriptions",
            "content": """Detailed use case specifications for six mission-critical system workflows are presented below:

Use Case UC-01: Vendor Onboarding and Document Submission
- Actor: Local Business Owner (Vendor)
- Pre-conditions: User is registered with basic account credentials.
- Description: The vendor accesses the registration/application portal, submits business details (trading name, business type, PAN/VAT number, physical address, and GIS map coordinates), and uploads required compliance documents (PAN certificate, citizenship copy, municipal trade permit).
- Main Flow:
  1. Vendor navigates to 'vendor/application.php'.
  2. System presents multi-part form and interactive Leaflet map.
  3. Vendor selects physical location on map; system populates latitude and longitude.
  4. Vendor selects compliance files and clicks 'Submit Application'.
  5. System validates file sizes (<5MB) and MIME types (PDF, JPG, PNG).
  6. System saves files to 'uploads/documents/' using randomized hex filenames.
  7. System records application entry (status 'Submitted') and links uploaded documents.
  8. System redirects vendor to application status tracker with confirmation flash message.
- Post-conditions: Application is placed in the Officer Review Queue.

Use Case UC-02: Officer Review and Digital License Issuance
- Actor: Municipal Government Officer
- Pre-conditions: Officer is authenticated with 'officer' or 'admin' role.
- Description: Officer reviews pending vendor applications, inspects attached legal certificates, verifies individual documents, and issues a cryptographically authenticated Digital Business License.
- Main Flow:
  1. Officer navigates to 'officer/vendor-applications.php'.
  2. Officer filters by status 'Submitted' and opens specific application.
  3. System renders application details, vendor metadata, GIS coordinates, and document viewer.
  4. Officer inspects each document; clicks 'Verify Document' (or 'Reject Document' with remarks).
  5. If all documents are verified, Officer clicks 'Approve & Issue License'.
  6. System generates unique serial 'LIC-YYYY-XXXXXX', creates QR code verification link, sets issue and expiration dates (1 year), and marks vendor as 'Verified'.
  7. System dispatches real-time notification to the vendor and logs the action in audit_logs.
- Post-conditions: Digital license is active; vendor is unlocked to publish marketplace products.

Use Case UC-03: Product Purchase and Atomic Checkout
- Actor: Citizen / Customer
- Pre-conditions: Customer is authenticated; items from verified vendors are present in cart.
- Description: Customer reviews cart, inputs shipping address, selects payment method, and places order.
- Main Flow:
  1. Customer navigates to checkout ('checkout.php').
  2. System recalculates item totals, verifies vendor active status, and checks live stock levels.
  3. Customer submits shipping address and chooses payment method (COD or Digital Gateway).
  4. Server initiates an atomic database transaction (beginTransaction).
  5. System generates order number 'ORD-YYYY-XXXXXX' and inserts record into 'orders'.
  6. System transfers cart items into 'order_items' and decrements product inventory.
  7. If COD is selected, system records payment record and sets status to 'Processing'.
  8. System empties customer cart, commits the transaction, sends notifications, and logs audit.
  9. If a digital gateway (eSewa/Khalti/ConnectIPS) was chosen, customer is redirected to 'demo-payment.php'.
- Post-conditions: Order is confirmed; stock is reserved; vendor is alerted for fulfillment.

Use Case UC-04: Public QR Code License Verification
- Actor: Any Citizen, Consumer, or Municipal Inspector (No login required)
- Pre-conditions: User possesses a mobile smartphone with camera or opens direct URL.
- Description: User scans the QR code embedded on a vendor's digital license certificate or inputs the license number directly into 'verify.php'.
- Main Flow:
  1. User accesses 'verify.php?license_no=LIC-YYYY-XXXXXX'.
  2. System queries 'licenses' table joined with 'vendors' and 'users'.
  3. System evaluates current timestamp against license 'expiry_date'.
  4. If expired, system dynamically updates license status to 'EXPIRED'.
  5. If valid, system displays green verification badge, business name, owner name, business type, registered address, issue date, and validity expiry.
  6. If license number does not exist, system renders red 'INVALID LICENSE' alert.
- Post-conditions: Official licensing validity is confirmed transparently.

Use Case UC-05: Public Grievance / Complaint Lodging
- Actor: Citizen / Customer
- Pre-conditions: User is authenticated.
- Description: Citizen lodges a formal complaint regarding vendor malpractice, defective products, or civic issues with photographic evidence and GIS coordinates.
- Main Flow:
  1. Citizen opens 'complaints.php' and clicks 'Lodge New Complaint'.
  2. Citizen selects category, priority level, enters description, and specifies address.
  3. Citizen interacts with map to pinpoint incident location (populating lat/lng).
  4. Citizen attaches image/PDF evidence file and submits.
  5. System saves complaint record with unique tracking ID 'CMP-YYYY-XXXXXX'.
  6. System saves attachment into 'uploads/complaints/' and assigns complaint to relevant department.
  7. System logs audit and alerts municipal officers.
- Post-conditions: Complaint appears on officer GIS map and grievance queue.

Use Case UC-06: Verified Purchaser Product Review
- Actor: Customer
- Pre-conditions: Customer must have purchased the product and order status must be 'Delivered'.
- Description: Customer submits star rating (1-5) and written feedback for an authentic purchased item.
- Main Flow:
  1. Customer navigates to product details or order history.
  2. System executes verification query checking 'orders' and 'order_items' for customer ID, product ID, and status 'Delivered'.
  3. If eligible, system renders the review submission form.
  4. Customer enters rating (1-5) and qualitative comment; clicks 'Submit Review'.
  5. System validates rating bounds and persists record into 'reviews'.
  6. System updates product average rating and notifies the vendor.
- Post-conditions: Authentic verified review is displayed on the product page."""
        },
        {
            "num": "4.6",
            "title": "Data Flow Diagrams (DFD)",
            "content": """Data Flow Diagrams illustrate the functional movement of information through the system.

4.6.1 Context-Level DFD (Level 0):
Shows the overall system boundary and interactions with external entities:

                        [Citizen / Customer]
                            |         ^
   Reg Data, Orders,        |         | Catalog Data, Order Status,
   Complaints, Payments     v         | Notifications, Receipts
                   +-----------------------------+
                   |                             |
                   |      SMARTGOV MARKET        |
                   |      INTEGRATED SYSTEM      |
                   |                             |
                   +-----------------------------+
                            ^         |
   Vendor Reg, Documents,   |         | Verification Status, Orders,
   Products, Stock Updates  |         v Digital License, Reviews
                        [Local Vendor]
                            |         ^
                            v         |
                   +-----------------------------+
                   |                             |
                   |      SMARTGOV MARKET        |
                   |                             |
                   +-----------------------------+
                            ^         |
   Inspection Remarks,      |         | Review Queue, Documents,
   Approvals, Resolutions   |         v Complaints Data, Analytics
                   [Municipal Gov Officer]
                            ^         |
                            v         |
                   [System Administrator] (Master Control, Audits, CSV Reports)

4.6.2 Level 1 DFD - E-Governance Subsystem:
1.0 Vendor Registration -> Writes to D1: Vendors & D2: Vendor Applications.
2.0 Document Processing -> Writes to D3: Vendor Documents (uploads/documents).
3.0 Officer Review & Verification -> Queries D2 & D3; Updates status; Writes to D4: Licenses (Generates LIC-YYYY-XXXXXX and QR Code).
4.0 Public Verification -> Queries D4: Licenses & D1: Vendors -> Displays status at verify.php.
5.0 Public Complaints -> Citizen inputs -> Writes to D5: Complaints & D6: Attachments -> Officer updates resolution.

4.6.3 Level 1 DFD - E-Commerce Subsystem:
1.0 Product Management -> Approved vendor creates catalog items -> Writes to D7: Products & D8: Categories.
2.0 Catalog Browsing & Cart -> Citizen selects items -> Writes to D9: Cart & D10: Cart Items.
3.0 Checkout & Ordering -> Customer submits delivery address & payment choice -> Transaction reads D7 (stock check), writes to D11: Orders & D12: Order Items, decrements D7 (stock).
4.0 Payment Handling -> Reads D11; Writes to D13: Payments (stores TXN-XXXXXXXX); Updates D11 to Paid/Processing.
5.0 Review Submission -> Customer submits review -> Validates against D11 (Delivered check) -> Writes to D14: Reviews."""
        },
        {
            "num": "4.7",
            "title": "Entity Relationship (ER) Diagram",
            "content": """The Entity-Relationship model models 25 normalized database tables in the SmartGov Market relational database. Key entity relationships and cardinalities are summarized below:

- roles (1) ----< (M) users: One role is assigned to many users.
- users (1) ----< (M) addresses: A user can maintain multiple shipping addresses.
- users (1) ---- (1) vendors: A user with role 'vendor' has exactly one business profile.
- vendors (1) ----< (M) vendor_applications: A vendor may submit initial and subsequent renewal applications.
- vendor_applications (1) ----< (M) vendor_documents: An application contains multiple compliance documents.
- vendors (1) ----< (M) licenses: A vendor is issued digital business licenses over time.
- departments (1) ----< (M) government_services: A department administers multiple civic services.
- government_services (1) ----< (M) service_applications: A service receives multiple citizen applications.
- users (1) ----< (M) service_applications: A citizen submits multiple service applications.
- categories (1) ----< (M) products: A category groups multiple products.
- vendors (1) ----< (M) products: A verified vendor lists multiple products.
- products (1) ----< (M) product_images: A product may possess multiple gallery photos.
- users (1) ---- (1) cart: A customer has one active shopping cart.
- cart (1) ----< (M) cart_items: A cart contains multiple itemized lines.
- products (1) ----< (M) cart_items: A product can exist in multiple customer carts.
- users (1) ----< (M) orders: A customer places multiple orders.
- orders (1) ----< (M) order_items: An order contains multiple line items.
- products (1) ----< (M) order_items: A product appears across multiple historical order items.
- vendors (1) ----< (M) order_items: Order items link to the respective selling vendor.
- orders (1) ----< (M) payments: An order has associated payment transaction records.
- users (1) ----< (M) complaints: A citizen lodges multiple complaints.
- departments (1) ----< (M) complaints: Complaints are dispatched to specific departments.
- complaints (1) ----< (M) complaint_attachments: A complaint includes multiple evidence files.
- products (1) ----< (M) reviews: A product accumulates multiple customer reviews.
- users (1) ----< (M) reviews: A user authors multiple reviews.
- orders (1) ----< (M) reviews: A review is anchored to a specific delivered order.
- users (1) ----< (M) notifications: A user receives multiple system notifications.
- users (1) ----< (M) audit_logs: User actions generate multiple immutable audit records.
- users (1) ----< (M) password_resets: A user may generate password recovery tokens."""
        },
        {
            "num": "4.8",
            "title": "Database Design & Data Dictionary",
            "content": """The SmartGov Market relational database consists of 25 normalized tables defined in InnoDB with utf8mb4_unicode_ci encoding. Complete schema structures and field specifications are detailed below:

Table 1: roles
- Purpose: Stores system authorization role definitions for RBAC middleware.
- Columns:
  * id: INT, Primary Key, Auto Increment. Unique role identifier.
  * name: VARCHAR(50), Unique, Not Null. Internal role slug ('admin', 'officer', 'vendor', 'customer').
  * display_name: VARCHAR(100), Not Null. Human-readable role title ('System Administrator').
  * created_at: TIMESTAMP, Default CURRENT_TIMESTAMP. Creation timestamp.

Table 2: departments
- Purpose: Catalogs municipal governance and inspection departments.
- Columns:
  * id: INT, Primary Key, Auto Increment. Department ID.
  * department_name: VARCHAR(150), Unique, Not Null. Name of municipal department.
  * code: VARCHAR(20), Unique, Not Null. Official departmental acronym ('DCI', 'DAFS', 'CPA').
  * description: TEXT, Nullable. Functional mandate and responsibilities.
  * is_active: TINYINT(1), Default 1. Operational status indicator.
  * created_at: TIMESTAMP, Default CURRENT_TIMESTAMP.

Table 3: users
- Purpose: Master user registry storing credentials and profile information.
- Columns:
  * id: INT, Primary Key, Auto Increment. User ID.
  * role_id: INT, Foreign Key referencing roles(id) ON DELETE CASCADE.
  * full_name: VARCHAR(150), Not Null. User's complete legal name.
  * email: VARCHAR(150), Unique, Not Null. Contact and login email address.
  * phone: VARCHAR(30), Not Null. Contact mobile number.
  * password_hash: VARCHAR(255), Not Null. BCRYPT-hashed password string.
  * address: TEXT, Nullable. Street and ward address.
  * municipality: VARCHAR(100), Nullable. Municipal authority.
  * district: VARCHAR(100), Nullable. Administrative district.
  * profile_image: VARCHAR(255), Default 'default-avatar.png'. Profile image path.
  * is_verified: TINYINT(1), Default 0. Account verification flag.
  * is_active: TINYINT(1), Default 1. Account active status.
  * last_login: DATETIME, Nullable. Timestamp of most recent authentication.
  * created_at, updated_at: TIMESTAMP.

Table 4: addresses
- Purpose: Stores primary and alternate delivery addresses for citizens.
- Columns:
  * id: INT, Primary Key, Auto Increment. Address record ID.
  * user_id: INT, Foreign Key referencing users(id) ON DELETE CASCADE.
  * address_line: TEXT, Not Null. Street, house number, and landmark.
  * municipality: VARCHAR(100), Not Null. Municipality.
  * district: VARCHAR(100), Not Null. District name.
  * latitude, longitude: DECIMAL(10,8) / DECIMAL(11,8), Nullable. Geolocation coordinates.
  * is_default: TINYINT(1), Default 0. Default shipping address flag.
  * created_at: TIMESTAMP, Default CURRENT_TIMESTAMP.

Table 5: vendors
- Purpose: Stores registered vendor business entities, trade categories, and licensing state.
- Columns:
  * id: INT, Primary Key, Auto Increment. Vendor ID.
  * user_id: INT, Unique, Foreign Key referencing users(id) ON DELETE CASCADE.
  * business_name: VARCHAR(200), Not Null. Registered enterprise trading name.
  * business_type: VARCHAR(100), Not Null. Enterprise category (e.g. Handicrafts, Agriculture).
  * pan_vat: VARCHAR(50), Nullable. Permanent Account Number / VAT tax registration.
  * registration_no: VARCHAR(80), Nullable. Official municipal registration number.
  * address: TEXT, Not Null. Physical store/workshop location.
  * municipality: VARCHAR(100), Not Null. Municipality.
  * district: VARCHAR(100), Not Null. District.
  * latitude, longitude: DECIMAL(10,8) / DECIMAL(11,8), Defaults (27.7172, 85.3240). Store GIS coordinates.
  * status: ENUM('Pending','Verified','Approved','Under Review','Correction Required','Rejected','Suspended'), Default 'Pending'.
  * created_at, updated_at: TIMESTAMP.

Table 6: vendor_applications
- Purpose: Manages the onboarding and inspection review lifecycle of vendor applications.
- Columns:
  * id: INT, Primary Key, Auto Increment. Application ID.
  * application_no: VARCHAR(50), Unique, Not Null. Formatted tracking code ('VND-YYYY-XXXXXX').
  * vendor_id: INT, Foreign Key referencing vendors(id) ON DELETE CASCADE.
  * department_id: INT, Nullable, Foreign Key referencing departments(id) ON DELETE SET NULL.
  * assigned_officer_id: INT, Nullable, Foreign Key referencing users(id) ON DELETE SET NULL.
  * status: ENUM('Draft','Submitted','Under Review','Correction Required','Approved','Rejected','Suspended'), Default 'Submitted'.
  * officer_remarks: TEXT, Nullable. Official review comments and instructions.
  * submitted_at: TIMESTAMP, Default CURRENT_TIMESTAMP.
  * reviewed_at, approved_at: DATETIME, Nullable.

Table 7: vendor_documents
- Purpose: Stores metadata and verification states for uploaded legal compliance documents.
- Columns:
  * id: INT, Primary Key, Auto Increment. Document ID.
  * application_id: INT, Foreign Key referencing vendor_applications(id) ON DELETE CASCADE.
  * document_type: VARCHAR(100), Not Null. Label ('PAN/VAT Certificate', 'Citizenship', 'Municipal Permit').
  * file_name: VARCHAR(255), Not Null. Original client filename.
  * file_path: VARCHAR(255), Not Null. Storage path ('uploads/documents/hash.ext').
  * file_size: INT, Default 0. File size in bytes.
  * verification_status: ENUM('Pending','Verified','Rejected','Resubmission Required'), Default 'Pending'.
  * officer_remarks: TEXT, Nullable. Document-specific officer audit remarks.
  * uploaded_at: TIMESTAMP, Default CURRENT_TIMESTAMP.

Table 8: licenses
- Purpose: Official Digital Business Licenses issued to verified vendors.
- Columns:
  * id: INT, Primary Key, Auto Increment. License record ID.
  * license_no: VARCHAR(50), Unique, Not Null. Standardized license code ('LIC-YYYY-XXXXXX').
  * vendor_id: INT, Foreign Key referencing vendors(id) ON DELETE CASCADE.
  * application_id: INT, Nullable, Foreign Key referencing vendor_applications(id) ON DELETE CASCADE.
  * issuing_authority: VARCHAR(150), Default 'SmartGov Market Licensing Board'.
  * issue_date, expiry_date: DATE, Not Null. Effective validity dates.
  * status: ENUM('VALID','EXPIRED','SUSPENDED','REVOKED'), Default 'VALID'.
  * renewal_status: ENUM('None','Requested','Approved','Rejected'), Default 'None'.
  * renewal_requested_at: DATETIME, Nullable.
  * qr_code_path: VARCHAR(255), Nullable. Verification URL encoded in QR.
  * created_at: TIMESTAMP, Default CURRENT_TIMESTAMP.

Table 9: government_services
- Purpose: Municipal digital service directory.
- Columns:
  * id: INT, Primary Key, Auto Increment. Service ID.
  * department_id: INT, Foreign Key referencing departments(id) ON DELETE CASCADE.
  * service_name: VARCHAR(200), Not Null. Official service title.
  * description, requirements: TEXT, Not Null. Service details and document checklist.
  * processing_time: VARCHAR(100), Not Null. Expected completion SLA (e.g. '2-3 Business Days').
  * fee: DECIMAL(10,2), Default 0.00. Statutory administrative fee.
  * status: TINYINT(1), Default 1. Active service flag.
  * created_at: TIMESTAMP, Default CURRENT_TIMESTAMP.

Table 10: service_applications
- Purpose: Citizen applications submitted for municipal services.
- Columns:
  * id: INT, Primary Key, Auto Increment. Application ID.
  * application_no: VARCHAR(50), Unique, Not Null. Tracking code ('SRV-YYYY-XXXXXX').
  * service_id: INT, Foreign Key referencing government_services(id) ON DELETE CASCADE.
  * user_id: INT, Foreign Key referencing users(id) ON DELETE CASCADE.
  * assigned_officer_id: INT, Nullable. Assigned handling officer.
  * status: ENUM('Draft','Submitted','Under Review','Processing','Correction Required','Approved','Rejected'), Default 'Submitted'.
  * remarks, document_path: TEXT / VARCHAR(255), Nullable. Officer notes and uploaded citizen file.
  * submitted_at, updated_at: TIMESTAMP.

Table 11: service_application_documents
- Purpose: Supporting document attachments for citizen service applications.
- Columns: id (PK), application_id (FK), document_type, file_name, file_path, file_size, uploaded_at.

Table 12: categories
- Purpose: Taxonomy for marketplace product classification.
- Columns: id (PK), name (VARCHAR 100, Unique), slug (VARCHAR 100, Unique), description (TEXT), image_path, is_active (TINYINT 1), created_at.

Table 13: products
- Purpose: Merchant catalog items listed on the marketplace.
- Columns:
  * id: INT, Primary Key, Auto Increment. Product ID.
  * vendor_id: INT, Foreign Key referencing vendors(id) ON DELETE CASCADE.
  * category_id: INT, Foreign Key referencing categories(id) ON DELETE CASCADE.
  * name: VARCHAR(200), Not Null. Product title.
  * slug: VARCHAR(200), Not Null. URL-friendly slug identifier.
  * description: TEXT, Not Null. Detailed product specifications.
  * price: DECIMAL(10,2), Not Null. Unit retail price in NPR.
  * stock_quantity: INT, Not Null, Default 0. Available inventory units.
  * image_path: VARCHAR(255), Default 'product-default.jpg'. Primary image.
  * status: ENUM('Active','Disabled','Inactive','Out of Stock','Pending Approval'), Default 'Active'.
  * created_at, updated_at: TIMESTAMP.

Table 14: product_images
- Purpose: Auxiliary product gallery images.
- Columns: id (PK), product_id (FK), image_path, is_primary (TINYINT 1).

Table 15: cart
- Purpose: Customer active shopping cart header.
- Columns: id (PK), user_id (INT, Unique, FK to users), created_at, updated_at.

Table 16: cart_items
- Purpose: Itemized lines inside active shopping carts.
- Columns: id (PK), cart_id (FK), product_id (FK), quantity (INT), price (DECIMAL 10,2).

Table 17: orders
- Purpose: Master customer orders.
- Columns:
  * id: INT, Primary Key, Auto Increment. Order ID.
  * order_no: VARCHAR(50), Unique, Not Null. Order identifier ('ORD-YYYY-XXXXXX').
  * customer_id: INT, Foreign Key referencing users(id) ON DELETE CASCADE.
  * customer_name, customer_phone: VARCHAR, Nullable. Contact snapshots at checkout.
  * total_amount: DECIMAL(10,2), Not Null. Grand total order value.
  * shipping_address, municipality, district: TEXT / VARCHAR. Delivery destination.
  * payment_method: VARCHAR(50), Default 'Cash on Delivery'.
  * payment_status: ENUM('Pending','Paid','Failed','Refunded'), Default 'Pending'.
  * status: ENUM('Pending','Pending Payment','Confirmed','Paid','Processing','Shipped','Delivered','Cancelled','Refund Requested','Refunded'), Default 'Processing'.
  * created_at, updated_at: TIMESTAMP.

Table 18: order_items
- Purpose: Individual product line items in completed orders.
- Columns: id (PK), order_id (FK), product_id (FK), vendor_id (FK), quantity (INT), price (DECIMAL 10,2), subtotal (DECIMAL 10,2).

Table 19: payments
- Purpose: Payment transaction records and gateway logs.
- Columns:
  * id: INT, Primary Key, Auto Increment. Payment record ID.
  * order_id: INT, Foreign Key referencing orders(id) ON DELETE CASCADE.
  * transaction_id: VARCHAR(100), Unique, Not Null. Transaction code ('TXN-XXXXXXXX' or 'DEMO-XXXXXXXX').
  * payment_method: VARCHAR(50), Not Null. Gateway identifier ('Cash on Delivery', 'eSewa', 'Khalti', 'ConnectIPS').
  * amount: DECIMAL(10,2), Not Null. Amount captured.
  * status: ENUM('Pending','Completed','Failed','Refunded'), Default 'Completed'.
  * payment_date: TIMESTAMP, Default CURRENT_TIMESTAMP.

Table 20: complaints
- Purpose: Public grievances and civic complaints.
- Columns:
  * id: INT, Primary Key, Auto Increment. Complaint ID.
  * complaint_no: VARCHAR(50), Unique, Not Null. Grievance tracking code ('CMP-YYYY-XXXXXX').
  * user_id: INT, Foreign Key referencing users(id) ON DELETE CASCADE.
  * category: ENUM('Vendor','Product','Order','Payment','Delivery','Government Service','License','Other'), Not Null.
  * priority: ENUM('Low','Medium','High','Urgent'), Default 'Medium'.
  * description, location_address: TEXT. Written statement and location.
  * latitude, longitude: DECIMAL(10,8) / DECIMAL(11,8). Incident GIS coordinates.
  * department_id: INT, Nullable, Foreign Key referencing departments(id) ON DELETE SET NULL.
  * assigned_officer_id: INT, Nullable, Foreign Key referencing users(id) ON DELETE SET NULL.
  * status: ENUM('Submitted','Under Review','Assigned','Under Investigation','In Progress','Resolved','Rejected','Closed'), Default 'Submitted'.
  * resolution: TEXT, Nullable. Official findings and corrective action.
  * created_at, updated_at: TIMESTAMP.

Table 21: complaint_attachments
- Purpose: Photographic or documentary evidence files for complaints.
- Columns: id (PK), complaint_id (FK), file_path, file_name.

Table 22: reviews
- Purpose: Verified purchaser product ratings and feedback.
- Columns: id (PK), product_id (FK), user_id (FK), order_id (FK), rating (INT CHECK 1-5), comment (TEXT), created_at.

Table 23: notifications
- Purpose: Real-time user dashboard notification messages.
- Columns: id (PK), user_id (FK), title, message, link, is_read (TINYINT 1, Default 0), created_at.

Table 24: audit_logs
- Purpose: Immutable security audit trail.
- Columns: id (PK), user_id (FK, Nullable), action, entity, entity_id, description, ip_address, created_at.

Table 25: password_resets
- Purpose: Time-sensitive cryptographic tokens for password recovery.
- Columns: id (PK), user_id (FK), token (VARCHAR 64, Unique), expires_at (DATETIME), used (TINYINT 1), created_at."""
        },
        {
            "num": "4.9",
            "title": "Class and Module Design",
            "content": """The SmartGov Market application architecture is organized into functional helper modules and controllers:

1. Database Connection Controller (config/database.php):
   - getDBConnection(): Singleton-pattern function returning a persistent PDO connection. Implements multi-port fallback logic (attempts port 3307, falls back to 3306) to ensure resilience across custom XAMPP configurations.
   - ensureSchemaUpgrades(): Dynamic schema migration runner that automatically verifies and provisions auxiliary tables (password_resets, service_application_documents) and synchronizes ENUM column expansions.

2. Authentication & Authorization Middleware (includes/auth.php):
   - enforceSessionTimeout(): Enforces 7200-second session inactivity limit.
   - isLoggedIn(), currentUser(), currentRole(), hasRole($roles): Contextual authentication helpers.
   - requireLogin(), requireRole($roles): Route protection interceptors redirecting unauthorized access attempts.
   - loginUser($user), logoutUser(): Secure session initializers and destroyers.

3. Security & Validation Engine (includes/security.php):
   - csrf_token(), csrf_field(), verifyCsrf(), requireCsrf(): Synchronizer token generator and validator.
   - storeUploadedFile(): Secure file handler validating MIME types via finfo, checking extensions against whitelists, and storing files under 16-byte random hex names.
   - restoreOrderStock(): Restores reserved inventory on cancelled orders.

4. Licensing & QR Generation Engine (includes/license_generator.php):
   - issueDigitalLicense(): Orchestrates digital license creation, generates sequential license serials, sets validity dates, and constructs QR verification URIs.
   - getVendorLicense(), verifyLicenseByNo(): License retrieval and real-time expiration validation.
   - requestLicenseRenewal(), approveLicenseRenewal(): License lifecycle management.

5. Payment Processing Engine (includes/payment.php):
   - processOrderPayment(): Atomic transaction handler for Cash on Delivery and payment gateway confirmations, creating payment rows and alerting customers.

6. Notifications Engine (includes/notifications.php):
   - sendNotification(): Inserts user notification alerts and handles unread counter increments."""
        },
        {
            "num": "4.10",
            "title": "Sequence Diagrams",
            "content": """Sequence diagrams model the chronological exchange of messages between actors and system components for vital workflows:

4.10.1 Citizen Registration Sequence:
Citizen -> Browser -> register.php -> auth.php -> Database (users)
1. Citizen fills registration form and clicks 'Register'.
2. Browser sends POST request with CSRF token to register.php.
3. register.php validates inputs (email uniqueness, password length).
4. register.php hashes password using password_hash(BCRYPT).
5. Database inserts new user record; returns user_id.
6. auth.php initializes session via loginUser(); logs audit in audit_logs.
7. System redirects citizen to index.php with welcome flash message.

4.10.2 Vendor Onboarding & Officer License Approval Sequence:
Vendor -> vendor/application.php -> Officer -> officer/application-details.php -> license_generator.php -> Database
1. Vendor submits application form and compliance files.
2. Server validates files via storeUploadedFile(), writes to vendor_applications and vendor_documents.
3. Application enters Officer queue with status 'Submitted'.
4. Officer logs in and navigates to application review screen.
5. Officer inspects documents via document viewer; marks files as 'Verified'.
6. Officer clicks 'Approve & Issue License'.
7. license_generator.php generates 'LIC-YYYY-XXXXXX', computes 1-year expiry, updates vendor status to 'Verified'.
8. Server sends notification to Vendor and logs audit trail.

4.10.3 Product Purchase & Atomic Order Sequence:
Customer -> checkout.php -> api/place_order.php -> Database (orders, order_items, products)
1. Customer clicks 'Place Order' on checkout form.
2. api/place_order.php validates CSRF token and cart contents.
3. Server opens database transaction (beginTransaction).
4. Server validates stock levels and recalculates totals.
5. Server inserts order into 'orders' ('ORD-YYYY-XXXXXX').
6. Server loops through items: inserts into 'order_items' and decrements 'products.stock_quantity'.
7. If COD: payment.php creates payment record; order status set to 'Processing'.
8. Server clears cart_items, commits transaction, and notifies vendors.
9. If digital gateway chosen: redirects customer to demo-payment.php."""
        },
        {
            "num": "4.11",
            "title": "Activity Diagrams",
            "content": """Activity diagrams illustrate procedural operational workflows:

4.11.1 Vendor Licensing Lifecycle Activity:
[Start] -> Vendor Registers -> Submits Trade Data & Documents -> [Status: Submitted] -> Officer Inspects Documents -> {All Documents Valid?}
  - If NO: Officer adds remarks -> [Status: Correction Required] -> Vendor Re-uploads Documents -> [Back to Officer Inspection]
  - If Rejected: Officer rejects application -> [Status: Rejected] -> [End]
  - If YES: Officer clicks Approve -> System generates Digital License (LIC-YYYY-XXXXXX) -> QR Code Path Created -> Vendor Status set to 'Verified' -> Vendor Permitted to Sell -> [End]

4.11.2 Order & Fulfillment Lifecycle Activity:
[Start] -> Customer Adds Items to Cart -> Navigates to Checkout -> Submits Address -> Selects Payment -> {Payment Method?}
  - If COD: System confirms Order -> [Status: Processing] -> Vendor Prepares Goods -> Vendor Marks as Shipped -> Customer Receives Goods -> Vendor Marks as Delivered -> Customer Submits Verified Review -> [End]
  - If Digital Gateway: Customer redirected to demo-payment.php -> Customer enters demo ID -> Demo Payment Processed -> [Status: Paid] -> Vendor Fulfills Order -> [Status: Delivered] -> [End]"""
        }
    ]
}

CHAPTER_5 = {
    "title": "CHAPTER 5: SYSTEM IMPLEMENTATION",
    "sections": [
        {
            "num": "5.1",
            "title": "Development Environment",
            "content": """The SmartGov Market platform was engineered and validated within a standardized modern web development environment:
- Operating System: Microsoft Windows 11 Professional (64-bit)
- Local Server Suite: XAMPP Version 8.2 (incorporating Apache 2.4.58 and MariaDB 10.4.32)
- Database Ports: MariaDB configured on custom service port 3307 with transparent fallback to standard port 3306.
- Integrated Development Environment (IDE): Visual Studio Code equipped with PHP Intelephense, SQLTools, and Git version control integration.
- Web Client Testing: Google Chrome 124, Mozilla Firefox 125, Microsoft Edge 124."""
        },
        {
            "num": "5.2",
            "title": "Technologies Used",
            "content": """The platform was constructed using a cohesive modern technology stack without extraneous bloat:
1. Backend: PHP 8.2+ utilizing object-oriented PDO extensions, BCRYPT password hashing, session hardening, and JSON serialization.
2. Relational Database: MariaDB / MySQL with InnoDB storage engine, foreign key cascade constraints, transactions, and utf8mb4 collation.
3. Frontend Framework: HTML5 semantic tags, Bootstrap 5.3 CSS grid and components, supplemented by custom glassmorphic CSS styling.
4. Typography & Iconography: Inter Google Font family and Font Awesome 6.5 Free Vector Icons.
5. Geospatial & Mapping: Leaflet.js (v1.9.4) paired with OpenStreetMap tile servers for interactive mapping, geocoding pin-drops, and store coordinates.
6. Data Visualization: Chart.js (v4.4.1) for dynamic database-driven admin analytics (pie, bar, and donut charts).
7. QR Code Engine: QRCode.js client-side generator with dynamic fallback to QRServer REST API.
8. Localization: Client-side internationalization dictionary (i18n.js) providing English and Nepali (नेपाली) language toggle."""
        },
        {
            "num": "5.3",
            "title": "Project Folder Structure",
            "content": """The repository is structured logically to enforce clean separation between configuration, libraries, role portals, uploaded media, and public web endpoints:

SmartGov-Market/
├── assets/
│   ├── css/          # Custom glassmorphic stylesheets & Bootstrap bundles
│   ├── images/       # Brand graphics, SVG placeholders, and system logos
│   └── js/           # i18n localization, Leaflet map helpers, Chart.js loaders
├── config/
│   ├── app.php       # Core application constants, sanitization, flash messages, audit helper
│   └── database.php  # Multi-port PDO connection singleton & auto-schema migration
├── database/
│   ├── schema.sql    # DDL schema definition for 23 base tables
│   ├── update_schema.sql # Incremental schema migration scripts
│   ├── seed.php      # Demo dataset populator for users, vendors, products, complaints
│   └── init_db.php   # Automated CLI database installation runner
├── includes/
│   ├── auth.php      # Session management and RBAC authorization middleware
│   ├── header.php    # Global HTML header, navigation, and flash message renderer
│   ├── footer.php    # Global HTML footer, JavaScript bundle, and copyright notice
│   ├── navbar.php    # Top navigation bar with bilingual language switcher
│   ├── sidebar.php   # Dynamic role-based dashboard navigation sidebars
│   ├── license_generator.php # Digital license creation and QR code encoder
│   ├── payment.php   # COD and simulated payment processing controller
│   ├── security.php  # CSRF tokens, secure file uploads, stock restoration
│   └── notifications.php # Dashboard notifications engine
├── uploads/
│   ├── documents/    # Vendor legal compliance documents (PAN, ID, permits)
│   ├── products/     # Product catalog images uploaded by merchants
│   ├── complaints/   # Photo and document attachments supporting complaints
│   └── profiles/     # User profile avatars
├── admin/            # Administrator management console (13 controller files)
│   ├── dashboard.php, users.php, vendors.php, products.php, categories.php,
│   ├── orders.php, complaints.php, licenses.php, departments.php, services.php,
│   └── reports.php, settings.php, audit-logs.php
├── officer/          # Government officer inspection portal (6 controller files)
│   ├── dashboard.php, vendor-applications.php, application-details.php,
│   └── complaints.php, licenses.php, reports.php
├── vendor/           # Merchant portal (10 controller files)
│   ├── dashboard.php, register.php, application.php, license.php,
│   ├── products.php, add-product.php, edit-product.php, orders.php, reviews.php, sales.php
├── api/              # Asynchronous backend endpoints
│   ├── cart.php      # Cart addition, quantity update, and item removal
│   └── place_order.php # Atomic order placement and transaction controller
├── index.php         # Marketplace landing page with featured verified products
├── login.php / register.php / logout.php / forgot-password.php / reset-password.php
├── products.php / product-details.php / cart.php / checkout.php / demo-payment.php
├── orders.php / order-details.php / transactions.php / reviews.php
├── complaints.php / complaint-details.php / map-view.php
├── government-services.php / service-details.php / applications.php
└── verify.php        # Public QR digital business license verification page"""
        },
        {
            "num": "5.4",
            "title": "Database Connection & Dynamic Resilience",
            "content": """The database layer is managed through config/database.php via the getDBConnection() function. Key technical implementation details include:
1. Multi-Port Fallback: In local environments, MySQL/MariaDB frequently runs on port 3307 due to port conflicts with existing MySQL installations on 3306. The connection logic loops through ports [3307, 3306], successfully binding to the active service port transparently.
2. PDO Configuration: Configured with PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION for robust error handling, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC for performant associative arrays, and PDO::ATTR_EMULATE_PREPARES => false to enforce real prepared statements on the database server.
3. Automated Schema Upgrades: The ensureSchemaUpgrades() function automatically checks for the existence of password_resets and service_application_documents tables upon initialization, verifies supplementary columns (pan_vat, registration_no, customer_name), and updates ENUM column constraints safely."""
        },
        {
            "num": "5.5",
            "title": "Authentication, Session Management & RBAC",
            "content": """User authentication and authorization are encapsulated within includes/auth.php:
- Password Security: Handled via PHP's native password_hash($password, PASSWORD_BCRYPT) and verified via password_verify(). Plaintext passwords are never stored or logged.
- Session Hardening: Upon successful credential verification, loginUser() executes session_regenerate_id(true) to mitigate session fixation attacks, records the user's last login timestamp, and initializes role session variables.
- Inactivity Timeout: enforceSessionTimeout() computes elapsed time since last_activity; if it exceeds 7,200 seconds (2 hours), the user is automatically logged out and redirected to login with an alert.
- Route Protection: requireRole($roles) intercepts unauthorized requests. If an authenticated vendor attempts to access admin/dashboard.php, they are intercepted and redirected to vendor/dashboard.php with an 'Unauthorized Access' flash warning."""
        },
        {
            "num": "5.6",
            "title": "User / Citizen Module Implementation",
            "content": """The Citizen subsystem powers civic and commercial interaction:
- Profile & Address Book: profile.php allows citizens to manage contact information, update passwords, and maintain primary delivery addresses.
- Digital Applications: applications.php displays historical municipal service requests submitted by the citizen, complete with downloadable submitted files, officer review remarks, and live approval badges.
- Orders & Transactions: orders.php and order-details.php provide a visual delivery progress timeline (Pending Payment -> Paid -> Processing -> Shipped -> Delivered). transactions.php displays all historical financial transactions with transaction IDs, payment methods, and timestamps.
- Grievance Redressal: complaints.php allows citizens to lodge complaints with category selection, priority, GIS map pinpointing, and photo evidence uploads."""
        },
        {
            "num": "5.7",
            "title": "Vendor Module Implementation",
            "content": """The Vendor subsystem governs commercial participation and compliance:
- Multi-Step Onboarding: vendor/application.php allows business owners to input enterprise information, trade sectors, and drag a marker on a Leaflet GIS map to pin their store coordinates.
- Compliance Document Upload: Implemented using storeUploadedFile(), validating file MIME types and storing files with randomized hex names under uploads/documents/.
- Digital License Certificate: vendor/license.php renders the official digital business license certificate, issue/expiry dates, issuing municipal authority, and live QR code. It features a one-click 'Print Official License' button with dedicated print CSS media queries.
- Catalog & Inventory Management: vendor/products.php, add-product.php, and edit-product.php provide complete CRUD capabilities over products, real-time stock updating, and image uploads.
- Order Fulfillment: vendor/orders.php alerts vendors when customer orders contain items from their inventory, enabling them to progress fulfillment states to 'Shipped' or 'Delivered'."""
        },
        {
            "num": "5.8",
            "title": "E-Commerce Marketplace Implementation",
            "content": """The marketplace subsystem bridges consumers and verified merchants:
- Verified-Only Product Catalog: products.php executes dynamic SQL joins filtering products by status = 'Active' and vendors status IN ('Approved', 'Verified'). Products from unapproved vendors are completely excluded from public browsing.
- Shopping Cart: cart.php and api/cart.php maintain customer shopping carts with live price lookups, stock checks, and asynchronous AJAX quantity adjustments.
- Atomic Checkout Transaction: api/place_order.php executes the entire checkout within a single database transaction:
  * Backend price validation: Recalculates total amount directly from product table prices, thwarting client-side price tampering.
  * Stock reservation: Decrements stock_quantity atomically.
  * Multi-table insertion: Inserts master order, line items, and payment rows.
  * Notification dispatch: Sends instant in-app alerts to both the buyer and all selling vendors.
- Verified Reviews: reviews.php enforces that product reviews can only be submitted by customers who have an existing order record for that product with status = 'Delivered'."""
        },
        {
            "num": "5.9",
            "title": "E-Governance Module Implementation",
            "content": """The e-governance subsystem modernizes municipal services and regulatory enforcement:
- Municipal Services Catalog: government-services.php and service-details.php showcase municipal services, processing times, fee schedules, and required documentation.
- Officer Inspection Queue: officer/vendor-applications.php presents inspection officers with pending applications, filtering options, and applicant summaries.
- Granular Document Viewer & Audit: officer/application-details.php renders applicant legal documents, allowing officers to individually verify or reject each document with custom remarks.
- Automated Digital License Generation: Upon officer approval, license_generator.php automatically issues an official license (LIC-YYYY-XXXXXX), computes an annual expiration date, marks the vendor as 'Verified', and encodes the public verification URL into a QR code.
- Public QR Verification Endpoint: verify.php provides a lightweight, public interface accessible by scanning the license QR code. It dynamically validates whether the license is VALID, EXPIRED, or SUSPENDED.
- GIS Public Grievance Resolution: officer/complaints.php allows officers to inspect lodged complaints, examine uploaded evidence photos, review incident GIS map coordinates, and record formal resolution findings."""
        },
        {
            "num": "5.10",
            "title": "Administrator Module Implementation",
            "content": """The Administrator console (admin/) provides centralized oversight across the entire platform:
- Real-Time Analytical Dashboard: admin/dashboard.php queries live database counts for total users, approved vs. pending vendors, total orders, and sales revenue. It dynamically initializes three Chart.js charts: Vendors by Status (Doughnut), Products by Category (Bar), and Grievances by Category (Doughnut).
- Merchant Moderation: admin/vendors.php enables administrators to inspect, approve, reject, or suspend vendor operating privileges.
- Product Oversight: admin/products.php allows administrators to toggle product visibility or disable non-compliant items.
- Security Audit Log Viewer: admin/audit-logs.php provides a searchable table of all recorded audit events (timestamp, action, entity, user ID, client IP).
- CSV Export Engine: admin/reports.php generates instant, downloadable CSV reports for vendors, marketplace sales orders, and civic complaints using PHP's native fputcsv() streaming."""
        },
        {
            "num": "5.11",
            "title": "Payment System Implementation (Demo Gateway)",
            "content": """The payment subsystem supports dual payment paradigms:
1. Native Cash on Delivery (COD): Automatically records payment status as 'Pending' and order status as 'Processing'. The customer pays upon physical goods delivery.
2. Simulated Digital Payment Gateway (eSewa / Khalti / ConnectIPS): Implemented via demo-payment.php and includes/payment.php. Designed strictly as an academic and demonstration gateway:
   - Captures order total and displays branded digital wallet interfaces (eSewa green, Khalti purple, ConnectIPS navy).
   - Prompts the user for a demo mobile/account number (e.g. 9841000000).
   - Generates a unique transaction identifier ('DEMO-XXXXXXXX' or 'TXN-XXXXXXXX').
   - Atomically transitions the order to payment_status = 'Paid' and status = 'Paid'.
   - Explicitly informs users that no real banking credentials or live financial charges are involved."""
        },
        {
            "num": "5.12",
            "title": "Security Implementation",
            "content": """SmartGov Market embeds defensive security controls across all architectural layers:
- SQL Injection Defense: 100% of database interactions utilize PDO prepared statements with bound parameters. Zero dynamic SQL string concatenation is permitted.
- Cross-Site Scripting (XSS) Prevention: All dynamic outputs rendered into HTML contexts pass through the sanitize() helper, which executes htmlspecialchars(..., ENT_QUOTES, 'UTF-8').
- Cross-Site Request Forgery (CSRF) Defense: Forms include hidden CSRF tokens generated via random_bytes(32). The requireCsrf() middleware validates tokens on all POST requests using constant-time comparison (hash_equals).
- Secure File Upload Handling: storeUploadedFile() enforces a 5MB maximum file size, inspects real file content MIME types using finfo(FILEINFO_MIME_TYPE), validates against strict extensions (pdf, jpg, jpeg, png), and stores files with randomized 16-byte hex filenames to prevent directory traversal and executable script execution.
- Password Security: User passwords are encrypted with BCRYPT using PHP's native password_hash().
- Session Protection: Enforces HttpOnly and SameSite=Lax cookie policies, session ID regeneration, and a 2-hour inactivity timeout."""
        },
        {
            "num": "5.13",
            "title": "UI/UX Design and Bilingual Localization",
            "content": """The user interface is designed around a modern glassmorphic theme:
- Visual Hierarchy: Clean card-based layouts with subtle drop-shadows (box-shadow: 0 4px 20px rgba(0,0,0,0.06)), rounded borders (border-radius: 12px), and a curated civic color palette (Navy Blue #1a2b4c, Government Accent Red #dc3545, Emerald Green #198754).
- Dynamic Animations: Micro-interactions and hover effects enhance user engagement without degrading browser performance.
- Bilingual Localization (i18n): Implemented via assets/js/i18n.js. Clicking the language toggle switch dynamically translates interface elements (navigation links, buttons, table headers) between English and Nepali (नेपाली) in the client DOM without requiring server reload."""
        }
    ]
}
