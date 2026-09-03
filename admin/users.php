<?php
// SmartGov Market - Admin Users & Role Management
// File: admin/users.php

$pageTitle = "User Management";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();

// Toggle active status
if (isset($_GET['toggle_id'])) {
    $uid = (int)$_GET['toggle_id'];
    if ($uid !== $_SESSION['user_id']) {
        $uStmt = $db->prepare("SELECT is_active, full_name FROM users WHERE id = ?");
        $uStmt->execute([$uid]);
        $u = $uStmt->fetch();
        if ($u) {
            $newStat = ($u['is_active'] == 1) ? 0 : 1;
            $update = $db->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $update->execute([$newStat, $uid]);
            
            logAudit('User Status Toggled', 'User', $uid, "Admin toggled user {$u['full_name']} active status to {$newStat}");
            setFlashMessage('success', "User '{$u['full_name']}' active status updated.");
            redirect('admin/users.php');
        }
    }
}

$search = sanitize($_GET['search'] ?? '');
$roleFilter = (int)($_GET['role_id'] ?? 0);

$query = "SELECT u.*, r.display_name as role_display 
          FROM users u 
          JOIN roles r ON u.role_id = r.id 
          WHERE 1=1";
$params = [];

if ($roleFilter > 0) {
    $query .= " AND u.role_id = ?";
    $params[] = $roleFilter;
}

if (!empty($search)) {
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term; $params[] = $term; $params[] = $term;
}

$query .= " ORDER BY u.id ASC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

$roles = $db->query("SELECT * FROM roles")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'users'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-users text-primary me-2"></i>User & Role Administration</h4>
                    <span class="badge bg-primary rounded-pill fs-6"><?= count($users) ?> Total Users</span>
                </div>

                <!-- Filters -->
                <form method="GET" action="" class="row g-2 mb-4">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search user name, email, phone..." value="<?= sanitize($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="role_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= $r['id'] == $roleFilter ? 'selected' : '' ?>><?= sanitize($r['display_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-filter me-1"></i>Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Name & Contact</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>#<?= $u['id'] ?></td>
                                    <td>
                                        <span class="fw-bold text-dark d-block"><?= sanitize($u['full_name']) ?></span>
                                        <small class="text-muted"><?= sanitize($u['phone']) ?></small>
                                    </td>
                                    <td><?= sanitize($u['email']) ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary border"><?= sanitize($u['role_display']) ?></span></td>
                                    <td><?= sanitize($u['municipality']) ?>, <?= sanitize($u['district']) ?></td>
                                    <td>
                                        <span class="badge <?= $u['is_active'] == 1 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $u['is_active'] == 1 ? 'Active' : 'Disabled' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                            <a href="admin/users.php?toggle_id=<?= $u['id'] ?>" class="btn btn-xs <?= $u['is_active'] == 1 ? 'btn-outline-danger' : 'btn-outline-success' ?>" onclick="return confirm('Toggle active status for user?');">
                                                <?= $u['is_active'] == 1 ? 'Deactivate' : 'Activate' ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Current Admin</span>
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
