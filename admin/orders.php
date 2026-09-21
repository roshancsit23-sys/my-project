<?php
// SmartGov Market - Admin System Orders Oversight & Inspection
// File: admin/orders.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';

requireRole('admin');

$db = getDBConnection();

// Status update handler BEFORE header.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $new_status = sanitize($_POST['status'] ?? '');
    $payment_status = sanitize($_POST['payment_status'] ?? '');
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

    $allowedStatuses = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Refunded', 'Paid'];
    $allowedPayStatuses = ['Pending', 'Paid', 'Failed', 'Refunded'];

    if ($order_id > 0 && in_array($new_status, $allowedStatuses, true)) {
        $stmt = $db->prepare("SELECT order_no, customer_id FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $ord = $stmt->fetch();

        if ($ord) {
            if (in_array($payment_status, $allowedPayStatuses, true)) {
                $uStmt = $db->prepare("UPDATE orders SET status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?");
                $uStmt->execute([$new_status, $payment_status, $order_id]);
            } else {
                $uStmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
                $uStmt->execute([$new_status, $order_id]);
            }

            // Notify Customer
            sendNotification($ord['customer_id'], "Order {$ord['order_no']} Status: {$new_status}", "Your order status has been updated to {$new_status}.", "order-details.php?id={$order_id}");

            logAudit('Order Status Updated', 'Order', $ord['order_no'], "Admin updated order #{$ord['order_no']} status to {$new_status}");
            $msg = "Order #{$ord['order_no']} status updated successfully to '{$new_status}'.";

            setFlashMessage('success', $msg);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $msg]);
                exit();
            }

            redirect('admin/orders.php');
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid order or status selected.']);
        exit();
    }
}

// Fetch Orders
$orders = $db->query("SELECT o.*, u.full_name as customer_name, u.phone as customer_phone, u.email as customer_email,
                             (SELECT p.transaction_id FROM payments p WHERE p.order_id = o.id ORDER BY p.id DESC LIMIT 1) as transaction_id
                      FROM orders o
                      JOIN users u ON o.customer_id = u.id
                      ORDER BY o.id DESC")->fetchAll();

// Fetch all order items mapped by order_id
$itemsData = $db->query("SELECT oi.*, p.name as product_name, p.image_path, v.business_name, u.full_name as vendor_owner, u.phone as vendor_phone
                         FROM order_items oi
                         JOIN products p ON oi.product_id = p.id
                         JOIN vendors v ON oi.vendor_id = v.id
                         JOIN users u ON v.user_id = u.id")->fetchAll();

$orderItemsMap = [];
foreach ($itemsData as $it) {
    $orderItemsMap[$it['order_id']][] = $it;
}

$pageTitle = "System Orders Overview";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'orders'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="fa-solid fa-cart-flatbed text-primary me-2"></i>System-wide E-Commerce Orders</h4>
                        <p class="text-muted small mb-0">Inspect real-time order lifecycle, verify customer delivery details, and manage payment settlements.</p>
                    </div>
                    <span class="badge bg-primary rounded-pill fs-6 px-3 py-2"><?= count($orders) ?> Total Orders</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order No</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Total Amount</th>
                                <th>Payment Status</th>
                                <th>Order Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders) === 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No orders found in database.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $o): 
                                    $orderItems = $orderItemsMap[$o['id']] ?? [];
                                    $statusClass = 'bg-warning text-dark';
                                    if ($o['status'] === 'Delivered') $statusClass = 'bg-success';
                                    elseif ($o['status'] === 'Shipped') $statusClass = 'bg-info text-dark';
                                    elseif ($o['status'] === 'Cancelled' || $o['status'] === 'Refunded') $statusClass = 'bg-danger';
                                    elseif ($o['status'] === 'Confirmed' || $o['status'] === 'Paid') $statusClass = 'bg-primary';

                                    $payClass = ($o['payment_status'] === 'Paid') ? 'bg-success' : (($o['payment_status'] === 'Failed') ? 'bg-danger' : 'bg-warning text-dark');
                                ?>
                                    <tr>
                                        <td class="fw-bold text-primary fs-6"><?= sanitize($o['order_no']) ?></td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= sanitize($o['customer_name']) ?></div>
                                            <small class="text-muted"><?= sanitize($o['customer_phone']) ?></small>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= sanitize($o['payment_method']) ?></span></td>
                                        <td class="fw-bold text-success fs-6"><?= formatCurrency($o['total_amount']) ?></td>
                                        <td><span class="badge <?= $payClass ?>"><?= sanitize($o['payment_status']) ?></span></td>
                                        <td><span class="badge <?= $statusClass ?>"><?= sanitize($o['status']) ?></span></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#inspectModal<?= $o['id'] ?>">
                                                <i class="fa-solid fa-eye me-1"></i>Inspect
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Complete Order Inspection Modal -->
                                    <div class="modal fade" id="inspectModal<?= $o['id'] ?>" tabindex="-1" aria-labelledby="inspectModalLabel<?= $o['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg">
                                                <div class="modal-header bg-light border-bottom">
                                                    <h5 class="modal-title fw-bold" id="inspectModalLabel<?= $o['id'] ?>">
                                                        <i class="fa-solid fa-receipt text-primary me-2"></i>Inspect Order #<?= sanitize($o['order_no']) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <!-- Top Order Summary Cards -->
                                                    <div class="row g-3 mb-4">
                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded border h-100">
                                                                <h6 class="fw-bold text-primary mb-2 border-bottom pb-2"><i class="fa-solid fa-user me-2"></i>Customer & Delivery Info</h6>
                                                                <p class="mb-1"><strong>Customer Name:</strong> <?= sanitize($o['customer_name']) ?></p>
                                                                <p class="mb-1"><strong>Contact Phone:</strong> <?= sanitize($o['customer_phone']) ?></p>
                                                                <p class="mb-1"><strong>Email:</strong> <?= sanitize($o['customer_email']) ?></p>
                                                                <p class="mb-0"><strong>Delivery Address:</strong> <?= sanitize($o['shipping_address']) ?>, <?= sanitize($o['municipality']) ?>, <?= sanitize($o['district']) ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded border h-100">
                                                                <h6 class="fw-bold text-primary mb-2 border-bottom pb-2"><i class="fa-solid fa-credit-card me-2"></i>Payment & Lifecycle Status</h6>
                                                                <p class="mb-1"><strong>Payment Method:</strong> <span class="badge bg-white text-dark border"><?= sanitize($o['payment_method']) ?></span></p>
                                                                <p class="mb-1"><strong>Payment Status:</strong> <span class="badge <?= $payClass ?>"><?= sanitize($o['payment_status']) ?></span></p>
                                                                <p class="mb-1"><strong>Transaction Ref:</strong> <?= sanitize($o['transaction_id'] ?: 'N/A (Cash on Delivery)') ?></p>
                                                                <p class="mb-1"><strong>Order Placed On:</strong> <?= date('F d, Y - h:i A', strtotime($o['created_at'])) ?></p>
                                                                <p class="mb-0"><strong>Current Status:</strong> <span class="badge <?= $statusClass ?> fs-6"><?= sanitize($o['status']) ?></span></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Ordered Products Table -->
                                                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-boxes-packing text-primary me-2"></i>Ordered Products & Vendors</h6>
                                                    <div class="table-responsive border rounded mb-4">
                                                        <table class="table table-sm align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Item</th>
                                                                    <th>Vendor</th>
                                                                    <th class="text-center">Quantity</th>
                                                                    <th class="text-end">Unit Price</th>
                                                                    <th class="text-end">Subtotal</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($orderItems as $item): 
                                                                    $img = $item['image_path'] ? (strpos($item['image_path'], 'uploads/') === 0 ? BASE_URL . $item['image_path'] : BASE_URL . $item['image_path']) : BASE_URL . 'assets/images/placeholder.svg';
                                                                ?>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center gap-2">
                                                                                <img src="<?= $img ?>" onerror="this.onerror=null;this.src='<?= BASE_URL ?>assets/images/placeholder.svg';" width="40" height="40" class="rounded border" style="object-fit:cover;">
                                                                                <span class="fw-bold text-dark"><?= sanitize($item['product_name']) ?></span>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="fw-semibold small"><?= sanitize($item['business_name']) ?></div>
                                                                            <small class="text-muted"><?= sanitize($item['vendor_owner']) ?></small>
                                                                        </td>
                                                                        <td class="text-center fw-bold"><?= (int)$item['quantity'] ?></td>
                                                                        <td class="text-end"><?= formatCurrency($item['price']) ?></td>
                                                                        <td class="text-end fw-bold text-dark"><?= formatCurrency($item['subtotal']) ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                            <tfoot class="table-light">
                                                                <tr>
                                                                    <td colspan="4" class="text-end fw-bold fs-6">Grand Total Amount:</td>
                                                                    <td class="text-end fw-bold text-success fs-5"><?= formatCurrency($o['total_amount']) ?></td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>

                                                    <!-- Order Status Update Form -->
                                                    <div class="p-3 bg-light rounded border">
                                                        <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-arrows-spin me-2"></i>Update Order Lifecycle & Payment Status</h6>
                                                        <form onsubmit="updateOrderStatus(event, <?= $o['id'] ?>)">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-semibold">Order Fulfillment Status</label>
                                                                    <select name="status" class="form-select form-select-sm" required>
                                                                        <option value="Pending" <?= $o['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                                        <option value="Confirmed" <?= $o['status'] === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                                        <option value="Processing" <?= $o['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                                                        <option value="Shipped" <?= $o['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                                                        <option value="Delivered" <?= $o['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                                                        <option value="Cancelled" <?= $o['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                                                        <option value="Refunded" <?= $o['status'] === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-semibold">Payment Status</label>
                                                                    <select name="payment_status" class="form-select form-select-sm" required>
                                                                        <option value="Pending" <?= $o['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                                        <option value="Paid" <?= $o['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                                                                        <option value="Failed" <?= $o['payment_status'] === 'Failed' ? 'selected' : '' ?>>Failed</option>
                                                                        <option value="Refunded" <?= $o['payment_status'] === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-12 d-flex justify-content-end gap-2">
                                                                    <button type="submit" class="btn btn-primary btn-sm fw-bold">
                                                                        <i class="fa-solid fa-floppy-disk me-1"></i>Save Status Changes
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light border-top">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                    <a href="<?= BASE_URL ?>order-details.php?id=<?= $o['id'] ?>" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
                                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Open Customer Tracking Page
                                                    </a>
                                                </div>
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

<!-- Instant Toast Notification Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="orderToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fs-6 fw-semibold" id="orderToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
function showToast(message, isSuccess = true) {
    const toastEl = document.getElementById('orderToast');
    const toastBody = document.getElementById('orderToastBody');
    if (!toastEl || !toastBody) return;
    
    toastEl.className = 'toast align-items-center text-white border-0 shadow-lg ' + (isSuccess ? 'bg-success' : 'bg-danger');
    toastBody.innerHTML = (isSuccess ? '<i class="fa-solid fa-circle-check me-2"></i>' : '<i class="fa-solid fa-triangle-exclamation me-2"></i>') + message;
    
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
}

function updateOrderStatus(e, orderId) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'update_order_status');
    formData.append('order_id', orderId);
    formData.append('ajax', '1');

    fetch('<?= BASE_URL ?>admin/orders.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            const modalEl = document.querySelector('#inspectModal' + orderId);
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Status update failed.', false);
        }
    })
    .catch(err => {
        showToast('Error connecting to server.', false);
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
