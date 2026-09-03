<?php
// SmartGov Market - Main Integrated E-Governance & E-Commerce Homepage
// File: index.php

$pageTitle = "Home - Integrated E-Governance & E-Commerce";
require_once __DIR__ . '/includes/header.php';

$db = getDBConnection();

// Fetch Categories
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 LIMIT 8")->fetchAll();

// Fetch Featured Active Products from Government Approved Vendors
$products = $db->query("SELECT p.*, v.business_name, v.municipality, c.name as category_name 
                        FROM products p
                        JOIN vendors v ON p.vendor_id = v.id
                        JOIN categories c ON p.category_id = c.id
                        WHERE p.status = 'Active' AND v.status = 'Approved'
                        ORDER BY p.id DESC LIMIT 8")->fetchAll();

// Fetch Verified Vendors for Directory
$approvedVendors = $db->query("SELECT v.*, u.full_name as owner_name, l.license_no 
                                FROM vendors v
                                JOIN users u ON v.user_id = u.id
                                LEFT JOIN licenses l ON v.id = l.vendor_id AND l.status = 'VALID'
                                WHERE v.status = 'Approved'
                                LIMIT 4")->fetchAll();

// Fetch Government Services
$services = $db->query("SELECT gs.*, d.department_name 
                        FROM government_services gs
                        JOIN departments d ON gs.department_id = d.id
                        WHERE gs.status = 1 LIMIT 3")->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section text-center text-md-start">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 mb-3 shadow-sm" data-i18n="heroBadge"><i class="fa-solid fa-shield-halved me-1"></i> Official Municipal E-Marketplace</span>
                <h1 class="display-4 fw-extrabold text-white mb-3" data-i18n="heroTitle">Integrated E-Governance & E-Commerce Platform</h1>
                <p class="lead opacity-90 mb-4" data-i18n="heroSubtitle">Empowering citizens, local businesses, and government administration through transparent digital services and verified commerce.</p>
                
                <!-- Live Search Box -->
                <form action="products.php" method="GET" class="hero-search p-2 bg-white rounded-pill shadow-lg d-flex gap-2 align-items-center">
                    <input type="text" name="search" class="form-control border-0 ps-3" placeholder="Search products, local vendors, or government services..." data-i18n="searchPlaceholder">
                    <button type="submit" class="btn btn-primary btn-lg px-4 rounded-pill fw-bold"><i class="fa-solid fa-magnifying-glass me-2"></i>Search</button>
                </form>
            </div>
            <div class="col-lg-5 text-center">
                <div class="p-4 bg-white bg-opacity-10 rounded-4 backdrop-blur border border-white border-opacity-20 shadow-lg text-white">
                    <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                        <i class="fa-solid fa-qrcode fs-1 text-warning"></i>
                        <div class="text-start">
                            <h5 class="fw-bold mb-0">Public License Verification</h5>
                            <small class="opacity-75">Instant QR Code Scan System</small>
                        </div>
                    </div>
                    <form action="verify.php" method="GET" class="d-flex gap-2">
                        <input type="text" name="license_no" class="form-control form-control-sm" placeholder="Enter License No (e.g. LIC-2026-000101)">
                        <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold">Verify</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Bar -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-3 justify-content-center text-center">
            <?php
            $categoryIcons = [
                'food' => 'fa-utensils',
                'grocery' => 'fa-basket-shopping',
                'handicraft' => 'fa-hands',
                'craft' => 'fa-hands',
                'agriculture' => 'fa-seedling',
                'farm' => 'fa-leaf',
                'textile' => 'fa-shirt',
                'clothing' => 'fa-shirt',
                'electronics' => 'fa-plug',
                'service' => 'fa-briefcase',
                'health' => 'fa-heart-pulse',
                'tourism' => 'fa-mountain-sun',
            ];
            foreach ($categories as $cat):
                $icon = 'fa-tag';
                $nameLower = strtolower($cat['name']);
                foreach ($categoryIcons as $keyword => $fa) {
                    if (str_contains($nameLower, $keyword)) {
                        $icon = $fa;
                        break;
                    }
                }
            ?>
                <div class="col-6 col-md-3 col-lg-3">
                    <a href="products.php?category=<?= $cat['id'] ?>" class="category-tile text-decoration-none text-dark d-block p-3">
                        <i class="fa-solid <?= $icon ?> text-primary fs-3 mb-2"></i>
                        <h6 class="fw-bold mb-0"><?= sanitize($cat['name']) ?></h6>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" data-i18n="featuredProducts"><i class="fa-solid fa-star text-warning me-2"></i>Featured Local Products</h3>
                <p class="text-muted small mb-0">Authentic goods sold directly by government-approved local vendors</p>
            </div>
            <a href="products.php" class="btn btn-outline-primary fw-bold btn-sm">Browse All Products <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <?php foreach ($products as $p): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card card-custom h-100 border-0 shadow-sm overflow-hidden">
                        <div class="position-relative bg-light" style="aspect-ratio: 4/3; overflow: hidden;">
                            <img src="<?= BASE_URL . ($p['image_path'] ? $p['image_path'] : 'assets/images/placeholder.svg') ?>" onerror="this.onerror=null;this.src='<?= BASE_URL ?>assets/images/placeholder.svg';" class="card-img-top h-100 w-100" style="object-fit: cover;" alt="<?= sanitize($p['name']) ?>">
                            <span class="position-absolute top-0 start-0 m-2 badge bg-success shadow-sm"><i class="fa-solid fa-shield-check me-1"></i>Gov Verified</span>
                        </div>
                        <div class="card-body d-flex flex-column p-3">
                            <span class="badge bg-light text-muted border align-self-start mb-2"><?= sanitize($p['category_name']) ?></span>
                            <h6 class="fw-bold mb-1 text-truncate" title="<?= sanitize($p['name']) ?>">
                                <a href="product-details.php?id=<?= $p['id'] ?>" class="text-dark text-decoration-none"><?= sanitize($p['name']) ?></a>
                            </h6>
                            <small class="text-muted mb-2"><i class="fa-solid fa-store me-1 text-primary"></i><?= sanitize($p['business_name']) ?></small>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary fs-5"><?= formatCurrency($p['price']) ?></span>
                                <a href="product-details.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary fw-bold"><i class="fa-solid fa-cart-plus me-1"></i>View</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Government Approved Local Vendors -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h3 class="fw-bold mb-2" data-i18n="approvedVendors"><i class="fa-solid fa-building-columns text-primary me-2"></i>Government Approved Vendors</h3>
            <p class="text-muted">Verified local businesses holding active digital government licenses</p>
        </div>

        <div class="row g-4">
            <?php foreach ($approvedVendors as $v): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-custom p-3 border-0 shadow-sm h-100 text-center">
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle d-inline-flex mx-auto mb-3">
                            <i class="fa-solid fa-shop fs-2"></i>
                        </div>
                        <h6 class="fw-bold mb-1"><?= sanitize($v['business_name']) ?></h6>
                        <p class="small text-muted mb-2"><?= sanitize($v['business_type']) ?></p>
                        <div class="mb-3">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                                <i class="fa-solid fa-award me-1"></i><?= $v['license_no'] ?: 'LICENSED' ?>
                            </span>
                        </div>
                        <small class="text-secondary d-block mb-3"><i class="fa-solid fa-location-dot me-1 text-danger"></i><?= sanitize($v['municipality']) ?>, <?= sanitize($v['district']) ?></small>
                        <a href="products.php?vendor=<?= $v['id'] ?>" class="btn btn-sm btn-outline-primary fw-semibold mt-auto">View Store Products</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Digital Government Services -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1" data-i18n="govServicesTitle"><i class="fa-solid fa-landmark text-primary me-2"></i>Digital Government Services</h3>
                <p class="text-muted small mb-0">Apply online for local business permits, food quality certifications, and grievance lodging</p>
            </div>
            <a href="government-services.php" class="btn btn-outline-primary fw-bold btn-sm">All Services <i class="fa-solid fa-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <?php foreach ($services as $s): ?>
                <div class="col-md-4">
                    <div class="card card-custom p-4 border-0 shadow-sm h-100">
                        <span class="badge bg-primary-subtle text-primary border me-auto mb-2"><?= sanitize($s['department_name']) ?></span>
                        <h5 class="fw-bold mb-2"><?= sanitize($s['service_name']) ?></h5>
                        <p class="small text-muted mb-3"><?= sanitize($s['description']) ?></p>
                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="small text-muted"><i class="fa-solid fa-clock me-1"></i><?= sanitize($s['processing_time']) ?></span>
                            <a href="service-details.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary fw-bold">Apply Online</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Vendor Registration CTA Banner -->
<section class="cta-banner py-5 text-white">
    <div class="container text-center py-3">
        <div class="max-w-700 mx-auto">
            <i class="fa-solid fa-award text-warning fs-1 mb-3"></i>
            <h2 class="fw-bold mb-3" data-i18n="vendorCTA">Are you a local vendor? Register and apply for your digital business license online.</h2>
            <p class="lead opacity-75 mb-4">Join SmartGov Market to gain official government license verification and reach thousands of local customers.</p>
            <a href="vendor/register.php" class="btn btn-warning btn-lg fw-bold text-dark px-4 me-2" data-i18n="registerVendorBtn">Register as Vendor</a>
            <a href="verify.php" class="btn btn-outline-light btn-lg fw-bold px-4" data-i18n="verifyLicense">Verify Digital License</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
