<?php
// SmartGov Market - In-App Notifications Hub
// File: notifications.php

$pageTitle = "Notifications";
require_once __DIR__ . '/includes/header.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Mark all as read
markNotificationsAsRead($user_id);

$notifications = getUserNotifications($user_id, 30);
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-regular fa-bell text-primary me-2"></i>Notifications & System Alerts</h4>
                    <span class="badge bg-primary rounded-pill fs-6"><?= count($notifications) ?> Alerts</span>
                </div>

                <div class="list-group list-group-flush">
                    <?php if (count($notifications) === 0): ?>
                        <div class="text-center text-muted py-5">No notifications.</div>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                            <div class="list-group-item p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold mb-0 text-dark"><?= sanitize($n['title']) ?></h6>
                                    <small class="text-muted"><?= date('M d, Y h:i A', strtotime($n['created_at'])) ?></small>
                                </div>
                                <p class="mb-1 text-secondary small"><?= sanitize($n['message']) ?></p>
                                <?php if ($n['link']): ?>
                                    <a href="<?= BASE_URL . $n['link'] ?>" class="small fw-bold text-decoration-none">View Details <i class="fa-solid fa-arrow-right ms-1"></i></a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
