<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=smartgov_market;charset=utf8mb4", "root", "");
$orders = $pdo->query("SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone FROM orders o JOIN users u ON o.customer_id = u.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($orders);

$items = $pdo->query("SELECT oi.*, p.name as product_name, p.image_path, v.business_name, u.full_name as vendor_owner FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN vendors v ON oi.vendor_id = v.id JOIN users u ON v.user_id = u.id")->fetchAll(PDO::FETCH_ASSOC);
print_r($items);
