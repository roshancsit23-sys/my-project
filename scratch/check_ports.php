<?php
echo "Testing port 3306...\n";
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", "root", "", [PDO::ATTR_TIMEOUT => 3]);
    echo "SUCCESS connecting to 3306!\n";
    $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Databases on 3306: " . implode(", ", $dbs) . "\n";
} catch (Exception $e) {
    echo "ERROR 3306: " . $e->getMessage() . "\n";
}

echo "\nTesting port 3307...\n";
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3307", "root", "", [PDO::ATTR_TIMEOUT => 3]);
    echo "SUCCESS connecting to 3307!\n";
    $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Databases on 3307: " . implode(", ", $dbs) . "\n";
} catch (Exception $e) {
    echo "ERROR 3307: " . $e->getMessage() . "\n";
}
