<?php
// SmartGov Market - Admin Digital Licenses Directory
// File: admin/licenses.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/license_generator.php';

requireRole('admin');

$db = getDBConnection();

$licenses = $db->query("SELECT l.*, v.id as vendor_id, v.business_name, v.business_type, v.address, v.municipality, v.district, v.status as vendor_status,
                               u.full_name as owner_name, u.email, u.phone 
                        FROM licenses l
                        JOIN vendors v ON l.vendor_id = v.id
                        JOIN users u ON v.user_id = u.id
                        ORDER BY l.id DESC")->fetchAll();

$pageTitle = "Digital Licenses Oversight";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'licenses'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="fa-solid fa-id-card text-primary me-2"></i>Issued Digital Business Licenses</h4>
                        <p class="text-muted small mb-0">Every verified business possesses a government-authenticated digital license and dynamically generated QR code verification certificate.</p>
                    </div>
                    <span class="badge bg-success rounded-pill fs-6 px-3 py-2"><?= count($licenses) ?> Active Licenses</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>License No</th>
                                <th>Business Name</th>
                                <th>Owner Name</th>
                                <th>Category</th>
                                <th>Issue Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th class="text-end">Verification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($licenses) === 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No business licenses have been generated yet. Verify a vendor in "Vendors & Approvals" to issue licenses.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($licenses as $lic): 
                                    $isExpired = strtotime($lic['expiry_date']) < time();
                                    $licStatus = $isExpired ? 'EXPIRED' : $lic['status'];
                                    $qrTargetUrl = BASE_URL . "verify.php?license_no=" . urlencode($lic['license_no']);
                                ?>
                                    <tr>
                                        <td class="fw-bold text-primary fs-6"><?= sanitize($lic['license_no']) ?></td>
                                        <td class="fw-semibold text-dark"><?= sanitize($lic['business_name']) ?></td>
                                        <td><?= sanitize($lic['owner_name']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= sanitize($lic['business_type']) ?></span></td>
                                        <td><?= date('M d, Y', strtotime($lic['issue_date'])) ?></td>
                                        <td class="<?= $isExpired ? 'text-danger fw-bold' : '' ?>"><?= date('M d, Y', strtotime($lic['expiry_date'])) ?></td>
                                        <td>
                                            <span class="badge <?= $licStatus === 'VALID' ? 'bg-success' : ($licStatus === 'SUSPENDED' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                                <?= sanitize($licStatus) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-dark fw-semibold" 
                                                    onclick="showQrModal('<?= htmlspecialchars($lic['license_no'], ENT_QUOTES) ?>', 
                                                                         '<?= htmlspecialchars($lic['business_name'], ENT_QUOTES) ?>', 
                                                                         '<?= htmlspecialchars($lic['owner_name'], ENT_QUOTES) ?>', 
                                                                         '<?= htmlspecialchars($lic['business_type'], ENT_QUOTES) ?>', 
                                                                         '<?= htmlspecialchars($lic['address'] . ', ' . $lic['municipality'] . ', ' . $lic['district'], ENT_QUOTES) ?>', 
                                                                         '<?= htmlspecialchars($licStatus, ENT_QUOTES) ?>', 
                                                                         '<?= htmlspecialchars($qrTargetUrl, ENT_QUOTES) ?>')">
                                                <i class="fa-solid fa-qrcode me-1"></i>QR Verify
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic QR Verification Modal -->
<div class="modal fade" id="qrVerifyModal" tabindex="-1" aria-labelledby="qrVerifyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold" id="qrVerifyModalLabel">
                    <i class="fa-solid fa-qrcode text-primary me-2"></i>Official Digital License QR Code
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <!-- QR Canvas Container -->
                <div class="p-3 bg-white border rounded shadow-sm d-inline-block mb-3">
                    <div id="modal-qr-container" style="width: 200px; height: 200px; margin: 0 auto;"></div>
                </div>
                
                <h5 class="fw-bold text-dark mb-1" id="modal-business-name"></h5>
                <p class="text-muted small mb-2" id="modal-license-no"></p>
                <div class="mb-3" id="modal-status-badge"></div>

                <div class="text-start bg-light p-3 rounded border small mb-3">
                    <div class="row g-2">
                        <div class="col-4 text-muted fw-bold">Owner:</div>
                        <div class="col-8" id="modal-owner-name"></div>
                        <div class="col-4 text-muted fw-bold">Category:</div>
                        <div class="col-8" id="modal-category"></div>
                        <div class="col-4 text-muted fw-bold">Address:</div>
                        <div class="col-8" id="modal-address"></div>
                    </div>
                </div>

                <p class="small text-muted mb-0">
                    <i class="fa-solid fa-camera me-1"></i> Scan with any mobile camera to open official verification page.
                </p>
            </div>
            <div class="modal-footer bg-light border-top d-flex justify-content-between">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <a href="#" id="modal-verify-link" target="_blank" class="btn btn-primary btn-sm fw-bold">
                    <i class="fa-solid fa-external-link me-1"></i>Open Public Verification URL
                </a>
            </div>
        </div>
    </div>
</div>

<script>
let currentQrCode = null;

function showQrModal(licenseNo, businessName, ownerName, category, address, status, url) {
    document.getElementById('modal-business-name').textContent = businessName;
    document.getElementById('modal-license-no').textContent = 'License No: ' + licenseNo;
    document.getElementById('modal-owner-name').textContent = ownerName;
    document.getElementById('modal-category').textContent = category;
    document.getElementById('modal-address').textContent = address;
    document.getElementById('modal-verify-link').href = url;

    const badgeClass = status === 'VALID' ? 'bg-success' : (status === 'SUSPENDED' ? 'bg-warning text-dark' : 'bg-danger');
    document.getElementById('modal-status-badge').innerHTML = `<span class="badge ${badgeClass} fs-6 px-3 py-1"><i class="fa-solid fa-shield-check me-1"></i>${status}</span>`;

    const container = document.getElementById('modal-qr-container');
    container.innerHTML = '';

    if (typeof QRCode !== 'undefined') {
        currentQrCode = new QRCode(container, {
            text: url,
            width: 200,
            height: 200,
            colorDark: "#0b1d3a",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    } else {
        container.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(url)}" width="200" height="200" alt="QR Code">`;
    }

    const modal = new bootstrap.Modal(document.getElementById('qrVerifyModal'));
    modal.show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
