<?php
// SmartGov Market - Edit Product Form
// File: vendor/edit-product.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('vendor');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$prod_id = (int)($_GET['id'] ?? 0);

$vStmt = $db->prepare("SELECT id FROM vendors WHERE user_id = ?");
$vStmt->execute([$user_id]);
$vendor = $vStmt->fetch();

$stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND vendor_id = ?");
$stmt->execute([$prod_id, $vendor['id'] ?? 0]);
$product = $stmt->fetch();

if (!$product) {
    setFlashMessage('danger', 'Product not found.');
    redirect('vendor/products.php');
}

$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock_quantity'] ?? 0, FILTER_VALIDATE_INT);
    $status = sanitize($_POST['status'] ?? 'Active');
    $description = sanitize($_POST['description'] ?? '');
    
    if (empty($name) || $category_id <= 0 || $price <= 0 || $stock < 0 || empty($description)) {
        $error = 'Please fill in all required product fields correctly.';
    } else {
        $imagePath = $product['image_path'];
        
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['product_image']['tmp_name'];
            $fileName = $_FILES['product_image']['name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($ext, $allowed, true)) {
                $newFileName = "PROD_" . time() . "_" . mt_rand(1000, 9999) . "." . $ext;
                $uploadDir = BASE_PATH . 'uploads/products/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                    $imagePath = 'uploads/products/' . $newFileName;
                }
            }
        }
        
        $uStmt = $db->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock_quantity = ?, image_path = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $uStmt->execute([$category_id, $name, $description, $price, $stock, $imagePath, $status, $prod_id]);
        
        logAudit('Product Updated', 'Product', $prod_id, "Vendor updated product {$name}");
        setFlashMessage('success', "✓ Product '{$name}' updated successfully.");
        redirect('vendor/products.php');
    }
}

$pageTitle = "Edit Product";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'products'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Product #<?= $product['id'] ?></h4>
                    <a href="<?= BASE_URL ?>vendor/products.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to Products</a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small shadow-sm"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Product Name *</label>
                            <input type="text" name="name" class="form-control" value="<?= sanitize($product['name']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Price (रु / NPR) *</label>
                            <input type="number" step="0.01" name="price" class="form-control" value="<?= sanitize($product['price']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Available Stock Quantity *</label>
                            <input type="number" name="stock_quantity" class="form-control" value="<?= sanitize($product['stock_quantity']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Listing Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="Active" <?= $product['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $product['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="Out of Stock" <?= $product['status'] === 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Change Product Image (Optional)</label>
                            <input type="file" name="product_image" class="form-control mb-2" accept=".jpg,.jpeg,.png,.webp">
                            <?php if ($product['image_path']): ?>
                                <div class="small text-muted">Current image: <img src="<?= BASE_URL . $product['image_path'] ?>" onerror="this.onerror=null;this.src='<?= BASE_URL ?>assets/images/placeholder.svg';" height="40" class="rounded border ms-1"></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Detailed Description *</label>
                            <textarea name="description" class="form-control" rows="5" required><?= sanitize($product['description']) ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold px-4 py-2 shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Save Product Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
