<?php
// SmartGov Market - Customer Order Details & Tracking Invoice
// File: order-details.php

$pageTitle = "Order Details & Tracking";
require_once __DIR__ . '/includes/header.php';
requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$order_id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND (customer_id = ? OR ? IN (SELECT id FROM users WHERE role_id IN (1,2)))");
$stmt->execute([$order_id, $user_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    setFlashMessage('danger', 'Order not found.');
    redirect('orders.php');
}

// Fetch Order Items
$itemsStmt = $db->prepare("SELECT oi.*, p.name as product_name, p.image_path, v.business_name 
                           FROM order_items oi
                           JOIN products p ON oi.product_id = p.id
                           JOIN vendors v ON oi.vendor_id = v.id
                           WHERE oi.order_id = ?");
$itemsStmt->execute([$order_id]);
$items = $itemsStmt->fetchAll();

// Fetch Payment Info
$payStmt = $db->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1");
$payStmt->execute([$order_id]);
$payment = $payStmt->fetch();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Order #<?= sanitize($order['order_no']) ?></h4>
            <small class="text-muted">Placed on <?= date('F d, Y \a\t h:i A', strtotime($order['created_at'])) ?></small>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary me-2"><i class="fa-solid fa-print me-1"></i>Print Invoice</button>
            <a href="orders.php" class="btn btn-sm btn-primary"><i class="fa-solid fa-arrow-left me-1"></i>Back to My Orders</a>
        </div>
    </div>

    <!-- Live Delivery Tracking Progress Bar -->
    <div class="card card-custom p-4 shadow-sm border-0 mb-4">
        <h6 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-truck-fast me-2 text-primary"></i>Live Delivery Tracking Progress</h6>
        <div class="progress mb-3" style="height: 12px;">
            <?php 
                $progress = 25;
                if ($order['status'] === 'Processing') $progress = 50;
                if ($order['status'] === 'Shipped') $progress = 75;
                if ($order['status'] === 'Delivered') $progress = 100;
            ?>
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between text-center small text-muted">
            <span class="fw-bold text-dark">Order Placed</span>
            <span class="<?= $progress >= 50 ? 'fw-bold text-dark' : '' ?>">Processing</span>
            <span class="<?= $progress >= 75 ? 'fw-bold text-dark' : '' ?>">Shipped</span>
            <span class="<?= $progress >= 100 ? 'fw-bold text-success' : '' ?>">Delivered</span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Invoice Items List -->
        <div class="col-lg-8">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Itemized Product Invoice</h5>
                
                <div class="table-responsive">
                    <table class="table align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Vendor</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                                <?php if ($order['status'] === 'Delivered'): ?>
                                    <th>Review</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="<?= BASE_URL . ($it['image_path'] ? $it['image_path'] : 'assets/images/product-default.svg') ?>" onerror="this.onerror=null;this.src='<?= BASE_URL ?>assets/images/product-default.svg';" class="rounded border" width="45" height="45" style="object-fit:cover;">
                                            <span class="fw-bold text-dark"><?= sanitize($it['product_name']) ?></span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= sanitize($it['business_name']) ?></span></td>
                                    <td><?= $it['quantity'] ?></td>
                                    <td><?= formatCurrency($it['price']) ?></td>
                                    <td class="fw-bold text-primary"><?= formatCurrency($it['subtotal']) ?></td>
                                    <?php if ($order['status'] === 'Delivered'): ?>
                                        <td>
                                            <a href="reviews.php?product_id=<?= $it['product_id'] ?>&order_id=<?= $order['id'] ?>" class="btn btn-xs btn-warning text-dark fw-bold">
                                                <i class="fa-solid fa-star me-1"></i>Rate
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary & Address -->
        <div class="col-lg-4">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Shipping Details</h5>
                <p class="small mb-1"><strong>Address:</strong> <?= sanitize($order['shipping_address']) ?></p>
                <p class="small mb-1"><strong>Municipality:</strong> <?= sanitize($order['municipality']) ?></p>
                <p class="small mb-0"><strong>District:</strong> <?= sanitize($order['district']) ?></p>
            </div>

            <div class="card card-custom p-4 shadow-sm border-0">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Payment Record</h5>
                <p class="small mb-1"><strong>Method:</strong> <?= sanitize($order['payment_method']) ?></p>
                <p class="small mb-1"><strong>Status:</strong> <span class="badge bg-success"><?= sanitize($order['payment_status']) ?></span></p>
                <?php if ($payment): ?>
                    <p class="small mb-2"><strong>Txn ID:</strong> <code><?= sanitize($payment['transaction_id']) ?></code></p>
                <?php endif; ?>
                <hr>
                <div class="d-flex justify-content-between fs-5 fw-bold">
                    <span>Total Paid:</span>
                    <span class="text-success"><?= formatCurrency($order['total_amount']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
