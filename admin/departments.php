<?php
// SmartGov Market - Admin Departments Management
// File: admin/departments.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$db = getDBConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_dept') {
    $name = sanitize($_POST['department_name'] ?? '');
    $code = strtoupper(sanitize($_POST['code'] ?? ''));
    $desc = sanitize($_POST['description'] ?? '');
    
    if (empty($name) || empty($code)) {
        $error = 'Department Name and Code are required.';
    } else {
        $stmt = $db->prepare("INSERT INTO departments (department_name, code, description, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$name, $code, $desc]);
        setFlashMessage('success', "Department '{$name}' created successfully.");
        redirect('admin/departments.php');
    }
}

$pageTitle = "Departments Management";
require_once __DIR__ . '/../includes/header.php';

$departments = $db->query("SELECT * FROM departments ORDER BY id ASC")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'departments'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-sitemap text-primary me-2"></i>Municipal Departments Directory</h4>
                    <button class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="collapse" data-bs-target="#addDeptForm">
                        <i class="fa-solid fa-plus me-1"></i>Add Department
                    </button>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <div class="collapse mb-4" id="addDeptForm">
                    <form method="POST" action="" class="p-3 bg-light rounded border">
                        <input type="hidden" name="action" value="add_dept">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Department Name *</label>
                                <input type="text" name="department_name" class="form-control form-control-sm" placeholder="e.g. Department of Urban Development" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Dept Code *</label>
                                <input type="text" name="code" class="form-control form-control-sm" placeholder="DUD" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Description</label>
                                <input type="text" name="description" class="form-control form-control-sm" placeholder="Mandate and scope...">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-success fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i>Save Department</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Department Name</th>
                                <th>Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $d): ?>
                                <tr>
                                    <td><span class="badge bg-primary"><?= sanitize($d['code']) ?></span></td>
                                    <td class="fw-bold text-dark"><?= sanitize($d['department_name']) ?></td>
                                    <td><?= sanitize($d['description']) ?></td>
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
