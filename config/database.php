<?php
// SmartGov Market - Central Database Connection Configuration
// File: config/database.php

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'smartgov_market');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            ensureSchemaUpgrades($pdo);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database Connection Failed: Unable to connect to SmartGov Market database. Please check your MySQL server setup.");
        }
    }
    return $pdo;
}

function ensureSchemaUpgrades(PDO $pdo) {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

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
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([DB_NAME, $table, $column]);
        if ((int)$stmt->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN {$ddl}");
        }
    };

    $addColumn('vendors', 'pan_vat', "`pan_vat` VARCHAR(50) NULL AFTER `business_type`");
    $addColumn('vendors', 'registration_no', "`registration_no` VARCHAR(80) NULL AFTER `pan_vat`");
    $addColumn('orders', 'customer_name', "`customer_name` VARCHAR(150) NULL AFTER `customer_id`");
    $addColumn('orders', 'customer_phone', "`customer_phone` VARCHAR(30) NULL AFTER `customer_name`");
    $addColumn('service_applications', 'assigned_officer_id', "`assigned_officer_id` INT NULL AFTER `user_id`");

    try {
        $pdo->exec("ALTER TABLE `service_applications` MODIFY COLUMN `status` ENUM('Draft','Submitted','Under Review','Processing','Correction Required','Approved','Rejected') DEFAULT 'Submitted'");
    } catch (Exception $e) {
        error_log('Schema status enum: ' . $e->getMessage());
    }
}
