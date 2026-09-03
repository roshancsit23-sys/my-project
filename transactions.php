<?php
// SmartGov Market - Customer Transaction History
// File: transactions.php

$pageTitle = "Transaction History";
require_once __DIR__ . '/includes/header.php';
requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT p.*, o.order_no, o.status as order_status 
                      FROM payments p
                      JOIN orders o ON p.order_id = o.id
                      WHERE o.customer_id = ?
                      ORDER BY p.payment_date DESC");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'transactions'; include __DIR__ . '/includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-receipt text-success me-2"></i>My Payment Transaction History</h4>
                    <span class="badge bg-light text-dark border">Total Records: <?= count($transactions) ?></span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Transaction ID</th>
                                <th>Order No</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Payment Status</th>
                                <th>Order Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($transactions) === 0): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No payment transactions recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td class="fw-bold text-dark">
                                            <code><?= sanitize($t['transaction_id']) ?></code>
                                        </td>
                                        <td class="fw-bold text-primary">
                                            <a href="order-details.php?id=<?= $t['order_id'] ?>" class="text-decoration-none"><?= sanitize($t['order_no']) ?></a>
                                        </td>
                                        <td><?= date('M d, Y H:i', strtotime($t['payment_date'])) ?></td>
                                        <td class="fw-bold text-success"><?= formatCurrency($t['amount']) ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="fa-solid fa-credit-card me-1"></i><?= sanitize($t['payment_method']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $t['status'] === 'Completed' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                <?= sanitize($t['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $t['order_status'] === 'Paid' || $t['order_status'] === 'Delivered' ? 'bg-success' : 'bg-info text-dark' ?>">
                                                <?= sanitize($t['order_status']) ?>
                                            </span>
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
