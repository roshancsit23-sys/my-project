# SmartGov Market - Integrated E-Governance and E-Commerce Platform

SmartGov Market is a full-stack, integrated **E-Governance and E-Commerce web application** designed to connect Citizens/Customers, Local Vendors, Government Officers, and System Administrators in a unified digital ecosystem.

The platform enables local businesses to apply for municipal approval, undergo government officer document verification, receive cryptographically traceable digital business licenses (`LIC-YYYY-XXXXXX`) with public QR code verification, list verified local products, process orders, and allow citizens to browse, purchase, lodge complaints on an interactive Leaflet GIS map, and track order fulfillment.

---

## Key Platform Modules & Features

### 🏛️ E-Governance Module
- **Vendor Registration & Application (`VND-YYYY-XXXXXX`):** Multi-step registration, GIS store location picker, and file uploads (PAN/VAT, ID Card, Municipal Permits).
- **Officer Review & Approval Workflow:** Inspection queue, uploaded document viewer, officer remarks, request corrections, approval, or rejection.
- **Digital Business License (`LIC-YYYY-XXXXXX`):** Automated license issuance, printable official certificate, embedded QR code.
- **Public License Verification (`verify.php`):** Public QR verification endpoint to validate license status (`VALID`, `EXPIRED`, `SUSPENDED`).
- **Government Services Directory:** Online services directory, requirements, processing time, and digital application submission (`SRV-YYYY-XXXXXX`).
- **Grievance & Public Complaint System (`CMP-YYYY-XXXXXX`):** Dual-scope complaints (Vendors / Gov Services), priority level, evidence attachment, officer assignment, and status updates.

### 🛒 E-Commerce Marketplace
- **Verified Product Catalog:** Category filtering, search keywords, sorting, stock indicators, and seller verification badges.
- **Product Management:** Vendor product CRUD, pricing, stock inventory management, and image upload.
- **Shopping Cart & Checkout:** Server-side price & stock validation, delivery address selection, Cash on Delivery (COD) & Simulated Payment Gateway.
- **Atomic Order Processing (`ORD-YYYY-XXXXXX`):** Database transactions, stock reduction, live customer delivery progress tracker, vendor fulfillment workflow.
- **Verified Purchase Product Reviews:** 1 to 5 star ratings and reviews restricted to verified purchasers of delivered orders.

### 🗺️ GIS & Visual Analytics
- **Leaflet + OpenStreetMap GIS:** Interactive map pins for verified vendor store locations and public complaint locations.
- **Real Database Dashboards:** Real-time metrics and dynamic Chart.js charts (Vendors by Status, Sales Revenue, Complaints by Category, Products per Category).
- **Bilingual Support (i18n):** Dynamic client-side English & Nepali (नेपाली) language switcher.
- **Audit Logging & CSV Reports:** Security audit log tracking and dynamic CSV report generation.

---

## 👥 Demo User Accounts

All demo accounts use the standard password: `password123`

| Role | Email | Password | Scope & Description |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@smartgov.gov.np` | `password123` | Master control console, user/vendor management, analytics, audit logs |
| **Government Officer** | `officer@smartgov.gov.np` | `password123` | Application review queue, document inspection, license issuance, complaints |
| **Approved Vendor** | `vendor@localcrafts.np` | `password123` | Active business license (`LIC-2026-000101`), product catalog management, orders |
| **Pending Vendor** | `pending.vendor@freshfarm.np` | `password123` | Submitted application (`VND-2026-000102`), document upload pending officer review |
| **Citizen / Customer** | `customer@gmail.com` | `password123` | Marketplace browsing, cart, checkout, order tracking, complaint submission |

---

## ⚙️ Technology Stack

- **Backend:** PHP 8.2+ (Modular architecture, PDO prepared statements, BCRYPT authentication)
- **Database:** MySQL 8.0+ / MariaDB (23 normalized tables with foreign keys and transactions)
- **Frontend:** HTML5, Bootstrap 5, Custom CSS3 (Glassmorphic design), Vanilla JavaScript
- **Maps & GIS:** Leaflet.js, OpenStreetMap
- **Analytics & QR:** Chart.js, QRCode.js, QR Server API
- **Localization:** Bilingual i18n (English & Nepali)

---

## 📁 Directory Structure

```text
SmartGov Market
├── assets/
│   ├── css/          # Custom stylesheet & Bootstrap
│   └── js/           # i18n, map.js, chart.js scripts
├── config/
│   ├── database.php  # Central PDO database connection
│   └── app.php       # App settings, flash messages, audit log helper
├── database/
│   ├── schema.sql    # 23 normalized MySQL tables
│   ├── seed.php      # Seed demo dataset
│   └── init_db.php   # Database initialization runner
├── includes/
│   ├── auth.php      # Session auth & RBAC middleware
│   ├── header.php    # Master HTML header & flash messages
│   ├── footer.php    # Master footer & script bundle
│   ├── navbar.php    # Top navigation & bilingual switcher
│   ├── sidebar.php   # Role-specific dashboard sidebars
│   ├── license_generator.php # License generator & QR renderer
│   ├── payment.php   # COD & Simulated Payment processor
│   └── notifications.php     # In-app notifications engine
├── uploads/
│   ├── documents/    # Vendor application verification files
│   ├── products/     # Product catalog images
│   ├── complaints/   # Complaint evidence files
│   └── profiles/     # User profile photos
├── vendor/           # Vendor dashboard, products, license & order pages
├── officer/          # Government officer dashboard, reviews, licenses & complaints
├── admin/            # Master admin dashboard, users, vendors, audit logs & reports
├── api/              # Cart, order placement & AJAX APIs
├── index.php         # Main homepage
├── login.php / register.php / logout.php / profile.php
├── products.php / product-details.php / cart.php / checkout.php
├── orders.php / order-details.php / reviews.php
├── complaints.php / complaint-details.php / map-view.php
├── government-services.php / service-details.php / applications.php
└── verify.php        # Public QR license verification page
```
