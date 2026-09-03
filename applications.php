<?php
// SmartGov Market - Customer Government Applications Tracker
// File: applications.php

$pageTitle = "My Government Applications";
require_once __DIR__ . '/includes/header.php';
requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT sa.*, gs.service_name, d.department_name 
                      FROM service_applications sa
                      JOIN government_services gs ON sa.service_id = gs.id
                      JOIN departments d ON gs.department_id = d.id
                      WHERE sa.user_id = ?
                      ORDER BY sa.submitted_at DESC");
$stmt->execute([$user_id]);
$applications = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'applications'; include __DIR__ . '/includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4 flex-wrap gap-2">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-file-lines text-primary me-2"></i>My Digital Government Applications</h4>
                    <a href="government-services.php" class="btn btn-sm btn-primary fw-bold"><i class="fa-solid fa-plus me-1"></i>Apply for New Service</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>App No</th>
                                <th>Service Name</th>
                                <th>Department</th>
                                <th>Submitted Date</th>
                                <th>Document</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($applications) === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No government service applications submitted yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= sanitize($app['application_no']) ?></td>
                                        <td>
                                            <strong class="d-block text-dark"><?= sanitize($app['service_name']) ?></strong>
                                            <?php if (!empty($app['remarks'])): ?>
                                                <small class="text-muted text-truncate d-block" style="max-width:200px;"><?= sanitize($app['remarks']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= sanitize($app['department_name']) ?></span></td>
                                        <td><?= date('M d, Y', strtotime($app['submitted_at'])) ?></td>
                                        <td>
                                            <?php if (!empty($app['document_path'])): ?>
                                                <a href="<?= BASE_URL . sanitize($app['document_path']) ?>" target="_blank" class="btn btn-xs btn-outline-primary fw-semibold">
                                                    <i class="fa-solid fa-file-arrow-down me-1"></i>View File
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted fs-7">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $app['status'] === 'Approved' ? 'bg-success' : ($app['status'] === 'Submitted' ? 'bg-warning text-dark' : ($app['status'] === 'Processing' ? 'bg-info text-dark' : 'bg-danger')) ?>">
                                                <?= sanitize($app['status']) ?>
                                            </span>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
