<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=smartgov_market;charset=utf8mb4", "root", "");
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "TABLES IN DATABASE:\n";
foreach ($tables as $t) {
    echo "\n--- TABLE: $t ---\n";
    $cols = $pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "  {$c['Field']} | {$c['Type']} | Null:{$c['Null']} | Key:{$c['Key']} | Default:{$c['Default']}\n";
    }
}
