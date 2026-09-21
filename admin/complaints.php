<?php
// SmartGov Market - Admin Complaints Oversight & GIS Location Mapping
// File: admin/complaints.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';

requireRole('admin');

$db = getDBConnection();

// Status update handler BEFORE header.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_complaint_status') {
    $cid = (int)($_POST['complaint_id'] ?? 0);
    $status = sanitize($_POST['status'] ?? '');
    $resolution = sanitize($_POST['resolution'] ?? '');
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

    $allowedStatuses = ['Submitted', 'Under Review', 'Assigned', 'In Progress', 'Resolved', 'Rejected', 'Closed'];

    if ($cid > 0 && in_array($status, $allowedStatuses, true)) {
        $cStmt = $db->prepare("SELECT complaint_no, user_id FROM complaints WHERE id = ?");
        $cStmt->execute([$cid]);
        $comp = $cStmt->fetch();

        if ($comp) {
            $uStmt = $db->prepare("UPDATE complaints SET status = ?, resolution = ?, updated_at = NOW() WHERE id = ?");
            $uStmt->execute([$status, $resolution, $cid]);

            // Notify Citizen
            sendNotification($comp['user_id'], "Complaint #{$comp['complaint_no']} Updated: {$status}", "Your grievance has been updated to {$status}. " . ($resolution ? "Officer note: {$resolution}" : ""), "complaint-details.php?id={$cid}");

            logAudit('Complaint Updated', 'Complaint', $comp['complaint_no'], "Admin updated complaint #{$comp['complaint_no']} status to {$status}");
            $msg = "Complaint #{$comp['complaint_no']} status updated successfully to '{$status}'.";

            setFlashMessage('success', $msg);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $msg]);
                exit();
            }

            redirect('admin/complaints.php');
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid complaint or status.']);
        exit();
    }
}

// Fetch all complaints with complainant details
$complaints = $db->query("SELECT c.*, u.full_name as complainant_name, u.email as complainant_email, u.phone as complainant_phone,
                                 d.department_name, off.full_name as officer_name
                          FROM complaints c
                          JOIN users u ON c.user_id = u.id
                          LEFT JOIN departments d ON c.department_id = d.id
                          LEFT JOIN users off ON c.assigned_officer_id = off.id
                          ORDER BY c.id DESC")->fetchAll();

// Fetch attachments
$allAtts = $db->query("SELECT * FROM complaint_attachments")->fetchAll();
$attMap = [];
foreach ($allAtts as $att) {
    $attMap[$att['complaint_id']][] = $att;
}

$pageTitle = "Complaints Oversight & GIS";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'complaints'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Public Complaints & GIS Oversight</h4>
                        <p class="text-muted small mb-0">Inspect citizen grievances, review exact pinned GPS locations on interactive GIS maps, and update resolution progress.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= BASE_URL ?>map-view.php?type=complaint" target="_blank" class="btn btn-sm btn-outline-danger fw-bold">
                            <i class="fa-solid fa-map-location-dot me-1"></i>Open Full GIS Heatmap
                        </a>
                        <span class="badge bg-danger rounded-pill fs-6 px-3 py-2"><?= count($complaints) ?> Complaints</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Complaint No</th>
                                <th>Complainant</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Incident Location</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($complaints) === 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No complaints submitted in database.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($complaints as $c): 
                                    $cStatus = $c['status'];
                                    $badgeClass = 'bg-primary';
                                    if ($cStatus === 'Resolved') $badgeClass = 'bg-success';
                                    elseif ($cStatus === 'Rejected') $badgeClass = 'bg-danger';
                                    elseif ($cStatus === 'Submitted') $badgeClass = 'bg-warning text-dark';
                                    elseif ($cStatus === 'Under Review' || $cStatus === 'In Progress') $badgeClass = 'bg-info text-dark';

                                    $priorityClass = 'bg-info text-dark';
                                    if ($c['priority'] === 'Urgent') $priorityClass = 'bg-danger';
                                    elseif ($c['priority'] === 'High') $priorityClass = 'bg-warning text-dark';

                                    $lat = (float)($c['latitude'] ?: 27.7172);
                                    $lng = (float)($c['longitude'] ?: 85.3240);
                                    $atts = $attMap[$c['id']] ?? [];
                                ?>
                                    <tr>
                                        <td class="fw-bold text-danger fs-6"><?= sanitize($c['complaint_no']) ?></td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= sanitize($c['complainant_name']) ?></div>
                                            <small class="text-muted"><?= sanitize($c['complainant_phone']) ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= sanitize($c['category']) ?></span></td>
                                        <td><span class="badge <?= $priorityClass ?>"><?= sanitize($c['priority']) ?></span></td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 180px;">
                                                <i class="fa-solid fa-location-dot text-danger me-1"></i><?= sanitize($c['location_address'] ?: 'Lat: ' . round($lat,4) . ', Lng: ' . round($lng,4)) ?>
                                            </span>
                                        </td>
                                        <td><span class="badge <?= $badgeClass ?>"><?= sanitize($cStatus) ?></span></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-primary fw-semibold" 
                                                    onclick="openComplaintModal(<?= $c['id'] ?>, <?= $lat ?>, <?= $lng ?>, '<?= htmlspecialchars($c['complaint_no'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['location_address'] ?: 'Incident Location', ENT_QUOTES) ?>')">
                                                <i class="fa-solid fa-eye me-1"></i>Inspect
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Complaint Inspect & GIS Modal -->
                                    <div class="modal fade" id="complaintModal<?= $c['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg">
                                                <div class="modal-header bg-light border-bottom">
                                                    <h5 class="modal-title fw-bold">
                                                        <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Inspect Complaint #<?= sanitize($c['complaint_no']) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row g-3 mb-4">
                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded border h-100">
                                                                <h6 class="fw-bold text-danger mb-2 border-bottom pb-2"><i class="fa-solid fa-user me-2"></i>Citizen Information</h6>
                                                                <p class="mb-1"><strong>Name:</strong> <?= sanitize($c['complainant_name']) ?></p>
                                                                <p class="mb-1"><strong>Phone:</strong> <?= sanitize($c['complainant_phone']) ?></p>
                                                                <p class="mb-1"><strong>Email:</strong> <?= sanitize($c['complainant_email']) ?></p>
                                                                <p class="mb-0"><strong>Lodged Date:</strong> <?= date('F d, Y - h:i A', strtotime($c['created_at'])) ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded border h-100">
                                                                <h6 class="fw-bold text-danger mb-2 border-bottom pb-2"><i class="fa-solid fa-bullhorn me-2"></i>Grievance Metadata</h6>
                                                                <p class="mb-1"><strong>Category:</strong> <span class="badge bg-white text-dark border"><?= sanitize($c['category']) ?></span></p>
                                                                <p class="mb-1"><strong>Priority:</strong> <span class="badge <?= $priorityClass ?>"><?= sanitize($c['priority']) ?></span></p>
                                                                <p class="mb-1"><strong>Authority:</strong> <?= sanitize($c['department_name'] ?: 'Consumer Protection Board') ?></p>
                                                                <p class="mb-0"><strong>Current Status:</strong> <span class="badge <?= $badgeClass ?> fs-6"><?= sanitize($c['status']) ?></span></p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Description -->
                                                    <div class="mb-4">
                                                        <label class="form-label fw-bold">Grievance Description:</label>
                                                        <div class="p-3 bg-light rounded border">
                                                            <?= nl2br(sanitize($c['description'])) ?>
                                                        </div>
                                                    </div>

                                                    <!-- Interactive Leaflet Map for this Complaint -->
                                                    <div class="mb-4">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <label class="form-label fw-bold text-danger mb-0">
                                                                <i class="fa-solid fa-map-location-dot me-1"></i>Actual Registered Incident Location Pin:
                                                            </label>
                                                            <small class="text-muted">
                                                                <strong>GPS Coordinates:</strong> <?= $lat ?>, <?= $lng ?>
                                                            </small>
                                                        </div>
                                                        <div id="modal-map-<?= $c['id'] ?>" style="height: 250px;" class="rounded border shadow-sm"></div>
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="fa-solid fa-location-arrow me-1"></i><?= sanitize($c['location_address'] ?: 'Coordinates registered via citizen GPS/address picker.') ?>
                                                        </small>
                                                    </div>

                                                    <!-- Attachments if any -->
                                                    <?php if (count($atts) > 0): ?>
                                                        <div class="mb-4">
                                                            <label class="form-label fw-bold">Submitted Evidence Attachments:</label>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <?php foreach ($atts as $at): ?>
                                                                    <a href="<?= BASE_URL . $at['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                        <i class="fa-solid fa-paperclip me-1 text-danger"></i><?= sanitize($at['file_name']) ?>
                                                                    </a>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Status Update & Resolution Form -->
                                                    <div class="p-3 bg-light rounded border">
                                                        <h6 class="fw-bold text-danger mb-3"><i class="fa-solid fa-clipboard-check me-2"></i>Investigation Decision & Status Update</h6>
                                                        <form onsubmit="updateComplaintStatus(event, <?= $c['id'] ?>)">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label small fw-semibold">Complaint Status *</label>
                                                                    <select name="status" class="form-select form-select-sm" required>
                                                                        <option value="Submitted" <?= $c['status'] === 'Submitted' ? 'selected' : '' ?>>Submitted</option>
                                                                        <option value="Under Review" <?= $c['status'] === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
                                                                        <option value="In Progress" <?= $c['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                                                        <option value="Resolved" <?= $c['status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                                                        <option value="Rejected" <?= $c['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <label class="form-label small fw-semibold">Resolution / Findings Notes</label>
                                                                    <textarea name="resolution" class="form-control form-control-sm" rows="2" placeholder="Official remarks for complainant..."><?= sanitize($c['resolution'] ?? '') ?></textarea>
                                                                </div>
                                                                <div class="col-12 d-flex justify-content-end gap-2">
                                                                    <button type="submit" class="btn btn-danger btn-sm fw-bold">
                                                                        <i class="fa-solid fa-floppy-disk me-1"></i>Save Status & Resolution
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light border-top">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                    <a href="<?= BASE_URL ?>complaint-details.php?id=<?= $c['id'] ?>" target="_blank" class="btn btn-outline-danger btn-sm fw-bold">
                                                        <i class="fa-solid fa-external-link me-1"></i>Open Public Tracking
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Instant Toast Notification Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="compToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fs-6 fw-semibold" id="compToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
const activeMaps = {};

function showToast(message, isSuccess = true) {
    const toastEl = document.getElementById('compToast');
    const toastBody = document.getElementById('compToastBody');
    if (!toastEl || !toastBody) return;
    
    toastEl.className = 'toast align-items-center text-white border-0 shadow-lg ' + (isSuccess ? 'bg-success' : 'bg-danger');
    toastBody.innerHTML = (isSuccess ? '<i class="fa-solid fa-circle-check me-2"></i>' : '<i class="fa-solid fa-triangle-exclamation me-2"></i>') + message;
    
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
}

function openComplaintModal(cid, lat, lng, compNo, address) {
    const modalEl = document.getElementById('complaintModal' + cid);
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    modalEl.addEventListener('shown.bs.modal', function () {
        const containerId = 'modal-map-' + cid;
        if (!activeMaps[containerId]) {
            const map = L.map(containerId).setView([lat, lng], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(`<strong>Complaint #${compNo}</strong><br>${address}`).openPopup();
            activeMaps[containerId] = map;
        } else {
            activeMaps[containerId].invalidateSize();
        }
    }, { once: true });
}

function updateComplaintStatus(e, cid) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'update_complaint_status');
    formData.append('complaint_id', cid);
    formData.append('ajax', '1');

    fetch('<?= BASE_URL ?>admin/complaints.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            const modalEl = document.getElementById('complaintModal' + cid);
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            showToast(data.message || 'Status update failed.', false);
        }
    })
    .catch(err => {
        showToast('Error connecting to server.', false);
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
