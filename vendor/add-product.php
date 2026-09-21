<?php
// SmartGov Market - Add New Product Form
// File: vendor/add-product.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('vendor');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

$vStmt = $db->prepare("SELECT id, status FROM vendors WHERE user_id = ?");
$vStmt->execute([$user_id]);
$vendor = $vStmt->fetch();

if (!$vendor || !in_array($vendor['status'], ['Approved', 'Verified'], true)) {
    setFlashMessage('danger', 'Only government-approved vendors can list products.');
    redirect('vendor/products.php');
}

$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock_quantity'] ?? 0, FILTER_VALIDATE_INT);
    $description = sanitize($_POST['description'] ?? '');
    
    if (empty($name) || $category_id <= 0 || $price <= 0 || $stock < 0 || empty($description)) {
        $error = 'Please fill in all required product fields correctly.';
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $imagePath = 'assets/images/placeholder.svg';
        
        // Handle Image Upload
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
        
        $stmt = $db->prepare("INSERT INTO products (vendor_id, category_id, name, slug, description, price, stock_quantity, image_path, status, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active', NOW())");
        $stmt->execute([$vendor['id'], $category_id, $name, $slug, $description, $price, $stock, $imagePath]);
        $prod_id = $db->lastInsertId();
        
        logAudit('Product Created', 'Product', $prod_id, "Vendor created product {$name} (Price: {$price}, Stock: {$stock})");
        setFlashMessage('success', "✓ Product '{$name}' created and listed on marketplace successfully!");
        redirect('vendor/products.php');
    }
}

$pageTitle = "Add New Product";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'add-product'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-plus-circle text-success me-2"></i>Add New Marketplace Product</h4>
                    <a href="<?= BASE_URL ?>vendor/products.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to Products</a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small shadow-sm"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Product Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Organic Mustang Honey 500g" value="<?= sanitize($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Price (रु / NPR) *</label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="e.g. 850.00" value="<?= sanitize($_POST['price'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Initial Available Stock *</label>
                            <input type="number" name="stock_quantity" class="form-control" placeholder="e.g. 25" value="<?= sanitize($_POST['stock_quantity'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Product Image (JPG/PNG/WebP)</label>
                            <input type="file" name="product_image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Detailed Description & Specifications *</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="Enter product origin, ingredients, specifications, craftsmanship..." required><?= sanitize($_POST['description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success fw-bold px-4 py-2 shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Publish Product to Marketplace
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
