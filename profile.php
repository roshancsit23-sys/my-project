<?php
// SmartGov Market - User Profile Management & Password Security
// File: profile.php

$pageTitle = "My Profile Settings";
require_once __DIR__ . '/includes/header.php';
requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$user = currentUser();

$error = '';
$success = '';

// Handle Profile Details Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $municipality = sanitize($_POST['municipality'] ?? '');
    $district = sanitize($_POST['district'] ?? '');
    
    if (empty($full_name) || empty($phone)) {
        $error = 'Full name and phone number are required.';
    } else {
        $uStmt = $db->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, municipality = ?, district = ?, updated_at = NOW() WHERE id = ?");
        $uStmt->execute([$full_name, $phone, $address, $municipality, $district, $user_id]);
        
        $_SESSION['user_name'] = $full_name;
        logAudit('Profile Updated', 'User', $user_id, "User ID {$user_id} updated profile details.");
        setFlashMessage('success', 'Profile information updated successfully!');
        redirect('profile.php');
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill in all password fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif (!password_verify($current_password, $user['password_hash'])) {
        $error = 'Incorrect current password.';
    } else {
        $newHash = password_hash($new_password, PASSWORD_BCRYPT);
        $pStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $pStmt->execute([$newHash, $user_id]);
        
        logAudit('Password Changed', 'User', $user_id, "User ID {$user_id} changed account password.");
        setFlashMessage('success', 'Password updated successfully!');
        redirect('profile.php');
    }
}
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'profile'; include __DIR__ . '/includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="fa-solid fa-user-gear text-primary me-2"></i>Account & Profile Settings</h4>
                        <small class="text-muted">Role: <span class="badge bg-primary text-uppercase"><?= sanitize($user['role_name']) ?></span></small>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Profile Information Form -->
                    <div class="col-md-7">
                        <div class="p-3 bg-light rounded border">
                            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-id-card me-2 text-primary"></i>Personal Information</h5>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Full Name *</label>
                                    <input type="text" name="full_name" class="form-control" value="<?= sanitize($user['full_name']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Email Address (Read-only)</label>
                                    <input type="email" class="form-control bg-light" value="<?= sanitize($user['email']) ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Phone Number *</label>
                                    <input type="text" name="phone" class="form-control" value="<?= sanitize($user['phone']) ?>" required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">District</label>
                                        <input type="text" name="district" class="form-control" value="<?= sanitize($user['district']) ?>">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">Municipality</label>
                                        <input type="text" name="municipality" class="form-control" value="<?= sanitize($user['municipality']) ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Street Address</label>
                                    <textarea name="address" class="form-control" rows="2"><?= sanitize($user['address']) ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                                    <i class="fa-solid fa-floppy-disk me-1"></i>Save Profile Changes
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Password Security Form -->
                    <div class="col-md-5">
                        <div class="p-3 bg-light rounded border">
                            <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-lock me-2 text-danger"></i>Change Password</h5>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="change_password">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Current Password *</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">New Password *</label>
                                    <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Confirm New Password *</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-danger fw-bold w-100 shadow-sm">
                                    <i class="fa-solid fa-key me-1"></i>Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
