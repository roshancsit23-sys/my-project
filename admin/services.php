<?php
// SmartGov Market - Admin Government Services Management
// File: admin/services.php

$pageTitle = "Government Services Management";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();
$departments = $db->query("SELECT * FROM departments WHERE is_active=1")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_service') {
    $dept_id = (int)$_POST['department_id'];
    $name = sanitize($_POST['service_name'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');
    $req = sanitize($_POST['requirements'] ?? '');
    $time = sanitize($_POST['processing_time'] ?? '');
    $fee = filter_var($_POST['fee'] ?? 0, FILTER_VALIDATE_FLOAT);
    
    if (empty($name) || $dept_id <= 0) {
        $error = 'Service name and department selection are required.';
    } else {
        $stmt = $db->prepare("INSERT INTO government_services (department_id, service_name, description, requirements, processing_time, fee, status) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$dept_id, $name, $desc, $req, $time, $fee]);
        setFlashMessage('success', "Government Service '{$name}' created.");
        redirect('admin/services.php');
    }
}

$services = $db->query("SELECT gs.*, d.department_name 
                        FROM government_services gs
                        JOIN departments d ON gs.department_id = d.id
                        ORDER BY gs.id DESC")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'services'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-landmark text-primary me-2"></i>Government Services Administration</h4>
                    <button class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="collapse" data-bs-target="#addSrvForm">
                        <i class="fa-solid fa-plus me-1"></i>Add New Service
                    </button>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <div class="collapse mb-4" id="addSrvForm">
                    <form method="POST" action="" class="p-3 bg-light rounded border">
                        <input type="hidden" name="action" value="add_service">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Department *</label>
                                <select name="department_id" class="form-select form-select-sm" required>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= sanitize($d['department_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Service Name *</label>
                                <input type="text" name="service_name" class="form-control form-control-sm" placeholder="e.g. Export Permit Clearance" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Processing Time</label>
                                <input type="text" name="processing_time" class="form-control form-control-sm" placeholder="2 Business Days">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fee (रु)</label>
                                <input type="number" step="0.01" name="fee" class="form-control form-control-sm" placeholder="500.00">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Requirements</label>
                                <input type="text" name="requirements" class="form-control form-control-sm" placeholder="Required documents list...">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Service Description</label>
                                <textarea name="description" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-success fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i>Save Service</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Service Name</th>
                                <th>Department</th>
                                <th>Processing Time</th>
                                <th>Fee</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $s): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?= sanitize($s['service_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= sanitize($s['department_name']) ?></span></td>
                                    <td><?= sanitize($s['processing_time']) ?></td>
                                    <td class="fw-bold text-success"><?= $s['fee'] > 0 ? formatCurrency($s['fee']) : 'FREE' ?></td>
                                    <td><span class="badge bg-success">Active</span></td>
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
