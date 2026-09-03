<?php
// SmartGov Market - Vendor Dashboard
// File: vendor/dashboard.php

$pageTitle = "Vendor Dashboard";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/license_generator.php';
requireRole('vendor');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get Vendor Info
$vStmt = $db->prepare("SELECT * FROM vendors WHERE user_id = ?");
$vStmt->execute([$user_id]);
$vendor = $vStmt->fetch();

if (!$vendor) {
    setFlashMessage('danger', 'Vendor account not found.');
    redirect('index.php');
}

$vendor_id = $vendor['id'];

// License Status
$license = getVendorLicense($vendor_id);

// Stats Queries
$totalProducts = $db->query("SELECT COUNT(*) FROM products WHERE vendor_id = {$vendor_id}")->fetchColumn();

$orderStatsStmt = $db->prepare("SELECT COUNT(DISTINCT o.id) as total_orders, 
                                       COALESCE(SUM(oi.subtotal), 0) as total_sales,
                                       COUNT(DISTINCT CASE WHEN o.status = 'Processing' THEN o.id END) as pending_orders
                                FROM orders o
                                JOIN order_items oi ON o.id = oi.order_id
                                WHERE oi.vendor_id = ?");
$orderStatsStmt->execute([$vendor_id]);
$stats = $orderStatsStmt->fetch();

// Recent Orders
$recentOrdersStmt = $db->prepare("SELECT DISTINCT o.id, o.order_no, o.status, o.created_at, u.full_name as customer_name
                                   FROM orders o
                                   JOIN order_items oi ON o.id = oi.order_id
                                   JOIN users u ON o.customer_id = u.id
                                   WHERE oi.vendor_id = ?
                                   ORDER BY o.created_at DESC LIMIT 5");
$recentOrdersStmt->execute([$vendor_id]);
$recentOrders = $recentOrdersStmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'dashboard'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <!-- Header Banner -->
            <div class="card card-custom p-4 bg-dark text-white mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="fa-solid fa-shop text-warning me-2"></i><?= sanitize($vendor['business_name']) ?></h3>
                        <p class="mb-0 text-secondary"><?= sanitize($vendor['business_type']) ?> | <?= sanitize($vendor['municipality']) ?>, <?= sanitize($vendor['district']) ?></p>
                    </div>
                    <?php if ($vendor['status'] === 'Approved'): ?>
                        <span class="badge bg-success px-3 py-2 fs-6"><i class="fa-solid fa-circle-check me-1"></i>Approved Seller</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark px-3 py-2 fs-6"><i class="fa-solid fa-clock me-1"></i><?= sanitize($vendor['status']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- License Status Card Notice -->
            <?php if (!$license): ?>
                <div class="alert alert-warning d-flex justify-content-between align-items-center mb-4 shadow-sm">
                    <div>
                        <h6 class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>Digital Business License Pending</h6>
                        <p class="mb-0 small">Please upload your business registration documents to receive government clearance.</p>
                    </div>
                    <a href="<?= BASE_URL ?>vendor/application.php" class="btn btn-sm btn-dark"><i class="fa-solid fa-file-arrow-up me-1"></i>Complete Application</a>
                </div>
            <?php else: ?>
                <div class="alert alert-success d-flex justify-content-between align-items-center mb-4 shadow-sm">
                    <div>
                        <h6 class="fw-bold mb-1"><i class="fa-solid fa-certificate me-2"></i>Government Digital License Active</h6>
                        <p class="mb-0 small">License No: <strong><?= sanitize($license['license_no']) ?></strong> | Status: <span class="badge bg-success">VALID</span> | Expiry: <?= sanitize($license['expiry_date']) ?></p>
                    </div>
                    <a href="<?= BASE_URL ?>vendor/license.php" class="btn btn-sm btn-success fw-bold"><i class="fa-solid fa-award me-1"></i>View Digital License</a>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-primary fw-bold mb-1"><?= number_format($totalProducts) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Listed Products</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-success fw-bold mb-1"><?= number_format($stats['total_orders']) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Total Orders</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-warning fw-bold mb-1"><?= number_format($stats['pending_orders']) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Pending Orders</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-emerald fw-bold mb-1 text-success"><?= formatCurrency($stats['total_sales']) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Total Sales Revenue</small>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Queue -->
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-truck-ramp-box text-primary me-2"></i>Recent Received Orders</h5>
                    <a href="<?= BASE_URL ?>vendor/orders.php" class="btn btn-sm btn-outline-primary">View All Orders</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order No</th>
                                <th>Customer Name</th>
                                <th>Order Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentOrders) === 0): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">No orders received yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $o): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= sanitize($o['order_no']) ?></td>
                                        <td><?= sanitize($o['customer_name']) ?></td>
                                        <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                        <td><span class="badge bg-info text-dark"><?= sanitize($o['status']) ?></span></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>vendor/orders.php?id=<?= $o['id'] ?>" class="btn btn-xs btn-primary"><i class="fa-solid fa-eye me-1"></i>Process</a>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
