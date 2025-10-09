<?php
// Debug script to check table structure after migration
require_once __DIR__ . '/../core/init.php';

global $db;

echo "<h2>Table Structure Debug</h2>\n";

// Get all page tables
$stmt = $db->query("SHOW TABLES LIKE 'page_%'");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    echo "<h3>Table: $table</h3>\n";

    // Show structure
    $stmt = $db->query("DESCRIBE `$table`");
    $columns = $stmt->fetchAll();

    echo "<pre>";
    print_r($columns);
    echo "</pre>";

    // Show sample data
    echo "<h4>Sample Data:</h4>";
    $stmt = $db->query("SELECT * FROM `$table` LIMIT 3");
    $data = $stmt->fetchAll();
    echo "<pre>";
    print_r($data);
    echo "</pre>";

    echo "<hr>";
}
