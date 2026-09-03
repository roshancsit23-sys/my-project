<?php
// SmartGov Market - Password Reset Execution Page
// File: reset-password.php

$pageTitle = "Reset Password";
require_once __DIR__ . '/includes/header.php';

$email = sanitize($_GET['email'] ?? '');
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $newHash = password_hash($new_password, PASSWORD_BCRYPT);
            $update = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $update->execute([$newHash, $user['id']]);

            logAudit('Password Reset Completed', 'User', $user['id'], "Password reset completed for {$email}");
            setFlashMessage('success', 'Your password has been reset successfully! Please sign in with your new password.');
            redirect('login.php');
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
                    <div class="bg-primary-subtle text-primary d-inline-flex p-3 rounded-circle mb-3">
                        <i class="fa-solid fa-key fs-2"></i>
                    </div>
                    <h3 class="fw-bold">Create New Password</h3>
                    <p class="text-muted small">Enter your account email and new password</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address *</label>
                        <input type="email" name="email" class="form-control" value="<?= sanitize($email) ?>" placeholder="user@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password *</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm New Password *</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mb-3 shadow-sm">
                        <i class="fa-solid fa-check-circle me-2"></i>Reset Password & Sign In
                    </button>
                </form>

                <div class="text-center small">
                    <a href="<?= BASE_URL ?>login.php" class="fw-bold text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
