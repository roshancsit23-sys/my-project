<?php
// SmartGov Market - Authentication & Role Authorization Helper
// File: includes/auth.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/security.php';

function enforceSessionTimeout() {
    if (empty($_SESSION['user_id'])) {
        return;
    }
    $maxIdle = 7200;
    if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity']) > $maxIdle) {
        logoutUser();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        setFlashMessage('warning', 'Your session expired. Please sign in again.');
        redirect('login.php');
    }
    $_SESSION['last_activity'] = time();
}

function isLoggedIn() {
    if (empty($_SESSION['user_id'])) {
        return false;
    }
    enforceSessionTimeout();
    return !empty($_SESSION['user_id']);
}

function currentUser() {
    if (!isLoggedIn()) return null;
    
    static $user = null;
    if ($user === null) {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT u.*, r.name as role_name, r.display_name as role_display 
                             FROM users u 
                             JOIN roles r ON u.role_id = r.id 
                             WHERE u.id = ? AND u.is_active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if (!$user) {
            logoutUser();
        }
    }
    return $user;
}

function currentRole() {
    $user = currentUser();
    return $user ? $user['role_name'] : null;
}

function hasRole($roles) {
    if (!isLoggedIn()) return false;
    $role = currentRole();
    if (is_array($roles)) {
        return in_array($role, $roles);
    }
    return $role === $roles;
}

function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('warning', 'Please log in to access this page.');
        redirect('login.php');
    }
}

function requireRole($roles) {
    requireLogin();
    if (!hasRole($roles)) {
        setFlashMessage('danger', 'Unauthorized access! You do not have permission to view that resource.');
        $role = currentRole();
        if ($role === 'admin') redirect('admin/dashboard.php');
        elseif ($role === 'officer') redirect('officer/dashboard.php');
        elseif ($role === 'vendor') redirect('vendor/dashboard.php');
        else redirect('index.php');
    }
}

function loginUser($user) {
    session_regenerate_id(true);
    $_SESSION['last_activity'] = time();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role_id'] = $user['role_id'];
    
    // Fetch role name
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT name FROM roles WHERE id = ?");
    $stmt->execute([$user['role_id']]);
    $_SESSION['role_name'] = $stmt->fetchColumn();
    
    // Update last login
    $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    logAudit('Login', 'User', $user['id'], 'User logged in successfully.');
}

function logoutUser() {
    if (isLoggedIn()) {
        logAudit('Logout', 'User', $_SESSION['user_id'], 'User logged out.');
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
