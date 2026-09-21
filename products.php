<?php
// SmartGov Market - Marketplace Products Catalog
// File: products.php

$pageTitle = "Marketplace Products";
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();

$category_id = (int)($_GET['category'] ?? 0);
$vendor_id = (int)($_GET['vendor'] ?? 0);
$search = sanitize($_GET['search'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'latest');
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
$availability = sanitize($_GET['availability'] ?? '');

$query = "SELECT p.*, v.business_name, v.municipality, c.name as category_name 
          FROM products p
          JOIN vendors v ON p.vendor_id = v.id
          JOIN categories c ON p.category_id = c.id
          WHERE p.status = 'Active' AND v.status IN ('Approved', 'Verified') AND c.is_active = 1";
$params = [];

if ($category_id > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
}

if ($vendor_id > 0) {
    $query .= " AND p.vendor_id = ?";
    $params[] = $vendor_id;
}

if (!empty($search)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ? OR v.business_name LIKE ? OR c.name LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($min_price !== null) {
    $query .= " AND p.price >= ?";
    $params[] = $min_price;
}

if ($max_price !== null) {
    $query .= " AND p.price <= ?";
    $params[] = $max_price;
}

if ($availability === 'in_stock') {
    $query .= " AND p.stock_quantity > 0";
} elseif ($availability === 'out_of_stock') {
    $query .= " AND p.stock_quantity <= 0";
}

if ($sort === 'price_low') {
    $query .= " ORDER BY p.price ASC";
} elseif ($sort === 'price_high') {
    $query .= " ORDER BY p.price DESC";
} elseif ($sort === 'name_asc') {
    $query .= " ORDER BY p.name ASC";
} elseif ($sort === 'name_desc') {
    $query .= " ORDER BY p.name DESC";
} else {
    $query .= " ORDER BY p.id DESC";
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Filter Sidebar -->
        <div class="col-lg-3">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4 sticky-top" style="top: 90px; z-index: 10;">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-filter text-primary me-2"></i>Filter Products</h5>
                
                <form method="GET" action="products.php">
                    <?php if ($vendor_id > 0): ?>
                        <input type="hidden" name="vendor" value="<?= $vendor_id ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Search Keywords</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Product name, vendor..." value="<?= sanitize($search) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category</label>
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $category_id ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Price Range (रु)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" step="0.01" name="min_price" class="form-control form-control-sm" placeholder="Min" value="<?= $min_price !== null ? $min_price : '' ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.01" name="max_price" class="form-control form-control-sm" placeholder="Max" value="<?= $max_price !== null ? $max_price : '' ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Availability</label>
                        <select name="availability" class="form-select form-select-sm">
                            <option value="">All Availability</option>
                            <option value="in_stock" <?= $availability === 'in_stock' ? 'selected' : '' ?>>In Stock Only</option>
                            <option value="out_of_stock" <?= $availability === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sort By</label>
                        <select name="sort" class="form-select form-select-sm">
                            <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Newest Arrivals</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name: A to Z</option>
                            <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name: Z to A</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold mb-2 shadow-sm"><i class="fa-solid fa-arrows-rotate me-1"></i>Apply Filters</button>
                    <a href="products.php" class="btn btn-sm btn-outline-secondary w-100">Reset Filters</a>
                </form>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-0">Marketplace Catalog</h4>
                    <?php if (!empty($search) || $category_id > 0 || $min_price !== null || $max_price !== null): ?>
                        <small class="text-muted">Filtered results matching your search criteria</small>
                    <?php endif; ?>
                </div>
                <span class="badge bg-primary fs-6 px-3 py-2">Showing <?= count($products) ?> item<?= count($products) === 1 ? '' : 's' ?></span>
            </div>

            <div class="row g-4">
                <?php if (count($products) === 0): ?>
                    <div class="col-12">
                        <div class="card card-custom p-5 text-center shadow-sm border-0">
                            <i class="fa-solid fa-box-open fs-1 mb-3 text-muted"></i>
                            <h5 class="fw-bold text-dark mb-2">No products found matching your search.</h5>
                            <p class="text-muted small mb-3">Try clearing search filters or searching for different keywords.</p>
                            <div>
                                <a href="products.php" class="btn btn-sm btn-primary fw-bold px-4 py-2">View All Products</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $p): 
                        $imgUrl = !empty($p['image_path']) ? BASE_URL . $p['image_path'] : BASE_URL . 'assets/images/placeholder.svg';
                    ?>
                        <div class="col-6 col-md-4">
                            <div class="card card-custom h-100 border-0 shadow-sm overflow-hidden d-flex flex-column">
                                <div class="position-relative bg-light" style="aspect-ratio: 4/3; overflow: hidden;">
                                    <img src="<?= $imgUrl ?>" onerror="this.onerror=null;this.src='<?= BASE_URL ?>assets/images/placeholder.svg';" class="card-img-top h-100 w-100" style="object-fit: cover;" alt="<?= sanitize($p['name']) ?>">
                                    <span class="position-absolute top-0 start-0 m-2 badge bg-success shadow-sm"><i class="fa-solid fa-shield-check me-1"></i>Gov Verified</span>
                                    <?php if ($p['stock_quantity'] <= 0): ?>
                                        <span class="position-absolute top-0 end-0 m-2 badge bg-danger shadow-sm">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column p-3">
                                    <span class="badge bg-light text-secondary border align-self-start mb-2"><?= sanitize($p['category_name']) ?></span>
                                    <h6 class="fw-bold mb-1 text-truncate" title="<?= sanitize($p['name']) ?>">
                                        <a href="product-details.php?id=<?= $p['id'] ?>" class="text-dark text-decoration-none"><?= sanitize($p['name']) ?></a>
                                    </h6>
                                    <small class="text-muted mb-3"><i class="fa-solid fa-store me-1 text-primary"></i><?= sanitize($p['business_name']) ?></small>
                                    
                                    <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
                                        <div>
                                            <span class="fw-bold text-primary fs-5 d-block"><?= formatCurrency($p['price']) ?></span>
                                            <small class="text-<?= $p['stock_quantity'] > 0 ? 'success' : 'danger' ?> fw-semibold" style="font-size: 0.75rem;">
                                                <?= $p['stock_quantity'] > 0 ? "Stock: {$p['stock_quantity']}" : 'Out of stock' ?>
                                            </small>
                                        </div>
                                        <a href="product-details.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary fw-bold px-3"><i class="fa-solid fa-cart-plus me-1"></i>View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
