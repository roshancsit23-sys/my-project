<?php
// SmartGov Market - Vendor Registration Page
// File: vendor/register.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    $role = currentRole();
    if ($role === 'vendor') redirect('vendor/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_name = sanitize($_POST['owner_name'] ?? '');
    $business_name = sanitize($_POST['business_name'] ?? '');
    $business_type = sanitize($_POST['business_type'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $municipality = sanitize($_POST['municipality'] ?? '');
    $district = sanitize($_POST['district'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($owner_name) || empty($business_name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Please complete all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email address already exists.';
        } else {
            $db->beginTransaction();
            try {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $role_id = 3; // Vendor role
                
                // 1. Create User
                $uInsert = $db->prepare("INSERT INTO users (role_id, full_name, email, phone, password_hash, address, municipality, district, is_verified, is_active, created_at) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 1, NOW())");
                $uInsert->execute([$role_id, $owner_name, $email, $phone, $passwordHash, $address, $municipality, $district]);
                $user_id = $db->lastInsertId();
                
                // 2. Create Vendor record (Status: Pending)
                $vInsert = $db->prepare("INSERT INTO vendors (user_id, business_name, business_type, address, municipality, district, status, created_at) 
                                         VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())");
                $vInsert->execute([$user_id, $business_name, $business_type, $address, $municipality, $district]);
                $vendor_id = $db->lastInsertId();
                
                // 3. Create Vendor Application (Status: Draft)
                $app_no = generateCode('VND', 'vendor_applications', 'application_no');
                $appInsert = $db->prepare("INSERT INTO vendor_applications (application_no, vendor_id, status, submitted_at) VALUES (?, ?, 'Draft', NOW())");
                $appInsert->execute([$app_no, $vendor_id]);
                
                $db->commit();
                
                // Auto login vendor
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $owner_name;
                $_SESSION['user_email'] = $email;
                $_SESSION['role_id'] = $role_id;
                $_SESSION['role_name'] = 'vendor';
                
                logAudit('Vendor Register', 'Vendor', $vendor_id, "Vendor registered: {$business_name} (App: {$app_no})");
                setFlashMessage('success', "✓ Vendor account created! Business application {$app_no} generated. Welcome to your Vendor Dashboard.");
                redirect('vendor/dashboard.php');
                
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'Registration Failed: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = "Vendor Registration";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Account Type Choice Header -->
            <div class="card p-3 mb-4 bg-light border-0 shadow-sm rounded-4 text-center">
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= BASE_URL ?>register.php" class="btn btn-outline-primary fw-bold px-4 py-2"><i class="fa-solid fa-user me-2"></i>Citizen Registration</a>
                    <a href="<?= BASE_URL ?>vendor/register.php" class="btn btn-success fw-bold px-4 py-2 shadow-sm"><i class="fa-solid fa-shop me-2"></i>Vendor / Business Registration</a>
                </div>
            </div>

            <div class="card card-custom p-4 shadow-lg border-0">
                <div class="text-center mb-4">
                    <div class="bg-success-subtle text-success d-inline-flex p-3 rounded-circle mb-3">
                        <i class="fa-solid fa-shop fs-2"></i>
                    </div>
                    <h3 class="fw-bold">Vendor Registration Portal</h3>
                    <p class="text-muted small">Register your business to obtain a Digital Business License and start selling on SmartGov Market.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small shadow-sm"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <h5 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="fa-solid fa-user me-2"></i>Owner & Account Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Business Owner Name *</label>
                            <input type="text" name="owner_name" class="form-control" placeholder="e.g. Ram Bahadur Thapa" value="<?= sanitize($_POST['owner_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="vendor@example.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Phone *</label>
                            <input type="text" name="phone" class="form-control" placeholder="98XXXXXXXX" value="<?= sanitize($_POST['phone'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password *</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>

                    <h5 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="fa-solid fa-store me-2"></i>Business Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Registered Business Name *</label>
                            <input type="text" name="business_name" class="form-control" placeholder="e.g. Kathmandu Himalayan Crafts" value="<?= sanitize($_POST['business_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Business Type / Sector *</label>
                            <select name="business_type" class="form-select" required>
                                <option value="">-- Select Business Type --</option>
                                <option value="Agriculture & Food Products">Agriculture & Food Products</option>
                                <option value="Handicrafts & Souvenirs">Handicrafts & Souvenirs</option>
                                <option value="Clothing & Textiles">Clothing & Textiles</option>
                                <option value="Ayurvedic & Herbal Wellness">Ayurvedic & Herbal Wellness</option>
                                <option value="General Retail Store">General Retail Store</option>
                                <option value="Electronics & Appliances">Electronics & Appliances</option>
                                <option value="Books & Stationery">Books & Stationery</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">District *</label>
                            <input type="text" name="district" class="form-control" placeholder="e.g. Kathmandu" value="<?= sanitize($_POST['district'] ?? 'Kathmandu') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Municipality / Local Body *</label>
                            <input type="text" name="municipality" class="form-control" placeholder="e.g. Kathmandu Metro" value="<?= sanitize($_POST['municipality'] ?? 'Kathmandu Metro') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Street Address *</label>
                            <input type="text" name="address" class="form-control" placeholder="e.g. Ward 3, Thamel" value="<?= sanitize($_POST['address'] ?? '') ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-3 fw-bold mb-3 shadow-sm">
                        <i class="fa-solid fa-paper-plane me-2"></i>Register Vendor Account & Open Dashboard
                    </button>
                </form>

                <div class="text-center small border-top pt-3">
                    Already a registered vendor? <a href="<?= BASE_URL ?>login.php" class="fw-bold text-decoration-none">Sign In to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
