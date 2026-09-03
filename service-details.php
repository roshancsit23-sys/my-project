<?php
// SmartGov Market - Government Service Application Form
// File: service-details.php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$db = getDBConnection();
$service_id = (int)($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT gs.*, d.department_name FROM government_services gs JOIN departments d ON gs.department_id = d.id WHERE gs.id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    setFlashMessage('danger', 'Government service not found.');
    redirect('government-services.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remarks = sanitize($_POST['remarks'] ?? '');
    $document_path = null;

    // Handle Optional File Upload (Citizenship document, photo, ID, etc.)
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['document']['tmp_name'];
        $fileName = $_FILES['document']['name'];
        $fileSize = $_FILES['document']['size'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

        if ($fileSize > 5 * 1024 * 1024) {
            $error = 'File size exceeds 5MB limit. Please upload a smaller document.';
        } elseif (!in_array($ext, $allowed, true)) {
            $error = 'Invalid file type. Only JPG, JPEG, PNG, and PDF files are allowed.';
        } else {
            $newFileName = "SRV_" . time() . "_" . mt_rand(1000, 9999) . "." . $ext;
            $uploadDir = BASE_PATH . 'uploads/services/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                $document_path = 'uploads/services/' . $newFileName;
            } else {
                $error = 'Failed to upload document file. Please try again.';
            }
        }
    }

    if (empty($error)) {
        $app_no = generateCode('SRV', 'service_applications', 'application_no');

        $insert = $db->prepare("INSERT INTO service_applications (application_no, service_id, user_id, status, remarks, document_path, submitted_at) VALUES (?, ?, ?, 'Submitted', ?, ?, NOW())");
        $insert->execute([$app_no, $service_id, $user_id, $remarks, $document_path]);

        logAudit('Gov Service Application', 'ServiceApplication', $app_no, "Customer applied for service {$service['service_name']} (App: {$app_no})");
        setFlashMessage('success', "✓ Application {$app_no} for '{$service['service_name']}' submitted successfully! You can track status below.");
        redirect('applications.php');
    }
}

$pageTitle = "Service Details & Application";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-custom p-4 shadow-lg border-0">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4 flex-wrap gap-2">
                    <div>
                        <span class="badge bg-primary-subtle text-primary border mb-1"><?= sanitize($service['department_name']) ?></span>
                        <h3 class="fw-bold mb-0"><?= sanitize($service['service_name']) ?></h3>
                    </div>
                    <a href="government-services.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to Services</a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small shadow-sm"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <p class="text-secondary mb-4"><?= sanitize($service['description']) ?></p>

                <div class="p-3 bg-light rounded border mb-4">
                    <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-list-check me-2"></i>Requirements & Eligibility Criteria:</h6>
                    <p class="mb-2 text-dark"><?= sanitize($service['requirements']) ?></p>
                    <div class="d-flex gap-4 small text-muted pt-2 border-top flex-wrap">
                        <span><i class="fa-solid fa-clock me-1 text-primary"></i>Processing Time: <strong><?= sanitize($service['processing_time']) ?></strong></span>
                        <span><i class="fa-solid fa-tag me-1 text-success"></i>Fee: <strong><?= $service['fee'] > 0 ? formatCurrency($service['fee']) : 'FREE' ?></strong></span>
                    </div>
                </div>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Application Details / Remarks *</label>
                        <textarea name="remarks" class="form-control" rows="4" placeholder="Enter your full name, citizenship no., property details, or application request notes..." required><?= sanitize($_POST['remarks'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Upload Supporting Document (Optional: JPG, PNG, PDF, Max 5MB)</label>
                        <input type="file" name="document" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="form-text text-muted">Attach citizenship scan, passport photo, recommendation letter, or business registration if required.</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                        <i class="fa-solid fa-paper-plane me-2"></i>Submit Official Service Application
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
