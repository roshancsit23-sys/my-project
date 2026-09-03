<?php
// SmartGov Market - Admin System Orders Overview
// File: admin/orders.php

$pageTitle = "System Orders Overview";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();
$orders = $db->query("SELECT o.*, u.full_name as customer_name, u.phone as customer_phone 
                      FROM orders o
                      JOIN users u ON o.customer_id = u.id
                      ORDER BY o.created_at DESC")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'orders'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-cart-flatbed text-primary me-2"></i>System-wide E-Commerce Orders</h4>
                    <span class="badge bg-primary rounded-pill fs-6"><?= count($orders) ?> Total Orders</span>
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
                                <th>Fulfillment Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= sanitize($o['order_no']) ?></td>
                                    <td><?= sanitize($o['customer_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= sanitize($o['payment_method']) ?></span></td>
                                    <td class="fw-bold text-success"><?= formatCurrency($o['total_amount']) ?></td>
                                    <td>
                                        <span class="badge <?= $o['status'] === 'Delivered' ? 'bg-success' : ($o['status'] === 'Shipped' ? 'bg-info text-dark' : 'bg-warning text-dark') ?>">
                                            <?= sanitize($o['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order-details.php?id=<?= $o['id'] ?>" class="btn btn-xs btn-outline-primary"><i class="fa-solid fa-eye me-1"></i>Inspect</a>
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
