<?php
// SmartGov Market - Password Reset Request Page
// File: forgot-password.php

$pageTitle = "Forgot Password";
require_once __DIR__ . '/includes/header.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your registered email address.';
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $resetLink = BASE_URL . "reset-password.php?email=" . urlencode($email);
            $success = "Reset link generated for <strong>{$email}</strong>.<br><a href='{$resetLink}' class='fw-bold text-success text-decoration-underline mt-2 d-inline-block'><i class='fa-solid fa-arrow-right me-1'></i>Click here to set new password now</a>";
            logAudit('Password Reset Requested', 'User', $user['id'], "Password reset requested for {$email}");
        } else {
            $error = 'No user account found with that email address.';
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card card-custom p-4 shadow-lg border-0">
                <div class="text-center mb-4">
                    <h3 class="fw-bold">Reset Password</h3>
                    <p class="text-muted small">Enter your email to receive password reset instructions</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success py-2 small"><i class="fa-solid fa-circle-check me-1"></i> <?= $success ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="user@example.com" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mb-3 shadow-sm">
                        <i class="fa-solid fa-paper-plane me-2"></i>Send Reset Instructions
                    </button>
                </form>

                <div class="text-center small">
                    <a href="<?= BASE_URL ?>login.php" class="fw-bold text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back to Sign In</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
