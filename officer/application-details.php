<?php
// SmartGov Market - Officer Application Review & Decision Portal
// File: officer/application-details.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/license_generator.php';
requireRole(['officer', 'admin']);

$db = getDBConnection();
$app_id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT va.*, v.id as vendor_id, v.business_name, v.business_type, v.address, v.municipality, v.district, v.latitude, v.longitude, v.status as vendor_status,
                      u.full_name as owner_name, u.email, u.phone, d.department_name
                      FROM vendor_applications va
                      JOIN vendors v ON va.vendor_id = v.id
                      JOIN users u ON v.user_id = u.id
                      LEFT JOIN departments d ON va.department_id = d.id
                      WHERE va.id = ?");
$stmt->execute([$app_id]);
$app = $stmt->fetch();

if (!$app) {
    setFlashMessage('danger', 'Application not found.');
    redirect('officer/vendor-applications.php');
}

// Fetch Documents
$docStmt = $db->prepare("SELECT * FROM vendor_documents WHERE application_id = ?");
$docStmt->execute([$app_id]);
$documents = $docStmt->fetchAll();

// Check if license already exists
$license = getVendorLicense($app['vendor_id']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $remarks = sanitize($_POST['officer_remarks'] ?? '');
    $officer_id = $_SESSION['user_id'];

    if ($action === 'verify_doc') {
        $doc_id = (int)($_POST['document_id'] ?? 0);
        $doc_remarks = sanitize($_POST['doc_remarks'] ?? 'Document verified');
        $uDoc = $db->prepare("UPDATE vendor_documents SET verification_status = 'Verified', officer_remarks = ? WHERE id = ? AND application_id = ?");
        $uDoc->execute([$doc_remarks, $doc_id, $app_id]);
        setFlashMessage('success', 'Document verified successfully.');
        redirect("officer/application-details.php?id={$app_id}");

    } elseif ($action === 'reject_doc') {
        $doc_id = (int)($_POST['document_id'] ?? 0);
        $doc_remarks = sanitize($_POST['doc_remarks'] ?? 'Document rejected. Please upload replacement.');
        $uDoc = $db->prepare("UPDATE vendor_documents SET verification_status = 'Rejected', officer_remarks = ? WHERE id = ? AND application_id = ?");
        $uDoc->execute([$doc_remarks, $doc_id, $app_id]);
        
        // Automatically set application to Correction Required
        $update = $db->prepare("UPDATE vendor_applications SET status = 'Correction Required', officer_remarks = ?, assigned_officer_id = ?, reviewed_at = NOW() WHERE id = ?");
        $update->execute(["Document rejected: {$doc_remarks}", $officer_id, $app_id]);
        $vUpdate = $db->prepare("UPDATE vendors SET status = 'Correction Required' WHERE id = ?");
        $vUpdate->execute([$app['vendor_id']]);

        setFlashMessage('warning', 'Document marked as Rejected. Application status updated to Correction Required.');
        redirect("officer/application-details.php?id={$app_id}");

    } elseif ($action === 'approve') {
        // Verify all uploaded documents are Verified
        $checkDocs = $db->prepare("SELECT COUNT(*) FROM vendor_documents WHERE application_id = ? AND verification_status != 'Verified'");
        $checkDocs->execute([$app_id]);
        $unverifiedCount = $checkDocs->fetchColumn();

        if (count($documents) === 0) {
            setFlashMessage('danger', 'Cannot approve application: Vendor has not submitted any verification documents.');
            redirect("officer/application-details.php?id={$app_id}");
        } elseif ($unverifiedCount > 0) {
            setFlashMessage('danger', 'Cannot approve application: All uploaded verification documents must be verified first.');
            redirect("officer/application-details.php?id={$app_id}");
        } else {
            // Update Application
            $update = $db->prepare("UPDATE vendor_applications SET status = 'Approved', officer_remarks = ?, assigned_officer_id = ?, reviewed_at = NOW(), approved_at = NOW() WHERE id = ?");
            $update->execute([$remarks, $officer_id, $app_id]);
            
            // Issue Digital License & Update Vendor status to Approved
            $lic = issueDigitalLicense($app['vendor_id'], $app_id, $app['department_name'] ?: 'Department of Commerce & Industry');
            
            logAudit('Vendor Approved', 'VendorApplication', $app['application_no'], "Vendor {$app['business_name']} approved by officer. License {$lic['license_no']} issued.");
            setFlashMessage('success', "Vendor {$app['business_name']} has been APPROVED! Digital License {$lic['license_no']} generated successfully.");
            redirect("officer/application-details.php?id={$app_id}");
        }
        
    } elseif ($action === 'request_correction') {
        $update = $db->prepare("UPDATE vendor_applications SET status = 'Correction Required', officer_remarks = ?, assigned_officer_id = ?, reviewed_at = NOW() WHERE id = ?");
        $update->execute([$remarks, $officer_id, $app_id]);
        
        $vUpdate = $db->prepare("UPDATE vendors SET status = 'Correction Required' WHERE id = ?");
        $vUpdate->execute([$app['vendor_id']]);
        
        sendNotification($app['vendor_id'], 'Correction Required for Application', "Officer remarks: {$remarks}. Please update documents.", 'vendor/application.php');
        
        setFlashMessage('warning', 'Correction request sent to vendor.');
        redirect("officer/application-details.php?id={$app_id}");
        
    } elseif ($action === 'reject') {
        $update = $db->prepare("UPDATE vendor_applications SET status = 'Rejected', officer_remarks = ?, assigned_officer_id = ?, reviewed_at = NOW() WHERE id = ?");
        $update->execute([$remarks, $officer_id, $app_id]);
        
        $vUpdate = $db->prepare("UPDATE vendors SET status = 'Rejected' WHERE id = ?");
        $vUpdate->execute([$app['vendor_id']]);
        
        sendNotification($app['vendor_id'], 'Vendor Application Rejected', "Your application {$app['application_no']} has been rejected. Remarks: {$remarks}", 'vendor/application.php');
        
        setFlashMessage('danger', 'Vendor application has been REJECTED.');
        redirect("officer/application-details.php?id={$app_id}");
    }
}

$pageTitle = "Review Vendor Application";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'applications'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="fa-solid fa-file-check text-primary me-2"></i>Vendor Application Review</h4>
                        <small class="text-muted">Application No: <strong><?= sanitize($app['application_no']) ?></strong></small>
                    </div>
                    <div>
                        <span class="badge bg-secondary fs-6 px-3 py-2 me-2"><?= sanitize($app['status']) ?></span>
                        <a href="<?= BASE_URL ?>officer/vendor-applications.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to List</a>
                    </div>
                </div>

                <!-- Issued License Notice -->
                <?php if ($license): ?>
                    <div class="alert alert-success d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h6 class="fw-bold mb-1"><i class="fa-solid fa-certificate me-2"></i>Active Digital License Issued</h6>
                            <p class="mb-0 small">License No: <strong><?= sanitize($license['license_no']) ?></strong> | Status: <span class="badge bg-success">VALID</span> | Expiry: <?= sanitize($license['expiry_date']) ?></p>
                        </div>
                        <a href="<?= BASE_URL ?>verify.php?license_no=<?= urlencode($license['license_no']) ?>" target="_blank" class="btn btn-sm btn-dark"><i class="fa-solid fa-qrcode me-1"></i>Verify QR</a>
                    </div>
                <?php endif; ?>

                <div class="row g-4 mb-4">
                    <!-- Business Info -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border h-100">
                            <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-store me-2"></i>Business Information</h6>
                            <table class="table table-sm table-borderless small mb-0">
                                <tr><td class="text-muted" width="40%">Business Name:</td><td class="fw-bold"><?= sanitize($app['business_name']) ?></td></tr>
                                <tr><td class="text-muted">Sector / Type:</td><td class="fw-semibold"><?= sanitize($app['business_type']) ?></td></tr>
                                <tr><td class="text-muted">Owner Name:</td><td><?= sanitize($app['owner_name']) ?></td></tr>
                                <tr><td class="text-muted">Contact Phone:</td><td><?= sanitize($app['phone']) ?></td></tr>
                                <tr><td class="text-muted">Email:</td><td><?= sanitize($app['email']) ?></td></tr>
                                <tr><td class="text-muted">Municipality / District:</td><td><?= sanitize($app['municipality']) ?>, <?= sanitize($app['district']) ?></td></tr>
                                <tr><td class="text-muted">Full Address:</td><td><?= sanitize($app['address']) ?></td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- GIS Map View -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border h-100">
                            <h6 class="fw-bold text-primary mb-2"><i class="fa-solid fa-map-pin me-2 text-danger"></i>GIS Location Verification</h6>
                            <div id="gis-map" style="height: 180px;" class="rounded border"></div>
                            <div class="mt-2 small text-muted">
                                <strong>Coordinates:</strong> Lat <?= $app['latitude'] ?>, Lng <?= $app['longitude'] ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification Documents -->
                <div class="mb-4">
                    <h5 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-folder-open me-2 text-warning"></i>Uploaded Legal Certificates & Documents</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Document Type</th>
                                    <th>File Name</th>
                                    <th>Status</th>
                                    <th>Officer Remarks</th>
                                    <th>Inspection / Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($documents) === 0): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No documents submitted.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($documents as $doc): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= sanitize($doc['document_type']) ?></td>
                                            <td><?= sanitize($doc['file_name']) ?></td>
                                            <td>
                                                <span class="badge <?= $doc['verification_status'] === 'Verified' ? 'bg-success' : ($doc['verification_status'] === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                                    <?= sanitize($doc['verification_status']) ?>
                                                </span>
                                            </td>
                                            <td><small class="text-muted"><?= sanitize($doc['officer_remarks'] ?: 'None') ?></small></td>
                                            <td>
                                                <div class="d-flex gap-1 align-items-center">
                                                    <a href="<?= BASE_URL ?>document-view.php?id=<?= $doc['id'] ?>" target="_blank" class="btn btn-xs btn-outline-primary"><i class="fa-solid fa-eye me-1"></i>View</a>
                                                    
                                                    <?php if ($doc['verification_status'] !== 'Verified'): ?>
                                                        <form method="POST" action="" class="d-inline">
                                                            <input type="hidden" name="action" value="verify_doc">
                                                            <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                                            <input type="hidden" name="doc_remarks" value="Document verified by officer">
                                                            <button type="submit" class="btn btn-xs btn-success"><i class="fa-solid fa-check me-1"></i>Verify</button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($doc['verification_status'] !== 'Rejected'): ?>
                                                        <form method="POST" action="" class="d-inline">
                                                            <input type="hidden" name="action" value="reject_doc">
                                                            <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                                            <input type="hidden" name="doc_remarks" value="Document rejected. Re-upload required.">
                                                            <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fa-solid fa-xmark me-1"></i>Reject</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Officer Decision & Remarks Form -->
                <div class="p-4 bg-light rounded-3 border">
                    <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-gavel me-2 text-primary"></i>Officer Approval & Remarks Form</h5>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Official Inspection Remarks</label>
                            <textarea name="officer_remarks" class="form-control" rows="3" placeholder="Enter officer comments, compliance notes, or correction reasons..."><?= sanitize($app['officer_remarks']) ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="action" value="approve" class="btn btn-success fw-bold px-4 shadow-sm" onclick="return confirm('Confirm APPROVAL for <?= sanitize($app['business_name']) ?>? This will generate an official Digital License.');">
                                <i class="fa-solid fa-check-circle me-2"></i>Approve & Issue License
                            </button>
                            <button type="submit" name="action" value="request_correction" class="btn btn-warning text-dark fw-bold px-3 shadow-sm">
                                <i class="fa-solid fa-pen-to-square me-2"></i>Request Correction
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger fw-bold px-3 shadow-sm" onclick="return confirm('Are you sure you want to REJECT this vendor application?');">
                                <i class="fa-solid fa-xmark-circle me-2"></i>Reject Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    initGISOverviewMap('gis-map', [{
        lat: <?= $app['latitude'] ?>,
        lng: <?= $app['longitude'] ?>,
        title: "<?= addslashes($app['business_name']) ?>",
        address: "<?= addslashes($app['address']) ?>",
        type: "vendor"
    }]);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
