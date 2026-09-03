<?php
// SmartGov Market - Secure Document Download & Inspection Handler
// File: document-view.php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$doc_id = (int)($_GET['id'] ?? 0);
if ($doc_id <= 0) {
    die("Invalid document request.");
}

$db = getDBConnection();
$stmt = $db->prepare("SELECT vd.*, va.vendor_id, v.user_id as vendor_user_id 
                      FROM vendor_documents vd
                      JOIN vendor_applications va ON vd.application_id = va.id
                      JOIN vendors v ON va.vendor_id = v.id
                      WHERE vd.id = ?");
$stmt->execute([$doc_id]);
$doc = $stmt->fetch();

if (!$doc) {
    die("Document record not found.");
}

// Authorization check: User must be document owner OR an Officer/Admin
$currentUser = currentUser();
$user_id = $_SESSION['user_id'];
$isOwner = ($doc['vendor_user_id'] == $user_id);
$isOfficial = hasRole(['officer', 'admin']);

if (!$isOwner && !$isOfficial) {
    logAudit('Unauthorized Access Attempt', 'VendorDocument', $doc_id, "User ID {$user_id} attempted unauthorized access to document ID {$doc_id}");
    http_response_code(403);
    die("Unauthorized access! You do not have permission to view this document.");
}

$filePath = BASE_PATH . $doc['file_path'];

if (!file_exists($filePath)) {
    die("Document file does not exist on server.");
}

$mime = mime_content_type($filePath);
header("Content-Type: " . $mime);
header("Content-Length: " . filesize($filePath));
header("Content-Disposition: inline; filename=\"" . basename($doc['file_name']) . "\"");
readfile($filePath);
exit();
