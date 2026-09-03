<?php
// SmartGov Market - Officer Complaint Management & Investigation
// File: officer/complaints.php

$pageTitle = "Officer Complaint Management";
require_once __DIR__ . '/../includes/header.php';
requireRole(['officer', 'admin']);

$db = getDBConnection();

// Update Complaint Status & Resolution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_complaint') {
    $cid = (int)$_POST['complaint_id'];
    $status = sanitize($_POST['status']);
    $resolution = sanitize($_POST['resolution']);
    $officer_id = $_SESSION['user_id'];
    
    $stmt = $db->prepare("UPDATE complaints SET status = ?, resolution = ?, assigned_officer_id = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $resolution, $officer_id, $cid]);
    
    // Notify complainant
    $cStmt = $db->prepare("SELECT user_id, complaint_no FROM complaints WHERE id = ?");
    $cStmt->execute([$cid]);
    $complaint = $cStmt->fetch();
    if ($complaint) {
        sendNotification($complaint['user_id'], 'Complaint Status Updated', "Complaint {$complaint['complaint_no']} status set to {$status}. Resolution notes updated.", 'complaint-details.php?id=' . $cid);
    }
    
    logAudit('Complaint Updated', 'Complaint', $cid, "Complaint ID {$cid} updated to {$status}.");
    setFlashMessage('success', 'Complaint status and resolution updated successfully.');
    redirect('officer/complaints.php');
}

$complaints = $db->query("SELECT c.*, u.full_name as complainant_name, u.phone as complainant_phone 
                          FROM complaints c
                          JOIN users u ON c.user_id = u.id
                          ORDER BY c.created_at DESC")->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'complaints'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Public Complaints & Investigation Queue</h4>
                    <span class="badge bg-danger rounded-pill fs-6"><?= count($complaints) ?> Complaints</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Complaint No</th>
                                <th>Complainant</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $c): ?>
                                <tr>
                                    <td class="fw-bold text-danger"><?= sanitize($c['complaint_no']) ?></td>
                                    <td><?= sanitize($c['complainant_name']) ?><br><small class="text-muted"><?= sanitize($c['complainant_phone']) ?></small></td>
                                    <td><span class="badge bg-light text-dark border"><?= sanitize($c['category']) ?></span></td>
                                    <td>
                                        <span class="badge <?= $c['priority'] === 'Urgent' ? 'bg-danger' : ($c['priority'] === 'High' ? 'bg-warning text-dark' : 'bg-info text-dark') ?>">
                                            <?= sanitize($c['priority']) ?>
                                        </span>
                                    </td>
                                    <td style="max-width: 200px;" class="text-truncate"><?= sanitize($c['description']) ?></td>
                                    <td>
                                        <span class="badge <?= $c['status'] === 'Resolved' ? 'bg-success' : ($c['status'] === 'Submitted' ? 'bg-warning text-dark' : 'bg-primary') ?>">
                                            <?= sanitize($c['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-xs btn-primary" data-bs-toggle="modal" data-bs-target="#complaintModal<?= $c['id'] ?>">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>Resolve
                                        </button>
                                    </td>
                                </tr>

                                <!-- Complaint Resolution Modal -->
                                <div class="modal fade" id="complaintModal<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Investigate Complaint: <?= sanitize($c['complaint_no']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="update_complaint">
                                                    <input type="hidden" name="complaint_id" value="<?= $c['id'] ?>">

                                                    <div class="p-3 bg-light rounded border mb-3">
                                                        <h6><strong>Complainant:</strong> <?= sanitize($c['complainant_name']) ?> (<?= sanitize($c['complainant_phone']) ?>)</h6>
                                                        <p class="mb-1"><strong>Category:</strong> <?= sanitize($c['category']) ?> | <strong>Priority:</strong> <?= sanitize($c['priority']) ?></p>
                                                        <p class="mb-0"><strong>Description:</strong> <?= sanitize($c['description']) ?></p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Complaint Status</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="Submitted" <?= $c['status'] === 'Submitted' ? 'selected' : '' ?>>Submitted</option>
                                                            <option value="Assigned" <?= $c['status'] === 'Assigned' ? 'selected' : '' ?>>Assigned</option>
                                                            <option value="Under Investigation" <?= $c['status'] === 'Under Investigation' ? 'selected' : '' ?>>Under Investigation</option>
                                                            <option value="In Progress" <?= $c['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                            <option value="Resolved" <?= $c['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                                            <option value="Closed" <?= $c['status'] === 'Closed' ? 'selected' : '' ?>>Closed</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Officer Investigation & Resolution Findings</label>
                                                        <textarea name="resolution" class="form-control" rows="4" placeholder="Enter detailed resolution report, corrective actions taken, or penalty details..."><?= sanitize($c['resolution']) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-success fw-bold"><i class="fa-solid fa-floppy-disk me-1"></i>Save Resolution</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
