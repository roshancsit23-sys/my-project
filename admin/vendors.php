<?php
// SmartGov Market - Admin Vendor Management & Approvals
// File: admin/vendors.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/license_generator.php';

requireRole('admin');

$db = getDBConnection();

// Handle AJAX or POST/GET Actions BEFORE header.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $vendor_id = (int)($_POST['vendor_id'] ?? $_GET['vendor_id'] ?? 0);
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

    if ($vendor_id > 0 && in_array($action, ['verify', 'reject', 'suspend', 'reactivate'], true)) {
        $vStmt = $db->prepare("SELECT v.*, u.full_name as owner_name, u.email FROM vendors v JOIN users u ON v.user_id = u.id WHERE v.id = ?");
        $vStmt->execute([$vendor_id]);
        $vendor = $vStmt->fetch();

        if ($vendor) {
            $msg = '';
            $newStatus = '';
            $adminId = $_SESSION['user_id'] ?? null;

            if ($action === 'verify' || $action === 'reactivate') {
                $newStatus = 'Verified';
                $lic = issueDigitalLicense($vendor_id);
                $uStmt = $db->prepare("UPDATE vendors SET status = 'Verified', verified_at = NOW(), verified_by = ? WHERE id = ?");
                $uStmt->execute([$adminId, $vendor_id]);

                // Also verify user account
                $db->prepare("UPDATE users SET is_verified = 1 WHERE id = ?")->execute([$vendor['user_id']]);

                // Ensure licenses status is VALID
                $db->prepare("UPDATE licenses SET status = 'VALID' WHERE vendor_id = ?")->execute([$vendor_id]);

                logAudit('Vendor Verified', 'Vendor', $vendor_id, "Admin verified vendor {$vendor['business_name']}. License {$lic['license_no']} active.");
                $msg = "Vendor verified successfully. The vendor can now sell goods.";
            } elseif ($action === 'reject') {
                $newStatus = 'Rejected';
                $db->prepare("UPDATE vendors SET status = 'Rejected' WHERE id = ?")->execute([$vendor_id]);
                // Mark any active applications as rejected
                $db->prepare("UPDATE vendor_applications SET status = 'Rejected' WHERE vendor_id = ?")->execute([$vendor_id]);

                logAudit('Vendor Rejected', 'Vendor', $vendor_id, "Admin rejected vendor {$vendor['business_name']}.");
                $msg = "Vendor has been rejected.";
            } elseif ($action === 'suspend') {
                $newStatus = 'Suspended';
                $db->prepare("UPDATE vendors SET status = 'Suspended' WHERE id = ?")->execute([$vendor_id]);
                // Suspend any active licenses
                $db->prepare("UPDATE licenses SET status = 'SUSPENDED' WHERE vendor_id = ?")->execute([$vendor_id]);

                logAudit('Vendor Suspended', 'Vendor', $vendor_id, "Admin suspended vendor {$vendor['business_name']}.");
                $msg = "Vendor has been suspended. Marketplace privileges revoked.";
            }

            // Always store session flash so it survives page reload
            setFlashMessage('success', $msg);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'status' => $newStatus, 'message' => $msg]);
                exit();
            }

            redirect('admin/vendors.php');
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid vendor or action.']);
        exit();
    }
}

// Fetch all vendors with complete registration, user, application, and license details
$vendors = $db->query("SELECT v.*, u.full_name as owner_name, u.email, u.phone, u.created_at as registered_at,
                              l.license_no, l.status as license_status, l.issue_date, l.expiry_date,
                              va.id as application_id, va.application_no, va.status as app_status,
                              (SELECT COUNT(*) FROM products p WHERE p.vendor_id = v.id) as product_count
                       FROM vendors v
                       JOIN users u ON v.user_id = u.id
                       LEFT JOIN licenses l ON v.id = l.vendor_id AND l.status != 'REVOKED'
                       LEFT JOIN vendor_applications va ON v.id = va.vendor_id
                       GROUP BY v.id
                       ORDER BY v.id DESC")->fetchAll();

// Fetch all vendor documents for modals
$allDocs = $db->query("SELECT vd.*, va.vendor_id 
                       FROM vendor_documents vd 
                       JOIN vendor_applications va ON vd.application_id = va.id")->fetchAll();
$vendorDocsMap = [];
foreach ($allDocs as $doc) {
    $vendorDocsMap[$doc['vendor_id']][] = $doc;
}

$pageTitle = "Vendors & Approvals";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php $activePage = 'vendors'; include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card card-custom p-4 shadow-sm border-0 mb-4">
                <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-4 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="fa-solid fa-store text-primary me-2"></i>Vendors & Approvals</h4>
                        <p class="text-muted small mb-0">Review registrations, verify merchants, manage business licenses, and grant marketplace selling access.</p>
                    </div>
                    <span class="badge bg-primary rounded-pill fs-6 px-3 py-2"><?= count($vendors) ?> Registered Vendors</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0" id="vendors-table">
                        <thead class="table-light">
                            <tr>
                                <th>Vendor ID</th>
                                <th>Business Name</th>
                                <th>Owner</th>
                                <th>Category / Type</th>
                                <th>Digital License</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($vendors) === 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No vendor registrations found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($vendors as $v): 
                                    $vStatus = $v['status'];
                                    $isVerified = in_array($vStatus, ['Approved', 'Verified'], true);
                                    $badgeClass = 'bg-warning text-dark';
                                    $badgeText = 'Pending';
                                    if ($isVerified) {
                                        $badgeClass = 'bg-success';
                                        $badgeText = 'Verified';
                                    } elseif ($vStatus === 'Suspended') {
                                        $badgeClass = 'bg-secondary';
                                        $badgeText = 'Suspended';
                                    } elseif ($vStatus === 'Rejected') {
                                        $badgeClass = 'bg-danger';
                                        $badgeText = 'Rejected';
                                    }
                                    $docs = $vendorDocsMap[$v['id']] ?? [];
                                ?>
                                    <tr id="vendor-row-<?= $v['id'] ?>">
                                        <td><span class="fw-bold text-muted">#<?= $v['id'] ?></span></td>
                                        <td>
                                            <div class="fw-bold text-dark fs-6"><?= sanitize($v['business_name']) ?></div>
                                            <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i><?= sanitize($v['municipality']) ?>, <?= sanitize($v['district']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= sanitize($v['owner_name']) ?></div>
                                            <small class="text-muted"><?= sanitize($v['phone']) ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= sanitize($v['business_type']) ?></span></td>
                                        <td>
                                            <?php if ($v['license_no']): ?>
                                                <a href="<?= BASE_URL ?>verify.php?license_no=<?= urlencode($v['license_no']) ?>" target="_blank" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none">
                                                    <i class="fa-solid fa-qrcode me-1"></i><?= sanitize($v['license_no']) ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border">No License Issued</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $badgeClass ?> status-badge" id="badge-<?= $v['id'] ?>"><?= $badgeText ?></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#vendorModal<?= $v['id'] ?>">
                                                    <i class="fa-solid fa-file-lines me-1"></i>Review
                                                </button>
                                                <?php if (!$isVerified): ?>
                                                    <button type="button" class="btn btn-success fw-semibold" onclick="handleVendorAction(<?= $v['id'] ?>, 'verify')">
                                                        <i class="fa-solid fa-check me-1"></i>Verify
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-danger fw-semibold" onclick="handleVendorAction(<?= $v['id'] ?>, 'suspend')">
                                                        <i class="fa-solid fa-ban me-1"></i>Suspend
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Complete Vendor Review Modal -->
                                    <div class="modal fade" id="vendorModal<?= $v['id'] ?>" tabindex="-1" aria-labelledby="vendorModalLabel<?= $v['id'] ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg">
                                                <div class="modal-header bg-light border-bottom">
                                                    <h5 class="modal-title fw-bold" id="vendorModalLabel<?= $v['id'] ?>">
                                                        <i class="fa-solid fa-store text-primary me-2"></i>Vendor Registration Review: <?= sanitize($v['business_name']) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row g-3">
                                                        <!-- Verification Status Banner -->
                                                        <div class="col-12">
                                                            <div class="p-3 rounded-3 d-flex justify-content-between align-items-center <?= $isVerified ? 'bg-success-subtle border border-success-subtle' : 'bg-warning-subtle border border-warning-subtle' ?>">
                                                                <div>
                                                                    <strong>Verification Status:</strong> 
                                                                    <span class="badge <?= $badgeClass ?> ms-2"><?= $badgeText ?></span>
                                                                    <div class="small text-muted mt-1">
                                                                        <?= $isVerified ? 'Vendor is approved and permitted to publish products to the public marketplace.' : 'Vendor is pending or restricted. Unverified vendors cannot publish products.' ?>
                                                                    </div>
                                                                </div>
                                                                <?php if ($v['license_no']): ?>
                                                                    <a href="<?= BASE_URL ?>verify.php?license_no=<?= urlencode($v['license_no']) ?>" target="_blank" class="btn btn-sm btn-dark">
                                                                        <i class="fa-solid fa-qrcode me-1"></i>View Digital License
                                                                    </a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- Business & Owner Information -->
                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded border h-100">
                                                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="fa-solid fa-building me-2"></i>Business Details</h6>
                                                                <p class="mb-2"><strong>Business Name:</strong> <?= sanitize($v['business_name']) ?></p>
                                                                <p class="mb-2"><strong>Category / Sector:</strong> <span class="badge bg-white text-dark border"><?= sanitize($v['business_type']) ?></span></p>
                                                                <p class="mb-2"><strong>PAN / VAT No:</strong> <?= sanitize($v['pan_vat'] ?: 'Not Provided') ?></p>
                                                                <p class="mb-2"><strong>Registration No:</strong> <?= sanitize($v['registration_no'] ?: 'Not Provided') ?></p>
                                                                <p class="mb-0"><strong>Full Address:</strong> <?= sanitize($v['address']) ?>, <?= sanitize($v['municipality']) ?>, <?= sanitize($v['district']) ?></p>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded border h-100">
                                                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="fa-solid fa-user-tie me-2"></i>Owner & Account Info</h6>
                                                                <p class="mb-2"><strong>Owner Name:</strong> <?= sanitize($v['owner_name']) ?></p>
                                                                <p class="mb-2"><strong>Email Address:</strong> <a href="mailto:<?= sanitize($v['email']) ?>" class="text-decoration-none"><?= sanitize($v['email']) ?></a></p>
                                                                <p class="mb-2"><strong>Phone Number:</strong> <?= sanitize($v['phone']) ?></p>
                                                                <p class="mb-2"><strong>Registered On:</strong> <?= date('F d, Y - h:i A', strtotime($v['registered_at'])) ?></p>
                                                                <p class="mb-0"><strong>Products in Catalog:</strong> <?= (int)$v['product_count'] ?> products</p>
                                                            </div>
                                                        </div>

                                                        <!-- Uploaded Documents -->
                                                        <div class="col-12">
                                                            <div class="p-3 bg-light rounded border">
                                                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i class="fa-solid fa-folder-open me-2"></i>Registration Documents</h6>
                                                                <?php if (count($docs) === 0): ?>
                                                                    <p class="text-muted small mb-0"><i class="fa-solid fa-circle-info me-1"></i>No additional verification documents uploaded yet. Registration data submitted online.</p>
                                                                <?php else: ?>
                                                                    <div class="d-flex flex-wrap gap-2">
                                                                        <?php foreach ($docs as $doc): ?>
                                                                            <a href="<?= BASE_URL . $doc['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                                <i class="fa-solid fa-file-pdf text-danger me-1"></i><?= sanitize($doc['document_type']) ?>: <?= sanitize($doc['file_name']) ?>
                                                                            </a>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light border-top d-flex justify-content-between">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <div class="d-flex gap-2">
                                                        <?php if (!$isVerified): ?>
                                                            <button type="button" class="btn btn-danger" onclick="handleVendorAction(<?= $v['id'] ?>, 'reject', '#vendorModal<?= $v['id'] ?>')">
                                                                <i class="fa-solid fa-xmark me-1"></i>Reject Vendor
                                                            </button>
                                                            <button type="button" class="btn btn-success fw-bold" onclick="handleVendorAction(<?= $v['id'] ?>, 'verify', '#vendorModal<?= $v['id'] ?>')">
                                                                <i class="fa-solid fa-check-circle me-1"></i>Verify Vendor
                                                            </button>
                                                        <?php else: ?>
                                                            <?php if ($vStatus === 'Suspended'): ?>
                                                                <button type="button" class="btn btn-success fw-bold" onclick="handleVendorAction(<?= $v['id'] ?>, 'reactivate', '#vendorModal<?= $v['id'] ?>')">
                                                                    <i class="fa-solid fa-rotate-left me-1"></i>Reactivate Vendor
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-outline-danger fw-bold" onclick="handleVendorAction(<?= $v['id'] ?>, 'suspend', '#vendorModal<?= $v['id'] ?>')">
                                                                    <i class="fa-solid fa-ban me-1"></i>Suspend Vendor
                                                                </button>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
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
    <div id="vendorToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fs-6 fw-semibold" id="vendorToastBody"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
function showToast(message, isSuccess = true) {
    const toastEl = document.getElementById('vendorToast');
    const toastBody = document.getElementById('vendorToastBody');
    if (!toastEl || !toastBody) return;
    
    toastEl.className = 'toast align-items-center text-white border-0 shadow-lg ' + (isSuccess ? 'bg-success' : 'bg-danger');
    toastBody.innerHTML = (isSuccess ? '<i class="fa-solid fa-circle-check me-2"></i>' : '<i class="fa-solid fa-triangle-exclamation me-2"></i>') + message;
    
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
}

function handleVendorAction(vendorId, action, modalId = null) {
    if (action === 'suspend' && !confirm('Are you sure you want to suspend this vendor? They will not be able to sell products.')) {
        return;
    }
    if (action === 'reject' && !confirm('Are you sure you want to reject this vendor application?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', action);
    formData.append('vendor_id', vendorId);
    formData.append('ajax', '1');

    fetch('<?= BASE_URL ?>admin/vendors.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            if (modalId) {
                const modalInstance = bootstrap.Modal.getInstance(document.querySelector(modalId));
                if (modalInstance) modalInstance.hide();
            }
            // Smoothly reload after 700ms so all details and badges update cleanly
            setTimeout(() => {
                window.location.reload();
            }, 800);
        } else {
            showToast(data.message || 'Action failed.', false);
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Server communication error. Please try again.', false);
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
