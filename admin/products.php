<?php
// SmartGov Market - Admin Product Moderation & Catalog Management
// File: admin/products.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$db = getDBConnection();

// Action handler BEFORE header.php
if (isset($_GET['toggle_id']) || isset($_POST['toggle_id']) || isset($_POST['action'])) {
    $pid = (int)($_POST['toggle_id'] ?? $_GET['toggle_id'] ?? 0);
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

    if ($pid > 0) {
        $pStmt = $db->prepare("SELECT status, name FROM products WHERE id = ?");
        $pStmt->execute([$pid]);
        $prod = $pStmt->fetch();

        if ($prod) {
            $newStatus = 'Active';
            $msg = '';

            if ($action === 'disable' || $prod['status'] === 'Active') {
                $newStatus = 'Disabled';
                $msg = "Product disabled successfully.";
            } else {
                $newStatus = 'Active';
                $msg = "Product enabled successfully.";
            }

            $uStmt = $db->prepare("UPDATE products SET status = ? WHERE id = ?");
            $uStmt->execute([$newStatus, $pid]);

            logAudit('Product Moderated', 'Product', $pid, "Admin updated product {$prod['name']} status to {$newStatus}");

            // Set session flash message so it appears immediately after page reload
            setFlashMessage('success', $msg);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'new_status' => $newStatus, 'message' => $msg]);
                exit();
            }

            redirect('admin/products.php');
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit();
    }
}

// Fetch all products across all vendors
$products = $db->query("SELECT p.*, v.business_name, v.status as vendor_status, c.name as category_name 
                        FROM products p
                        JOIN vendors v ON p.vendor_id = v.id
                        JOIN categories c ON p.category_id = c.id
                        ORDER BY p.id DESC")->fetchAll();

$pageTitle = "Product Moderation";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'products'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Marketplace Product Moderation</h4>
                        <p class="text-muted small mb-0">Disable violating products to immediately remove them from the public store, or re-enable approved items.</p>
                    </div>
                    <span class="badge bg-primary rounded-pill fs-6 px-3 py-2"><?= count($products) ?> Products</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Vendor</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th class="text-end">Moderation Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) === 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No products found in marketplace database.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $p): 
                                    $pStat = $p['status'];
                                    $badgeClass = 'bg-success';
                                    if ($pStat === 'Disabled' || $pStat === 'Inactive') {
                                        $badgeClass = 'bg-danger';
                                        $pStat = 'Disabled';
                                    } elseif ($pStat === 'Out of Stock') {
                                        $badgeClass = 'bg-warning text-dark';
                                    } elseif ($pStat === 'Pending Approval') {
                                        $badgeClass = 'bg-info text-dark';
                                    }
                                ?>
                                    <tr id="product-row-<?= $p['id'] ?>">
                                        <td><span class="fw-bold text-muted">#<?= $p['id'] ?></span></td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6"><?= sanitize($p['name']) ?></div>
                                            <small class="text-muted">Slug: <?= sanitize($p['slug']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= sanitize($p['business_name']) ?></div>
                                            <small class="text-muted">Vendor: <?= sanitize($p['vendor_status']) ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= sanitize($p['category_name']) ?></span></td>
                                        <td class="fw-bold text-primary"><?= formatCurrency($p['price']) ?></td>
                                        <td>
                                            <?php if ($p['stock_quantity'] > 0): ?>
                                                <span class="badge bg-light text-dark border"><?= $p['stock_quantity'] ?> in stock</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $badgeClass ?>" id="status-badge-<?= $p['id'] ?>">
                                                <?= sanitize($pStat) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" id="action-btn-<?= $p['id'] ?>">
                                                <?php if ($pStat === 'Active'): ?>
                                                    <button type="button" class="btn btn-outline-danger fw-semibold" onclick="toggleProductModeration(<?= $p['id'] ?>, 'disable')">
                                                        <i class="fa-solid fa-ban me-1"></i>Disable
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-success fw-semibold" onclick="toggleProductModeration(<?= $p['id'] ?>, 'enable')">
                                                        <i class="fa-solid fa-check me-1"></i>Enable
                                                    </button>
                                                <?php endif; ?>
                                                <a href="<?= BASE_URL ?>product-details.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-outline-secondary" title="Preview product">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            </div>
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

<!-- Instant Toast Notification Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="productToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fs-6 fw-semibold" id="productToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
function showToast(message, isSuccess = true) {
    const toastEl = document.getElementById('productToast');
    const toastBody = document.getElementById('productToastBody');
    if (!toastEl || !toastBody) return;
    
    toastEl.className = 'toast align-items-center text-white border-0 shadow-lg ' + (isSuccess ? 'bg-success' : 'bg-danger');
    toastBody.innerHTML = (isSuccess ? '<i class="fa-solid fa-circle-check me-2"></i>' : '<i class="fa-solid fa-triangle-exclamation me-2"></i>') + message;
    
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
}

function toggleProductModeration(productId, targetAction) {
    if (targetAction === 'disable') {
        if (!confirm('Are you sure you want to disable this product? It will be hidden from the marketplace.')) {
            return;
        }
    }

    const formData = new FormData();
    formData.append('toggle_id', productId);
    formData.append('action', targetAction);
    formData.append('ajax', '1');

    fetch('<?= BASE_URL ?>admin/products.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            setTimeout(() => {
                window.location.reload();
            }, 600);
        } else {
            showToast(data.message || 'Action failed.', false);
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Server communication error. Please try again.', false);
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
