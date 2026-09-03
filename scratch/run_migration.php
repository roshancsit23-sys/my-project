<?php
require_once __DIR__ . '/../config/database.php';
try {
    $db = getDBConnection();
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM service_applications LIKE 'document_path'");
    if (!$stmt->fetch()) {
        $db->exec("ALTER TABLE service_applications ADD COLUMN document_path VARCHAR(255) NULL AFTER remarks");
        echo "Column 'document_path' added to service_applications.\n";
    } else {
        echo "Column 'document_path' already exists in service_applications.\n";
    }
} catch (Exception $e) {
    echo "Migration Exception: " . $e->getMessage() . "\n";
}
