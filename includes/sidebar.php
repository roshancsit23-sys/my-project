<?php
// HATIYA - Master Dashboard Sidebar Navigation
// File: includes/sidebar.php

$currentRole = currentRole();
$activePage = $activePage ?? '';
?>
<div class="sidebar-wrapper">
    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-secondary">
        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:44px; height:44px; font-size: 1.2rem;">
            <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="overflow-hidden">
            <h6 class="fw-bold mb-0 text-truncate text-white"><?= sanitize($_SESSION['user_name'] ?? 'User') ?></h6>
            <span class="badge bg-danger text-uppercase" style="font-size:0.68rem;"><?= sanitize($currentRole) ?></span>
        </div>
    </div>

    <nav class="nav flex-column gap-1">
        <?php if ($currentRole === 'admin'): ?>
            <a class="sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/dashboard.php"><i class="fa-solid fa-chart-line"></i> <?= __('nav_dashboard', 'Dashboard') ?></a>
            <a class="sidebar-link <?= $activePage === 'users' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/users.php"><i class="fa-solid fa-users"></i> <?= isNepali() ? 'नागरिकहरू' : 'Citizens' ?></a>
            <a class="sidebar-link <?= $activePage === 'vendors' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/vendors.php"><i class="fa-solid fa-store"></i> <?= __('nav_vendors_approvals', 'Vendors & Approvals') ?></a>
            <a class="sidebar-link <?= $activePage === 'products' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/products.php"><i class="fa-solid fa-boxes-stacked"></i> <?= __('nav_products_catalog', 'Products Catalog') ?></a>
            <a class="sidebar-link <?= $activePage === 'categories' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/categories.php"><i class="fa-solid fa-tags"></i> <?= __('nav_categories', 'Categories') ?></a>
            <a class="sidebar-link <?= $activePage === 'licenses' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/licenses.php"><i class="fa-solid fa-id-card"></i> <?= __('nav_licenses', 'Digital Licenses') ?></a>
            <a class="sidebar-link <?= $activePage === 'orders' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/orders.php"><i class="fa-solid fa-cart-flatbed"></i> <?= __('nav_system_orders', 'System Orders') ?></a>
            <a class="sidebar-link <?= $activePage === 'complaints' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/complaints.php"><i class="fa-solid fa-triangle-exclamation"></i> <?= __('nav_complaints', 'Complaints & GIS') ?></a>
            <a class="sidebar-link <?= $activePage === 'services' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/services.php"><i class="fa-solid fa-bullhorn"></i> <?= __('nav_notices', 'Public Notices') ?></a>
            <a class="sidebar-link <?= $activePage === 'reports' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/reports.php"><i class="fa-solid fa-file-csv"></i> <?= isNepali() ? 'प्रतिवेदनहरू' : 'Reports' ?></a>
            <a class="sidebar-link <?= $activePage === 'settings' ? 'active' : '' ?>" href="<?= BASE_URL ?>admin/settings.php"><i class="fa-solid fa-gear"></i> <?= isNepali() ? 'सेटिङहरू' : 'Settings' ?></a>

        <?php elseif ($currentRole === 'officer'): ?>
            <a class="sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>officer/dashboard.php"><i class="fa-solid fa-gauge"></i> <?= isNepali() ? 'अधिकृत ड्यासबोर्ड' : 'Officer Dashboard' ?></a>
            <a class="sidebar-link <?= $activePage === 'applications' ? 'active' : '' ?>" href="<?= BASE_URL ?>officer/vendor-applications.php"><i class="fa-solid fa-file-signature"></i> <?= isNepali() ? 'व्यवसायी आवेदनहरू' : 'Vendor Applications' ?></a>
            <a class="sidebar-link <?= $activePage === 'licenses' ? 'active' : '' ?>" href="<?= BASE_URL ?>officer/licenses.php"><i class="fa-solid fa-id-card"></i> <?= __('nav_licenses', 'Issued Licenses') ?></a>
            <a class="sidebar-link <?= $activePage === 'complaints' ? 'active' : '' ?>" href="<?= BASE_URL ?>officer/complaints.php"><i class="fa-solid fa-shield-cat"></i> <?= isNepali() ? 'तोकिएका गुनासोहरू' : 'Assigned Complaints' ?></a>
            <a class="sidebar-link <?= $activePage === 'reports' ? 'active' : '' ?>" href="<?= BASE_URL ?>officer/reports.php"><i class="fa-solid fa-file-invoice"></i> <?= isNepali() ? 'शाखा प्रतिवेदन' : 'Department Reports' ?></a>

        <?php elseif ($currentRole === 'vendor'): ?>
            <a class="sidebar-link <?= $activePage === 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/dashboard.php"><i class="fa-solid fa-chart-pie"></i> <?= __('nav_vendor_dashboard', 'Vendor Overview') ?></a>
            <a class="sidebar-link <?= $activePage === 'license' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/license.php"><i class="fa-solid fa-award"></i> <?= __('nav_digital_license', 'Digital Business License') ?></a>
            <a class="sidebar-link <?= $activePage === 'products' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/products.php"><i class="fa-solid fa-box-open"></i> <?= __('nav_my_products', 'Manage Products') ?></a>
            <a class="sidebar-link <?= $activePage === 'add-product' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/add-product.php"><i class="fa-solid fa-plus-circle"></i> <?= __('nav_add_product', 'Add New Product') ?></a>
            <a class="sidebar-link <?= $activePage === 'orders' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/orders.php"><i class="fa-solid fa-truck-ramp-box"></i> <?= __('nav_received_orders', 'Received Orders') ?></a>
            <a class="sidebar-link <?= $activePage === 'sales' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/sales.php"><i class="fa-solid fa-receipt"></i> <?= isNepali() ? 'बिक्री विवरण' : 'Sales History' ?></a>
            <a class="sidebar-link <?= $activePage === 'reviews' ? 'active' : '' ?>" href="<?= BASE_URL ?>vendor/reviews.php"><i class="fa-solid fa-star"></i> <?= isNepali() ? 'ग्राहक समीक्षाहरू' : 'Customer Reviews' ?></a>

        <?php else: ?>
            <a class="sidebar-link <?= $activePage === 'orders' ? 'active' : '' ?>" href="<?= BASE_URL ?>orders.php"><i class="fa-solid fa-bag-shopping"></i> <?= __('nav_orders', 'My Orders') ?></a>
            <a class="sidebar-link <?= $activePage === 'transactions' ? 'active' : '' ?>" href="<?= BASE_URL ?>transactions.php"><i class="fa-solid fa-receipt"></i> <?= isNepali() ? 'कारोबार इतिहास' : 'Transaction History' ?></a>
            <a class="sidebar-link <?= $activePage === 'applications' ? 'active' : '' ?>" href="<?= BASE_URL ?>applications.php"><i class="fa-solid fa-file-lines"></i> <?= __('nav_applications', 'Gov Applications') ?></a>
            <a class="sidebar-link <?= $activePage === 'complaints' ? 'active' : '' ?>" href="<?= BASE_URL ?>complaints.php"><i class="fa-solid fa-circle-exclamation"></i> <?= __('nav_my_complaints', 'My Complaints') ?></a>
            <a class="sidebar-link <?= $activePage === 'profile' ? 'active' : '' ?>" href="<?= BASE_URL ?>profile.php"><i class="fa-solid fa-user-gear"></i> <?= __('nav_profile', 'Account Settings') ?></a>
        <?php endif; ?>
    </nav>
</div>
