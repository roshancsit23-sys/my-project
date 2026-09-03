<?php
// SmartGov Market - Checkout & Order Review Page
// File: checkout.php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$user = currentUser();

// Fetch Cart items
$cartStmt = $db->prepare("SELECT ci.*, p.name as product_name, p.price as current_price, p.stock_quantity, p.vendor_id, v.business_name, v.status as vendor_status 
                          FROM cart c
                          JOIN cart_items ci ON c.id = ci.cart_id
                          JOIN products p ON ci.product_id = p.id
                          JOIN vendors v ON p.vendor_id = v.id
                          WHERE c.user_id = ?");
$cartStmt->execute([$user_id]);
$cartItems = $cartStmt->fetchAll();

if (count($cartItems) === 0) {
    setFlashMessage('warning', 'Your shopping cart is empty.');
    redirect('cart.php');
}

// Calculate Total
$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += ($item['current_price'] * $item['quantity']);
}

$pageTitle = "Order Checkout";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <h3 class="fw-bold mb-4"><i class="fa-solid fa-credit-card text-primary me-2"></i>Checkout & Order Review</h3>

    <form action="api/place_order.php" method="POST">
        <?= csrf_field() ?>
        <div class="row g-4">
            <!-- Shipping & Customer Details -->
            <div class="col-lg-7">
                <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-truck-fast me-2 text-primary"></i>Delivery & Shipping Address</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Customer Full Name *</label>
                            <input type="text" name="customer_name" class="form-control" value="<?= sanitize($user['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Phone Number *</label>
                            <input type="text" name="phone" class="form-control" value="<?= sanitize($user['phone']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">District *</label>
                            <input type="text" name="district" class="form-control" value="<?= sanitize($user['district'] ?: 'Kathmandu') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Municipality / Local Body *</label>
                            <input type="text" name="municipality" class="form-control" value="<?= sanitize($user['municipality'] ?: 'Kathmandu Metro') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Full Residential Address / Landmark *</label>
                            <textarea name="shipping_address" class="form-control" rows="2" placeholder="Enter full street address..." required><?= sanitize($user['address']) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Payment Options -->
                <div class="card card-custom p-4 shadow-sm border-0">
                    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-wallet me-2 text-success"></i>Select Payment Method</h5>
                    
                    <div class="form-check p-3 bg-light rounded border mb-2">
                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="payCOD" value="Cash on Delivery" checked>
                        <label class="form-check-label fw-bold d-block cursor-pointer" for="payCOD">
                            <i class="fa-solid fa-hand-holding-dollar text-success me-2 fs-5"></i>Cash on Delivery (COD)
                            <small class="d-block text-muted fw-normal mt-1">Pay with cash upon package receipt at your doorstep.</small>
                        </label>
                    </div>

                    <div class="form-check p-3 bg-light rounded border mb-2">
                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="payEsewa" value="eSewa">
                        <label class="form-check-label fw-bold d-block cursor-pointer" for="payEsewa">
                            <i class="fa-solid fa-wallet text-success me-2 fs-5"></i>eSewa Wallet (Simulated Demo Payment)
                            <small class="d-block text-muted fw-normal mt-1">Instant electronic payment via eSewa digital wallet gateway.</small>
                        </label>
                    </div>

                    <div class="form-check p-3 bg-light rounded border mb-2">
                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="payKhalti" value="Khalti">
                        <label class="form-check-label fw-bold d-block cursor-pointer" for="payKhalti">
                            <i class="fa-solid fa-mobile-screen-button text-purple me-2 fs-5" style="color: #5c2d91;"></i>Khalti Wallet (Simulated Demo Payment)
                            <small class="d-block text-muted fw-normal mt-1">Instant electronic payment via Khalti mobile wallet gateway.</small>
                        </label>
                    </div>

                    <div class="form-check p-3 bg-light rounded border">
                        <input class="form-check-input ms-0 me-3" type="radio" name="payment_method" id="payConnectIPS" value="ConnectIPS">
                        <label class="form-check-label fw-bold d-block cursor-pointer" for="payConnectIPS">
                            <i class="fa-solid fa-building-columns text-primary me-2 fs-5"></i>ConnectIPS (Simulated Demo Payment)
                            <small class="d-block text-muted fw-normal mt-1">Instant electronic bank transfer via ConnectIPS gateway.</small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Order Items Summary -->
            <div class="col-lg-5">
                <div class="card card-custom p-4 shadow-sm border-0 sticky-top" style="top: 90px; z-index: 10;">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Order Items (<?= count($cartItems) ?>)</h5>
                    
                    <div class="mb-3" style="max-height: 250px; overflow-y: auto;">
                        <?php foreach ($cartItems as $it): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div>
                                    <strong class="text-dark d-block text-truncate" style="max-width:200px;"><?= sanitize($it['product_name']) ?></strong>
                                    <small class="text-muted">Qty: <?= $it['quantity'] ?> x <?= formatCurrency($it['current_price']) ?></small>
                                </div>
                                <span class="fw-bold text-primary"><?= formatCurrency($it['current_price'] * $it['quantity']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-bold"><?= formatCurrency($totalAmount) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Delivery Charges:</span>
                        <span class="text-success fw-bold">FREE (Local)</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4 fs-4">
                        <span class="fw-bold">Total Pay:</span>
                        <span class="fw-bold text-success"><?= formatCurrency($totalAmount) ?></span>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg fw-bold w-100 py-3 shadow-sm">
                        <i class="fa-solid fa-check-circle me-2"></i>Confirm & Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
