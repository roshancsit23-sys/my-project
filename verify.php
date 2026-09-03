<?php
// SmartGov Market - Public Digital License QR Verification Page
// File: verify.php

$pageTitle = "Public License Verification";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/license_generator.php';

$license_no = sanitize($_GET['license_no'] ?? '');
$license = null;

if (!empty($license_no)) {
    $license = verifyLicenseByNo($license_no);
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card card-custom p-4 shadow-lg border-0 mb-4">
                <div class="text-center mb-4">
                    <div class="bg-primary-subtle text-primary d-inline-flex p-3 rounded-circle mb-3">
                        <i class="fa-solid fa-qrcode fs-1"></i>
                    </div>
                    <h3 class="fw-bold">Public License Verification System</h3>
                    <p class="text-muted small">Verify government-issued digital business licenses & merchant validity in real-time</p>
                </div>

                <!-- Search Bar -->
                <form method="GET" action="" class="row g-2 mb-4">
                    <div class="col-8 col-sm-9">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-barcode text-muted"></i></span>
                            <input type="text" name="license_no" class="form-control fs-6" placeholder="Enter License No (e.g. LIC-2026-000101)" value="<?= sanitize($license_no) ?>" required>
                        </div>
                    </div>
                    <div class="col-4 col-sm-3">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold fs-6">
                            <i class="fa-solid fa-search me-1"></i>Verify
                        </button>
                    </div>
                </form>

                <?php if (!empty($license_no)): ?>
                    <?php if (!$license): ?>
                        <div class="alert alert-danger p-4 text-center border-0 shadow-sm rounded-3">
                            <i class="fa-solid fa-triangle-exclamation fs-1 mb-2"></i>
                            <h5 class="fw-bold">LICENSE NOT FOUND OR INVALID</h5>
                            <p class="mb-0 small">The entered license number <strong><?= sanitize($license_no) ?></strong> does not match any official government record in SmartGov Market.</p>
                        </div>
                    <?php else: ?>
                        <div class="card p-4 border-2 <?= $license['status'] === 'VALID' ? 'border-success bg-success-subtle' : 'border-danger bg-danger-subtle' ?> rounded-3">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-dark border-opacity-25">
                                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-certificate me-2 text-primary"></i>Official Government Verification Result</h5>
                                <span class="badge <?= $license['status'] === 'VALID' ? 'bg-success' : 'bg-danger' ?> fs-6 px-3 py-2">
                                    <i class="fa-solid <?= $license['status'] === 'VALID' ? 'fa-check-circle' : 'fa-xmark-circle' ?> me-1"></i>STATUS: <?= sanitize($license['status']) ?>
                                </span>
                            </div>

                            <table class="table table-sm table-borderless small mb-0 fs-6">
                                <tr>
                                    <td class="fw-bold text-muted" width="40%">License Number:</td>
                                    <td class="fw-bold text-primary"><?= sanitize($license['license_no']) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Registered Business:</td>
                                    <td class="fw-bold text-dark"><?= sanitize($license['business_name']) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Owner Name:</td>
                                    <td><?= sanitize($license['owner_name']) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Business Sector:</td>
                                    <td><?= sanitize($license['business_type']) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Issuing Authority:</td>
                                    <td><?= sanitize($license['issuing_authority']) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Issue Date:</td>
                                    <td><?= sanitize($license['issue_date']) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Expiration Date:</td>
                                    <td class="fw-bold <?= strtotime($license['expiry_date']) < time() ? 'text-danger' : 'text-success' ?>">
                                        <?= sanitize($license['expiry_date']) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Location:</td>
                                    <td><?= sanitize($license['address']) ?>, <?= sanitize($license['municipality']) ?>, <?= sanitize($license['district']) ?></td>
                                </tr>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
