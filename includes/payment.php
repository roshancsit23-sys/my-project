<?php
// SmartGov Market - Payment Processing System
// File: includes/payment.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/notifications.php';

function processOrderPayment($order_id, $payment_method, $amount) {
    $db = getDBConnection();
    $ownTxn = !$db->inTransaction();
    if ($ownTxn) {
        $db->beginTransaction();
    }

    try {
        $txn_id = 'TXN-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 10));
        $isCod = ($payment_method === 'Cash on Delivery');
        $payStatus = $isCod ? 'Pending' : 'Completed';
        $orderPayStatus = $isCod ? 'Pending' : 'Paid';
        $orderStatus = $isCod ? 'Processing' : 'Paid';

        $stmt = $db->prepare("INSERT INTO payments (order_id, transaction_id, payment_method, amount, status, payment_date)
                              VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$order_id, $txn_id, $payment_method, $amount, $payStatus]);

        $updateOrder = $db->prepare("UPDATE orders SET payment_method = ?, payment_status = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $updateOrder->execute([$payment_method, $orderPayStatus, $orderStatus, $order_id]);

        $oStmt = $db->prepare("SELECT customer_id, order_no FROM orders WHERE id = ?");
        $oStmt->execute([$order_id]);
        $order = $oStmt->fetch();

        if ($order) {
            $msg = $isCod
                ? "Order {$order['order_no']} was placed. Pay " . formatCurrency($amount) . " in cash on delivery."
                : "Simulated payment of " . formatCurrency($amount) . " for order {$order['order_no']} is recorded as paid (demo gateway).";
            sendNotification(
                $order['customer_id'],
                $isCod ? 'Order Confirmed (COD)' : 'Simulated Payment Recorded',
                $msg,
                "order-details.php?id={$order_id}"
            );
        }

        if ($ownTxn) {
            $db->commit();
        }

        logAudit('Payment Processed', 'Payment', $txn_id, "Order ID {$order_id} via {$payment_method}. Amount: {$amount}");

        return [
            'success' => true,
            'transaction_id' => $txn_id,
            'payment_status' => $orderPayStatus
        ];
    } catch (Exception $e) {
        if ($ownTxn && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Payment Error: " . $e->getMessage());
        throw $e;
    }
}
