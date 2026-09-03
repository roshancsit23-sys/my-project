# SmartGov Market - Local Setup & Installation Guide

This guide details how to configure and run **SmartGov Market** locally using XAMPP and PHP built-in CLI server.

---

## Prerequisites

1. **PHP:** PHP 8.0 or higher with `pdo_mysql` enabled.
2. **MySQL / MariaDB:** MySQL Server (e.g. via XAMPP on `localhost:3306`).
3. **Web Browser:** Google Chrome, Mozilla Firefox, or Microsoft Edge.

---

## Step-by-Step Installation

### 1. Database Initialization

Ensure MySQL daemon is running on `localhost:3306`. Run the database setup script via PHP CLI:

```powershell
& "C:\xampp\php\php.exe" database/init_db.php
```

This will automatically create the `smartgov_market` database, create all 23 schema tables, and populate seed demo accounts, categories, products, orders, and complaints.

---

### 2. Running local PHP Server

Launch PHP built-in web server in the root project directory:

```powershell
& "C:\xampp\php\php.exe" -S localhost:8000
```

---

### 3. Open in Browser

Navigate to:

```text
http://localhost:8000
```

---

## Demo Credentials Overview

- **Admin Console:** `http://localhost:8000/admin/dashboard.php` (`admin@smartgov.gov.np` / `password123`)
- **Officer Console:** `http://localhost:8000/officer/dashboard.php` (`officer@smartgov.gov.np` / `password123`)
- **Vendor Portal:** `http://localhost:8000/vendor/dashboard.php` (`vendor@localcrafts.np` / `password123`)
- **Citizen Customer:** `http://localhost:8000/login.php` (`customer@gmail.com` / `password123`)
- **Public Verification:** `http://localhost:8000/verify.php?license_no=LIC-2026-000101`
