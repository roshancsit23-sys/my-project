<?php
// SmartGov Market - Interactive GIS Map View (Vendors & Complaints)
// File: map-view.php

$pageTitle = "Public GIS Map View";
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();
$filterType = sanitize($_GET['type'] ?? 'all');

$locations = [];

// Fetch Approved Vendors
if ($filterType === 'all' || $filterType === 'vendor') {
    $vendors = $db->query("SELECT v.*, l.license_no 
                           FROM vendors v 
                           LEFT JOIN licenses l ON v.id = l.vendor_id AND l.status = 'VALID' 
                           WHERE v.status IN ('Approved', 'Verified')")->fetchAll();
    foreach ($vendors as $v) {
        $locations[] = [
            'lat' => (float)$v['latitude'],
            'lng' => (float)$v['longitude'],
            'title' => $v['business_name'],
            'address' => $v['address'] . ', ' . $v['municipality'],
            'category' => 'Verified Vendor (' . ($v['license_no'] ?: 'Licensed') . ')',
            'type' => 'vendor',
            'link' => BASE_URL . 'products.php?vendor=' . $v['id']
        ];
    }
}

// Fetch Public Complaints
if ($filterType === 'all' || $filterType === 'complaint') {
    $complaints = $db->query("SELECT * FROM complaints WHERE status != 'Closed'")->fetchAll();
    foreach ($complaints as $c) {
        $locations[] = [
            'lat' => (float)$c['latitude'],
            'lng' => (float)$c['longitude'],
            'title' => 'Complaint: ' . $c['complaint_no'],
            'address' => $c['location_address'] ?: $c['description'],
            'category' => 'Grievance (' . $c['status'] . ')',
            'type' => 'complaint',
            'link' => BASE_URL . 'complaint-details.php?id=' . $c['id']
        ];
    }
}
?>

<div class="container py-4">
    <div class="card card-custom p-4 shadow-sm border-0 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center pb-3 border-bottom mb-3 gap-2">
            <div>
                <h4 class="fw-bold mb-1"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>SmartGov GIS Map Overview</h4>
                <p class="text-muted small mb-0">Interactive Leaflet GIS view displaying verified local business stores & public complaint locations.</p>
            </div>
            
            <form method="GET" action="" class="d-flex gap-2">
                <select name="type" class="form-select form-select-sm fw-bold" onchange="this.form.submit()">
                    <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>Show All (Vendors & Complaints)</option>
                    <option value="vendor" <?= $filterType === 'vendor' ? 'selected' : '' ?>>Approved Vendors Only</option>
                    <option value="complaint" <?= $filterType === 'complaint' ? 'selected' : '' ?>>Public Complaints Only</option>
                </select>
            </form>
        </div>

        <!-- Legend Bar -->
        <div class="d-flex gap-3 mb-3 small">
            <div><span class="badge bg-success me-1"><i class="fa-solid fa-store"></i></span> Approved Vendor Store</div>
            <div><span class="badge bg-danger me-1"><i class="fa-solid fa-triangle-exclamation"></i></span> Public Complaint / Grievance</div>
        </div>

        <div id="gis-map" style="height: 520px;" class="rounded border shadow-sm"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const locationsData = <?= json_encode($locations) ?>;
    initGISOverviewMap('gis-map', locationsData);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
