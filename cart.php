<?php
// SmartGov Market - Shopping Cart Page
// File: cart.php

$pageTitle = "Shopping Cart";
require_once __DIR__ . '/includes/header.php';
requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

$cartStmt = $db->prepare("SELECT ci.*, p.name as product_name, p.image_path, p.stock_quantity, v.business_name 
                          FROM cart c
                          JOIN cart_items ci ON c.id = ci.cart_id
                          JOIN products p ON ci.product_id = p.id
                          JOIN vendors v ON p.vendor_id = v.id
                          WHERE c.user_id = ?");
$cartStmt->execute([$user_id]);
$cartItems = $cartStmt->fetchAll();

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}
?>

<div class="container py-4">
    <h3 class="fw-bold mb-4"><i class="fa-solid fa-cart-shopping text-primary me-2"></i>My Shopping Cart</h3>

    <?php if (count($cartItems) === 0): ?>
        <div class="card card-custom p-5 text-center shadow-sm border-0">
            <i class="fa-solid fa-cart-flatbed fs-1 text-muted mb-3"></i>
            <h4 class="fw-bold">Your Shopping Cart is Empty</h4>
            <p class="text-muted">Explore verified local products from approved government vendors.</p>
            <div>
                <a href="products.php" class="btn btn-primary fw-bold px-4 py-2">Start Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Unit Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= BASE_URL . ($item['image_path'] ? $item['image_path'] : 'assets/css/product-default.jpg') ?>" onerror="this.src='https://via.placeholder.com/60';" class="rounded border" width="55" height="55" style="object-fit:cover;">
                                                <div>
                                                    <a href="product-details.php?id=<?= $item['product_id'] ?>" class="fw-bold text-dark text-decoration-none"><?= sanitize($item['product_name']) ?></a>
                                                    <small class="d-block text-muted">Vendor: <?= sanitize($item['business_name']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-semibold"><?= formatCurrency($item['price']) ?></td>
                                        <td style="width: 130px;">
                                            <form action="api/cart.php" method="POST" class="d-flex gap-1">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                                <input type="number" name="quantity" class="form-control form-control-sm text-center" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock_quantity'] ?>" onchange="this.form.submit()">
                                            </form>
                                        </td>
                                        <td class="fw-bold text-primary"><?= formatCurrency($item['price'] * $item['quantity']) ?></td>
                                        <td>
                                            <form action="api/cart.php" method="POST" class="d-inline" onsubmit="return confirm('Remove item from cart?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="cart_item_id" value="<?= (int)$item['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Remove item"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <a href="products.php" class="btn btn-outline-primary fw-semibold"><i class="fa-solid fa-arrow-left me-1"></i>Continue Shopping</a>
                <form action="api/cart.php" method="POST" class="d-inline" onsubmit="return confirm('Clear the entire cart?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn btn-outline-danger fw-semibold">Clear Cart</button>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="card card-custom p-4 shadow-sm border-0">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Order Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-bold"><?= formatCurrency($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Estimated Delivery:</span>
                        <span class="text-success fw-bold">FREE (Local)</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4 fs-5">
                        <span class="fw-bold">Total Amount:</span>
                        <span class="fw-bold text-primary"><?= formatCurrency($subtotal) ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-success btn-lg fw-bold w-100 shadow-sm"><i class="fa-solid fa-credit-card me-2"></i>Proceed to Checkout</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
