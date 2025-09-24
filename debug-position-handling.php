<?php
/**
 * Debug script to test position handling in drag-drop builder
 */

require_once 'core/init.php';
require_once 'core/classes/DragDropBuilder.php';

// Test various position values
$testCases = [
    'null' => null,
    'end string' => 'end',
    'empty string' => '',
    'zero' => 0,
    'valid integer' => 5,
    'string number' => '3',
    'invalid string' => 'invalid',
];

echo "<h2>Position Handling Test</h2>\n";
echo "<pre>\n";

foreach ($testCases as $description => $testValue) {
    echo "Testing: $description (value: " . var_export($testValue, true) . ")\n";
    
    // Simulate the AJAX handler logic
    $position = $testValue;
    
    echo "  Original: " . var_export($position, true) . " (type: " . gettype($position) . ")\n";
    echo "  is_numeric: " . (is_numeric($position) ? 'yes' : 'no') . "\n";
    
    // Apply the conversion logic from AJAX handler
    if ($position === 'end' || $position === '' || !is_numeric($position)) {
        $position = null;
        echo "  After AJAX conversion: NULL (will be calculated)\n";
    } else {
        $position = intval($position);
        echo "  After AJAX conversion: $position (integer)\n";
    }
    
    // Apply the DragDropBuilder logic
    if (!is_numeric($position) || $position === null || $position === 'end' || $position === '') {
        $position = 999; // Simulated calculated position
        echo "  After DragDropBuilder safety check: $position (calculated)\n";
    } else {
        $position = intval($position);
        if ($position <= 0) {
            $position = 1;
        }
        echo "  After DragDropBuilder safety check: $position (validated integer)\n";
    }
    
    echo "  Final result: " . var_export($position, true) . " (type: " . gettype($position) . ")\n";
    echo "\n";
}

echo "</pre>\n";

// Test the actual database column type
echo "<h3>Database Schema Check</h3>\n";
echo "<pre>\n";

try {
    $stmt = $db->query("DESCRIBE page_index");
    $columns = $stmt->fetchAll();
    
    echo "page_index table structure:\n";
    foreach ($columns as $column) {
        if ($column['Field'] === 'position') {
            echo "  Position column: " . print_r($column, true) . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error checking database: " . $e->getMessage() . "\n";
}

echo "</pre>\n";
?>
