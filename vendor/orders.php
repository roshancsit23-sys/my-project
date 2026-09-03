<?php
// SmartGov Market - Vendor Orders Management
// File: vendor/orders.php

$pageTitle = "Vendor Orders Fulfillment";
require_once __DIR__ . '/../includes/header.php';
requireRole('vendor');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

$vStmt = $db->prepare("SELECT id FROM vendors WHERE user_id = ?");
$vStmt->execute([$user_id]);
$vendor = $vStmt->fetch();

if (!$vendor) redirect('index.php');
$vendor_id = $vendor['id'];

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)$_POST['order_id'];
    $new_status = sanitize($_POST['status']);
    
    // Verify order contains vendor products
    $check = $db->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ? AND vendor_id = ?");
    $check->execute([$order_id, $vendor_id]);
    
    if ($check->fetchColumn() > 0) {
        $uStmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $uStmt->execute([$new_status, $order_id]);
        
        // Notify Customer
        $oStmt = $db->prepare("SELECT customer_id, order_no FROM orders WHERE id = ?");
        $oStmt->execute([$order_id]);
        $order = $oStmt->fetch();
        
        if ($order) {
            sendNotification(
                $order['customer_id'],
                'Order Status Updated',
                "Your order {$order['order_no']} has been updated to status: {$new_status}.",
                "order-details.php?id={$order_id}"
            );
        }
        
        logAudit('Order Status Updated', 'Order', $order_id, "Vendor updated order ID {$order_id} status to {$new_status}");
        setFlashMessage('success', "Order status updated to {$new_status}.");
        redirect('vendor/orders.php');
    }
}

// Fetch orders containing vendor items
$ordersStmt = $db->prepare("SELECT DISTINCT o.*, u.full_name as customer_name, u.phone as customer_phone, u.email as customer_email 
                            FROM orders o
                            JOIN order_items oi ON o.id = oi.order_id
                            JOIN users u ON o.customer_id = u.id
                            WHERE oi.vendor_id = ?
                            ORDER BY o.created_at DESC");
$ordersStmt->execute([$vendor_id]);
$orders = $ordersStmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'orders'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-truck-ramp-box text-primary me-2"></i>Received Orders & Fulfillment</h4>
                    <span class="badge bg-primary rounded-pill fs-6"><?= count($orders) ?> Orders</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order No</th>
                                <th>Customer</th>
                                <th>Shipping Address</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders) === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No orders received yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders as $o): ?>
                                    <?php
                                        // Fetch vendor specific items for this order
                                        $itemStmt = $db->prepare("SELECT oi.*, p.name as product_name 
                                                                  FROM order_items oi
                                                                  JOIN products p ON oi.product_id = p.id
                                                                  WHERE oi.order_id = ? AND oi.vendor_id = ?");
                                        $itemStmt->execute([$o['id'], $vendor_id]);
                                        $items = $itemStmt->fetchAll();
                                        
                                        $vendorTotal = 0;
                                        foreach ($items as $it) $vendorTotal += $it['subtotal'];
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= sanitize($o['order_no']) ?></td>
                                        <td>
                                            <?= sanitize($o['customer_name']) ?><br>
                                            <small class="text-muted"><?= sanitize($o['customer_phone']) ?></small>
                                        </td>
                                        <td><?= sanitize($o['shipping_address']) ?>, <?= sanitize($o['municipality']) ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?= sanitize($o['payment_method']) ?></span><br>
                                            <small class="fw-bold text-success"><?= formatCurrency($vendorTotal) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= $o['status'] === 'Delivered' ? 'bg-success' : ($o['status'] === 'Shipped' ? 'bg-info text-dark' : 'bg-warning text-dark') ?>">
                                                <?= sanitize($o['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-xs btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal<?= $o['id'] ?>">
                                                <i class="fa-solid fa-boxes-packing me-1"></i>Process Order
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Process Order Modal -->
                                    <div class="modal fade" id="orderModal<?= $o['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Process Order: <?= sanitize($o['order_no']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">

                                                        <div class="p-3 bg-light rounded border mb-3">
                                                            <h6><strong>Customer:</strong> <?= sanitize($o['customer_name']) ?> (<?= sanitize($o['customer_phone']) ?>)</h6>
                                                            <p class="mb-0"><strong>Delivery Address:</strong> <?= sanitize($o['shipping_address']) ?>, <?= sanitize($o['municipality']) ?>, <?= sanitize($o['district']) ?></p>
                                                        </div>

                                                        <h6 class="fw-bold mb-2">Ordered Items from your store:</h6>
                                                        <ul class="list-group mb-3 small">
                                                            <?php foreach ($items as $it): ?>
                                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <strong class="text-dark"><?= sanitize($it['product_name']) ?></strong>
                                                                        <span class="text-muted"> x <?= $it['quantity'] ?></span>
                                                                    </div>
                                                                    <span class="fw-bold text-primary"><?= formatCurrency($it['subtotal']) ?></span>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Update Fulfillment Status</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="Processing" <?= $o['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                                                <option value="Shipped" <?= $o['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                                                <option value="Delivered" <?= $o['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                                                <option value="Cancelled" <?= $o['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-success fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i>Save Status</button>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
