<?php
// SmartGov Market - Public Digital License QR Verification Page
// File: verify.php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/license_generator.php';

$pageTitle = "Public License Verification System";
require_once __DIR__ . '/includes/header.php';

$license_no = sanitize($_GET['license_no'] ?? $_GET['code'] ?? $_GET['license'] ?? '');
$license = null;
$verificationStatus = 'Invalid'; // Verified, Valid, Suspended, Expired, Invalid

if (!empty($license_no)) {
    $license = verifyLicenseByNo($license_no);
    if ($license) {
        $now = time();
        $expiryTime = strtotime($license['expiry_date']);

        if ($license['vendor_status'] === 'Suspended' || $license['status'] === 'SUSPENDED') {
            $verificationStatus = 'Suspended';
        } elseif ($expiryTime < $now || $license['status'] === 'EXPIRED') {
            $verificationStatus = 'Expired';
        } elseif (in_array($license['vendor_status'], ['Approved', 'Verified'], true) && $license['status'] === 'VALID') {
            $verificationStatus = 'Valid';
        } else {
            $verificationStatus = 'Under Review';
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card card-custom p-4 p-md-5 shadow-lg border-0 mb-4">
                <div class="text-center mb-4">
                    <img src="<?= BASE_URL ?>assets/images/nepal-emblem.svg" alt="Government of Nepal" height="80" class="mb-3">
                    <h3 class="fw-bold mb-1"><?= __('verify_title', 'Official Digital Business License Verification') ?></h3>
                    <p class="text-muted small">Government of Nepal / Local Municipality • HATIYA Digital Platform</p>
                </div>

                <!-- Verification Search Form -->
                <form method="GET" action="verify.php" class="row g-2 mb-4">
                    <div class="col-8 col-sm-9">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-barcode text-muted"></i></span>
                            <input type="text" name="license_no" class="form-control fs-6" placeholder="Enter License No (e.g. LIC-2026-000101)" value="<?= sanitize($license_no) ?>" required>
                        </div>
                    </div>
                    <div class="col-4 col-sm-3">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold fs-6 shadow-sm">
                            <i class="fa-solid fa-search me-1"></i><?= __('verify_btn', 'Verify') ?>
                        </button>
                    </div>
                </form>

                <?php if (!empty($license_no)): ?>
                    <?php if (!$license): ?>
                        <!-- Invalid / Not Found State -->
                        <div class="alert alert-danger p-4 text-center border-0 shadow-sm rounded-4">
                            <div class="mb-3">
                                <i class="fa-solid fa-triangle-exclamation fs-1 text-danger"></i>
                            </div>
                            <h4 class="fw-bold text-danger mb-2">INVALID LICENSE</h4>
                            <p class="mb-3 text-muted">The license number <strong class="text-dark"><?= sanitize($license_no) ?></strong> was not found in the official government registry.</p>
                            <span class="badge bg-danger fs-6 px-4 py-2 text-uppercase">
                                <i class="fa-solid fa-circle-xmark me-1"></i>INVALID LICENSE
                            </span>
                        </div>
                    <?php else: 
                        $statusBadgeClass = 'bg-success';
                        $statusBadgeText = 'VERIFIED BUSINESS';
                        $statusIcon = 'fa-shield-halved';
                        if ($verificationStatus === 'Suspended') {
                            $statusBadgeClass = 'bg-danger';
                            $statusBadgeText = 'LICENSE SUSPENDED';
                            $statusIcon = 'fa-ban';
                        } elseif ($verificationStatus === 'Expired') {
                            $statusBadgeClass = 'bg-warning text-dark';
                            $statusBadgeText = 'LICENSE EXPIRED';
                            $statusIcon = 'fa-clock';
                        }
                    ?>
                        <!-- Official Verified Certificate Card -->
                        <div class="card p-4 border-2 rounded-4 shadow-sm <?= $verificationStatus === 'Valid' ? 'border-success bg-white' : 'border-danger bg-white' ?>">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom flex-wrap gap-2">
                                <div>
                                    <span class="badge bg-primary text-uppercase px-2 py-1 mb-1">SmartGov Digital Verification</span>
                                    <h5 class="fw-bold mb-0 text-dark"><?= sanitize($license['business_name']) ?></h5>
                                </div>
                                <div class="text-end">
                                    <span class="badge <?= $statusBadgeClass ?> fs-6 px-3 py-2 text-uppercase">
                                        <i class="fa-solid <?= $statusIcon ?> me-1"></i><?= $statusBadgeText ?>
                                    </span>
                                </div>
                            </div>

                            <div class="row g-4 align-items-center mb-4">
                                <div class="col-md-8">
                                    <table class="table table-sm table-borderless small mb-0 fs-6">
                                        <tr>
                                            <td class="fw-bold text-muted" width="42%">Business Name:</td>
                                            <td class="fw-bold text-dark fs-5"><?= sanitize($license['business_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Owner Name:</td>
                                            <td class="fw-semibold"><?= sanitize($license['owner_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">License Number:</td>
                                            <td class="fw-bold text-primary"><?= sanitize($license['license_no']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Vendor ID:</td>
                                            <td><span class="badge bg-light text-dark border">#<?= (int)$license['vendor_id'] ?></span></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Business Category:</td>
                                            <td><span class="badge bg-info-subtle text-dark border"><?= sanitize($license['business_type']) ?></span></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Registered Address:</td>
                                            <td><?= sanitize($license['address']) ?>, <?= sanitize($license['municipality']) ?>, <?= sanitize($license['district']) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Verification Status:</td>
                                            <td>
                                                <span class="fw-bold <?= in_array($license['vendor_status'], ['Approved', 'Verified'], true) ? 'text-success' : 'text-danger' ?>">
                                                    <?= sanitize($license['vendor_status'] ?: 'Verified') ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Verification / Issue Date:</td>
                                            <td><?= date('F d, Y', strtotime($license['issue_date'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">License Validity:</td>
                                            <td class="fw-bold <?= strtotime($license['expiry_date']) < time() ? 'text-danger' : 'text-success' ?>">
                                                Valid until <?= date('F d, Y', strtotime($license['expiry_date'])) ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold text-muted">Issuing Authority:</td>
                                            <td><?= sanitize($license['issuing_authority']) ?></td>
                                        </tr>
                                    </table>
                                </div>

                                <!-- Dynamically Rendered Live QR Code on Public Certificate -->
                                <div class="col-md-4 text-center">
                                    <div class="p-3 bg-light rounded-3 border d-inline-block shadow-sm">
                                        <div id="verify-qr-code" style="width: 150px; height: 150px; margin: 0 auto;"></div>
                                        <small class="text-muted d-block mt-2 fw-semibold">Authenticated QR Code</small>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center flex-wrap gap-2 small text-muted">
                                <div>
                                    <i class="fa-solid fa-shield-halved text-success me-1"></i> Digitally Signed & Authenticated by SmartGov Market Licensing Authority.
                                </div>
                                <div>
                                    <a href="<?= BASE_URL ?>products.php?vendor=<?= (int)$license['vendor_id'] ?>" class="btn btn-sm btn-outline-primary fw-bold">
                                        <i class="fa-solid fa-store me-1"></i>View Vendor Store
                                    </a>
                                </div>
                            </div>
                        </div>

                        <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const qrContainer = document.getElementById('verify-qr-code');
                            if (qrContainer) {
                                const currentUrl = window.location.href;
                                if (typeof QRCode !== 'undefined') {
                                    new QRCode(qrContainer, {
                                        text: currentUrl,
                                        width: 150,
                                        height: 150,
                                        colorDark: "#0b1d3a",
                                        colorLight: "#ffffff",
                                        correctLevel: QRCode.CorrectLevel.H
                                    });
                                } else {
                                    qrContainer.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(currentUrl)}" width="150" height="150" alt="QR Code">`;
                                }
                            }
                        });
                        </script>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
