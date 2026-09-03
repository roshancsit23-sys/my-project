<?php
// SmartGov Market - Officer Vendor Applications List
// File: officer/vendor-applications.php

$pageTitle = "Vendor Applications Management";
require_once __DIR__ . '/../includes/header.php';
requireRole(['officer', 'admin']);

$db = getDBConnection();

$statusFilter = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');

$query = "SELECT va.*, v.business_name, v.business_type, v.municipality, v.district, u.full_name as owner_name, u.email, u.phone 
          FROM vendor_applications va
          JOIN vendors v ON va.vendor_id = v.id
          JOIN users u ON v.user_id = u.id
          WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $query .= " AND va.status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $query .= " AND (va.application_no LIKE ? OR v.business_name LIKE ? OR u.full_name LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$query .= " ORDER BY va.submitted_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$applications = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'applications'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-file-signature text-primary me-2"></i>Vendor Applications</h4>
                    <span class="badge bg-primary rounded-pill fs-6"><?= count($applications) ?> Total</span>
                </div>

                <!-- Filters -->
                <form method="GET" action="" class="row g-2 mb-4">
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search application no, business, owner..." value="<?= sanitize($search) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- All Statuses --</option>
                            <option value="Submitted" <?= $statusFilter === 'Submitted' ? 'selected' : '' ?>>Submitted</option>
                            <option value="Under Review" <?= $statusFilter === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
                            <option value="Correction Required" <?= $statusFilter === 'Correction Required' ? 'selected' : '' ?>>Correction Required</option>
                            <option value="Approved" <?= $statusFilter === 'Approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-filter me-1"></i>Filter</button>
                    </div>
                </form>

                <!-- Applications Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>App No</th>
                                <th>Business Name</th>
                                <th>Owner</th>
                                <th>Location</th>
                                <th>Submitted Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($applications) === 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No vendor applications found matching criteria.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= sanitize($app['application_no']) ?></td>
                                        <td class="fw-semibold"><?= sanitize($app['business_name']) ?></td>
                                        <td><?= sanitize($app['owner_name']) ?></td>
                                        <td><?= sanitize($app['municipality']) ?>, <?= sanitize($app['district']) ?></td>
                                        <td><?= date('M d, Y', strtotime($app['submitted_at'])) ?></td>
                                        <td>
                                            <span class="badge <?= $app['status'] === 'Approved' ? 'bg-success' : ($app['status'] === 'Submitted' ? 'bg-warning text-dark' : ($app['status'] === 'Rejected' ? 'bg-danger' : 'bg-secondary')) ?>">
                                                <?= sanitize($app['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>officer/application-details.php?id=<?= $app['id'] ?>" class="btn btn-xs btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i>Inspect & Action</a>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
