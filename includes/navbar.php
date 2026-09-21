<?php
// HATIYA - Top Navigation Bar Component
// File: includes/navbar.php

$cartCount = 0;
$unreadCount = 0;
$user = null;

if (isLoggedIn()) {
    $db = getDBConnection();

    // Cart count
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(ci.quantity), 0)
        FROM cart c
        JOIN cart_items ci ON c.id = ci.cart_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartCount = (int)$stmt->fetchColumn();

    // Unread notifications
    $uStmt = $db->prepare("
        SELECT COUNT(*)
        FROM notifications
        WHERE user_id = ?
        AND is_read = 0
    ");
    $uStmt->execute([$_SESSION['user_id']]);
    $unreadCount = (int)$uStmt->fetchColumn();

    // Current user
    $user = currentUser();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-hatiya sticky-top">
    <div class="container">
        <!-- BRAND LOGO (For Mobile View fallback or navbar view) -->
        <a class="navbar-brand d-lg-none d-flex align-items-center gap-2" href="<?= BASE_URL ?>index.php">
            <span class="fw-bold text-white fs-5">HATIYA</span>
            <span class="badge bg-danger">GOV</span>
        </a>

        <!-- MOBILE TOGGLE -->
        <button class="navbar-toggler py-2 px-3 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#hatiyaNavbar" aria-controls="hatiyaNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- NAVBAR CONTENT -->
        <div class="collapse navbar-collapse" id="hatiyaNavbar">
            <!-- MAIN NAVIGATION LINKS -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>index.php">
                        <i class="fa-solid fa-house me-1"></i><?= __('nav_home', 'Home') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>government-services.php">
                        <i class="fa-solid fa-landmark me-1"></i><?= __('nav_services', 'Government Services') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>products.php">
                        <i class="fa-solid fa-store me-1"></i><?= __('nav_marketplace', 'Marketplace') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>verify.php">
                        <i class="fa-solid fa-award me-1"></i><?= __('nav_licenses', 'Digital Licenses') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>map-view.php">
                        <i class="fa-solid fa-map-location-dot me-1"></i><?= __('nav_complaints', 'Complaints & GIS') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>government-services.php#notices">
                        <i class="fa-solid fa-bullhorn me-1"></i><?= __('nav_notices', 'Public Notices') ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>index.php#about-hatiya">
                        <i class="fa-solid fa-circle-info me-1"></i><?= __('nav_about', 'About HATIYA') ?>
                    </a>
                </li>
            </ul>

            <!-- RIGHT ACTION CONTROLS -->
            <div class="d-flex align-items-center gap-2 py-2 py-lg-0 flex-wrap">
                <!-- CART -->
                <a href="<?= BASE_URL ?>cart.php" class="btn btn-outline-light btn-sm position-relative fw-semibold">
                    <i class="fa-solid fa-cart-shopping me-1"></i><?= __('nav_cart', 'Cart') ?>
                    <?php if ($cartCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $cartCount ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if (isLoggedIn()): ?>
                    <!-- USER PROFILE DROPDOWN -->
                    <div class="dropdown">
                        <button class="btn btn-warning btn-sm dropdown-toggle d-flex align-items-center gap-2 text-dark fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-circle-user fs-6"></i>
                            <span><?= sanitize($user['full_name']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                            <li><span class="dropdown-header text-uppercase text-muted fw-bold"><?= sanitize($user['role_display']) ?></span></li>
                            
                            <?php if (hasRole('admin')): ?>
                                <li><a class="dropdown-item fw-semibold text-primary" href="<?= BASE_URL ?>admin/dashboard.php"><i class="fa-solid fa-gauge me-2"></i><?= __('nav_admin_panel', 'Admin Panel') ?></a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/vendors.php"><i class="fa-solid fa-store me-2"></i><?= __('nav_vendors_approvals', 'Vendors & Approvals') ?></a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/products.php"><i class="fa-solid fa-boxes-stacked me-2"></i><?= __('nav_products_catalog', 'Products Catalog') ?></a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/categories.php"><i class="fa-solid fa-tags me-2"></i><?= __('nav_categories', 'Categories') ?></a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/orders.php"><i class="fa-solid fa-cart-flatbed me-2"></i><?= __('nav_system_orders', 'System Orders') ?></a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin/complaints.php"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= __('nav_complaints', 'Complaints & GIS') ?></a></li>
                            <?php elseif (hasRole('vendor')): ?>
                                <li><a class="dropdown-item fw-semibold text-primary" href="<?= BASE_URL ?>vendor/dashboard.php"><i class="fa-solid fa-chart-line me-2"></i><?= __('nav_vendor_dashboard', 'Vendor Dashboard') ?></a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>vendor/products.php"><i class="fa-solid fa-box-open me-2"></i><?= __('nav_my_products', 'My Products') ?></a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>vendor/add-product.php"><i class="fa-solid fa-plus-circle me-2"></i><?= __('nav_add_product', 'Add Product') ?></a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>vendor/orders.php"><i class="fa-solid fa-truck-ramp-box me-2"></i><?= __('nav_received_orders', 'Received Orders') ?></a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>vendor/license.php"><i class="fa-solid fa-award me-2"></i><?= __('nav_digital_license', 'Digital Business License') ?></a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>orders.php"><i class="fa-solid fa-box me-2"></i><?= __('nav_orders', 'My Orders') ?></a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>complaints.php"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= __('nav_my_complaints', 'My Complaints') ?></a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>applications.php"><i class="fa-solid fa-file-lines me-2"></i><?= __('nav_applications', 'My Applications') ?></a></li>
                            <?php endif; ?>

                            <li><a class="dropdown-item" href="<?= BASE_URL ?>profile.php"><i class="fa-solid fa-user-gear me-2"></i><?= __('nav_profile', 'Profile Settings') ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger fw-semibold" href="<?= BASE_URL ?>logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i><?= __('nav_logout', 'Logout') ?></a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-light btn-sm fw-semibold"><?= __('nav_login', 'Login') ?></a>
                    <a href="<?= BASE_URL ?>register.php" class="btn btn-warning btn-sm text-dark fw-bold"><?= __('nav_register', 'Register') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>