<?php
// SmartGov Market - Admin Product Categories CRUD
// File: admin/categories.php

$pageTitle = "Category Management";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    
    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        $stmt = $db->prepare("INSERT INTO categories (name, slug, description, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute([$name, $slug, $description]);
        
        logAudit('Category Created', 'Category', $db->lastInsertId(), "Admin created category {$name}");
        setFlashMessage('success', "Category '{$name}' created.");
        redirect('admin/categories.php');
    }
}

// Toggle Category Active Status
if (isset($_GET['toggle_id'])) {
    $cid = (int)$_GET['toggle_id'];
    $cStmt = $db->prepare("SELECT is_active, name FROM categories WHERE id = ?");
    $cStmt->execute([$cid]);
    $cat = $cStmt->fetch();
    if ($cat) {
        $newStat = ($cat['is_active'] == 1) ? 0 : 1;
        $uStmt = $db->prepare("UPDATE categories SET is_active = ? WHERE id = ?");
        $uStmt->execute([$newStat, $cid]);
        setFlashMessage('success', "Category status updated.");
        redirect('admin/categories.php');
    }
}

$categories = $db->query("SELECT c.*, COUNT(p.id) as product_count 
                          FROM categories c 
                          LEFT JOIN products p ON c.id = p.category_id 
                          GROUP BY c.id 
                          ORDER BY c.name ASC")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'categories'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-tags text-primary me-2"></i>Product Categories CRUD</h4>
                    <button class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="collapse" data-bs-target="#addCatForm">
                        <i class="fa-solid fa-plus me-1"></i>Add New Category
                    </button>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <!-- Add Category Form -->
                <div class="collapse mb-4" id="addCatForm">
                    <form method="POST" action="" class="p-3 bg-light rounded border">
                        <input type="hidden" name="action" value="add_category">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category Name *</label>
                                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Dairy & Livestock" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Description</label>
                                <input type="text" name="description" class="form-control form-control-sm" placeholder="Brief category summary...">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-success fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i>Save Category</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Products Count</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $c): ?>
                                <tr>
                                    <td>#<?= $c['id'] ?></td>
                                    <td class="fw-bold text-dark"><?= sanitize($c['name']) ?></td>
                                    <td><?= sanitize($c['description']) ?></td>
                                    <td><span class="badge bg-primary rounded-pill"><?= $c['product_count'] ?> Products</span></td>
                                    <td>
                                        <span class="badge <?= $c['is_active'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $c['is_active'] == 1 ? 'Active' : 'Disabled' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="admin/categories.php?toggle_id=<?= $c['id'] ?>" class="btn btn-xs <?= $c['is_active'] == 1 ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                            <?= $c['is_active'] == 1 ? 'Disable' : 'Enable' ?>
                                        </a>
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
