<?php
// HATIYA - Master Header Template
// File: includes/header.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/notifications.php';

$user = currentUser();
$unreadCount = $user ? getUnreadNotificationsCount($user['id']) : 0;
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' . APP_NAME : APP_NAME . ' – ' . APP_TAGLINE ?></title>
    
    <!-- Meta tags -->
    <meta name="description" content="HATIYA – Digital Governance & Marketplace Platform. Connecting citizens, government, and local businesses in Nepal.">
    <meta name="keywords" content="HATIYA, Digital Governance, Nepal Government, Local Marketplace, Digital Business License, GIS Complaints">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Leaflet CSS for GIS Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<!-- =====================================================
     OFFICIAL TOP GOV BAR
     ===================================================== -->
<div class="gov-top-bar">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold"><i class="fa-solid fa-flag text-danger me-1"></i> <?= __('gov_portal_banner', 'नेपाल सरकार / Government of Nepal Portal') ?></span>
            <span class="text-white-50">|</span>
            <span class="small opacity-75"><?= __('portal_tagline_short', 'Digital Governance & Marketplace Platform') ?></span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <!-- Language Switcher: Visible at TOP of site -->
            <div class="d-flex align-items-center gap-1 bg-black bg-opacity-25 px-2 py-1 rounded border border-white-50">
                <i class="fa-solid fa-globe text-warning me-1 small"></i>
                <a href="?lang=ne" class="badge text-decoration-none <?= isNepali() ? 'bg-warning text-dark fw-bold' : 'text-white' ?>" title="नेपाली भाषा छान्नुहोस्">नेपाली</a>
                <span class="text-white-50">|</span>
                <a href="?lang=en" class="badge text-decoration-none <?= !isNepali() ? 'bg-warning text-dark fw-bold' : 'text-white' ?>" title="Switch to English">English</a>
            </div>
            <a href="<?= BASE_URL ?>government-services.php" class="text-decoration-none text-white small"><i class="fa-regular fa-circle-question me-1"></i><?= __('nav_services', 'Services') ?></a>
            <a href="<?= BASE_URL ?>complaints.php" class="text-decoration-none text-white small"><i class="fa-solid fa-headset me-1"></i><?= __('nav_complaints', 'Complaints') ?></a>
            <?php if (!$user): ?>
                <a href="<?= BASE_URL ?>login.php" class="text-warning font-bold text-decoration-none small"><i class="fa-solid fa-right-to-bracket me-1"></i><?= __('nav_login', 'Login') ?> / <?= __('nav_register', 'Register') ?></a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- =====================================================
     MAIN HEADER (OFFICIAL EMBLEM & BRANDING)
     ===================================================== -->
<header class="hatiya-main-header py-2 bg-white shadow-sm border-bottom">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
        <!-- LEFT: Official Government of Nepal Emblem & Title -->
        <a href="<?= BASE_URL ?>index.php" class="d-flex align-items-center gap-3 text-decoration-none text-dark">
            <img src="<?= BASE_URL ?>assets/images/nepal-emblem.svg" alt="<?= __('official_emblem_alt', 'Emblem of Nepal') ?>" height="64" class="d-inline-block flex-shrink-0">
            <div>
                <div class="text-danger fw-bold lh-1" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                    <?= isNepali() ? 'नेपाल सरकार • स्थानीय तह' : 'GOVERNMENT OF NEPAL • LOCAL LEVEL' ?>
                </div>
                <div class="fw-black text-navy lh-sm" style="font-size: 1.65rem; font-weight: 900; letter-spacing: 1px; color: #0b2545;">
                    <?= isNepali() ? 'हटिया (HATIYA)' : 'HATIYA' ?>
                </div>
                <div class="text-muted fw-semibold small lh-1" style="font-size: 0.78rem;">
                    <?= __('portal_subtitle', 'Digital Governance & Marketplace Platform') ?>
                </div>
            </div>
        </a>

        <!-- RIGHT: Search Bar & User Quick Actions -->
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <form action="<?= BASE_URL ?>products.php" method="GET" class="d-none d-md-flex">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control border-secondary-subtle" placeholder="<?= isNepali() ? 'सामग्री वा सेवा खोज्नुहोस्...' : 'Search marketplace & services...' ?>" style="width: 240px;">
                    <button class="btn btn-gov-secondary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </form>

            <?php if ($user): ?>
                <a href="<?= BASE_URL ?>notifications.php" class="btn btn-outline-secondary btn-sm position-relative" title="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- =====================================================
     NAVIGATION BAR
     ===================================================== -->
<?php include __DIR__ . '/navbar.php'; ?>

<!-- FLASH MESSAGES -->
<?php if ($flash): ?>
<div class="container mt-3">
    <div class="alert alert-<?= sanitize($flash['type']) ?> alert-dismissible fade show shadow-sm border-0" role="alert">
        <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check text-success' : 'fa-triangle-exclamation text-danger' ?> me-2 fs-5"></i>
        <strong><?= sanitize($flash['message']) ?></strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>

<main class="py-4">
