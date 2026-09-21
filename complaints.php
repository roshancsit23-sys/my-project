<?php
// SmartGov Market - Customer Grievance & Public Complaint Submission
// File: complaints.php

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = sanitize($_POST['category'] ?? '');
    $priority = sanitize($_POST['priority'] ?? 'Medium');
    $description = sanitize($_POST['description'] ?? '');
    $location_address = sanitize($_POST['location_address'] ?? '');
    $lat = filter_var($_POST['latitude'] ?? 27.7172, FILTER_VALIDATE_FLOAT);
    $lng = filter_var($_POST['longitude'] ?? 85.3240, FILTER_VALIDATE_FLOAT);
    $department_id = 3; // Consumer Protection Authority by default
    
    if ($category === 'Government Service' || $category === 'License') {
        $department_id = 1; // Dept of Commerce
    }
    
    if (empty($category) || empty($description)) {
        $error = 'Please fill in category and complaint description.';
    } else {
        $complaint_no = generateCode('CMP', 'complaints', 'complaint_no');
        
        $cInsert = $db->prepare("INSERT INTO complaints (complaint_no, user_id, category, priority, description, location_address, latitude, longitude, department_id, status, created_at) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Submitted', NOW())");
        $cInsert->execute([$complaint_no, $user_id, $category, $priority, $description, $location_address, $lat, $lng, $department_id]);
        $complaint_id = $db->lastInsertId();
        
        // Attachment Handle
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['attachment']['tmp_name'];
            $fileName = $_FILES['attachment']['name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (in_array($ext, $allowed)) {
                $newFileName = "CMP_" . time() . "_" . mt_rand(1000, 9999) . "." . $ext;
                $uploadDir = BASE_PATH . 'uploads/complaints/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                    $relPath = 'uploads/complaints/' . $newFileName;
                    $attInsert = $db->prepare("INSERT INTO complaint_attachments (complaint_id, file_path, file_name) VALUES (?, ?, ?)");
                    $attInsert->execute([$complaint_id, $relPath, $fileName]);
                }
            }
        }
        
        logAudit('Complaint Lodged', 'Complaint', $complaint_no, "Customer lodged complaint {$complaint_no} in category {$category}");
        setFlashMessage('success', "✓ Complaint {$complaint_no} registered successfully! Assigned to government investigation officers.");
        redirect('complaints.php');
    }
}

// Fetch Customer's Complaints
$cStmt = $db->prepare("SELECT c.*, d.department_name 
                       FROM complaints c 
                       LEFT JOIN departments d ON c.department_id = d.id 
                       WHERE c.user_id = ? 
                       ORDER BY c.created_at DESC");
$cStmt->execute([$user_id]);
$complaints = $cStmt->fetchAll();

$pageTitle = "Public Complaints & Grievance Portal";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'complaints'; include __DIR__ . '/includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-circle-exclamation text-danger me-2"></i>Public Complaints & Grievance Lodging</h4>
                    <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="collapse" data-bs-target="#newComplaintForm">
                        <i class="fa-solid fa-plus me-1"></i>Lodge New Complaint
                    </button>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?></div>
                <?php endif; ?>

                <!-- New Complaint Form Collapse -->
                <div class="collapse mb-4" id="newComplaintForm">
                    <div class="p-4 bg-light rounded-3 border">
                        <h5 class="fw-bold text-danger mb-3"><i class="fa-solid fa-bullhorn me-2"></i>Lodge Official Grievance Form</h5>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Complaint Category *</label>
                                    <select name="category" class="form-select" required>
                                        <option value="">-- Select Category --</option>
                                        <option value="Vendor">Vendor Misconduct / Overcharging</option>
                                        <option value="Product">Defective Product / Fake Goods</option>
                                        <option value="Order">Order Delivery Delay</option>
                                        <option value="Payment">Payment / Refund Issue</option>
                                        <option value="Government Service">Government Service Delay</option>
                                        <option value="License">License Compliance Issue</option>
                                        <option value="Other">Other Grievance</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Priority Level *</label>
                                    <select name="priority" class="form-select" required>
                                        <option value="Low">Low</option>
                                        <option value="Medium" selected>Medium</option>
                                        <option value="High">High</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Detailed Description *</label>
                                    <textarea name="description" class="form-control" rows="4" placeholder="Describe the incident, vendor involved, product names, or service reference..." required></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Location / Address</label>
                                    <input type="text" name="location_address" id="location_address" class="form-control" placeholder="e.g. Baneshwor Height, Ward 10">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Evidence Attachment (Image / PDF)</label>
                                    <input type="file" name="attachment" class="form-control">
                                </div>
                            </div>

                            <!-- Map location pin selection with GPS Auto-detect -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold text-danger mb-0">
                                        <i class="fa-solid fa-map-pin me-1"></i>Incident GIS Map Location (Click or Drag Pin)
                                    </label>
                                    <button type="button" class="btn btn-xs btn-outline-danger fw-bold" onclick="useCurrentLocation('lat-input', 'lng-input', 'location_address')">
                                        <i class="fa-solid fa-crosshairs me-1"></i>Detect Current GPS Location
                                    </button>
                                </div>
                                <div id="map" style="height:220px;" class="rounded border"></div>
                                <input type="hidden" name="latitude" id="lat-input" value="27.7172">
                                <input type="hidden" name="longitude" id="lng-input" value="85.3240">
                            </div>

                            <button type="submit" class="btn btn-danger fw-bold px-4 py-2 shadow-sm">
                                <i class="fa-solid fa-paper-plane me-2"></i>Register Complaint
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Complaints Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Complaint No</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($complaints) === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4">No complaints registered yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($complaints as $c): ?>
                                    <tr>
                                        <td class="fw-bold text-danger"><?= sanitize($c['complaint_no']) ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= sanitize($c['category']) ?></span></td>
                                        <td>
                                            <span class="badge <?= $c['priority'] === 'Urgent' ? 'bg-danger' : ($c['priority'] === 'High' ? 'bg-warning text-dark' : 'bg-info text-dark') ?>">
                                                <?= sanitize($c['priority']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                                        <td>
                                            <span class="badge <?= $c['status'] === 'Resolved' ? 'bg-success' : ($c['status'] === 'Submitted' ? 'bg-warning text-dark' : 'bg-primary') ?>">
                                                <?= sanitize($c['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="complaint-details.php?id=<?= $c['id'] ?>" class="btn btn-xs btn-outline-primary"><i class="fa-solid fa-eye me-1"></i>Track Progress</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.getElementById('map');
    if (mapEl) {
        initLocationPicker('map', 'lat-input', 'lng-input', 27.7172, 85.3240, 'location_address');
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
