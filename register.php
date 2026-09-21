<?php
// SmartGov Market - Citizen Account Registration Page
// File: register.php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim(sanitize($_POST['full_name'] ?? ''));
    $email = trim(sanitize($_POST['email'] ?? ''));
    $phone = trim(sanitize($_POST['phone'] ?? ''));
    $address = trim(sanitize($_POST['address'] ?? ''));
    $municipality = trim(sanitize($_POST['municipality'] ?? ''));
    $district = trim(sanitize($_POST['district'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($municipality) || empty($district) || empty($password)) {
        $error = 'Please fill in all required fields including your full residential address details.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $db = getDBConnection();

        // Prevent duplicate email registrations
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email address already exists. Please sign in instead.';
        } else {
            // Check duplicate phone
            $pCheck = $db->prepare("SELECT id FROM users WHERE phone = ?");
            $pCheck->execute([$phone]);
            if ($pCheck->fetch()) {
                $error = 'An account with this phone number is already registered.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $role_id = 4; // Customer / Citizen role
                
                $fullAddress = "{$address}, {$municipality}, {$district}";

                $insert = $db->prepare("INSERT INTO users (role_id, full_name, email, phone, password_hash, address, municipality, district, is_verified, is_active, created_at) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 1, NOW())");
                $insert->execute([$role_id, $full_name, $email, $phone, $passwordHash, $fullAddress, $municipality, $district]);
                $user_id = $db->lastInsertId();
                
                // Add default address to addresses table
                $addrInsert = $db->prepare("INSERT INTO addresses (user_id, address_line, municipality, district, is_default) VALUES (?, ?, ?, ?, 1)");
                $addrInsert->execute([$user_id, $address, $municipality, $district]);
                
                logAudit('Register', 'User', $user_id, "New citizen registered: {$full_name} ({$email})");
                
                setFlashMessage('success', 'Registration successful! Your citizen account has been created. Please sign in below.');
                redirect('login.php');
            }
        }
    }
}

$pageTitle = "Citizen Account Registration";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Account Type Choice Header -->
            <div class="card p-3 mb-4 bg-light border-0 shadow-sm rounded-4 text-center">
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= BASE_URL ?>register.php" class="btn btn-primary fw-bold px-4 py-2 shadow-sm"><i class="fa-solid fa-user me-2"></i>Citizen Registration</a>
                    <a href="<?= BASE_URL ?>vendor/register.php" class="btn btn-outline-success fw-bold px-4 py-2"><i class="fa-solid fa-shop me-2"></i>Vendor / Business Registration</a>
                </div>
            </div>

            <div class="card card-custom p-4 p-md-5 shadow-lg border-0 rounded-4">
                <div class="text-center mb-4">
                    <div class="bg-primary-subtle text-primary d-inline-flex p-3 rounded-circle mb-3">
                        <i class="fa-solid fa-user-plus fs-2"></i>
                    </div>
                    <h3 class="fw-bold">Citizen Account Registration</h3>
                    <p class="text-muted small">Create your verified citizen account to access e-governance services, track grievances, and purchase local market products.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small shadow-sm"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php" id="citizenRegisterForm" onsubmit="handleRegistrationSubmit(event)">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" placeholder="e.g. Sita Sharma" value="<?= sanitize($_POST['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="sita@example.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone Number *</label>
                            <input type="tel" name="phone" class="form-control" placeholder="98XXXXXXXX" value="<?= sanitize($_POST['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">District *</label>
                            <input type="text" name="district" class="form-control" placeholder="e.g. Kathmandu" value="<?= sanitize($_POST['district'] ?? 'Kathmandu') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Municipality / Local Area *</label>
                            <input type="text" name="municipality" class="form-control" placeholder="e.g. Kathmandu Metropolitan City" value="<?= sanitize($_POST['municipality'] ?? 'Kathmandu Metropolitan') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Street / House / Tole Address *</label>
                            <input type="text" name="address" class="form-control" placeholder="e.g. House 42, Ward 10, New Baneshwor" value="<?= sanitize($_POST['address'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password *</label>
                            <input type="password" name="password" id="reg-password" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm Password *</label>
                            <input type="password" name="confirm_password" id="reg-confirm-password" class="form-control" placeholder="Re-enter password" required minlength="6">
                        </div>
                    </div>

                    <button type="submit" id="regSubmitBtn" class="btn btn-primary w-100 py-3 fw-bold mb-3 shadow-sm">
                        <i class="fa-solid fa-user-plus me-2"></i>Create Citizen Account
                    </button>
                </form>

                <div class="text-center small border-top pt-3">
                    Already registered? <a href="<?= BASE_URL ?>login.php" class="fw-bold text-decoration-none">Sign In Here</a> | Are you a local vendor? <a href="<?= BASE_URL ?>vendor/register.php" class="fw-bold text-success text-decoration-none">Register as Vendor</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let formSubmitting = false;

function handleRegistrationSubmit(e) {
    if (formSubmitting) {
        e.preventDefault();
        return false;
    }

    const pass = document.getElementById('reg-password').value;
    const confirmPass = document.getElementById('reg-confirm-password').value;

    if (pass !== confirmPass) {
        alert('Passwords do not match. Please ensure both passwords match.');
        e.preventDefault();
        return false;
    }

    formSubmitting = true;
    const btn = document.getElementById('regSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Creating Citizen Account...';
    return true;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
