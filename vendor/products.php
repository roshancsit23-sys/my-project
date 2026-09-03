<?php
// SmartGov Market - Vendor Products List
// File: vendor/products.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('vendor');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

$vStmt = $db->prepare("SELECT id, status FROM vendors WHERE user_id = ?");
$vStmt->execute([$user_id]);
$vendor = $vStmt->fetch();

if (!$vendor) {
    redirect('index.php');
}

$isApproved = ($vendor['status'] === 'Approved');

// Toggle Product Status (Active / Inactive)
if (isset($_GET['toggle_id']) && $isApproved) {
    $pid = (int)$_GET['toggle_id'];
    $pStmt = $db->prepare("SELECT status FROM products WHERE id = ? AND vendor_id = ?");
    $pStmt->execute([$pid, $vendor['id']]);
    $prod = $pStmt->fetch();
    if ($prod) {
        $newStatus = ($prod['status'] === 'Active') ? 'Inactive' : 'Active';
        $uStmt = $db->prepare("UPDATE products SET status = ? WHERE id = ?");
        $uStmt->execute([$newStatus, $pid]);
        setFlashMessage('success', "✓ Product status updated to {$newStatus}.");
        redirect('vendor/products.php');
    }
}

// Delete Product
if (isset($_GET['delete_id']) && $isApproved) {
    $pid = (int)$_GET['delete_id'];
    $pStmt = $db->prepare("SELECT name FROM products WHERE id = ? AND vendor_id = ?");
    $pStmt->execute([$pid, $vendor['id']]);
    $prod = $pStmt->fetch();
    if ($prod) {
        $dStmt = $db->prepare("DELETE FROM products WHERE id = ? AND vendor_id = ?");
        $dStmt->execute([$pid, $vendor['id']]);
        logAudit('Product Deleted', 'Product', $pid, "Vendor deleted product {$prod['name']}");
        setFlashMessage('info', "Product '{$prod['name']}' deleted.");
        redirect('vendor/products.php');
    }
}

// Fetch Vendor Products
$products = $db->query("SELECT p.*, c.name as category_name 
                        FROM products p
                        JOIN categories c ON p.category_id = c.id
                        WHERE p.vendor_id = {$vendor['id']}
                        ORDER BY p.created_at DESC")->fetchAll();

$pageTitle = "Manage Products";
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
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Product Catalog</h4>
                    <?php if ($isApproved): ?>
                        <a href="<?= BASE_URL ?>vendor/add-product.php" class="btn btn-success fw-bold shadow-sm"><i class="fa-solid fa-plus me-1"></i>Add New Product</a>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-lock me-1"></i>Approval Required to Add Products</span>
                    <?php endif; ?>
                </div>

                <?php if (!$isApproved): ?>
                    <div class="alert alert-warning py-2 small mb-4 shadow-sm">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Your vendor account is currently <strong><?= sanitize($vendor['status']) ?></strong>. Only government-approved vendors can list products on the marketplace.
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No products added yet. Click "Add New Product" to list your items.</td></tr>
                            <?php else: ?>
                                <?php foreach ($products as $p): 
                                    $imgUrl = !empty($p['image_path']) ? BASE_URL . $p['image_path'] : BASE_URL . 'assets/images/placeholder.svg';
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="<?= $imgUrl ?>" onerror="this.onerror=null;this.src='<?= BASE_URL ?>assets/images/placeholder.svg';" class="rounded border" width="45" height="45" style="object-fit:cover;">
                                                <div>
                                                    <span class="fw-bold text-dark d-block"><?= sanitize($p['name']) ?></span>
                                                    <small class="text-muted">ID: #<?= $p['id'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= sanitize($p['category_name']) ?></span></td>
                                        <td class="fw-bold text-primary"><?= formatCurrency($p['price']) ?></td>
                                        <td>
                                            <span class="fw-semibold <?= $p['stock_quantity'] > 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= $p['stock_quantity'] ?> in stock
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>vendor/products.php?toggle_id=<?= $p['id'] ?>" class="badge text-decoration-none <?= $p['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>" title="Click to toggle status">
                                                <?= sanitize($p['status']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>vendor/edit-product.php?id=<?= $p['id'] ?>" class="btn btn-xs btn-outline-primary me-1"><i class="fa-solid fa-pen me-1"></i>Edit</a>
                                            <a href="<?= BASE_URL ?>product-details.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-xs btn-outline-secondary me-1"><i class="fa-solid fa-eye"></i></a>
                                            <a href="<?= BASE_URL ?>vendor/products.php?delete_id=<?= $p['id'] ?>" onclick="return confirm('Delete this product permanently?');" class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-trash"></i></a>
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
