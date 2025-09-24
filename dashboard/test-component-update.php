<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

require_once __DIR__ . '/../core/classes/DragDropBuilder.php';

// Test component update functionality
$pageId = 1; // Test with page_index
$builder = new DragDropBuilder($db);

echo "<h1>Component Update Test</h1>";

// Get existing components
echo "<h2>Existing Components:</h2>";
$components = $builder->getPageComponents($pageId);
echo "<pre>";
print_r($components);
echo "</pre>";

if (!empty($components)) {
    $component = reset($components);
    $componentId = $component['id'];
    
    echo "<h2>Testing Update for Component ID: $componentId</h2>";
    
    // Test settings
    $testSettings = [
        'title' => 'Test Updated Title - ' . date('H:i:s'),
        'subtitle' => 'Test subtitle updated at ' . date('Y-m-d H:i:s')
    ];
    
    echo "<h3>Settings to Update:</h3>";
    echo "<pre>";
    print_r($testSettings);
    echo "</pre>";
    
    try {
        $builder->updateComponent($pageId, $componentId, $testSettings);
        echo "<p style='color: green;'>✓ Update successful!</p>";
        
        // Verify the update
        echo "<h3>Component after update:</h3>";
        $updatedComponents = $builder->getPageComponents($pageId);
        $updatedComponent = array_filter($updatedComponents, function($c) use ($componentId) {
            return $c['id'] == $componentId;
        });
        
        echo "<pre>";
        print_r(reset($updatedComponent));
        echo "</pre>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Update failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>No components found to test with.</p>";
}

echo "<br><a href='page-builder.php?id=$pageId'>← Back to Page Builder</a>";
?>
