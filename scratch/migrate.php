<?php
require_once __DIR__ . '/../config/database.php';
try {
    $db = getDBConnection();
    
    // 1. Add officer_remarks to vendor_documents if not exists
    $cols = $db->query("SHOW COLUMNS FROM vendor_documents LIKE 'officer_remarks'")->fetchAll();
    if (count($cols) === 0) {
        $db->exec("ALTER TABLE vendor_documents ADD COLUMN officer_remarks TEXT NULL AFTER verification_status");
        echo "[✓] Added officer_remarks column to vendor_documents.\n";
    }
    
    $db->exec("ALTER TABLE vendor_documents MODIFY COLUMN verification_status ENUM('Pending', 'Verified', 'Rejected', 'Resubmission Required') DEFAULT 'Pending'");
    echo "[✓] Updated vendor_documents ENUM.\n";

    // 2. Add renewal_requested_at, renewal_status to licenses if not exists
    $lCols = $db->query("SHOW COLUMNS FROM licenses LIKE 'renewal_status'")->fetchAll();
    if (count($lCols) === 0) {
        $db->exec("ALTER TABLE licenses ADD COLUMN renewal_status ENUM('None', 'Requested', 'Approved', 'Rejected') DEFAULT 'None' AFTER status");
        $db->exec("ALTER TABLE licenses ADD COLUMN renewal_requested_at DATETIME NULL AFTER renewal_status");
        echo "[✓] Added renewal columns to licenses table.\n";
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
