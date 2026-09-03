<?php
// SmartGov Market Database Initializer
// File: database/init_db.php

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    echo "Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Creating database if not exists...\n";
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($sql);
    echo "[✓] Database schema initialized successfully.\n";
    
    echo "Running database seeder...\n";
    require_once __DIR__ . '/seed.php';
    
    echo "\n=============================================\n";
    echo "SMARTGOV MARKET DATABASE IS READY FOR USE!\n";
    echo "=============================================\n";

} catch (PDOException $e) {
    die("Database Initialization Error: " . $e->getMessage() . "\n");
}
