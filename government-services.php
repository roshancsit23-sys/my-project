<?php
// SmartGov Market - Government Services Catalog
// File: government-services.php

$pageTitle = "Government Digital Services";
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();
$services = $db->query("SELECT gs.*, d.department_name, d.code as dept_code 
                        FROM government_services gs
                        JOIN departments d ON gs.department_id = d.id
                        WHERE gs.status = 1
                        ORDER BY gs.id ASC")->fetchAll();
?>

<div class="container py-4">
    <div class="card card-custom p-4 bg-primary text-white mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #1a2b4c 0%, #0d6efd 100%);">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1"><i class="fa-solid fa-landmark text-warning me-2"></i>Municipal Government Digital Services</h3>
                <p class="mb-0 opacity-75">Apply for digital business licenses, quality certifications, and municipal permits online.</p>
            </div>
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 fs-6">E-GOVERNANCE DIRECTORY</span>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($services as $s): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-custom p-4 shadow-sm border-0 h-100 d-flex flex-column">
                    <span class="badge bg-primary-subtle text-primary border me-auto mb-2"><?= sanitize($s['department_name']) ?></span>
                    <h5 class="fw-bold mb-2 text-dark"><?= sanitize($s['service_name']) ?></h5>
                    <p class="small text-secondary mb-3"><?= sanitize($s['description']) ?></p>

                    <div class="p-3 bg-light rounded border small mb-3">
                        <strong class="text-dark d-block mb-1"><i class="fa-solid fa-list-check me-1 text-primary"></i>Requirements:</strong>
                        <span class="text-muted"><?= sanitize($s['requirements']) ?></span>
                    </div>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block"><i class="fa-solid fa-clock me-1"></i><?= sanitize($s['processing_time']) ?></small>
                            <span class="fw-bold text-success"><?= $s['fee'] > 0 ? formatCurrency($s['fee']) : 'FREE' ?></span>
                        </div>
                        <a href="service-details.php?id=<?= $s['id'] ?>" class="btn btn-primary fw-bold btn-sm"><i class="fa-solid fa-paper-plane me-1"></i>Apply Now</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
