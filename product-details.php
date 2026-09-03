<?php
// SmartGov Market - Product Details Page
// File: product-details.php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/license_generator.php';

$db = getDBConnection();
$prod_id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT p.*, v.business_name, v.business_type, v.address as v_address, v.municipality as v_muni, v.district as v_dist, v.id as vendor_id, c.name as category_name 
                      FROM products p
                      JOIN vendors v ON p.vendor_id = v.id
                      JOIN categories c ON p.category_id = c.id
                      WHERE p.id = ?");
$stmt->execute([$prod_id]);
$product = $stmt->fetch();

if (!$product) {
    setFlashMessage('danger', 'Product not found.');
    redirect('products.php');
}

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    requireLogin();
    $user_id = $_SESSION['user_id'];
    $rating = (int)($_POST['rating'] ?? 5);
    $comment = sanitize($_POST['comment'] ?? '');
    
    if ($rating < 1 || $rating > 5 || empty($comment)) {
        setFlashMessage('danger', 'Please provide a valid rating (1-5) and comment.');
        redirect("product-details.php?id={$prod_id}");
    }
    
    // Check if user has a paid order for this product
    $rCheck = $db->prepare("SELECT oi.id, oi.order_id 
                            FROM order_items oi 
                            JOIN orders o ON oi.order_id = o.id 
                            WHERE o.customer_id = ? AND oi.product_id = ? AND (o.payment_status = 'Paid' OR o.status = 'Delivered' OR o.status = 'Confirmed' OR o.status = 'Processing')");
    $rCheck->execute([$user_id, $prod_id]);
    $purchasedItem = $rCheck->fetch();
    
    if (!$purchasedItem) {
        setFlashMessage('danger', 'Only verified buyers who ordered this product can submit a review.');
        redirect("product-details.php?id={$prod_id}");
    }
    
    // Check existing review
    $revCheck = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
    $revCheck->execute([$user_id, $prod_id]);
    if ($revCheck->fetch()) {
        setFlashMessage('warning', 'You have already submitted a review for this product.');
        redirect("product-details.php?id={$prod_id}");
    }
    
    $insRev = $db->prepare("INSERT INTO reviews (product_id, user_id, order_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $insRev->execute([$prod_id, $user_id, $purchasedItem['order_id'], $rating, $comment]);
    
    setFlashMessage('success', 'Thank you! Your product review has been published.');
    redirect("product-details.php?id={$prod_id}");
}

// Fetch Vendor License
$license = getVendorLicense($product['vendor_id']);

// Fetch Reviews
$revStmt = $db->prepare("SELECT r.*, u.full_name as customer_name 
                         FROM reviews r
                         JOIN users u ON r.user_id = u.id
                         WHERE r.product_id = ?
                         ORDER BY r.created_at DESC");
$revStmt->execute([$prod_id]);
$reviews = $revStmt->fetchAll();

$avgRating = 0;
if (count($reviews) > 0) {
    $sum = 0;
    foreach ($reviews as $r) $sum += $r['rating'];
    $avgRating = round($sum / count($reviews), 1);
}

// Check if user is eligible to review
$canReview = false;
if (isLoggedIn()) {
    $u_id = $_SESSION['user_id'];
    $rCheck = $db->prepare("SELECT oi.id 
                            FROM order_items oi 
                            JOIN orders o ON oi.order_id = o.id 
                            WHERE o.customer_id = ? AND oi.product_id = ? AND (o.payment_status = 'Paid' OR o.status = 'Delivered' OR o.status = 'Confirmed' OR o.status = 'Processing')");
    $rCheck->execute([$u_id, $prod_id]);
    if ($rCheck->fetch()) {
        $revCheck = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ?");
        $revCheck->execute([$u_id, $prod_id]);
        if (!$revCheck->fetch()) {
            $canReview = true;
        }
    }
}

$pageTitle = sanitize($product['name']) . " - Details";
require_once __DIR__ . '/includes/header.php';
$imgUrl = !empty($product['image_path']) ? BASE_URL . $product['image_path'] : BASE_URL . 'assets/images/placeholder.svg';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php">Products</a></li>
            <li class="breadcrumb-item active"><?= sanitize($product['name']) ?></li>
        </ol>
    </nav>

    <div class="card card-custom p-4 shadow-sm border-0 mb-4">
        <div class="row g-4">
            <!-- Product Image -->
            <div class="col-lg-5">
                <div class="bg-light rounded border overflow-hidden position-relative" style="aspect-ratio: 4/3;">
                    <img src="<?= $imgUrl ?>" onerror="this.onerror=null;this.src='<?= BASE_URL ?>assets/images/placeholder.svg';" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="<?= sanitize($product['name']) ?>">
                    <span class="position-absolute top-0 start-0 m-2 badge bg-success shadow-sm"><i class="fa-solid fa-shield-check me-1"></i>Gov Verified</span>
                </div>
            </div>

            <!-- Details & Purchasing -->
            <div class="col-lg-7">
                <span class="badge bg-primary-subtle text-primary border mb-2"><?= sanitize($product['category_name']) ?></span>
                <h2 class="fw-bold mb-2"><?= sanitize($product['name']) ?></h2>

                <!-- Rating summary -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="text-warning">
                        <?php for ($i=1; $i<=5; $i++): ?>
                            <i class="fa-<?= $i <= floor($avgRating) ? 'solid' : 'regular' ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="fw-bold text-dark"><?= $avgRating ?> / 5</span>
                    <span class="text-muted"> (<?= count($reviews) ?> customer reviews)</span>
                </div>

                <div class="fs-2 fw-bold text-primary mb-3"><?= formatCurrency($product['price']) ?></div>

                <!-- Stock availability -->
                <div class="mb-3">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
                            <i class="fa-solid fa-check-circle me-1"></i>In Stock (<?= $product['stock_quantity'] ?> units available)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-6">
                            <i class="fa-solid fa-xmark-circle me-1"></i>Out of Stock
                        </span>
                    <?php endif; ?>
                </div>

                <p class="text-secondary mb-4"><?= nl2br(sanitize($product['description'])) ?></p>

                <!-- Purchase Buttons -->
                <?php if ($product['stock_quantity'] > 0): ?>
                    <div class="card p-3 bg-light border-0 rounded-3 mb-4">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-sm-auto">
                                <label class="form-label small fw-bold mb-0">Quantity:</label>
                            </div>
                            <div class="col-12 col-sm-auto" style="width: 110px;">
                                <input type="number" id="buy-qty" class="form-control text-center fw-bold" value="1" min="1" max="<?= $product['stock_quantity'] ?>" oninput="syncQty(this.value)">
                            </div>
                            
                            <!-- Add to Cart Form -->
                            <div class="col-6 col-sm-auto">
                                <form action="api/cart.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="quantity" class="qty-field" value="1">
                                    <button type="submit" class="btn btn-outline-primary btn-lg fw-bold w-100 px-3 shadow-sm">
                                        <i class="fa-solid fa-cart-plus me-1"></i>Add to Cart
                                    </button>
                                </form>
                            </div>

                            <!-- Order Now Form (Direct Checkout) -->
                            <div class="col-6 col-sm-auto">
                                <form action="api/cart.php" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="quantity" class="qty-field" value="1">
                                    <input type="hidden" name="redirect_to" value="checkout">
                                    <button type="submit" class="btn btn-success btn-lg fw-bold w-100 px-4 shadow-sm">
                                        <i class="fa-solid fa-bolt me-1"></i>Order Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <script>
                    function syncQty(val) {
                        document.querySelectorAll('.qty-field').forEach(el => el.value = val);
                    }
                    </script>
                <?php endif; ?>

                <!-- Vendor License Card -->
                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-shop me-2 text-primary"></i>Sold by: <?= sanitize($product['business_name']) ?></h6>
                        <?php if ($license): ?>
                            <a href="verify.php?license_no=<?= urlencode($license['license_no']) ?>" target="_blank" class="badge bg-success text-white text-decoration-none">
                                <i class="fa-solid fa-award me-1"></i>Verified License: <?= $license['license_no'] ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <p class="small text-muted mb-0"><i class="fa-solid fa-location-dot me-1 text-danger"></i><?= sanitize($product['v_muni']) ?>, <?= sanitize($product['v_dist']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Reviews Section -->
    <div class="card card-custom p-4 shadow-sm border-0 mb-4">
        <h4 class="fw-bold mb-3"><i class="fa-solid fa-comments me-2 text-primary"></i>Verified Customer Reviews</h4>

        <!-- Leave a review box for verified buyers -->
        <?php if ($canReview): ?>
            <div class="p-3 bg-light rounded-3 border mb-4">
                <h5 class="fw-bold text-primary mb-2"><i class="fa-solid fa-pen-to-square me-2"></i>Write a Product Review</h5>
                <form method="POST" action="">
                    <input type="hidden" name="submit_review" value="1">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rating (1 to 5 Stars):</label>
                        <select name="rating" class="form-select style-select w-auto" required>
                            <option value="5" selected>5 Stars - Excellent</option>
                            <option value="4">4 Stars - Good</option>
                            <option value="3">3 Stars - Average</option>
                            <option value="2">2 Stars - Poor</option>
                            <option value="1">1 Star - Very Bad</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Your Review / Comment *</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience with product quality, packaging, or vendor service..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fa-solid fa-paper-plane me-1"></i>Post Review</button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if (count($reviews) === 0): ?>
            <p class="text-muted mb-0">No reviews submitted yet for this product. Be the first verified buyer to review!</p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($reviews as $r): ?>
                    <div class="col-12 border-bottom pb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark"><i class="fa-solid fa-circle-check text-success me-1"></i><?= sanitize($r['customer_name']) ?></strong>
                            <small class="text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                        </div>
                        <div class="text-warning small mb-1">
                            <?php for ($i=1; $i<=5; $i++): ?>
                                <i class="fa-<?= $i <= $r['rating'] ? 'solid' : 'regular' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="mb-0 small text-secondary">"<?= sanitize($r['comment']) ?>"</p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
