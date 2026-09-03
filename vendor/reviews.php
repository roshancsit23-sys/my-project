<?php
// SmartGov Market - Vendor Customer Product Reviews
// File: vendor/reviews.php

$pageTitle = "Customer Product Reviews";
require_once __DIR__ . '/../includes/header.php';
requireRole('vendor');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

$vStmt = $db->prepare("SELECT id FROM vendors WHERE user_id = ?");
$vStmt->execute([$user_id]);
$vendor = $vStmt->fetch();
$vendor_id = $vendor['id'] ?? 0;

$reviews = $db->query("SELECT r.*, p.name as product_name, u.full_name as customer_name, o.order_no 
                       FROM reviews r
                       JOIN products p ON r.product_id = p.id
                       JOIN users u ON r.user_id = u.id
                       JOIN orders o ON r.order_id = o.id
                       WHERE p.vendor_id = {$vendor_id}
                       ORDER BY r.created_at DESC")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'reviews'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-star text-warning me-2"></i>Verified Customer Product Reviews</h4>
                    <span class="badge bg-primary rounded-pill fs-6"><?= count($reviews) ?> Reviews</span>
                </div>

                <div class="row g-3">
                    <?php if (count($reviews) === 0): ?>
                        <div class="col-12 text-center text-muted py-4">No reviews submitted yet for your products.</div>
                    <?php else: ?>
                        <?php foreach ($reviews as $r): ?>
                            <div class="col-12">
                                <div class="p-3 bg-light rounded border">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <strong class="text-dark me-2"><?= sanitize($r['customer_name']) ?></strong>
                                            <span class="badge bg-success-subtle text-success fs-7"><i class="fa-solid fa-circle-check me-1"></i>Verified Purchaser</span>
                                        </div>
                                        <small class="text-muted"><?= date('M d, Y', strtotime($r['created_at'])) ?></small>
                                    </div>
                                    <div class="text-warning mb-2">
                                        <?php for ($i=1; $i<=5; $i++): ?>
                                            <i class="fa-<?= $i <= $r['rating'] ? 'solid' : 'regular' ?> fa-star"></i>
                                        <?php endfor; ?>
                                        <span class="fw-bold text-dark ms-2"><?= $r['rating'] ?>/5</span>
                                    </div>
                                    <p class="mb-1 text-secondary">"<?= sanitize($r['comment']) ?>"</p>
                                    <small class="text-muted">Product: <strong class="text-primary"><?= sanitize($r['product_name']) ?></strong> | Order: <?= sanitize($r['order_no']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
