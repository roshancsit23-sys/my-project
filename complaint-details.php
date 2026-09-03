<?php
// SmartGov Market - Complaint Detail & Tracking View
// File: complaint-details.php

$pageTitle = "Complaint Investigation Progress";
require_once __DIR__ . '/includes/header.php';
requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$cid = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT c.*, d.department_name, u.full_name as officer_name 
                      FROM complaints c
                      LEFT JOIN departments d ON c.department_id = d.id
                      LEFT JOIN users u ON c.assigned_officer_id = u.id
                      WHERE c.id = ? AND (c.user_id = ? OR ? IN (SELECT id FROM users WHERE role_id IN (1,2)))");
$stmt->execute([$cid, $user_id, $user_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    setFlashMessage('danger', 'Complaint record not found.');
    redirect('complaints.php');
}

// Fetch Attachments
$attStmt = $db->prepare("SELECT * FROM complaint_attachments WHERE complaint_id = ?");
$attStmt->execute([$cid]);
$attachments = $attStmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Complaint #<?= sanitize($complaint['complaint_no']) ?></h4>
            <small class="text-muted">Lodged on <?= date('F d, Y \a\t h:i A', strtotime($complaint['created_at'])) ?></small>
        </div>
        <a href="complaints.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to Complaints</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
                    <h5 class="fw-bold mb-0">Complaint Details</h5>
                    <span class="badge bg-danger fs-6 px-3 py-2"><?= sanitize($complaint['status']) ?></span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <strong>Category:</strong> <span class="badge bg-light text-dark border ms-1"><?= sanitize($complaint['category']) ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Priority:</strong> <span class="badge bg-warning text-dark ms-1"><?= sanitize($complaint['priority']) ?></span>
                    </div>
                    <div class="col-md-12">
                        <strong>Handling Authority:</strong> <?= sanitize($complaint['department_name'] ?: 'Consumer Protection Authority') ?>
                    </div>
                    <div class="col-md-12">
                        <strong>Description:</strong>
                        <p class="p-3 bg-light rounded border mt-1 mb-0"><?= nl2br(sanitize($complaint['description'])) ?></p>
                    </div>
                </div>

                <!-- Officer Resolution Section -->
                <?php if ($complaint['resolution']): ?>
                    <div class="alert alert-success p-3 rounded mb-3">
                        <h6 class="fw-bold mb-2"><i class="fa-solid fa-user-shield me-2"></i>Officer Resolution Findings</h6>
                        <p class="mb-1"><?= nl2br(sanitize($complaint['resolution'])) ?></p>
                        <?php if ($complaint['officer_name']): ?>
                            <small class="text-muted">Investigated by: Officer <strong><?= sanitize($complaint['officer_name']) ?></strong></small>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="fa-solid fa-clock me-1"></i> Government officers are currently investigating this complaint. Updates will appear here.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Evidence Attachments -->
            <?php if (count($attachments) > 0): ?>
                <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Submitted Evidence</h5>
                    <div class="d-flex gap-2">
                        <?php foreach ($attachments as $att): ?>
                            <a href="<?= BASE_URL . $att['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-paperclip me-1"></i><?= sanitize($att['file_name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- GIS Location Card -->
        <div class="col-lg-4">
            <div class="card card-custom p-4 shadow-sm border-0">
                <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-map-pin text-danger me-2"></i>Grievance GIS Pin</h5>
                <div id="gis-map" style="height:220px;" class="rounded border mb-2"></div>
                <small class="text-muted d-block">
                    <strong>Coordinates:</strong> Lat <?= $complaint['latitude'] ?>, Lng <?= $complaint['longitude'] ?>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    initGISOverviewMap('gis-map', [{
        lat: <?= $complaint['latitude'] ?>,
        lng: <?= $complaint['longitude'] ?>,
        title: "Complaint: <?= addslashes($complaint['complaint_no']) ?>",
        address: "<?= addslashes($complaint['location_address'] ?: 'Kathmandu') ?>",
        type: "complaint"
    }]);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
