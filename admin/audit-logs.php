<?php
// SmartGov Market - System Security Audit Logs
// File: admin/audit-logs.php

$pageTitle = "System Audit Logs";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();
$logs = $db->query("SELECT a.*, u.full_name as user_name, u.email as user_email 
                    FROM audit_logs a
                    LEFT JOIN users u ON a.user_id = u.id
                    ORDER BY a.id DESC LIMIT 100")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'audit-logs'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Security & System Audit Logs</h4>
                    <span class="badge bg-secondary rounded-pill fs-6">Recent 100 Events</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Entity</th>
                                <th>IP Address</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $l): ?>
                                <tr>
                                    <td class="text-muted"><?= date('M d, Y H:i:s', strtotime($l['created_at'])) ?></td>
                                    <td class="fw-bold text-dark"><?= sanitize($l['user_name'] ?: 'System / Guest') ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= sanitize($l['action']) ?></span></td>
                                    <td><code><?= sanitize($l['entity']) ?></code></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary"><?= sanitize($l['ip_address']) ?></span></td>
                                    <td class="text-muted" style="max-width:250px;"><?= sanitize($l['description']) ?></td>
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
