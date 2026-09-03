<?php
// SmartGov Market - Master Dashboard Sidebar Navigation
// File: includes/sidebar.php

$currentRole = currentRole();
$activePage = $activePage ?? '';
?>
<div class="sidebar-wrapper">
    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
        <img src="<?= BASE_URL ?>assets/css/default-avatar.png" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name'] ?? 'User') ?>&background=0d6efd&color=fff';" class="rounded-circle" width="48" height="48" alt="Avatar">
        <div class="overflow-hidden">
            <h6 class="fw-bold mb-0 text-truncate"><?= sanitize($_SESSION['user_name'] ?? 'User') ?></h6>
            <span class="badge bg-primary-subtle text-primary text-uppercase fs-7" style="font-size:0.7rem;"><?= sanitize($currentRole) ?></span>
        </div>
    </div>

    <nav class="nav flex-column gap-1">
        <?php if ($currentRole === 'admin'): ?>
            <a class="sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/dashboard.php"><i class="fa-solid fa-chart-line"></i> Analytics Overview</a>
            <a class="sidebar-link <?= $activePage === 'users' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/users.php"><i class="fa-solid fa-users"></i> Users & Roles</a>
            <a class="sidebar-link <?= $activePage === 'vendors' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/vendors.php"><i class="fa-solid fa-store"></i> Vendors & Approvals</a>
            <a class="sidebar-link <?= $activePage === 'licenses' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/licenses.php"><i class="fa-solid fa-id-card"></i> Digital Licenses</a>
            <a class="sidebar-link <?= $activePage === 'products' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/products.php"><i class="fa-solid fa-boxes-stacked"></i> Products Catalog</a>
            <a class="sidebar-link <?= $activePage === 'categories' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/categories.php"><i class="fa-solid fa-tags"></i> Categories</a>
            <a class="sidebar-link <?= $activePage === 'orders' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/orders.php"><i class="fa-solid fa-cart-flatbed"></i> System Orders</a>
            <a class="sidebar-link <?= $activePage === 'complaints' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/complaints.php"><i class="fa-solid fa-triangle-exclamation"></i> Complaints & GIS</a>
            <a class="sidebar-link <?= $activePage === 'services' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/services.php"><i class="fa-solid fa-landmark"></i> Gov Services</a>
            <a class="sidebar-link <?= $activePage === 'departments' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/departments.php"><i class="fa-solid fa-sitemap"></i> Departments</a>
            <a class="sidebar-link <?= $activePage === 'reports' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/reports.php"><i class="fa-solid fa-file-csv"></i> Reports & Analytics</a>
            <a class="sidebar-link <?= $activePage === 'audit-logs' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/audit-logs.php"><i class="fa-solid fa-clock-rotate-left"></i> Audit Logs</a>

        <?php elseif ($currentRole === 'officer'): ?>
            <a class="sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>officer/dashboard.php"><i class="fa-solid fa-gauge"></i> Officer Dashboard</a>
            <a class="sidebar-link <?= $activePage === 'applications' ? 'active' : '' ?>" href="<?= BASE_URL ?>officer/vendor-applications.php"><i class="fa-solid fa-file-signature"></i> Vendor Applications</a>
            <a class="sidebar-link <?= $activePage === 'licenses' ? 'active' : '' ?>" href="<?= BASE_URL ?>officer/licenses.php"><i class="fa-solid fa-id-card"></i> Issued Licenses</a>
            <a class="sidebar-link <?= $activePage === 'complaints' ? 'active' : '' ?>" href="<?= BASE_URL ?>officer/complaints.php"><i class="fa-solid fa-shield-cat"></i> Assigned Complaints</a>
            <a class="sidebar-link <?= $activePage === 'reports' ? 'active' : '' ?>" href="<?= BASE_URL ?>officer/reports.php"><i class="fa-solid fa-file-invoice"></i> Department Reports</a>

        <?php elseif ($currentRole === 'vendor'): ?>
            <a class="sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/dashboard.php"><i class="fa-solid fa-chart-pie"></i> Vendor Overview</a>
            <a class="sidebar-link <?= $activePage === 'license' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/license.php"><i class="fa-solid fa-award"></i> Digital Business License</a>
            <a class="sidebar-link <?= $activePage === 'products' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/products.php"><i class="fa-solid fa-box-open"></i> Manage Products</a>
            <a class="sidebar-link <?= $activePage === 'add-product' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/add-product.php"><i class="fa-solid fa-plus-circle"></i> Add New Product</a>
            <a class="sidebar-link <?= $activePage === 'orders' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/orders.php"><i class="fa-solid fa-truck-ramp-box"></i> Received Orders</a>
            <a class="sidebar-link <?= $activePage === 'sales' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/sales.php"><i class="fa-solid fa-receipt"></i> Sales History</a>
            <a class="sidebar-link <?= $activePage === 'reviews' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/reviews.php"><i class="fa-solid fa-star"></i> Customer Reviews</a>

        <?php else: ?>
            <a class="sidebar-link <?= $activePage === 'orders' ? 'active' : '' ?>" href="<?= BASE_URL ?>orders.php"><i class="fa-solid fa-bag-shopping"></i> My Orders</a>
            <a class="sidebar-link <?= $activePage === 'transactions' ? 'active' : '' ?>" href="<?= BASE_URL ?>transactions.php"><i class="fa-solid fa-receipt"></i> Transaction History</a>
            <a class="sidebar-link <?= $activePage === 'applications' ? 'active' : '' ?>" href="<?= BASE_URL ?>applications.php"><i class="fa-solid fa-file-lines"></i> Gov Applications</a>
            <a class="sidebar-link <?= $activePage === 'complaints' ? 'active' : '' ?>" href="<?= BASE_URL ?>complaints.php"><i class="fa-solid fa-circle-exclamation"></i> My Complaints</a>
            <a class="sidebar-link <?= $activePage === 'profile' ? 'active' : '' ?>" href="<?= BASE_URL ?>profile.php"><i class="fa-solid fa-user-gear"></i> Account Settings</a>
        <?php endif; ?>
    </nav>
</div>
