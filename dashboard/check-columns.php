<?php
require_once __DIR__ . '/../core/init.php';
checkActiveOrLimitedAccess();

header('Content-Type: application/json');

try {
    global $db;

    // Get users table structure
    $stmt = $db->prepare("DESCRIBE users");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $columnNames = array_column($columns, 'Field');

    $result = [
        'has_description' => in_array('description', $columnNames),
        'has_bio' => in_array('bio', $columnNames),
        'all_columns' => $columnNames,
        'profile_related_columns' => array_filter($columnNames, function ($col) {
            return strpos($col, 'description') !== false ||
                strpos($col, 'bio') !== false ||
                strpos($col, 'profile') !== false;
        })
    ];

    echo json_encode($result, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
