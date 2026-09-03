<?php
// SmartGov Market - Application Settings & Helper Functions
// File: config/app.php

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

define('APP_NAME', 'SmartGov Market');
define('APP_TAGLINE', 'Integrated E-Governance and E-Commerce Platform');
define('APP_VERSION', '1.1.0');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$appRoot = str_replace('\\', '/', dirname(__DIR__));
$subdir = '';
if ($docRoot !== '' && strncasecmp($appRoot, $docRoot, strlen($docRoot)) === 0) {
    $subdir = substr($appRoot, strlen($docRoot));
}
define('BASE_URL', $protocol . $host . rtrim($subdir, '/') . '/');

// Absolute base directory path
define('BASE_PATH', dirname(__DIR__) . '/');

// Sanitization function
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}

// Redirect helper
function redirect($path) {
    if (strpos($path, 'http') !== 0) {
        $path = BASE_URL . ltrim($path, '/');
    }
    header("Location: " . $path);
    exit();
}

// Flash messages setter & getter
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Format currency (NPR / USD)
function formatCurrency($amount) {
    return 'रु ' . number_format((float)$amount, 2);
}

// Unique Code Generators
function generateCode($prefix, $table, $column) {
    require_once __DIR__ . '/database.php';
    $allowed = [
        'orders' => 'order_no',
        'complaints' => 'complaint_no',
        'vendor_applications' => 'application_no',
        'service_applications' => 'application_no',
        'licenses' => 'license_no',
    ];
    if (!isset($allowed[$table]) || $allowed[$table] !== $column) {
        throw new InvalidArgumentException('Invalid code generator target.');
    }
    $db = getDBConnection();
    $year = date('Y');
    $rand = str_pad((string)random_int(1, 999999), 6, '0', STR_PAD_LEFT);
    $code = "{$prefix}-{$year}-{$rand}";

    $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
    $stmt->execute([$code]);
    if ($stmt->fetchColumn() > 0) {
        return generateCode($prefix, $table, $column);
    }
    return $code;
}

// Audit Logger
function logAudit($action, $entity, $entity_id = null, $description = '') {
    try {
        require_once __DIR__ . '/database.php';
        $db = getDBConnection();
        $user_id = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, entity, entity_id, description, ip_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $action, $entity, $entity_id, $description, $ip]);
    } catch (Exception $e) {
        error_log("Audit Log Error: " . $e->getMessage());
    }
}
