<?php
$pages = [
    'index.php',
    'government-services.php',
    'products.php',
    'cart.php',
    'verify.php',
    'complaints.php',
    'map-view.php',
    'login.php',
    'register.php',
    'forgot-password.php',
    'admin/dashboard.php',
    'admin/orders.php',
    'admin/vendors.php',
    'admin/products.php',
    'admin/categories.php',
    'admin/licenses.php',
    'admin/departments.php',
    'admin/services.php',
    'admin/complaints.php',
    'admin/users.php',
    'admin/reports.php',
    'admin/audit-logs.php',
    'admin/settings.php',
    'officer/dashboard.php',
    'officer/applications.php',
    'officer/complaints.php',
    'officer/verifications.php',
    'vendor/dashboard.php',
    'vendor/products.php',
    'vendor/orders.php',
    'vendor/license.php',
    'vendor/register.php',
];

$baseUrl = 'http://localhost:8080/SmartGov-Market/';
$results = [];

foreach ($pages as $p) {
    $url = $baseUrl . $p;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 5
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        $results[] = ["page" => $p, "status" => "FAILED", "code" => 0, "msg" => $error];
        continue;
    }
    
    // Check for PHP warnings or fatal errors in output
    $hasPhpError = false;
    $errorMsg = '';
    if (preg_match('/(Fatal error|Parse error|Warning:|Notice:|Uncaught)/i', $response, $matches)) {
        // Exclude legitimate words in HTML text like 'warning' in bootstrap classes (e.g. alert-warning)
        if (preg_match('/(<b>(Fatal error|Warning|Notice|Parse error)<\/b>:|PHP (Fatal error|Warning|Notice|Parse error)|Uncaught [a-zA-Z0-9_\\\\]*Exception)/i', $response, $realMatch)) {
            $hasPhpError = true;
            $errorMsg = substr($realMatch[0], 0, 100);
        }
    }
    
    $results[] = [
        "page" => $p,
        "status" => ($hasPhpError ? "PHP_ERROR" : "OK"),
        "code" => $httpCode,
        "error" => $errorMsg
    ];
}

$allGood = true;
foreach ($results as $r) {
    $statusText = ($r['status'] === 'OK' ? '✓ OK' : '✗ ' . $r['status']);
    printf("%-30s [%3d] %s %s\n", $r['page'], $r['code'], $statusText, $r['error'] ?? '');
    if ($r['status'] !== 'OK') {
        $allGood = false;
    }
}

if ($allGood) {
    echo "\n>>> ALL PAGES TESTED SUCCESSFULLY WITHOUT PHP ERRORS! <<<\n";
} else {
    echo "\n>>> SOME PAGES HAVE ISSUES! <<<\n";
}
