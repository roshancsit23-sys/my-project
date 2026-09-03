<?php
// SmartGov Market - Master Footer Template
// File: includes/footer.php
?>
</main> <!-- /main -->

<footer class="mt-auto">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-4 col-md-6">
                <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-building-columns text-warning me-2"></i>SmartGov Market</h5>
                <p class="small text-secondary mb-3" data-i18n="footerTagline">Connecting citizens, vendors, and government for transparent governance and thriving local economy.</p>
                <div class="d-flex gap-2">
                    <span class="badge bg-secondary p-2"><i class="fa-solid fa-shield-halved me-1"></i> SSL Secured</span>
                    <span class="badge bg-success p-2"><i class="fa-solid fa-qrcode me-1"></i> QR Verified</span>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6 class="text-white fw-semibold mb-3" data-i18n="quickLinks">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL ?>index.php" data-i18n="home">Home</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>products.php" data-i18n="products">Products</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>government-services.php" data-i18n="services">Government Services</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>map-view.php" data-i18n="map">GIS Vendor Map</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>complaints.php" data-i18n="complaints">Public Complaints</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-semibold mb-3">E-Governance Modules</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL ?>vendor/register.php">Vendor Registration</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>verify.php">Verify Business License</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>login.php">Officer Portal</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>login.php">Admin Portal</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-semibold mb-3" data-i18n="contactGov">Government Support</h6>
                <p class="small text-secondary mb-1"><i class="fa-solid fa-location-dot me-2 text-warning"></i>Singha Durbar, Kathmandu, Nepal</p>
                <p class="small text-secondary mb-1"><i class="fa-solid fa-phone me-2 text-warning"></i>+977 1 4200000 / 1111 (Toll-Free)</p>
                <p class="small text-secondary mb-2"><i class="fa-solid fa-envelope me-2 text-warning"></i>support@smartgov.gov.np</p>
            </div>
        </div>
        <hr class="border-secondary my-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary">
            <span data-i18n="copyright">© 2026 SmartGov Market. All Rights Reserved.</span>
            <div>
                <a href="#" class="me-3">Privacy Policy</a>
                <a href="#" class="me-3">Terms of Service</a>
                <a href="#">Help & FAQs</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Chart.js for Dashboards -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- QRCode JS for Client-side QR Rendering -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<!-- Custom JS Files -->
<script src="<?= BASE_URL ?>assets/js/i18n.js"></script>
<script src="<?= BASE_URL ?>assets/js/map.js"></script>

</body>
</html>
