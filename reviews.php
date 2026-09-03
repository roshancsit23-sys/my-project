<?php
// SmartGov Market - Customer Product Review Submission
// File: reviews.php

$pageTitle = "Write Product Review";
require_once __DIR__ . '/includes/header.php';
requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$product_id = (int)($_GET['product_id'] ?? 0);
$order_id = (int)($_GET['order_id'] ?? 0);

// Verify purchase of delivered order
$verifyStmt = $db->prepare("SELECT oi.id, p.name as product_name, o.order_no 
                            FROM order_items oi
                            JOIN orders o ON oi.order_id = o.id
                            JOIN products p ON oi.product_id = p.id
                            WHERE oi.product_id = ? AND oi.order_id = ? AND o.customer_id = ? AND o.status = 'Delivered'");
$verifyStmt->execute([$product_id, $order_id, $user_id]);
$purchase = $verifyStmt->fetch();

if (!$purchase) {
    setFlashMessage('danger', 'You can only review products from your completed and delivered orders.');
    redirect('orders.php');
}

// Check existing review
$checkRev = $db->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ? AND order_id = ?");
$checkRev->execute([$product_id, $user_id, $order_id]);
if ($checkRev->fetch()) {
    setFlashMessage('info', 'You have already submitted a review for this delivered product item.');
    redirect("order-details.php?id={$order_id}");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 5);
    $comment = sanitize($_POST['comment'] ?? '');
    
    if ($rating < 1 || $rating > 5 || empty($comment)) {
        $error = 'Please provide a star rating (1-5) and review comment.';
    } else {
        $rInsert = $db->prepare("INSERT INTO reviews (product_id, user_id, order_id, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $rInsert->execute([$product_id, $user_id, $order_id, $rating, $comment]);
        
        logAudit('Review Submitted', 'Review', $db->lastInsertId(), "Customer reviewed product ID {$product_id} with rating {$rating}");
        setFlashMessage('success', 'Thank you! Your verified product review has been published.');
        redirect("order-details.php?id={$order_id}");
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card card-custom p-4 shadow-lg border-0">
                <div class="text-center mb-4">
                    <div class="bg-warning-subtle text-warning d-inline-flex p-3 rounded-circle mb-3">
                        <i class="fa-solid fa-star fs-2"></i>
                    </div>
                    <h4 class="fw-bold">Write Verified Product Review</h4>
                    <p class="text-muted small">Share your experience for <strong><?= sanitize($purchase['product_name']) ?></strong> (Order #<?= sanitize($purchase['order_no']) ?>)</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4 text-center">
                        <label class="form-label d-block fw-semibold">Select Rating (1 to 5 Stars):</label>
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" checked><label for="star5" title="5 stars"><i class="fa-solid fa-star"></i></label>
                            <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="4 stars"><i class="fa-solid fa-star"></i></label>
                            <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="3 stars"><i class="fa-solid fa-star"></i></label>
                            <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="2 stars"><i class="fa-solid fa-star"></i></label>
                            <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="1 star"><i class="fa-solid fa-star"></i></label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Your Review & Product Feedback</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Write about product quality, freshness, vendor delivery, or overall satisfaction..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 py-2 fw-bold text-dark shadow-sm">
                        <i class="fa-solid fa-paper-plane me-2"></i>Publish Review
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
