<?php
// SmartGov Market - Admin Product Categories CRUD
// File: admin/categories.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$db = getDBConnection();
$error = '';

// Handle actions BEFORE header.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

    // 1. ADD CATEGORY
    if ($action === 'add_category') {
        $name = trim(sanitize($_POST['name'] ?? ''));
        $description = trim(sanitize($_POST['description'] ?? ''));
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        if (empty($name)) {
            $msg = 'Category name is required.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit(); }
            setFlashMessage('danger', $msg);
            redirect('admin/categories.php');
        }

        // Duplicate name check
        $dupStmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE LOWER(name) = LOWER(?)");
        $dupStmt->execute([$name]);
        if ($dupStmt->fetchColumn() > 0) {
            $msg = "Category already exists.";
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit(); }
            setFlashMessage('danger', $msg);
            redirect('admin/categories.php');
        }

        $insStmt = $db->prepare("INSERT INTO categories (name, slug, description, is_active, created_at) VALUES (?, ?, ?, 1, NOW())");
        $insStmt->execute([$name, $slug, $description]);
        $catId = $db->lastInsertId();

        logAudit('Category Created', 'Category', $catId, "Admin created category '{$name}'");
        $msg = "Category added successfully.";

        // Always save flash message so it appears immediately on page reload
        setFlashMessage('success', $msg);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $msg, 'category_id' => $catId]);
            exit();
        }

        redirect('admin/categories.php');
    }

    // 2. EDIT CATEGORY
    if ($action === 'edit_category') {
        $cid = (int)($_POST['category_id'] ?? 0);
        $name = trim(sanitize($_POST['name'] ?? ''));
        $description = trim(sanitize($_POST['description'] ?? ''));
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        if ($cid <= 0 || empty($name)) {
            $msg = 'Valid category ID and name are required.';
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit(); }
            setFlashMessage('danger', $msg);
            redirect('admin/categories.php');
        }

        // Check duplicate name on other categories
        $dupStmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE LOWER(name) = LOWER(?) AND id != ?");
        $dupStmt->execute([$name, $cid]);
        if ($dupStmt->fetchColumn() > 0) {
            $msg = "Category already exists.";
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit(); }
            setFlashMessage('danger', $msg);
            redirect('admin/categories.php');
        }

        $uStmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, is_active = ? WHERE id = ?");
        $uStmt->execute([$name, $slug, $description, $is_active, $cid]);

        logAudit('Category Updated', 'Category', $cid, "Admin updated category #{$cid} '{$name}'");
        $msg = "Category updated successfully.";

        setFlashMessage('success', $msg);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $msg]);
            exit();
        }

        redirect('admin/categories.php');
    }

    // 3. TOGGLE ACTIVE STATUS
    if ($action === 'toggle_status' || isset($_GET['toggle_id'])) {
        $cid = (int)($_POST['category_id'] ?? $_GET['toggle_id'] ?? 0);
        $cStmt = $db->prepare("SELECT id, name, is_active FROM categories WHERE id = ?");
        $cStmt->execute([$cid]);
        $cat = $cStmt->fetch();

        if ($cat) {
            $newStat = ($cat['is_active'] == 1) ? 0 : 1;
            $db->prepare("UPDATE categories SET is_active = ? WHERE id = ?")->execute([$newStat, $cid]);
            $actionLabel = $newStat == 1 ? 'enabled' : 'disabled';
            $msg = "Category '{$cat['name']}' has been {$actionLabel} successfully.";

            setFlashMessage('success', $msg);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $msg, 'is_active' => $newStat]);
                exit();
            }

            redirect('admin/categories.php');
        }
    }

    // 4. DELETE CATEGORY (Only if 0 products depend on it)
    if ($action === 'delete_category') {
        $cid = (int)($_POST['category_id'] ?? 0);
        $prodCount = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $prodCount->execute([$cid]);
        $count = (int)$prodCount->fetchColumn();

        if ($count > 0) {
            $msg = "Cannot delete category with assigned products.";
            if ($isAjax) { echo json_encode(['success' => false, 'message' => $msg]); exit(); }
            setFlashMessage('danger', $msg);
            redirect('admin/categories.php');
        }

        $dStmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $dStmt->execute([$cid]);

        logAudit('Category Deleted', 'Category', $cid, "Admin deleted category ID #{$cid}");
        $msg = "Category deleted successfully.";

        setFlashMessage('success', $msg);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $msg]);
            exit();
        }

        redirect('admin/categories.php');
    }
}

// Fetch all categories with product counts
$categories = $db->query("SELECT c.*, COUNT(p.id) as product_count 
                          FROM categories c 
                          LEFT JOIN products p ON c.id = p.category_id 
                          GROUP BY c.id 
                          ORDER BY c.name ASC")->fetchAll();

$pageTitle = "Category Management";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'categories'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="fa-solid fa-tags text-primary me-2"></i>Product Categories</h4>
                        <p class="text-muted small mb-0">Create, edit, toggle, or safely delete categories connected to the marketplace catalog and vendor product forms.</p>
                    </div>
                    <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="collapse" data-bs-target="#addCatForm">
                        <i class="fa-solid fa-plus me-1"></i>Add New Category
                    </button>
                </div>

                <!-- Add Category Collapsible Form -->
                <div class="collapse mb-4" id="addCatForm">
                    <form id="newCatForm" class="p-4 bg-light rounded-3 border shadow-sm" onsubmit="submitNewCategory(event)">
                        <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-plus-circle me-2"></i>Create New Product Category</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category Name *</label>
                                <input type="text" id="cat-name-input" name="name" class="form-control" placeholder="e.g. Dairy, Livestock & Poultry" required>
                                <small class="text-muted">Must be unique across the marketplace.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Description</label>
                                <input type="text" id="cat-desc-input" name="description" class="form-control" placeholder="Brief summary of items in this category...">
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#addCatForm">Cancel</button>
                                <button type="submit" id="save-cat-btn" class="btn btn-success fw-bold px-4">
                                    <i class="fa-solid fa-floppy-disk me-1"></i>Save Category
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Categories Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0" id="categories-table">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th>Products Count</th>
                                <th>Created Date</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($categories) === 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No product categories created yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $c): 
                                    $isActive = ($c['is_active'] == 1);
                                ?>
                                    <tr id="cat-row-<?= $c['id'] ?>">
                                        <td><span class="fw-bold text-muted">#<?= $c['id'] ?></span></td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6"><?= sanitize($c['name']) ?></div>
                                            <small class="text-muted">Slug: <?= sanitize($c['slug']) ?></small>
                                        </td>
                                        <td><?= sanitize($c['description'] ?: '—') ?></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>products.php?category=<?= $c['id'] ?>" target="_blank" class="badge bg-primary text-decoration-none rounded-pill px-2 py-1">
                                                <?= (int)$c['product_count'] ?> Products
                                            </a>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                                        <td>
                                            <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>" id="cat-badge-<?= $c['id'] ?>">
                                                <?= $isActive ? 'Active' : 'Disabled' ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCatModal<?= $c['id'] ?>" title="Edit Category">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button type="button" class="btn <?= $isActive ? 'btn-outline-warning' : 'btn-outline-success' ?>" onclick="toggleCategoryStatus(<?= $c['id'] ?>)" title="<?= $isActive ? 'Disable Category' : 'Enable Category' ?>">
                                                    <i class="fa-solid <?= $isActive ? 'fa-ban' : 'fa-check' ?>"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="deleteCategory(<?= $c['id'] ?>, <?= (int)$c['product_count'] ?>)" title="Delete Category">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Edit Category Modal -->
                                    <div class="modal fade" id="editCatModal<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg">
                                                <form onsubmit="submitEditCategory(event, <?= $c['id'] ?>)">
                                                    <div class="modal-header bg-light border-bottom">
                                                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Category #<?= $c['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Category Name *</label>
                                                            <input type="text" name="name" class="form-control" value="<?= sanitize($c['name']) ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Description</label>
                                                            <textarea name="description" class="form-control" rows="2"><?= sanitize($c['description'] ?? '') ?></textarea>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Status</label>
                                                            <select name="is_active" class="form-select">
                                                                <option value="1" <?= $c['is_active'] == 1 ? 'selected' : '' ?>>Active (Visible in Marketplace & Vendor Form)</option>
                                                                <option value="0" <?= $c['is_active'] == 0 ? 'selected' : '' ?>>Disabled (Hidden from Add Product Form)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light border-top">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i>Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Instant Toast Notification Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="catToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fs-6 fw-semibold" id="catToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
function showToast(message, isSuccess = true) {
    const toastEl = document.getElementById('catToast');
    const toastBody = document.getElementById('catToastBody');
    if (!toastEl || !toastBody) return;
    
    toastEl.className = 'toast align-items-center text-white border-0 shadow-lg ' + (isSuccess ? 'bg-success' : 'bg-danger');
    toastBody.innerHTML = (isSuccess ? '<i class="fa-solid fa-circle-check me-2"></i>' : '<i class="fa-solid fa-triangle-exclamation me-2"></i>') + message;
    
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
}

function submitNewCategory(e) {
    e.preventDefault();
    const btn = document.getElementById('save-cat-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...';

    const formData = new FormData(document.getElementById('newCatForm'));
    formData.append('action', 'add_category');
    formData.append('ajax', '1');

    fetch('<?= BASE_URL ?>admin/categories.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Save Category';
        if (data.success) {
            showToast(data.message, true);
            document.getElementById('cat-name-input').value = '';
            document.getElementById('cat-desc-input').value = '';
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Failed to add category.', false);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Save Category';
        showToast('Error connecting to server.', false);
    });
}

function submitEditCategory(e, catId) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'edit_category');
    formData.append('category_id', catId);
    formData.append('ajax', '1');

    fetch('<?= BASE_URL ?>admin/categories.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            const modalEl = document.querySelector('#editCatModal' + catId);
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Failed to update category.', false);
        }
    })
    .catch(err => {
        showToast('Error updating category.', false);
    });
}

function toggleCategoryStatus(catId) {
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('category_id', catId);
    formData.append('ajax', '1');

    fetch('<?= BASE_URL ?>admin/categories.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Failed to update status.', false);
        }
    })
    .catch(err => {
        showToast('Error connecting to server.', false);
    });
}

function deleteCategory(catId, prodCount) {
    if (prodCount > 0) {
        showToast('Cannot delete category with assigned products.', false);
        return;
    }
    if (!confirm('Are you sure you want to permanently delete this category?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete_category');
    formData.append('category_id', catId);
    formData.append('ajax', '1');

    fetch('<?= BASE_URL ?>admin/categories.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            const row = document.getElementById('cat-row-' + catId);
            if (row) row.remove();
        } else {
            showToast(data.message || 'Delete failed.', false);
        }
    })
    .catch(err => {
        showToast('Error connecting to server.', false);
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
