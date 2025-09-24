<?php
/**
 * Test script to verify position handling
 */

require_once 'core/init.php';
require_once 'core/classes/DragDropBuilder.php';

// Test position handling
$builder = new DragDropBuilder($db);

echo "Testing position handling...\n";

// Test cases
$testCases = [
    ['position' => 'end', 'expected' => 'auto-calculated'],
    ['position' => '1', 'expected' => '1'],
    ['position' => 1, 'expected' => '1'],
    ['position' => null, 'expected' => 'auto-calculated'],
    ['position' => '', 'expected' => 'auto-calculated'],
    ['position' => 'invalid', 'expected' => 'auto-calculated'],
];

foreach ($testCases as $i => $test) {
    echo "\nTest case " . ($i + 1) . ": position = " . var_export($test['position'], true) . "\n";
    
    try {
        $componentId = $builder->addComponent(1, 'text', ['content' => 'Test content ' . ($i + 1)], $test['position']);
        echo "Success! Component added with ID: $componentId\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\nTest completed.\n";
?>
