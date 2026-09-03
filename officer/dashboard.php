<?php
// SmartGov Market - Government Officer Dashboard
// File: officer/dashboard.php

$pageTitle = "Government Officer Dashboard";
require_once __DIR__ . '/../includes/header.php';
requireRole(['officer', 'admin']);

$db = getDBConnection();

// Officer Stats
$pendingApps = $db->query("SELECT COUNT(*) FROM vendor_applications WHERE status IN ('Submitted', 'Under Review')")->fetchColumn();
$approvedApps = $db->query("SELECT COUNT(*) FROM vendor_applications WHERE status = 'Approved'")->fetchColumn();
$activeLicenses = $db->query("SELECT COUNT(*) FROM licenses WHERE status = 'VALID'")->fetchColumn();
$pendingComplaints = $db->query("SELECT COUNT(*) FROM complaints WHERE status IN ('Submitted', 'Assigned', 'Under Investigation')")->fetchColumn();

// Recent Applications Queue
$recentApps = $db->query("SELECT va.*, v.business_name, v.business_type, u.full_name as owner_name, u.phone 
                          FROM vendor_applications va
                          JOIN vendors v ON va.vendor_id = v.id
                          JOIN users u ON v.user_id = u.id
                          ORDER BY va.submitted_at DESC LIMIT 5")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'dashboard'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <!-- Header Banner -->
            <div class="card card-custom p-4 bg-primary text-white mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #1a2b4c 0%, #0d6efd 100%);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="fa-solid fa-user-shield me-2 text-warning"></i>Government Officer Portal</h3>
                        <p class="mb-0 opacity-75">Review business applications, verify certificates, issue digital licenses & resolve public grievances.</p>
                    </div>
                    <span class="badge bg-warning text-dark fw-bold px-3 py-2 fs-6">OFFICER ACTIVE</span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-warning fw-bold mb-1"><?= number_format($pendingApps) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Pending Applications</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-success fw-bold mb-1"><?= number_format($approvedApps) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Approved Vendors</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-primary fw-bold mb-1"><?= number_format($activeLicenses) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Active Licenses</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-0 shadow-sm text-center">
                        <div class="fs-1 text-danger fw-bold mb-1"><?= number_format($pendingComplaints) ?></div>
                        <small class="text-muted fw-semibold text-uppercase">Open Complaints</small>
                    </div>
                </div>
            </div>

            <!-- Pending Review Queue -->
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-file-signature text-primary me-2"></i>Vendor Application Review Queue</h5>
                    <a href="<?= BASE_URL ?>officer/vendor-applications.php" class="btn btn-sm btn-outline-primary">View All Applications</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>App No</th>
                                <th>Business Name</th>
                                <th>Owner</th>
                                <th>Sector</th>
                                <th>Submitted Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentApps as $app): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= sanitize($app['application_no']) ?></td>
                                    <td class="fw-semibold"><?= sanitize($app['business_name']) ?></td>
                                    <td><?= sanitize($app['owner_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= sanitize($app['business_type']) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($app['submitted_at'])) ?></td>
                                    <td>
                                        <span class="badge <?= $app['status'] === 'Approved' ? 'bg-success' : ($app['status'] === 'Submitted' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                            <?= sanitize($app['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>officer/application-details.php?id=<?= $app['id'] ?>" class="btn btn-xs btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Review</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
