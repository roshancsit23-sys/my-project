<?php
// SmartGov Market - Admin Digital Licenses Directory
// File: admin/licenses.php

$pageTitle = "Digital Licenses Oversight";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();
$licenses = $db->query("SELECT l.*, v.business_name, u.full_name as owner_name 
                        FROM licenses l
                        JOIN vendors v ON l.vendor_id = v.id
                        JOIN users u ON v.user_id = u.id
                        ORDER BY l.created_at DESC")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'licenses'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-id-card text-primary me-2"></i>Issued Digital Business Licenses</h4>
                    <span class="badge bg-success rounded-pill fs-6"><?= count($licenses) ?> Licenses</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>License No</th>
                                <th>Business Name</th>
                                <th>Owner Name</th>
                                <th>Issue Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Verification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($licenses as $lic): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= sanitize($lic['license_no']) ?></td>
                                    <td class="fw-semibold"><?= sanitize($lic['business_name']) ?></td>
                                    <td><?= sanitize($lic['owner_name']) ?></td>
                                    <td><?= sanitize($lic['issue_date']) ?></td>
                                    <td><?= sanitize($lic['expiry_date']) ?></td>
                                    <td><span class="badge bg-success"><?= sanitize($lic['status']) ?></span></td>
                                    <td>
                                        <a href="verify.php?license_no=<?= urlencode($lic['license_no']) ?>" target="_blank" class="btn btn-xs btn-dark">
                                            <i class="fa-solid fa-qrcode me-1"></i>QR Verify
                                        </a>
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
