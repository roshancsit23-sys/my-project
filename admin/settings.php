<?php
// SmartGov Market - System Configuration Settings
// File: admin/settings.php

$pageTitle = "System Settings";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'settings'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-gears text-primary me-2"></i>System Configuration Settings</h4>
                    <span class="badge bg-success">OPERATIONAL</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <h6 class="fw-bold text-primary mb-2">Platform Identity</h6>
                            <p class="small text-muted mb-1">Application Name: <strong>SmartGov Market</strong></p>
                            <p class="small text-muted mb-0">Version: <strong>1.0.0 (Production Release)</strong></p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <h6 class="fw-bold text-primary mb-2">Database Connection</h6>
                            <p class="small text-muted mb-1">Host: <strong>localhost:3306</strong></p>
                            <p class="small text-muted mb-0">Database: <strong>smartgov_market (MySQL 8.0/MariaDB)</strong></p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <h6 class="fw-bold text-primary mb-2">Security & Encryption</h6>
                            <p class="small text-muted mb-1">Password Hashing: <strong>BCRYPT</strong></p>
                            <p class="small text-muted mb-0">SQL Protection: <strong>PDO Prepared Statements</strong></p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <h6 class="fw-bold text-primary mb-2">GIS & Localization</h6>
                            <p class="small text-muted mb-1">Maps Provider: <strong>Leaflet.js + OpenStreetMap</strong></p>
                            <p class="small text-muted mb-0">Language Support: <strong>English & Nepali (नेपाली)</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
