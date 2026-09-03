<?php
// SmartGov Market - Vendor Application & Document Submission
// File: vendor/application.php

$pageTitle = "Vendor Digital License Application";
require_once __DIR__ . '/../includes/header.php';
requireRole('vendor');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

// Fetch vendor record
$vStmt = $db->prepare("SELECT * FROM vendors WHERE user_id = ?");
$vStmt->execute([$user_id]);
$vendor = $vStmt->fetch();

if (!$vendor) {
    setFlashMessage('danger', 'Vendor profile not found.');
    redirect('index.php');
}

// Fetch Application record
$appStmt = $db->prepare("SELECT * FROM vendor_applications WHERE vendor_id = ? ORDER BY id DESC LIMIT 1");
$appStmt->execute([$vendor['id']]);
$application = $appStmt->fetch();

// Fetch Uploaded Documents
$docStmt = $db->prepare("SELECT * FROM vendor_documents WHERE application_id = ?");
$docStmt->execute([$application['id'] ?? 0]);
$documents = $docStmt->fetchAll();

$error = '';
$success = '';

// Handle Location & Details Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_location') {
    $lat = filter_var($_POST['latitude'] ?? 27.7172, FILTER_VALIDATE_FLOAT);
    $lng = filter_var($_POST['longitude'] ?? 85.3240, FILTER_VALIDATE_FLOAT);
    $address = sanitize($_POST['address'] ?? '');
    
    $updateLoc = $db->prepare("UPDATE vendors SET latitude = ?, longitude = ?, address = ? WHERE id = ?");
    $updateLoc->execute([$lat, $lng, $address, $vendor['id']]);
    setFlashMessage('success', 'Store map location updated successfully.');
    redirect('vendor/application.php');
}

// Handle Document Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_doc') {
    $doc_type = sanitize($_POST['document_type'] ?? '');
    
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['document_file']['tmp_name'];
        $fileName = $_FILES['document_file']['name'];
        $fileSize = $_FILES['document_file']['size'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) {
            $error = 'Invalid file format. Allowed formats: PDF, JPG, JPEG, PNG.';
        } elseif ($fileSize > 5 * 1024 * 1024) {
            $error = 'File size exceeds maximum limit of 5MB.';
        } else {
            $newFileName = "VND_DOC_" . time() . "_" . mt_rand(1000, 9999) . "." . $ext;
            $uploadDir = BASE_PATH . 'uploads/documents/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $targetPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmp, $targetPath)) {
                $relativePath = 'uploads/documents/' . $newFileName;

                // Check if document of this type already exists for this application
                $chkDoc = $db->prepare("SELECT id FROM vendor_documents WHERE application_id = ? AND document_type = ?");
                $chkDoc->execute([$application['id'], $doc_type]);
                $existDoc = $chkDoc->fetch();

                if ($existDoc) {
                    $docUpdate = $db->prepare("UPDATE vendor_documents SET file_name = ?, file_path = ?, file_size = ?, verification_status = 'Pending', officer_remarks = NULL, uploaded_at = NOW() WHERE id = ?");
                    $docUpdate->execute([$fileName, $relativePath, $fileSize, $existDoc['id']]);
                } else {
                    $docInsert = $db->prepare("INSERT INTO vendor_documents (application_id, document_type, file_name, file_path, file_size, verification_status) 
                                               VALUES (?, ?, ?, ?, ?, 'Pending')");
                    $docInsert->execute([$application['id'], $doc_type, $fileName, $relativePath, $fileSize]);
                }

                // If application was in Correction Required status, reset to Draft or Under Review for officer
                if ($application['status'] === 'Correction Required') {
                    $uApp = $db->prepare("UPDATE vendor_applications SET status = 'Under Review' WHERE id = ?");
                    $uApp->execute([$application['id']]);
                    $uVen = $db->prepare("UPDATE vendors SET status = 'Under Review' WHERE id = ?");
                    $uVen->execute([$vendor['id']]);
                }
                
                setFlashMessage('success', 'Document uploaded/updated successfully! Status set to Pending re-verification.');
                redirect('vendor/application.php');
            } else {
                $error = 'Failed to move uploaded file.';
            }
        }
    } else {
        $error = 'Please select a valid document file to upload.';
    }
}

// Handle Application Final Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_application') {
    if (count($documents) === 0) {
        $error = 'You must upload at least one verification document before submitting.';
    } else {
        $dept_id = (int)($_POST['department_id'] ?? 1);
        $updateApp = $db->prepare("UPDATE vendor_applications SET department_id = ?, status = 'Submitted', submitted_at = NOW() WHERE id = ?");
        $updateApp->execute([$dept_id, $application['id']]);
        
        $updateVendor = $db->prepare("UPDATE vendors SET status = 'Under Review' WHERE id = ?");
        $updateVendor->execute([$vendor['id']]);
        
        logAudit('Vendor Application Submitted', 'VendorApplication', $application['application_no'], "Application {$application['application_no']} submitted for review.");
        setFlashMessage('success', "Application {$application['application_no']} submitted successfully! Government officers will review your documents.");
        redirect('vendor/application.php');
    }
}
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'application'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="fa-solid fa-file-signature text-primary me-2"></i>Vendor Application & Verification</h4>
                        <small class="text-muted">Application No: <strong><?= sanitize($application['application_no']) ?></strong></small>
                    </div>
                    <?php 
                        $statusClass = 'bg-warning text-dark';
                        if ($application['status'] === 'Approved') $statusClass = 'bg-success text-white';
                        if ($application['status'] === 'Rejected') $statusClass = 'bg-danger text-white';
                    ?>
                    <span class="badge <?= $statusClass ?> fs-6 px-3 py-2"><?= sanitize($application['status']) ?></span>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <?php if ($application['officer_remarks']): ?>
                    <div class="alert alert-info py-2 small">
                        <strong><i class="fa-solid fa-comment-dots me-1"></i> Government Officer Remarks:</strong>
                        <p class="mb-0 mt-1"><?= sanitize($application['officer_remarks']) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Step 1: GIS Store Location Picker -->
                <div class="mb-4">
                    <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-map-location-dot me-2 text-danger"></i>Step 1: Set Business GIS Map Location</h5>
                    <p class="small text-muted mb-2">Click on the map or drag the pin to set your physical storefront location for citizens and inspectors.</p>
                    <div id="map" class="mb-3"></div>
                    
                    <form method="POST" action="" class="row g-2 align-items-end">
                        <input type="hidden" name="action" value="update_location">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Latitude</label>
                            <input type="text" name="latitude" id="lat-input" class="form-control form-control-sm" value="<?= sanitize($vendor['latitude']) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Longitude</label>
                            <input type="text" name="longitude" id="lng-input" class="form-control form-control-sm" value="<?= sanitize($vendor['longitude']) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="fa-solid fa-floppy-disk me-1"></i>Save Map Coordinates</button>
                        </div>
                    </form>
                </div>

                <!-- Step 2: Upload Documents -->
                <div class="mb-4 border-top pt-3">
                    <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-cloud-arrow-up me-2 text-success"></i>Step 2: Upload Required Business Certificates</h5>
                    
                    <form method="POST" action="" enctype="multipart/form-data" class="row g-3 mb-4 p-3 bg-light rounded border">
                        <input type="hidden" name="action" value="upload_doc">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Document Type</label>
                            <select name="document_type" class="form-select form-select-sm" required>
                                <option value="Tax Registration Certificate (PAN/VAT)">Tax Registration Certificate (PAN/VAT)</option>
                                <option value="Citizenship / ID Card Copy">Citizenship / ID Card Copy</option>
                                <option value="Local Municipality Business Permit">Local Municipality Business Permit</option>
                                <option value="Agriculture / Quality Clearance Certificate">Agriculture / Quality Clearance Certificate</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Choose File (PDF, JPG, PNG)</label>
                            <input type="file" name="document_file" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-success w-100"><i class="fa-solid fa-upload me-1"></i>Upload</button>
                        </div>
                    </form>

                    <!-- Uploaded Documents List -->
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>Document Type</th>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Verification Status</th>
                                    <th>Officer Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($documents) === 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No documents uploaded yet. Please upload required certificates.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($documents as $doc): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= sanitize($doc['document_type']) ?></td>
                                            <td><?= sanitize($doc['file_name']) ?></td>
                                            <td><?= number_format($doc['file_size'] / 1024, 1) ?> KB</td>
                                            <td>
                                                <span class="badge <?= $doc['verification_status'] === 'Verified' ? 'bg-success' : ($doc['verification_status'] === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                                    <?= sanitize($doc['verification_status']) ?>
                                                </span>
                                            </td>
                                            <td><small class="text-muted"><?= sanitize($doc['officer_remarks'] ?: 'N/A') ?></small></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>document-view.php?id=<?= $doc['id'] ?>" target="_blank" class="btn btn-xs btn-outline-info"><i class="fa-solid fa-eye me-1"></i>View Document</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Step 3: Final Submission -->
                <?php if ($application['status'] === 'Draft' || $application['status'] === 'Correction Required'): ?>
                    <div class="border-top pt-3">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="submit_application">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Target Department for Licensing</label>
                                <select name="department_id" class="form-select" required>
                                    <option value="1">Department of Commerce & Industry (Default)</option>
                                    <option value="2">Department of Agriculture & Food Safety</option>
                                    <option value="3">Consumer Protection Authority</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm" onclick="return confirm('Are you sure you want to submit your application for Government Review?');">
                                <i class="fa-solid fa-paper-plane me-2"></i>Submit Application to Government Officer
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    initLocationPicker('map', 'lat-input', 'lng-input', <?= $vendor['latitude'] ?>, <?= $vendor['longitude'] ?>);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
