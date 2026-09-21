<?php
require_once __DIR__ . '/../config/database.php';
$db = getDBConnection();
echo "Success: Connected to " . DB_NAME . " on MySQL port: " . $db->query('SELECT @@port')->fetchColumn() . "\n";
