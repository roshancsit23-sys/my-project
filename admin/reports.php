<?php
// SmartGov Market - Reports Generator & CSV Export
// File: admin/reports.php

$pageTitle = "Reports & Analytics";
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');

$db = getDBConnection();

// CSV Export Handler
if (isset($_GET['export'])) {
    $exportType = $_GET['export'];
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=SmartGov_' . $exportType . '_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    if ($exportType === 'vendors') {
        fputcsv($output, ['Vendor ID', 'Business Name', 'Owner Name', 'Email', 'Phone', 'Sector', 'Municipality', 'District', 'Status', 'Registered Date']);
        $rows = $db->query("SELECT v.id, v.business_name, u.full_name, u.email, u.phone, v.business_type, v.municipality, v.district, v.status, v.created_at 
                            FROM vendors v JOIN users u ON v.user_id = u.id")->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $r) fputcsv($output, $r);
    } elseif ($exportType === 'orders') {
        fputcsv($output, ['Order ID', 'Order No', 'Customer Name', 'Total Amount', 'Payment Method', 'Payment Status', 'Status', 'Order Date']);
        $rows = $db->query("SELECT o.id, o.order_no, u.full_name, o.total_amount, o.payment_method, o.payment_status, o.status, o.created_at 
                            FROM orders o JOIN users u ON o.customer_id = u.id")->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $r) fputcsv($output, $r);
    } elseif ($exportType === 'complaints') {
        fputcsv($output, ['Complaint ID', 'Complaint No', 'Complainant', 'Category', 'Priority', 'Department', 'Status', 'Created Date']);
        $rows = $db->query("SELECT c.id, c.complaint_no, u.full_name, c.category, c.priority, d.department_name, c.status, c.created_at 
                            FROM complaints c JOIN users u ON c.user_id = u.id LEFT JOIN departments d ON c.department_id = d.id")->fetchAll(PDO::FETCH_NUM);
        foreach ($rows as $r) fputcsv($output, $r);
    }
    exit();
}
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'reports'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4">
                    <h4 class="fw-bold mb-0"><i class="fa-solid fa-file-csv text-primary me-2"></i>E-Governance & E-Commerce CSV Report Generator</h4>
                    <span class="badge bg-success rounded-pill fs-6"><i class="fa-solid fa-download me-1"></i>Instant CSV Export</span>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="p-4 bg-light rounded border text-center h-100">
                            <i class="fa-solid fa-store fs-1 text-primary mb-3"></i>
                            <h5 class="fw-bold mb-2">Vendors & Licensing Report</h5>
                            <p class="small text-muted mb-3">Download complete list of approved and pending vendors with license details.</p>
                            <a href="admin/reports.php?export=vendors" class="btn btn-primary fw-bold w-100"><i class="fa-solid fa-file-excel me-1"></i>Export Vendors CSV</a>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-4 bg-light rounded border text-center h-100">
                            <i class="fa-solid fa-cart-flatbed fs-1 text-success mb-3"></i>
                            <h5 class="fw-bold mb-2">Marketplace Sales & Orders</h5>
                            <p class="small text-muted mb-3">Download system-wide orders summary, revenue metrics, and customer details.</p>
                            <a href="admin/reports.php?export=orders" class="btn btn-success fw-bold w-100"><i class="fa-solid fa-file-excel me-1"></i>Export Orders CSV</a>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-4 bg-light rounded border text-center h-100">
                            <i class="fa-solid fa-bullhorn fs-1 text-danger mb-3"></i>
                            <h5 class="fw-bold mb-2">Public Complaints Metrics</h5>
                            <p class="small text-muted mb-3">Download lodged grievances, priority levels, and resolution status records.</p>
                            <a href="admin/reports.php?export=complaints" class="btn btn-danger fw-bold w-100"><i class="fa-solid fa-file-excel me-1"></i>Export Complaints CSV</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
