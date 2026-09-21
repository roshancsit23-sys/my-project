<?php
// HATIYA - Digital Governance & Marketplace Platform Homepage
// File: index.php

$pageTitle = "Digital Governance & Marketplace Platform";
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();

// Fetch Categories
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 LIMIT 8")->fetchAll();

// Fetch Verified Local Marketplace Products (Active products, verified vendors, available stock)
$products = $db->query("SELECT p.*, v.business_name, v.municipality, c.name as category_name 
                        FROM products p
                        JOIN vendors v ON p.vendor_id = v.id
                        JOIN categories c ON p.category_id = c.id
                        WHERE p.status = 'Active' AND p.stock_quantity > 0 AND v.status IN ('Approved', 'Verified') AND c.is_active = 1
                        ORDER BY p.id DESC LIMIT 8")->fetchAll();

// Fetch Verified Vendors for Directory
$approvedVendors = $db->query("SELECT v.*, u.full_name as owner_name, l.license_no 
                                FROM vendors v
                                JOIN users u ON v.user_id = u.id
                                LEFT JOIN licenses l ON v.id = l.vendor_id AND l.status = 'VALID'
                                WHERE v.status IN ('Approved', 'Verified')
                                LIMIT 4")->fetchAll();

// Government Services List
$govServicesList = [
    [
        'icon' => 'fa-users',
        'title' => 'Citizen Services',
        'desc' => 'Access municipal registration, vital records, certificates, and citizen welfare programs.',
        'btn_text' => 'Access Services',
        'link' => 'government-services.php'
    ],
    [
        'icon' => 'fa-building',
        'title' => 'Business Registration',
        'desc' => 'Register local enterprises, trade permits, and submit compliance documentation digitally.',
        'btn_text' => 'Register Business',
        'link' => 'vendor/register.php'
    ],
    [
        'icon' => 'fa-certificate',
        'title' => 'Digital License',
        'desc' => 'Authenticate government business permits with real-time QR code certificate verification.',
        'btn_text' => 'Verify License',
        'link' => 'verify.php'
    ],
    [
        'icon' => 'fa-shield-check',
        'title' => 'Vendor Verification',
        'desc' => 'Official government inspection and verification system for certified merchant operations.',
        'btn_text' => 'Check Status',
        'link' => 'verify.php'
    ],
    [
        'icon' => 'fa-file-circle-exclamation',
        'title' => 'Complaint Registration',
        'desc' => 'Lodge public grievances with geo-location pinning for direct municipal resolution tracking.',
        'btn_text' => 'Submit Complaint',
        'link' => 'complaints.php'
    ],
    [
        'icon' => 'fa-map-location-dot',
        'title' => 'GIS Mapping Services',
        'desc' => 'Interactive map oversight displaying registered local vendors, infrastructure, and complaints.',
        'btn_text' => 'View GIS Map',
        'link' => 'map-view.php'
    ],
    [
        'icon' => 'fa-bullhorn',
        'title' => 'Public Notices',
        'desc' => 'Official government announcements, tenders, municipal directives, and public circulars.',
        'btn_text' => 'Read Notices',
        'link' => 'government-services.php#notices'
    ],
    [
        'icon' => 'fa-route',
        'title' => 'Service Tracking',
        'desc' => 'Track your filed application status, license approvals, and grievance processing timeline.',
        'btn_text' => 'Track Application',
        'link' => 'applications.php'
    ],
];
?>

<!-- =====================================================
     HERO BANNER
     ===================================================== -->
<section class="hatiya-hero">
    <div class="container py-3">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-danger text-white fw-bold px-3 py-2 mb-3 shadow-sm" style="letter-spacing:0.5px;">
                    <i class="fa-solid fa-flag me-1 text-warning"></i> GOVERNMENT OF NEPAL DIGITAL PORTAL
                </span>
                <h1 class="hero-title mb-3">Welcome to HATIYA</h1>
                <h4 class="fw-bold text-warning mb-3">Digital Governance &amp; Marketplace Platform</h4>
                <p class="hero-subtitle mb-4">
                    "Connecting Citizens, Government and Local Businesses Through Digital Services."
                </p>
                
                <div class="d-flex gap-3 flex-wrap">
                    <a href="government-services.php" class="btn btn-gov-primary btn-lg shadow-sm">
                        <i class="fa-solid fa-landmark me-2"></i>Explore Services
                    </a>
                    <a href="products.php" class="btn btn-gov-secondary btn-lg shadow-sm">
                        <i class="fa-solid fa-store me-2"></i>Visit Marketplace
                    </a>
                </div>
            </div>

            <!-- License Quick Check Box -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card p-4 border-0 shadow-lg text-dark bg-white rounded-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-danger text-white p-3 rounded-circle">
                            <i class="fa-solid fa-qrcode fs-3"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">Official License Verification</h6>
                            <small class="text-muted">Search by License Number</small>
                        </div>
                    </div>
                    <form action="verify.php" method="GET">
                        <div class="mb-3">
                            <input type="text" name="license_no" class="form-control border-secondary-subtle" placeholder="e.g. LIC-2026-000101" required>
                        </div>
                        <button type="submit" class="btn btn-gov-primary w-100 fw-bold">
                            <i class="fa-solid fa-shield-check me-1"></i>Verify Business
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================
     DIGITAL GOVERNMENT SERVICES SECTION
     ===================================================== -->
<section class="py-5" id="gov-services">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary border fw-bold px-3 py-1 mb-2">PUBLIC SERVICES</span>
            <h2 class="fw-bold text-dark mb-2">Digital Government Services</h2>
            <p class="text-muted">Streamlined e-governance solutions for citizens and verified enterprises</p>
        </div>

        <div class="row g-4">
            <?php foreach ($govServicesList as $srv): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-gov service-card d-flex flex-column">
                        <div class="service-icon-wrapper">
                            <i class="fa-solid <?= $srv['icon'] ?>"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2 fs-6"><?= $srv['title'] ?></h5>
                        <p class="small text-muted mb-4 flex-grow-1"><?= $srv['desc'] ?></p>
                        <a href="<?= BASE_URL . $srv['link'] ?>" class="btn btn-sm btn-gov-outline fw-bold mt-auto align-self-start">
                            <?= $srv['btn_text'] ?> <i class="fa-solid fa-chevron-right ms-1"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- =====================================================
     VERIFIED LOCAL MARKETPLACE SECTION
     ===================================================== -->
<section class="py-5 bg-white border-top border-bottom" id="marketplace">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <span class="badge badge-gov-verified mb-1"><i class="fa-solid fa-check-double me-1"></i>Official Vendor Catalog</span>
                <h3 class="fw-bold text-dark mb-1">Verified Local Marketplace</h3>
                <p class="text-muted small mb-0">Browse active local products supplied strictly by verified government-licensed vendors</p>
            </div>
            <a href="products.php" class="btn btn-gov-primary btn-sm fw-bold">
                View Full Marketplace <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <!-- Categories Filter Pills -->
        <?php if (!empty($categories)): ?>
            <div class="d-flex gap-2 overflow-auto mb-4 pb-2">
                <a href="products.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 active fw-bold">All Categories</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="products.php?category=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-nowrap">
                        <?= sanitize($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Product Cards Grid -->
        <div class="row g-4">
            <?php if (empty($products)): ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fa-solid fa-box-open fs-1 mb-2 d-block opacity-50"></i>
                    No active verified products currently listed in the marketplace.
                </div>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card card-gov h-100 overflow-hidden">
                            <div class="position-relative bg-light" style="aspect-ratio: 4/3; overflow: hidden;">
                                <img src="<?= BASE_URL . ($p['image_path'] ? $p['image_path'] : 'assets/images/placeholder.svg') ?>" 
                                     onerror="this.onerror=null;this.src='<?= BASE_URL ?>assets/images/placeholder.svg';" 
                                     class="card-img-top h-100 w-100" style="object-fit: cover;" alt="<?= sanitize($p['name']) ?>">
                                <span class="position-absolute top-0 start-0 m-2 badge bg-success shadow-sm">
                                    <i class="fa-solid fa-award me-1"></i>Verified Vendor
                                </span>
                            </div>
                            <div class="card-body d-flex flex-column p-3">
                                <span class="badge bg-light text-dark border align-self-start mb-2 small"><?= sanitize($p['category_name']) ?></span>
                                <h6 class="fw-bold mb-1 text-truncate" title="<?= sanitize($p['name']) ?>">
                                    <a href="product-details.php?id=<?= $p['id'] ?>" class="text-dark"><?= sanitize($p['name']) ?></a>
                                </h6>
                                <small class="text-muted mb-2 text-truncate">
                                    <i class="fa-solid fa-store me-1 text-primary"></i><?= sanitize($p['business_name']) ?>
                                </small>
                                <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="fw-bold text-danger fs-5"><?= formatCurrency($p['price']) ?></span>
                                    <a href="product-details.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-gov-primary fw-bold">
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- =====================================================
     ABOUT HATIYA & PUBLIC NOTICES SECTION
     ===================================================== -->
<section class="py-5" id="about-hatiya">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <span class="badge bg-danger text-white mb-2">ABOUT THE PLATFORM</span>
                <h2 class="fw-bold text-dark mb-3">HATIYA – Digital Governance &amp; Marketplace Platform</h2>
                <p class="text-muted mb-3">
                    HATIYA is a centralized digital governance portal created to bridge citizens, local government authorities, and registered business entities across Nepal.
                </p>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-check-circle text-success fs-5"></i>
                        <span><strong>Official Business Verification:</strong> QR code powered digital permits.</span>
                    </li>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-check-circle text-success fs-5"></i>
                        <span><strong>Transparent Grievance Handling:</strong> Geo-tagged GIS complaint mapping.</span>
                    </li>
                    <li class="mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-check-circle text-success fs-5"></i>
                        <span><strong>Empowered Local Commerce:</strong> Verified local marketplace.</span>
                    </li>
                </ul>
                <a href="register.php" class="btn btn-gov-primary fw-bold">Create Citizen Account</a>
            </div>

            <div class="col-lg-6">
                <div class="card card-gov p-4 bg-light border-0 shadow-sm">
                    <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-bullhorn text-danger me-2"></i>Public Announcements &amp; Notices</h5>
                    <div class="list-group list-group-flush border-0">
                        <div class="list-group-item bg-transparent px-0 py-3 border-bottom">
                            <span class="badge bg-danger mb-1">NOTICE #2026-09</span>
                            <h6 class="fw-bold mb-1">Mandatory Digital Permitting for Local Merchants</h6>
                            <small class="text-muted d-block mb-1">All local vendors must upload registration certificates to acquire digital QR license credentials.</small>
                            <span class="small text-secondary"><i class="fa-regular fa-clock me-1"></i>Issued: Sept 01, 2026</span>
                        </div>
                        <div class="list-group-item bg-transparent px-0 py-3">
                            <span class="badge bg-primary mb-1">GIS SERVICE UPDATE</span>
                            <h6 class="fw-bold mb-1">Public Complaint Location Geocoding Integrated</h6>
                            <small class="text-muted d-block mb-1">Citizens can pin incident coordinates directly on Leaflet maps during complaint filing.</small>
                            <span class="small text-secondary"><i class="fa-regular fa-clock me-1"></i>Issued: Sept 05, 2026</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
