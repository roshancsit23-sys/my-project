<?php
$candidates = [
    'https://commons.wikimedia.org/wiki/Special:FilePath/Emblem_of_Nepal.svg',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Emblem_of_Nepal_(2020).svg',
    'https://raw.githubusercontent.com/wikimedia/wikipedia-ios/main/Wikipedia/assets/emblems/np.svg',
    'https://commons.wikimedia.org/wiki/Special:FilePath/Emblem_of_Nepal.png'
];

$ctx = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: SmartGovAcademicPortal/1.0 (info@smartgov.gov.np)\r\n",
        'follow_location' => 1,
        'timeout' => 15
    ]
]);

foreach ($candidates as $url) {
    echo "Trying: $url ... ";
    $data = @file_get_contents($url, false, $ctx);
    if ($data && strlen($data) > 1000) {
        $ext = (strpos($data, '<svg') !== false) ? 'svg' : 'png';
        $dest = __DIR__ . '/../assets/images/nepal-emblem.' . $ext;
        file_put_contents($dest, $data);
        echo "SUCCESS! Saved to $dest (" . strlen($data) . " bytes)\n";
        break;
    } else {
        echo "FAILED\n";
    }
}
