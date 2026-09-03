<?php
// SmartGov Market - Vendor Sales & Revenue Analytics
// File: vendor/sales.php

$pageTitle = "Sales Revenue Report";
require_once __DIR__ . '/../includes/header.php';
requireRole('vendor');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

$vStmt = $db->prepare("SELECT id FROM vendors WHERE user_id = ?");
$vStmt->execute([$user_id]);
$vendor = $vStmt->fetch();
$vendor_id = $vendor['id'] ?? 0;

$salesData = $db->query("SELECT oi.*, o.order_no, o.created_at, p.name as product_name, u.full_name as customer_name
                         FROM order_items oi
                         JOIN orders o ON oi.order_id = o.id
                         JOIN products p ON oi.product_id = p.id
                         JOIN users u ON o.customer_id = u.id
                         WHERE oi.vendor_id = {$vendor_id}
                         ORDER BY o.created_at DESC")->fetchAll();

$totalRevenue = 0;
foreach ($salesData as $s) $totalRevenue += $s['subtotal'];
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'sales'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-receipt text-success me-2"></i>Sales Revenue History</h4>
                    <span class="badge bg-success fs-6 px-3 py-2">Total Revenue: <?= formatCurrency($totalRevenue) ?></span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Order No</th>
                                <th>Product Name</th>
                                <th>Customer</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($salesData) === 0): ?>
                                <tr><td colspan="7" class="text-center text-muted py-4">No sales records found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($salesData as $s): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                                        <td class="fw-bold text-primary"><?= sanitize($s['order_no']) ?></td>
                                        <td class="fw-semibold"><?= sanitize($s['product_name']) ?></td>
                                        <td><?= sanitize($s['customer_name']) ?></td>
                                        <td><?= $s['quantity'] ?></td>
                                        <td><?= formatCurrency($s['price']) ?></td>
                                        <td class="fw-bold text-success"><?= formatCurrency($s['subtotal']) ?></td>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
