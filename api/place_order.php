<?php
// SmartGov Market - Atomic Order Placement Handler
// File: api/place_order.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/../includes/payment.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart.php');
}

requireCsrf();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

$shipping_address = sanitize($_POST['shipping_address'] ?? '');
$municipality = sanitize($_POST['municipality'] ?? '');
$district = sanitize($_POST['district'] ?? '');
$customer_name = sanitize($_POST['customer_name'] ?? '');
$customer_phone = sanitize($_POST['phone'] ?? '');
$payment_method = sanitize($_POST['payment_method'] ?? 'Cash on Delivery');

$allowedPay = ['Cash on Delivery', 'eSewa', 'Khalti', 'ConnectIPS', 'Simulated Digital Gateway'];
if (!in_array($payment_method, $allowedPay, true)) {
    $payment_method = 'Cash on Delivery';
}

if (empty($shipping_address) || empty($municipality) || empty($district) || empty($customer_name) || empty($customer_phone)) {
    setFlashMessage('danger', 'Please provide complete delivery address and contact details.');
    redirect('checkout.php');
}

// Fetch Cart & Items
$cStmt = $db->prepare("SELECT id FROM cart WHERE user_id = ?");
$cStmt->execute([$user_id]);
$cart_id = $cStmt->fetchColumn();

if (!$cart_id) {
    setFlashMessage('danger', 'Cart not found.');
    redirect('cart.php');
}

$itemsStmt = $db->prepare("SELECT ci.*, p.price as db_price, p.stock_quantity, p.vendor_id, p.name as product_name, v.status as vendor_status 
                           FROM cart_items ci
                           JOIN products p ON ci.product_id = p.id
                           JOIN vendors v ON p.vendor_id = v.id
                           WHERE ci.cart_id = ?");
$itemsStmt->execute([$cart_id]);
$cartItems = $itemsStmt->fetchAll();

if (count($cartItems) === 0) {
    setFlashMessage('danger', 'Cart is empty.');
    redirect('cart.php');
}

// 1. Stock Validation
foreach ($cartItems as $item) {
    if ($item['vendor_status'] !== 'Approved') {
        setFlashMessage('danger', "Product '{$item['product_name']}' is from an unapproved vendor.");
        redirect('cart.php');
    }
    if ($item['quantity'] > $item['stock_quantity']) {
        setFlashMessage('danger', "Insufficient stock for '{$item['product_name']}'. Only {$item['stock_quantity']} remaining.");
        redirect('cart.php');
    }
}

// 2. Calculate Total on Backend
$total_amount = 0;
foreach ($cartItems as $item) {
    $total_amount += ($item['db_price'] * $item['quantity']);
}

// 3. Atomic Database Transaction
$db->beginTransaction();

try {
    $order_no = generateCode('ORD', 'orders', 'order_no');
    $isCod = ($payment_method === 'Cash on Delivery');
    $payStatus = 'Pending';
    $ordStatus = $isCod ? 'Processing' : 'Pending Payment';
    
    // Create Order
    $oInsert = $db->prepare("INSERT INTO orders (order_no, customer_id, customer_name, customer_phone, total_amount, shipping_address, municipality, district, payment_method, payment_status, status, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $oInsert->execute([$order_no, $user_id, $customer_name, $customer_phone, $total_amount, $shipping_address, $municipality, $district, $payment_method, $payStatus, $ordStatus]);
    $order_id = $db->lastInsertId();
    
    $vendorIdsNotified = [];
    
    // Create Order Items & Decrement Stock
    $oiInsert = $db->prepare("INSERT INTO order_items (order_id, product_id, vendor_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $stockUpdate = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
    
    foreach ($cartItems as $item) {
        $subtotal = $item['db_price'] * $item['quantity'];
        $oiInsert->execute([$order_id, $item['product_id'], $item['vendor_id'], $item['quantity'], $item['db_price'], $subtotal]);
        $stockUpdate->execute([$item['quantity'], $item['product_id']]);
        
        $vendorIdsNotified[$item['vendor_id']] = true;
    }
    
    if ($isCod) {
        processOrderPayment($order_id, $payment_method, $total_amount);
    }
    
    // Clear Cart
    $clearCart = $db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $clearCart->execute([$cart_id]);
    
    // Send Customer Notification
    sendNotification($user_id, 'Order Placed Successfully!', "Your order {$order_no} for " . formatCurrency($total_amount) . " has been placed.", "order-details.php?id={$order_id}");
    
    // Send Vendor Notifications
    foreach (array_keys($vendorIdsNotified) as $v_id) {
        $vUserStmt = $db->prepare("SELECT user_id FROM vendors WHERE id = ?");
        $vUserStmt->execute([$v_id]);
        $v_user_id = $vUserStmt->fetchColumn();
        if ($v_user_id) {
            sendNotification($v_user_id, 'New Customer Order Received', "New order {$order_no} contains items from your store.", "vendor/orders.php");
        }
    }
    
    $db->commit();
    logAudit('Order Placed', 'Order', $order_no, "Customer ID {$user_id} placed order {$order_no} for amount {$total_amount}");
    
    if ($isCod) {
        setFlashMessage('success', "✓ Order {$order_no} placed successfully with Cash on Delivery!");
        redirect("order-details.php?id={$order_id}");
    } else {
        redirect("demo-payment.php?order_id={$order_id}&method=" . urlencode($payment_method));
    }

} catch (Exception $e) {
    $db->rollBack();
    error_log("Order Placement Error: " . $e->getMessage());
    setFlashMessage('danger', 'Order could not be completed. Please try again or contact support.');
    redirect('cart.php');
}
