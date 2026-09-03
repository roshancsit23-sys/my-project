<?php
// SmartGov Market - Admin Complaints Oversight
// File: admin/complaints.php

$pageTitle = "Complaints Oversight";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();
$complaints = $db->query("SELECT c.*, u.full_name as complainant_name, d.department_name 
                          FROM complaints c
                          JOIN users u ON c.user_id = u.id
                          LEFT JOIN departments d ON c.department_id = d.id
                          ORDER BY c.created_at DESC")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'complaints'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Public Complaints System Oversight</h4>
                    <a href="map-view.php?type=complaint" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-map-location-dot me-1"></i>View GIS Heatmap</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Complaint No</th>
                                <th>Complainant</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $c): ?>
                                <tr>
                                    <td class="fw-bold text-danger"><?= sanitize($c['complaint_no']) ?></td>
                                    <td><?= sanitize($c['complainant_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= sanitize($c['category']) ?></span></td>
                                    <td><span class="badge bg-warning text-dark"><?= sanitize($c['priority']) ?></span></td>
                                    <td><?= sanitize($c['department_name'] ?: 'Consumer Protection') ?></td>
                                    <td><span class="badge bg-primary"><?= sanitize($c['status']) ?></span></td>
                                    <td>
                                        <a href="complaint-details.php?id=<?= $c['id'] ?>" class="btn btn-xs btn-primary"><i class="fa-solid fa-eye me-1"></i>Inspect</a>
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
