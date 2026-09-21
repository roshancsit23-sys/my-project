<?php
// HATIYA - Master Footer Template
// File: includes/footer.php
?>
</main> <!-- /main -->

<footer class="mt-auto">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Col 1: Branding & Mission -->
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="<?= BASE_URL ?>assets/images/nepal-emblem.svg" alt="Emblem of Nepal" height="42" class="bg-white p-1 rounded">
                    <span class="text-white fw-bold fs-5">HATIYA</span>
                </div>
                <p class="small text-secondary mb-3">
                    HATIYA is Nepal's integrated digital portal empowering citizens, verifying local businesses, and providing transparent government services.
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-danger p-2"><i class="fa-solid fa-shield-halved me-1"></i> Government Authenticated</span>
                    <span class="badge bg-success p-2"><i class="fa-solid fa-qrcode me-1"></i> QR Business Verified</span>
                </div>
            </div>

            <!-- Col 2: Navigation Links -->
            <div class="col-lg-2 col-md-6">
                <h6 class="text-white fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL ?>index.php">Home</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>government-services.php">Government Services</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>products.php">Marketplace</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>verify.php">Digital Licenses</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>complaints.php">Public Complaints</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>map-view.php">GIS Vendor Map</a></li>
                </ul>
            </div>

            <!-- Col 3: Portal Modules -->
            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-bold mb-3">E-Governance Portals</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL ?>vendor/register.php">Business &amp; Vendor Registration</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>verify.php">Verify Business License</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>register.php">Citizen Account Creation</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>login.php">Officer &amp; Administrative Portal</a></li>
                </ul>
            </div>

            <!-- Col 4: Official Contact -->
            <div class="col-lg-3 col-md-6">
                <h6 class="text-white fw-bold mb-3">Government Support</h6>
                <p class="small text-secondary mb-2"><i class="fa-solid fa-location-dot me-2 text-danger"></i>Singha Durbar, Kathmandu, Nepal</p>
                <p class="small text-secondary mb-2"><i class="fa-solid fa-phone me-2 text-danger"></i>+977 1 4200000 / 1111 (Toll-Free)</p>
                <p class="small text-secondary mb-2"><i class="fa-solid fa-envelope me-2 text-danger"></i>support@hatiya.gov.np</p>
                <p class="small text-secondary mb-0"><i class="fa-solid fa-clock me-2 text-danger"></i>Sun - Fri: 10:00 AM - 5:00 PM</p>
            </div>
        </div>

        <hr class="border-secondary my-3">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary py-2">
            <span>© 2026 HATIYA – Digital Governance &amp; Marketplace Platform. Government of Nepal Standards.</span>
            <div class="mt-2 mt-md-0">
                <a href="<?= BASE_URL ?>government-services.php#privacy" class="me-3">Privacy Policy</a>
                <a href="<?= BASE_URL ?>government-services.php#terms" class="me-3">Terms of Use</a>
                <a href="<?= BASE_URL ?>government-services.php#accessibility">Accessibility Statement</a>
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
