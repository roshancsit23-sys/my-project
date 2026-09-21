<?php
// SmartGov Market - Vendor Digital License Display & Printable Certificate
// File: vendor/license.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/license_generator.php';

requireRole('vendor');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

$vStmt = $db->prepare("SELECT id, business_name, status FROM vendors WHERE user_id = ?");
$vStmt->execute([$user_id]);
$vendor = $vStmt->fetch();

$license = $vendor ? getVendorLicense($vendor['id']) : null;

// Process renewal action BEFORE header.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_renewal' && $license) {
    requestLicenseRenewal($license['id']);
    setFlashMessage('success', '✓ License renewal request submitted to government officers.');
    redirect('vendor/license.php');
}

$pageTitle = "Digital Business License";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3 no-print">
            <?php $activePage = 'license'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <?php if (!$license): ?>
                <div class="card card-custom p-5 shadow-sm border-0 text-center">
                    <div class="bg-warning-subtle text-warning d-inline-flex p-3 rounded-circle mb-3 mx-auto">
                        <i class="fa-solid fa-award fs-1"></i>
                    </div>
                    <h4 class="fw-bold">Digital Business License Not Available</h4>
                    <p class="text-muted">Your vendor account is currently <strong><?= sanitize($vendor['status'] ?? 'Pending') ?></strong>. Once verified by government administration, your official digital business license certificate and QR code will appear here.</p>
                    <div>
                        <a href="<?= BASE_URL ?>vendor/dashboard.php" class="btn btn-primary fw-bold px-4">Go to Vendor Dashboard</a>
                    </div>
                </div>
            <?php else: ?>
                <?php 
                    $daysToExpiry = (int)((strtotime($license['expiry_date']) - time()) / 86400);
                ?>
                <?php if ($daysToExpiry <= 60 || $license['status'] === 'EXPIRED'): ?>
                    <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3 no-print">
                        <div>
                            <strong><i class="fa-solid fa-triangle-exclamation me-2"></i>License Expiry Warning:</strong>
                            <?= $daysToExpiry < 0 ? 'Your license has expired!' : "Your license expires in {$daysToExpiry} days." ?>
                            <?php if ($license['renewal_status'] === 'Requested'): ?>
                                <span class="badge bg-info text-dark ms-2">Renewal Under Review</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($license['renewal_status'] !== 'Requested'): ?>
                            <form method="POST" action="" class="mb-0">
                                <input type="hidden" name="action" value="request_renewal">
                                <button type="submit" class="btn btn-sm btn-dark fw-bold"><i class="fa-solid fa-rotate-right me-1"></i>Apply for Renewal</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-3 no-print flex-wrap gap-2">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-award text-success me-2"></i>Digital Business License</h4>
                    <div>
                        <button onclick="window.print()" class="btn btn-primary fw-bold shadow-sm me-2"><i class="fa-solid fa-print me-1"></i>Print Certificate</button>
                        <a href="<?= BASE_URL ?>verify.php?license_no=<?= urlencode($license['license_no']) ?>" target="_blank" class="btn btn-dark fw-bold"><i class="fa-solid fa-qrcode me-1"></i>Test Public QR Verification</a>
                    </div>
                </div>

                <!-- Printable Certificate Container -->
                <div class="printable-area">
                    <div class="license-certificate shadow-lg p-4 p-md-5 rounded-4 bg-white border border-2 border-primary border-opacity-25">
                        <div class="text-center license-header pb-4 border-bottom mb-4">
                            <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
                                <i class="fa-solid fa-building-columns text-primary fs-1"></i>
                                <div>
                                    <h2 class="fw-bold text-dark mb-0" style="font-family: 'Times New Roman', serif; letter-spacing: 1px;">GOVERNMENT OF NEPAL / LOCAL MUNICIPALITY</h2>
                                    <h5 class="fw-semibold text-primary mb-0">DEPARTMENT OF COMMERCE & INDUSTRY</h5>
                                </div>
                            </div>
                            <h3 class="fw-bold text-uppercase mt-3 mb-1" style="letter-spacing: 2px; color: #1a2b4c;">DIGITAL BUSINESS LICENSE CERTIFICATE</h3>
                            <span class="badge bg-success fs-6 px-3 py-1">OFFICIALLY REGISTERED & VERIFIED</span>
                        </div>

                        <div class="row align-items-center my-4">
                            <div class="col-md-8">
                                <table class="table table-borderless fs-6 mb-0">
                                    <tr>
                                        <td class="fw-bold text-muted" width="40%">License Number:</td>
                                        <td class="fw-bold text-primary fs-5"><?= sanitize($license['license_no']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Business Name:</td>
                                        <td class="fw-bold text-dark fs-5"><?= sanitize($license['business_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Owner / Licensee:</td>
                                        <td class="fw-semibold"><?= sanitize($license['owner_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Business Category / Type:</td>
                                        <td><span class="badge bg-light text-dark border"><?= sanitize($license['business_type']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Registered Address:</td>
                                        <td><?= sanitize($license['address']) ?>, <?= sanitize($license['municipality']) ?>, <?= sanitize($license['district']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Issuing Authority:</td>
                                        <td><?= sanitize($license['issuing_authority']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Date of Issue:</td>
                                        <td><?= date('F d, Y', strtotime($license['issue_date'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-muted">Expiration Date:</td>
                                        <td class="fw-bold text-success"><?= date('F d, Y', strtotime($license['expiry_date'])) ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-4 text-center">
                                <div class="p-3 bg-light rounded-3 border d-inline-block shadow-sm">
                                    <div id="vendor-cert-qr" style="width: 140px; height: 140px; margin: 0 auto;"></div>
                                    <small class="text-muted d-block mt-2 fw-semibold" style="font-size: 0.75rem;">SCAN TO VERIFY</small>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-3 d-flex justify-content-between align-items-end mt-4">
                            <div class="small text-muted">
                                <i class="fa-solid fa-lock me-1"></i>Cryptographically verified digital seal.<br>
                                SmartGov Market Integrated E-Governance Platform
                            </div>
                            <div class="text-center" style="width: 200px;">
                                <div class="fw-bold border-bottom pb-1 text-primary">Chief Licensing Officer</div>
                                <small class="text-muted">Authorized Signature</small>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const qrEl = document.getElementById('vendor-cert-qr');
                    if (qrEl) {
                        const verifyUrl = "<?= BASE_URL ?>verify.php?license_no=<?= urlencode($license['license_no']) ?>";
                        if (typeof QRCode !== 'undefined') {
                            new QRCode(qrEl, {
                                text: verifyUrl,
                                width: 140,
                                height: 140,
                                colorDark: "#0b1d3a",
                                colorLight: "#ffffff",
                                correctLevel: QRCode.CorrectLevel.H
                            });
                        } else {
                            qrEl.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=${encodeURIComponent(verifyUrl)}" width="140" height="140" alt="QR Code">`;
                        }
                    }
                });
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
