<?php
// SmartGov Market - CSRF, uploads, role helpers
// File: includes/security.php

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verifyCsrf() {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    return is_string($token) && $sessionToken !== '' && hash_equals($sessionToken, $token);
}

function requireCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verifyCsrf()) {
        setFlashMessage('danger', 'Invalid security token. Please submit the form again.');
        $back = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        if (strpos($back, 'http') !== 0) {
            redirect($back);
        }
        header('Location: ' . $back);
        exit();
    }
}

function getRoleId($roleName) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt->execute([$roleName]);
    return (int)$stmt->fetchColumn();
}

function notifyRole($roleName, $title, $message, $link = null) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = ? AND u.is_active = 1");
        $stmt->execute([$roleName]);
        foreach ($stmt->fetchAll() as $row) {
            sendNotification($row['id'], $title, $message, $link);
        }
    } catch (Exception $e) {
        error_log('notifyRole: ' . $e->getMessage());
    }
}

function storeUploadedFile($fileKey, $subdir, $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'], $maxBytes = 5242880) {
    if (!isset($_FILES[$fileKey]) || !is_array($_FILES[$fileKey])) {
        return ['ok' => false, 'error' => 'No file uploaded.'];
    }
    $file = $_FILES[$fileKey];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'No file selected.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload failed. Please try again.'];
    }
    if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) {
        return ['ok' => false, 'error' => 'File must be under 5MB.'];
    }

    $original = (string)($file['name'] ?? 'file');
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return ['ok' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExt)];
    }

    $mimeMap = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset($mimeMap[$ext]) || !in_array($mime, $mimeMap[$ext], true)) {
        return ['ok' => false, 'error' => 'File content does not match the allowed type.'];
    }

    $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
    $dir = BASE_PATH . 'uploads/' . trim($subdir, '/') . '/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        return ['ok' => false, 'error' => 'Could not create upload directory.'];
    }
    if (!move_uploaded_file($file['tmp_name'], $dir . $safeName)) {
        return ['ok' => false, 'error' => 'Could not save uploaded file.'];
    }

    return [
        'ok' => true,
        'path' => 'uploads/' . trim($subdir, '/') . '/' . $safeName,
        'original' => basename($original),
        'size' => (int)$file['size'],
    ];
}

function restoreOrderStock($db, $order_id) {
    $items = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $items->execute([$order_id]);
    $upd = $db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
    foreach ($items->fetchAll() as $row) {
        $upd->execute([(int)$row['quantity'], (int)$row['product_id']]);
    }
}
