<?php
// SmartGov Market - Backend Shopping Cart Operations Handler
// File: api/cart.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
}

// Ensure user has a cart database record
$cartStmt = $db->prepare("SELECT id FROM cart WHERE user_id = ?");
$cartStmt->execute([$user_id]);
$cart_id = $cartStmt->fetchColumn();

if (!$cart_id) {
    $cInsert = $db->prepare("INSERT INTO cart (user_id, created_at) VALUES (?, NOW())");
    $cInsert->execute([$user_id]);
    $cart_id = $db->lastInsertId();
}

if ($action === 'add') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    
    // Verify Product exists and stock is available
    $pStmt = $db->prepare("SELECT p.*, v.status as vendor_status FROM products p JOIN vendors v ON p.vendor_id = v.id WHERE p.id = ?");
    $pStmt->execute([$product_id]);
    $product = $pStmt->fetch();
    
    if (!$product || $product['status'] !== 'Active' || $product['vendor_status'] !== 'Approved') {
        setFlashMessage('danger', 'Product is currently unavailable.');
        redirect('products.php');
    }
    
    if ($quantity > $product['stock_quantity']) {
        setFlashMessage('danger', "Cannot add {$quantity} items. Maximum available stock is {$product['stock_quantity']}.");
        redirect("product-details.php?id={$product_id}");
    }
    
    // Check if item already exists in cart
    $itemStmt = $db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $itemStmt->execute([$cart_id, $product_id]);
    $existingItem = $itemStmt->fetch();
    
    if ($existingItem) {
        $newQty = $existingItem['quantity'] + $quantity;
        if ($newQty > $product['stock_quantity']) $newQty = $product['stock_quantity'];
        
        $uItem = $db->prepare("UPDATE cart_items SET quantity = ?, price = ? WHERE id = ?");
        $uItem->execute([$newQty, $product['price'], $existingItem['id']]);
    } else {
        $iItem = $db->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $iItem->execute([$cart_id, $product_id, $quantity, $product['price']]);
    }
    
    setFlashMessage('success', "Added '{$product['name']}' to your order!");
    if (isset($_POST['redirect_to']) && $_POST['redirect_to'] === 'checkout') {
        redirect('checkout.php');
    }
    redirect('cart.php');

} elseif ($action === 'update') {
    $cart_item_id = (int)($_POST['cart_item_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($quantity <= 0) {
        $dItem = $db->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?");
        $dItem->execute([$cart_item_id, $cart_id]);
    } else {
        // Stock check
        $pCheck = $db->prepare("SELECT p.stock_quantity FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.id = ?");
        $pCheck->execute([$cart_item_id]);
        $maxStock = (int)$pCheck->fetchColumn();
        
        if ($quantity > $maxStock) $quantity = $maxStock;
        
        $uItem = $db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?");
        $uItem->execute([$quantity, $cart_item_id, $cart_id]);
    }
    
    setFlashMessage('success', 'Cart updated successfully.');
    redirect('cart.php');

} elseif ($action === 'remove') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
        setFlashMessage('danger', 'Invalid request.');
        redirect('cart.php');
    }
    $cart_item_id = (int)($_POST['cart_item_id'] ?? 0);
    $dItem = $db->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?");
    $dItem->execute([$cart_item_id, $cart_id]);
    
    setFlashMessage('info', 'Item removed from cart.');
    redirect('cart.php');

} elseif ($action === 'clear') {
    $dItem = $db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $dItem->execute([$cart_id]);
    setFlashMessage('info', 'Your cart has been cleared.');
    redirect('cart.php');
}

redirect('cart.php');
