<?php
// SmartGov Market - Top Navbar Navigation
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

<!-- =====================================================
     SMARTGOV MARKET NAVBAR
     ===================================================== -->

<nav class="navbar navbar-expand-lg navbar-dark navbar-smartgov sticky-top">

    <div class="container">

        <!-- BRAND -->
        <a class="navbar-brand d-flex align-items-center gap-2"
           href="<?= BASE_URL ?>index.php">

            <i class="fa-solid fa-building-columns text-warning fs-4"></i>

            <div class="d-flex align-items-center flex-wrap">

                <span class="fw-bold fs-5 text-white"
                      data-i18n="appName">
                    SmartGov Market
                </span>

                <span class="brand-badge ms-2">
                    GOV + MARKET
                </span>

            </div>

        </a>


        <!-- MOBILE TOGGLE -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#smartGovNavbar"
            aria-controls="smartGovNavbar"
            aria-expanded="false"
            aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- NAVBAR CONTENT -->
        <div class="collapse navbar-collapse" id="smartGovNavbar">


            <!-- MAIN NAVIGATION -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">


                <!-- HOME -->
                <li class="nav-item">

                    <a class="nav-link"
                       href="<?= BASE_URL ?>index.php">

                        <i class="fa-solid fa-house me-1"></i>

                        <span data-i18n="home">
                            Home
                        </span>

                    </a>

                </li>


                <!-- PRODUCTS -->
                <li class="nav-item">

                    <a class="nav-link"
                       href="<?= BASE_URL ?>products.php">

                        <i class="fa-solid fa-store me-1"></i>

                        <span data-i18n="products">
                            Products
                        </span>

                    </a>

                </li>


                <!-- GOVERNMENT SERVICES -->
                <li class="nav-item">

                    <a class="nav-link"
                       href="<?= BASE_URL ?>government-services.php">

                        <i class="fa-solid fa-landmark me-1"></i>

                        <span data-i18n="services">
                            Gov Services
                        </span>

                    </a>

                </li>


                <!-- GIS MAP -->
                <li class="nav-item">

                    <a class="nav-link"
                       href="<?= BASE_URL ?>map-view.php">

                        <i class="fa-solid fa-map-location-dot me-1"></i>

                        <span data-i18n="map">
                            GIS Map
                        </span>

                    </a>

                </li>


                <!-- COMPLAINTS -->
                <li class="nav-item">

                    <a class="nav-link"
                       href="<?= BASE_URL ?>complaints.php">

                        <i class="fa-solid fa-circle-exclamation me-1"></i>

                        <span data-i18n="complaints">
                            Complaints
                        </span>

                    </a>

                </li>

            </ul>


            <!-- RIGHT SIDE -->
            <div class="d-flex align-items-center gap-2 flex-wrap">


                <!-- CART -->
                <a
                    href="<?= BASE_URL ?>cart.php"
                    class="btn btn-outline-light btn-sm position-relative">

                    <i class="fa-solid fa-cart-shopping me-1"></i>

                    <span data-i18n="cart">
                        Cart
                    </span>

                    <?php if ($cartCount > 0): ?>

                        <span
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                            <?= $cartCount ?>

                        </span>

                    <?php endif; ?>

                </a>


                <!-- LANGUAGE -->
                <button
                    class="btn btn-warning btn-sm text-dark fw-semibold"
                    id="lang-toggle-btn"
                    type="button">

                    नेपाली

                </button>


                <?php if (isLoggedIn()): ?>


                    <!-- NOTIFICATIONS -->
                    <a
                        href="<?= BASE_URL ?>notifications.php"
                        class="navbar-notification text-white position-relative fs-5">

                        <i class="fa-regular fa-bell"></i>

                        <?php if ($unreadCount > 0): ?>

                            <span
                                class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">

                                <span class="visually-hidden">
                                    New alerts
                                </span>

                            </span>

                        <?php endif; ?>

                    </a>


                    <!-- USER DROPDOWN -->
                    <div class="dropdown">

                        <button
                            class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-2"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">

                            <i class="fa-solid fa-circle-user fs-5"></i>

                            <span>
                                <?= sanitize($user['full_name']) ?>
                            </span>

                        </button>


                        <ul class="dropdown-menu dropdown-menu-end shadow">


                            <!-- ROLE -->
                            <li>

                                <span class="dropdown-header text-uppercase text-muted fw-bold">

                                    <?= sanitize($user['role_display']) ?>

                                </span>

                            </li>


                            <!-- ADMIN -->
                            <?php if (hasRole('admin')): ?>

                                <li>

                                    <a
                                        class="dropdown-item fw-semibold text-primary"
                                        href="<?= BASE_URL ?>admin/dashboard.php">

                                        <i class="fa-solid fa-gauge me-2"></i>

                                        Admin Dashboard

                                    </a>

                                </li>


                            <!-- OFFICER -->
                            <?php elseif (hasRole('officer')): ?>

                                <li>

                                    <a
                                        class="dropdown-item fw-semibold text-primary"
                                        href="<?= BASE_URL ?>officer/dashboard.php">

                                        <i class="fa-solid fa-user-shield me-2"></i>

                                        Officer Dashboard

                                    </a>

                                </li>


                            <!-- VENDOR -->
                            <?php elseif (hasRole('vendor')): ?>

                                <li>
                                    <a class="dropdown-item fw-semibold text-primary" href="<?= BASE_URL ?>vendor/dashboard.php">
                                        <i class="fa-solid fa-shop me-2"></i>Vendor Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>vendor/products.php">
                                        <i class="fa-solid fa-box-open me-2"></i>My Products
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>vendor/orders.php">
                                        <i class="fa-solid fa-truck-ramp-box me-2"></i>Received Orders
                                    </a>
                                </li>

                            <!-- CUSTOMER -->
                            <?php else: ?>

                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>orders.php">
                                        <i class="fa-solid fa-box me-2"></i>My Orders
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>transactions.php">
                                        <i class="fa-solid fa-receipt me-2"></i>Transaction History
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="<?= BASE_URL ?>applications.php">
                                        <i class="fa-solid fa-file-lines me-2"></i>My Gov Applications
                                    </a>
                                </li>

                            <?php endif; ?>


                            <!-- PROFILE -->
                            <li>

                                <a
                                    class="dropdown-item"
                                    href="<?= BASE_URL ?>profile.php">

                                    <i class="fa-solid fa-user-gear me-2"></i>

                                    Edit Profile

                                </a>

                            </li>


                            <!-- DIVIDER -->
                            <li>
                                <hr class="dropdown-divider">
                            </li>


                            <!-- LOGOUT -->
                            <li>

                                <a
                                    class="dropdown-item text-danger fw-semibold"
                                    href="<?= BASE_URL ?>logout.php">

                                    <i class="fa-solid fa-right-from-bracket me-2"></i>

                                    Logout

                                </a>

                            </li>

                        </ul>

                    </div>


                <?php else: ?>


                    <!-- LOGIN -->
                    <a
                        href="<?= BASE_URL ?>login.php"
                        class="btn btn-outline-light btn-sm"
                        data-i18n="login">

                        Login

                    </a>


                    <!-- REGISTER -->
                    <a
                        href="<?= BASE_URL ?>register.php"
                        class="btn btn-warning btn-sm text-dark fw-bold"
                        data-i18n="register">

                        Register

                    </a>


                <?php endif; ?>

            </div>

        </div>

    </div>

</nav>