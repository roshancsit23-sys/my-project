<?php
// SmartGov Market - Admin Product Moderation
// File: admin/products.php

$pageTitle = "Product Moderation";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();

// Disable or Enable Product Moderation
if (isset($_GET['toggle_id'])) {
    $pid = (int)$_GET['toggle_id'];
    $pStmt = $db->prepare("SELECT status, name FROM products WHERE id = ?");
    $pStmt->execute([$pid]);
    $prod = $pStmt->fetch();
    if ($prod) {
        $newStat = ($prod['status'] === 'Active') ? 'Inactive' : 'Active';
        $uStmt = $db->prepare("UPDATE products SET status = ? WHERE id = ?");
        $uStmt->execute([$newStat, $pid]);
        
        logAudit('Product Moderated', 'Product', $pid, "Admin updated product {$prod['name']} status to {$newStat}");
        setFlashMessage('success', "Product '{$prod['name']}' status updated.");
        redirect('admin/products.php');
    }
}

$products = $db->query("SELECT p.*, v.business_name, c.name as category_name 
                        FROM products p
                        JOIN vendors v ON p.vendor_id = v.id
                        JOIN categories c ON p.category_id = c.id
                        ORDER BY p.id DESC")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'products'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Marketplace Product Moderation</h4>
                    <span class="badge bg-primary rounded-pill fs-6"><?= count($products) ?> Products</span>
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>#<?= $p['id'] ?></td>
                                    <td class="fw-bold"><?= sanitize($p['name']) ?></td>
                                    <td><?= sanitize($p['business_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= sanitize($p['category_name']) ?></span></td>
                                    <td class="fw-bold text-primary"><?= formatCurrency($p['price']) ?></td>
                                    <td><?= $p['stock_quantity'] ?></td>
                                    <td>
                                        <span class="badge <?= $p['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>"><?= sanitize($p['status']) ?></span>
                                    </td>
                                    <td>
                                        <a href="admin/products.php?toggle_id=<?= $p['id'] ?>" class="btn btn-xs <?= $p['status'] === 'Active' ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                            <?= $p['status'] === 'Active' ? 'Disable' : 'Enable' ?>
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
