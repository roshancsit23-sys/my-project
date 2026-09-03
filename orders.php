<?php
// SmartGov Market - Customer Order History
// File: orders.php

$pageTitle = "My Orders";
require_once __DIR__ . '/includes/header.php';
requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'orders'; include __DIR__ . '/includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-bag-shopping text-primary me-2"></i>My Orders & Tracking</h4>
                    <span class="badge bg-primary rounded-pill fs-6"><?= count($orders) ?> Orders</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order No</th>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders) === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No orders placed yet. Explore verified local products!</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= sanitize($o['order_no']) ?></td>
                                        <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= sanitize($o['payment_method']) ?></span></td>
                                        <td class="fw-bold text-success"><?= formatCurrency($o['total_amount']) ?></td>
                                        <td>
                                            <span class="badge <?= $o['status'] === 'Delivered' ? 'bg-success' : ($o['status'] === 'Shipped' ? 'bg-info text-dark' : 'bg-warning text-dark') ?>">
                                                <?= sanitize($o['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order-details.php?id=<?= $o['id'] ?>" class="btn btn-xs btn-outline-primary"><i class="fa-solid fa-truck-fast me-1"></i>Track & Invoice</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
