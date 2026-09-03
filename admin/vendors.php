<?php
// SmartGov Market - Admin Vendor Management
// File: admin/vendors.php

$pageTitle = "Vendor Administration";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();

// Action handler (Suspend / Reactivate)
if (isset($_GET['action_id']) && isset($_GET['status'])) {
    $vid = (int)$_GET['action_id'];
    $status = sanitize($_GET['status']);
    
    $update = $db->prepare("UPDATE vendors SET status = ? WHERE id = ?");
    $update->execute([$status, $vid]);
    
    logAudit('Vendor Status Updated', 'Vendor', $vid, "Admin updated vendor ID {$vid} status to {$status}");
    setFlashMessage('success', "Vendor status updated to {$status}.");
    redirect('admin/vendors.php');
}

$vendors = $db->query("SELECT v.*, u.full_name as owner_name, u.email, u.phone, l.license_no 
                       FROM vendors v
                       JOIN users u ON v.user_id = u.id
                       LEFT JOIN licenses l ON v.id = l.vendor_id AND l.status = 'VALID'
                       ORDER BY v.created_at DESC")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'vendors'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-store text-primary me-2"></i>Vendor Administration & Licenses</h4>
                    <span class="badge bg-primary rounded-pill fs-6"><?= count($vendors) ?> Vendors</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Business Name</th>
                                <th>Owner</th>
                                <th>Type</th>
                                <th>License No</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vendors as $v): ?>
                                <tr>
                                    <td>#<?= $v['id'] ?></td>
                                    <td class="fw-bold"><?= sanitize($v['business_name']) ?></td>
                                    <td><?= sanitize($v['owner_name']) ?><br><small class="text-muted"><?= sanitize($v['phone']) ?></small></td>
                                    <td><span class="badge bg-light text-dark border"><?= sanitize($v['business_type']) ?></span></td>
                                    <td>
                                        <?php if ($v['license_no']): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $v['license_no'] ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">No License</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $v['status'] === 'Approved' ? 'bg-success' : ($v['status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                            <?= sanitize($v['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($v['status'] === 'Approved'): ?>
                                            <a href="admin/vendors.php?action_id=<?= $v['id'] ?>&status=Suspended" class="btn btn-xs btn-outline-danger" onclick="return confirm('Suspend vendor?');">Suspend</a>
                                        <?php elseif ($v['status'] === 'Suspended'): ?>
                                            <a href="admin/vendors.php?action_id=<?= $v['id'] ?>&status=Approved" class="btn btn-xs btn-outline-success">Reactivate</a>
                                        <?php else: ?>
                                            <a href="officer/application-details.php?id=<?= $v['id'] ?>" class="btn btn-xs btn-primary">Review App</a>
                                        <?php endif; ?>
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
