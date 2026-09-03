<?php
// SmartGov Market - Officer Issued Licenses Directory
// File: officer/licenses.php

$pageTitle = "Issued Digital Licenses";
require_once __DIR__ . '/../includes/header.php';
requireRole(['officer', 'admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $lic_id = (int)($_POST['license_id'] ?? 0);
    $act = $_POST['action'];
    
    if ($act === 'approve_renewal') {
        approveLicenseRenewal($lic_id);
        setFlashMessage('success', 'License renewal approved successfully.');
        redirect('officer/licenses.php');
    } elseif ($act === 'suspend') {
        $db->prepare("UPDATE licenses SET status = 'SUSPENDED' WHERE id = ?")->execute([$lic_id]);
        setFlashMessage('warning', 'License status updated to SUSPENDED.');
        redirect('officer/licenses.php');
    } elseif ($act === 'reactivate') {
        $db->prepare("UPDATE licenses SET status = 'VALID' WHERE id = ?")->execute([$lic_id]);
        setFlashMessage('success', 'License status reactivated to VALID.');
        redirect('officer/licenses.php');
    }
}

$licenses = $db->query("SELECT l.*, v.business_name, v.business_type, v.municipality, v.district, u.full_name as owner_name 
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
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-certificate text-primary me-2"></i>Government Digital Licenses Registry</h4>
                    <span class="badge bg-success rounded-pill fs-6"><?= count($licenses) ?> Licenses</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>License No</th>
                                <th>Business Name</th>
                                <th>Owner</th>
                                <th>Issue Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Renewal</th>
                                <th>Actions</th>
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
                                    <td>
                                        <span class="badge <?= $lic['status'] === 'VALID' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= sanitize($lic['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($lic['renewal_status'] === 'Requested'): ?>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="action" value="approve_renewal">
                                                <input type="hidden" name="license_id" value="<?= $lic['id'] ?>">
                                                <button type="submit" class="btn btn-xs btn-warning text-dark fw-bold"><i class="fa-solid fa-rotate-right me-1"></i>Approve Renewal</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border"><?= sanitize($lic['renewal_status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="<?= BASE_URL ?>verify.php?license_no=<?= urlencode($lic['license_no']) ?>" target="_blank" class="btn btn-xs btn-dark">
                                                <i class="fa-solid fa-qrcode"></i>
                                            </a>
                                            <?php if ($lic['status'] === 'VALID'): ?>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="action" value="suspend">
                                                    <input type="hidden" name="license_id" value="<?= $lic['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-outline-danger" onclick="return confirm('Suspend license?');"><i class="fa-solid fa-ban"></i></button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="action" value="reactivate">
                                                    <input type="hidden" name="license_id" value="<?= $lic['id'] ?>">
                                                    <button type="submit" class="btn btn-xs btn-outline-success"><i class="fa-solid fa-check"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
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
