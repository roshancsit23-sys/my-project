<?php
// SmartGov Market - In-App Notification Helper
// File: includes/notifications.php

require_once __DIR__ . '/../config/database.php';

function sendNotification($user_id, $title, $message, $link = null) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, link, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())");
        return $stmt->execute([$user_id, $title, $message, $link]);
    } catch (Exception $e) {
        error_log("Notification Error: " . $e->getMessage());
        return false;
    }
}

function getUnreadNotificationsCount($user_id) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function getUserNotifications($user_id, $limit = 10) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function markNotificationsAsRead($user_id) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    } catch (Exception $e) {
        return false;
    }
}

function markNotificationAsRead($user_id, $notification_id) {
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        return $stmt->execute([(int)$notification_id, (int)$user_id]);
    } catch (Exception $e) {
        return false;
    }
}
