<?php
// HATIYA - User Authentication Login Page
// File: login.php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    $role = currentRole();
    if ($role === 'admin') redirect('admin/dashboard.php');
    elseif ($role === 'officer') redirect('officer/dashboard.php');
    elseif ($role === 'vendor') redirect('vendor/dashboard.php');
    else redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['is_active'] != 1) {
                $error = 'Your account has been deactivated. Please contact administration.';
            } else {
                loginUser($user);
                setFlashMessage('success', "Welcome back, {$user['full_name']}!");
                
                $role = currentRole();
                if ($role === 'admin') redirect('admin/dashboard.php');
                elseif ($role === 'officer') redirect('officer/dashboard.php');
                elseif ($role === 'vendor') redirect('vendor/dashboard.php');
                else redirect('index.php');
            }
        } else {
            $error = 'Invalid email address or password.';
        }
    }
}

$pageTitle = "Portal Sign In";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card card-gov p-4 shadow-lg border-0">
                <div class="text-center mb-4">
                    <img src="<?= BASE_URL ?>assets/images/nepal-emblem.svg" alt="Emblem of Nepal" height="68" class="mb-3">
                    <h4 class="fw-bold text-dark">Sign In to HATIYA</h4>
                    <p class="text-muted small mb-0">Digital Governance &amp; Marketplace Platform</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small shadow-sm border-0"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="user@example.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Password</label>
                            <a href="<?= BASE_URL ?>forgot-password.php" class="small text-decoration-none text-danger fw-semibold">Forgot?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gov-primary w-100 py-2.5 fw-bold mb-3 shadow-sm">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
                    </button>
                </form>

                <!-- Demo Credentials Box -->
                <div class="p-3 bg-light rounded-3 border small mb-3">
                    <strong class="d-block mb-1 text-primary"><i class="fa-solid fa-key me-1"></i> Quick Demo Logins:</strong>
                    <div class="row g-1 text-muted fs-7">
                        <div class="col-6"><strong>Admin:</strong> admin@smartgov.gov.np</div>
                        <div class="col-6"><strong>Officer:</strong> officer@smartgov.gov.np</div>
                        <div class="col-6"><strong>Vendor:</strong> vendor@localcrafts.np</div>
                        <div class="col-6"><strong>Citizen:</strong> customer@gmail.com</div>
                    </div>
                    <div class="mt-1 text-muted fs-7"><em>Default Demo Password: <code>password123</code></em></div>
                </div>

                <div class="text-center small border-top pt-3">
                    Don't have an account? <a href="<?= BASE_URL ?>register.php" class="fw-bold text-danger">Register as Citizen</a> | <a href="<?= BASE_URL ?>vendor/register.php" class="fw-bold text-primary">Register as Vendor</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
