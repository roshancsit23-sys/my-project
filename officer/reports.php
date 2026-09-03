<?php
// SmartGov Market - Officer Department Reports & Metrics
// File: officer/reports.php

$pageTitle = "Department Processing Reports";
require_once __DIR__ . '/../includes/header.php';
requireRole(['officer', 'admin']);

$db = getDBConnection();

$totalApps = $db->query("SELECT COUNT(*) FROM vendor_applications")->fetchColumn();
$approved = $db->query("SELECT COUNT(*) FROM vendor_applications WHERE status='Approved'")->fetchColumn();
$rejected = $db->query("SELECT COUNT(*) FROM vendor_applications WHERE status='Rejected'")->fetchColumn();
$pending = $db->query("SELECT COUNT(*) FROM vendor_applications WHERE status IN ('Submitted','Under Review')")->fetchColumn();

$complaintTotal = $db->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
$complaintResolved = $db->query("SELECT COUNT(*) FROM complaints WHERE status='Resolved'")->fetchColumn();

?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'reports'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-file-invoice text-primary me-2"></i>Department Governance Processing Metrics</h4>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-print me-1"></i>Print Report</button>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <h6 class="fw-bold text-primary mb-3">Vendor Applications Clearance Summary</h6>
                            <table class="table table-sm table-borderless small mb-0">
                                <tr><td class="text-muted">Total Received Applications:</td><td class="fw-bold"><?= $totalApps ?></td></tr>
                                <tr><td class="text-muted">Approved & Licensed:</td><td class="fw-bold text-success"><?= $approved ?></td></tr>
                                <tr><td class="text-muted">Pending Review:</td><td class="fw-bold text-warning"><?= $pending ?></td></tr>
                                <tr><td class="text-muted">Rejected / Non-Compliant:</td><td class="fw-bold text-danger"><?= $rejected ?></td></tr>
                                <tr><td class="text-muted">Approval Rate:</td><td class="fw-bold text-primary"><?= $totalApps > 0 ? round(($approved/$totalApps)*100, 1) : 0 ?>%</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <h6 class="fw-bold text-primary mb-3">Grievance & Complaint Resolution Performance</h6>
                            <table class="table table-sm table-borderless small mb-0">
                                <tr><td class="text-muted">Total Lodged Complaints:</td><td class="fw-bold"><?= $complaintTotal ?></td></tr>
                                <tr><td class="text-muted">Resolved & Closed:</td><td class="fw-bold text-success"><?= $complaintResolved ?></td></tr>
                                <tr><td class="text-muted">Under Investigation:</td><td class="fw-bold text-warning"><?= $complaintTotal - $complaintResolved ?></td></tr>
                                <tr><td class="text-muted">Resolution Efficiency:</td><td class="fw-bold text-primary"><?= $complaintTotal > 0 ? round(($complaintResolved/$complaintTotal)*100, 1) : 0 ?>%</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
