<?php
// SmartGov Market - Central Database Connection Configuration
// File: config/database.php

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'smartgov_market');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

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
            error_log("Database Connection Error: " . ($lastException ? $lastException->getMessage() : 'Unknown error'));
            die("Database Connection Failed: Unable to connect to SmartGov Market database on port 3307/3306. Please ensure MySQL is running in XAMPP.");
        }

        ensureSchemaUpgrades($pdo);
    }
    return $pdo;
}

function ensureSchemaUpgrades(PDO $pdo) {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    // Upgrades and missing tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS `password_resets` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `token` VARCHAR(64) NOT NULL UNIQUE,
        `expires_at` DATETIME NOT NULL,
        `used` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `service_application_documents` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `application_id` INT NOT NULL,
        `document_type` VARCHAR(100) NOT NULL,
        `file_name` VARCHAR(255) NOT NULL,
        `file_path` VARCHAR(255) NOT NULL,
        `file_size` INT DEFAULT 0,
        `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`application_id`) REFERENCES `service_applications`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $addColumn = function ($table, $column, $ddl) use ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");
            $stmt->execute([DB_NAME, $table, $column]);
            if ((int)$stmt->fetchColumn() === 0) {
                $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN {$ddl}");
            }
        } catch (Exception $e) {
            error_log("Schema addColumn [{$table}.{$column}]: " . $e->getMessage());
        }
    };

    $addColumn('vendors', 'pan_vat', "`pan_vat` VARCHAR(50) NULL AFTER `business_type`");
    $addColumn('vendors', 'registration_no', "`registration_no` VARCHAR(80) NULL AFTER `pan_vat`");
    $addColumn('vendors', 'verified_at', "`verified_at` DATETIME NULL AFTER `status`");
    $addColumn('vendors', 'verified_by', "`verified_by` INT NULL AFTER `verified_at`");
    $addColumn('orders', 'customer_name', "`customer_name` VARCHAR(150) NULL AFTER `customer_id`");
    $addColumn('orders', 'customer_phone', "`customer_phone` VARCHAR(30) NULL AFTER `customer_name`");
    $addColumn('service_applications', 'assigned_officer_id', "`assigned_officer_id` INT NULL AFTER `user_id`");
    $addColumn('service_applications', 'document_path', "`document_path` VARCHAR(255) NULL AFTER `remarks`");
    $addColumn('complaints', 'title', "`title` VARCHAR(255) NULL AFTER `user_id`");
    $addColumn('complaints', 'location_name', "`location_name` VARCHAR(255) NULL AFTER `location_address`");

    // Upgrade ENUMs safely
    try {
        $pdo->exec("ALTER TABLE `service_applications` MODIFY COLUMN `status` ENUM('Draft','Submitted','Under Review','Processing','Correction Required','Approved','Rejected') DEFAULT 'Submitted'");
    } catch (Exception $e) {
        error_log('Schema status enum service_applications: ' . $e->getMessage());
    }

    try {
        $pdo->exec("ALTER TABLE `products` MODIFY COLUMN `status` ENUM('Active','Disabled','Inactive','Out of Stock','Pending Approval') DEFAULT 'Active'");
    } catch (Exception $e) {
        error_log('Schema status enum products: ' . $e->getMessage());
    }

    try {
        $pdo->exec("ALTER TABLE `complaints` MODIFY COLUMN `status` ENUM('Submitted','Under Review','Assigned','Under Investigation','In Progress','Resolved','Rejected','Closed') DEFAULT 'Submitted'");
    } catch (Exception $e) {
        error_log('Schema status enum complaints: ' . $e->getMessage());
    }

    try {
        $pdo->exec("ALTER TABLE `vendors` MODIFY COLUMN `status` ENUM('Pending','Verified','Approved','Under Review','Correction Required','Rejected','Suspended') DEFAULT 'Pending'");
    } catch (Exception $e) {
        error_log('Schema status enum vendors: ' . $e->getMessage());
    }

    try {
        $pdo->exec("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('Pending','Pending Payment','Confirmed','Paid','Processing','Shipped','Delivered','Cancelled','Refund Requested','Refunded') DEFAULT 'Pending'");
    } catch (Exception $e) {
        error_log('Schema status enum orders: ' . $e->getMessage());
    }

    try {
        $pdo->exec("ALTER TABLE `licenses` MODIFY COLUMN `application_id` INT NULL");
    } catch (Exception $e) {
        error_log('Schema licenses application_id null: ' . $e->getMessage());
    }
}
