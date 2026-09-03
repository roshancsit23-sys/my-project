<?php
// SmartGov Market - Digital License Generator & Certificate Handler
// File: includes/license_generator.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/notifications.php';

function issueDigitalLicense($vendor_id, $application_id, $issuing_authority = 'Department of Commerce & Industry') {
    $db = getDBConnection();
    
    // Check if license already exists
    $stmt = $db->prepare("SELECT * FROM licenses WHERE vendor_id = ? AND application_id = ?");
    $stmt->execute([$vendor_id, $application_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        return $existing;
    }
    
    $license_no = generateCode('LIC', 'licenses', 'license_no');
    $issue_date = date('Y-m-d');
    $expiry_date = date('Y-m-d', strtotime('+1 year'));
    $qr_code_url = BASE_URL . "verify.php?license_no=" . urlencode($license_no);
    
    $insertStmt = $db->prepare("INSERT INTO licenses (license_no, vendor_id, application_id, issuing_authority, issue_date, expiry_date, status, qr_code_path, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, 'VALID', ?, NOW())");
    $insertStmt->execute([$license_no, $vendor_id, $application_id, $issuing_authority, $issue_date, $expiry_date, $qr_code_url]);
    
    // Update vendor status to Approved
    $updateVendor = $db->prepare("UPDATE vendors SET status = 'Approved' WHERE id = ?");
    $updateVendor->execute([$vendor_id]);
    
    // Get vendor user_id for notification
    $vStmt = $db->prepare("SELECT user_id, business_name FROM vendors WHERE id = ?");
    $vStmt->execute([$vendor_id]);
    $vendor = $vStmt->fetch();
    
    if ($vendor) {
        sendNotification(
            $vendor['user_id'],
            'Digital Business License Issued!',
            "Your business license {$license_no} for {$vendor['business_name']} has been issued successfully and is now VALID.",
            'vendor/license.php'
        );
    }
    
    logAudit('License Issued', 'License', $license_no, "Digital license {$license_no} issued to vendor ID {$vendor_id}.");
    
    return [
        'license_no' => $license_no,
        'vendor_id' => $vendor_id,
        'issue_date' => $issue_date,
        'expiry_date' => $expiry_date,
        'status' => 'VALID',
        'qr_code_url' => $qr_code_url
    ];
}

function getVendorLicense($vendor_id) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT l.*, v.business_name, v.business_type, v.address, v.municipality, v.district, u.full_name as owner_name, u.email, u.phone 
                          FROM licenses l
                          JOIN vendors v ON l.vendor_id = v.id
                          JOIN users u ON v.user_id = u.id
                          WHERE l.vendor_id = ? AND l.status != 'REVOKED'
                          ORDER BY l.created_at DESC LIMIT 1");
    $stmt->execute([$vendor_id]);
    return $stmt->fetch();
}

function verifyLicenseByNo($license_no) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT l.*, v.business_name, v.business_type, v.address, v.municipality, v.district, u.full_name as owner_name 
                          FROM licenses l
                          JOIN vendors v ON l.vendor_id = v.id
                          JOIN users u ON v.user_id = u.id
                          WHERE l.license_no = ?");
    $stmt->execute([$license_no]);
    $license = $stmt->fetch();
    
    if ($license) {
        // Check expiry date
        if (strtotime($license['expiry_date']) < time() && $license['status'] === 'VALID') {
            $license['status'] = 'EXPIRED';
            $update = $db->prepare("UPDATE licenses SET status = 'EXPIRED' WHERE id = ?");
            $update->execute([$license['id']]);
        }
    }
    
    return $license;
}

function requestLicenseRenewal($license_id) {
    $db = getDBConnection();
    $stmt = $db->prepare("UPDATE licenses SET renewal_status = 'Requested', renewal_requested_at = NOW() WHERE id = ?");
    $stmt->execute([$license_id]);
    logAudit('License Renewal Requested', 'License', $license_id, "Vendor requested renewal for license ID {$license_id}");
}

function approveLicenseRenewal($license_id) {
    $db = getDBConnection();
    $lStmt = $db->prepare("SELECT * FROM licenses WHERE id = ?");
    $lStmt->execute([$license_id]);
    $lic = $lStmt->fetch();
    
    if ($lic) {
        $newExpiry = date('Y-m-d', strtotime('+1 year', strtotime($lic['expiry_date'] > date('Y-m-d') ? $lic['expiry_date'] : date('Y-m-d'))));
        $update = $db->prepare("UPDATE licenses SET expiry_date = ?, status = 'VALID', renewal_status = 'Approved' WHERE id = ?");
        $update->execute([$newExpiry, $license_id]);
        
        // Notify vendor
        $vStmt = $db->prepare("SELECT user_id FROM vendors WHERE id = ?");
        $vStmt->execute([$lic['vendor_id']]);
        $vendor_user_id = $vStmt->fetchColumn();
        if ($vendor_user_id) {
            sendNotification($vendor_user_id, 'License Renewal Approved!', "Your digital business license {$lic['license_no']} has been renewed until {$newExpiry}.", 'vendor/license.php');
        }
        
        logAudit('License Renewal Approved', 'License', $lic['license_no'], "License {$lic['license_no']} renewed until {$newExpiry}");
    }
}

