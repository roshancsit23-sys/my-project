<?php
// SmartGov Market - Master Admin Dashboard & Real DB Analytics
// File: admin/dashboard.php

$pageTitle = "Administrator Dashboard";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();

// Real Database Metrics (NO HARDCODED NUMBERS)
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalVendors = $db->query("SELECT COUNT(*) FROM vendors")->fetchColumn();
$approvedVendors = $db->query("SELECT COUNT(*) FROM vendors WHERE status IN ('Approved', 'Verified')")->fetchColumn();
$pendingVendors = $db->query("SELECT COUNT(*) FROM vendors WHERE status='Pending'")->fetchColumn();
$totalProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalSalesRevenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE payment_status='Paid'")->fetchColumn();
$totalComplaints = $db->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
$resolvedComplaints = $db->query("SELECT COUNT(*) FROM complaints WHERE status='Resolved'")->fetchColumn();

// Chart Data 1: Vendors by Status
$vendorStatusQuery = $db->query("SELECT status, COUNT(*) as count FROM vendors GROUP BY status")->fetchAll();
$vLabels = []; $vData = [];
foreach ($vendorStatusQuery as $v) { $vLabels[] = $v['status']; $vData[] = (int)$v['count']; }

// Chart Data 2: Products by Category
$catQuery = $db->query("SELECT c.name, COUNT(p.id) as count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id")->fetchAll();
$cLabels = []; $cData = [];
foreach ($catQuery as $c) { $cLabels[] = $c['name']; $cData[] = (int)$c['count']; }

// Chart Data 3: Complaints by Category
$cmpQuery = $db->query("SELECT category, COUNT(*) as count FROM complaints GROUP BY category")->fetchAll();
$cmpLabels = []; $cmpData = [];
foreach ($cmpQuery as $cp) { $cmpLabels[] = $cp['category']; $cmpData[] = (int)$cp['count']; }
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'dashboard'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <!-- Admin Header -->
            <div class="card p-4 text-white mb-4 border-0 shadow-sm" style="background: var(--dark-blue); border-left: 5px solid var(--primary-red) !important;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="fa-solid fa-building-columns text-warning me-2"></i>HATIYA Administration Console</h3>
                        <p class="mb-0 text-white-50">Real-time database metrics, merchant approvals, digital licensing &amp; municipal oversight.</p>
                    </div>
                    <span class="badge bg-danger px-3 py-2 fs-6">SUPER ADMIN</span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-primary fw-bold mb-1"><?= number_format($totalUsers) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Total Registered Users</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-success fw-bold mb-1"><?= number_format($approvedVendors) ?> / <?= number_format($totalVendors) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Approved Vendors</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-warning fw-bold mb-1"><?= number_format($pendingVendors) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Pending Applications</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-emerald fw-bold mb-1 text-success"><?= formatCurrency($totalSalesRevenue) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Total Market Sales</small>
                    </div>
                </div>
            </div>

            <!-- Chart.js Real Database Analytics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card card-custom p-4 shadow-sm border-0 h-100">
                        <h6 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Vendors Distribution by Status</h6>
                        <canvas id="vendorChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-custom p-4 shadow-sm border-0 h-100">
                        <h6 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-chart-bar me-2 text-success"></i>Products Count by Category</h6>
                        <canvas id="categoryChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="card card-custom p-4 shadow-sm border-0">
                        <h6 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-chart-donut me-2 text-danger"></i>Grievances & Complaints by Category</h6>
                        <canvas id="complaintChart" style="max-height: 220px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Vendor Status Chart
    new Chart(document.getElementById('vendorChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($vLabels) ?>,
            datasets: [{
                data: <?= json_encode($vData) ?>,
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#64748b']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Category Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($cLabels) ?>,
            datasets: [{
                label: 'Products',
                data: <?= json_encode($cData) ?>,
                backgroundColor: '#0d6efd'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // Complaint Chart
    new Chart(document.getElementById('complaintChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($cmpLabels) ?>,
            datasets: [{
                label: 'Complaints Count',
                data: <?= json_encode($cmpData) ?>,
                backgroundColor: '#dc3545'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
