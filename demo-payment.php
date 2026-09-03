<?php
// SmartGov Market - Simulated Payment Gateway Portal (eSewa / Khalti / ConnectIPS)
// File: demo-payment.php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/payment.php';

requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$order_id = (int)($_GET['order_id'] ?? 0);
$method = sanitize($_GET['method'] ?? 'eSewa');

$oStmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$oStmt->execute([$order_id, $user_id]);
$order = $oStmt->fetch();

if (!$order) {
    setFlashMessage('danger', 'Order not found.');
    redirect('orders.php');
}

$error = '';

// Process Simulated Payment Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $demo_id = sanitize($_POST['demo_account'] ?? '');
    
    if (empty($demo_id)) {
        $error = 'Please enter a demo Mobile Number / ID to simulate payment.';
    } else {
        try {
            $txn_id = 'DEMO-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
            
            // Process Simulated Payment
            $stmt = $db->prepare("INSERT INTO payments (order_id, transaction_id, payment_method, amount, status, payment_date)
                                  VALUES (?, ?, ?, ?, 'Completed', NOW())");
            $stmt->execute([$order_id, $txn_id, $method, $order['total_amount']]);
            
            // Update Order Status
            $uOrder = $db->prepare("UPDATE orders SET payment_method = ?, payment_status = 'Paid', status = 'Paid', updated_at = NOW() WHERE id = ?");
            $uOrder->execute([$method, $order_id]);
            
            logAudit('Demo Payment Processed', 'Payment', $txn_id, "Order #{$order['order_no']} paid via {$method} (Demo ID: {$demo_id})");
            setFlashMessage('success', "✓ Demo Payment Successful via {$method}! Transaction ID: {$txn_id}. Order #{$order['order_no']} confirmed.");
            redirect("order-details.php?id={$order_id}");
        } catch (Exception $e) {
            $error = 'Payment processing failed: ' . $e->getMessage();
        }
    }
}

$brandColors = [
    'eSewa' => ['bg' => '#60bb46', 'text' => '#ffffff', 'icon' => 'fa-wallet', 'label' => 'eSewa Digital Wallet'],
    'Khalti' => ['bg' => '#5c2d91', 'text' => '#ffffff', 'icon' => 'fa-mobile-screen-button', 'label' => 'Khalti Digital Wallet'],
    'ConnectIPS' => ['bg' => '#0056b3', 'text' => '#ffffff', 'icon' => 'fa-building-columns', 'label' => 'ConnectIPS e-Payment System']
];

$brand = $brandColors[$method] ?? $brandColors['eSewa'];
$pageTitle = "Simulated Payment - " . $brand['label'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card card-custom border-0 shadow-lg overflow-hidden">
                <!-- Gateway Header -->
                <div class="p-4 text-center text-white" style="background: <?= $brand['bg'] ?>;">
                    <div class="d-inline-flex p-3 rounded-circle bg-white bg-opacity-20 mb-2">
                        <i class="fa-solid <?= $brand['icon'] ?> fs-1"></i>
                    </div>
                    <h3 class="fw-bold mb-1"><?= $brand['label'] ?></h3>
                    <span class="badge bg-white text-dark fw-bold px-3 py-1">Simulated Gateway Demo</span>
                </div>

                <div class="card-body p-4">
                    <div class="alert alert-info py-2 small mb-4">
                        <i class="fa-solid fa-circle-info me-1"></i><strong>Academic/Demo System:</strong> Do NOT enter real passwords or banking credentials. Any demo mobile number (e.g. 9841000000) works!
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small mb-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <div class="p-3 bg-light rounded border mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Order Reference:</span>
                            <strong class="text-primary"><?= sanitize($order['order_no']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Customer Name:</span>
                            <strong><?= sanitize($order['customer_name']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between fs-5 fw-bold border-top pt-2 mt-2">
                            <span>Amount Payable:</span>
                            <span class="text-success"><?= formatCurrency($order['total_amount']) ?></span>
                        </div>
                    </div>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Demo Mobile Number / Gateway ID *</label>
                            <input type="text" name="demo_account" class="form-control form-control-lg" placeholder="e.g. 9841234567" value="<?= sanitize($_POST['demo_account'] ?? '9841234567') ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Demo PIN / OTP (Any number)</label>
                            <input type="password" name="demo_pin" class="form-control" placeholder="••••" value="1234" required>
                        </div>

                        <button type="submit" class="btn btn-lg w-100 fw-bold text-white shadow-sm py-3 mb-2" style="background: <?= $brand['bg'] ?>;">
                            <i class="fa-solid fa-lock me-2"></i>Pay <?= formatCurrency($order['total_amount']) ?> Now
                        </button>
                        
                        <a href="checkout.php" class="btn btn-sm btn-outline-secondary w-100">Cancel & Return to Checkout</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
