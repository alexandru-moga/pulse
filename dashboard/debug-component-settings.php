<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

require_once __DIR__ . '/../core/classes/DragDropBuilder.php';

$pageId = isset($_GET['page_id']) ? intval($_GET['page_id']) : 1;
$builder = new DragDropBuilder($db);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Component Settings Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        form { margin: 10px 0; }
        input, textarea, select { margin: 5px; padding: 8px; width: 200px; }
        button { padding: 8px 15px; margin: 5px; }
    </style>
</head>
<body>
    <h1>Component Settings Debug Tool</h1>
    
    <div class="debug-section">
        <h2>Page Information</h2>
        <?php
        $page = $builder->getPage($pageId);
        echo "<p><strong>Page ID:</strong> $pageId</p>";
        if ($page) {
            echo "<p><strong>Page Name:</strong> " . htmlspecialchars($page['name']) . "</p>";
            echo "<p><strong>Table Name:</strong> " . htmlspecialchars($page['table_name']) . "</p>";
        } else {
            echo "<p class='error'>Page not found!</p>";
        }
        ?>
    </div>

    <div class="debug-section">
        <h2>Components on Page</h2>
        <?php
        $components = $builder->getPageComponents($pageId);
        if (!empty($components)) {
            echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Type</th><th>Settings Preview</th><th>Actions</th></tr>";
            
            foreach ($components as $component) {
                $componentType = $component['component_type'] ?? $component['block_type'] ?? 'unknown';
                $settingsJson = $component['settings'] ?? $component['content'] ?? '{}';
                $settings = json_decode($settingsJson, true) ?: [];
                $settingsPreview = htmlspecialchars(substr($settingsJson, 0, 100)) . (strlen($settingsJson) > 100 ? '...' : '');
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($component['id']) . "</td>";
                echo "<td>" . htmlspecialchars($componentType) . "</td>";
                echo "<td><small>$settingsPreview</small></td>";
                echo "<td><a href='?page_id=$pageId&test_component=" . $component['id'] . "'>Test Update</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No components found.</p>";
        }
        ?>
    </div>

    <?php if (isset($_GET['test_component'])): ?>
        <div class="debug-section">
            <h2>Testing Component Update</h2>
            <?php
            $testComponentId = intval($_GET['test_component']);
            $testComponent = array_filter($components, function($c) use ($testComponentId) {
                return $c['id'] == $testComponentId;
            });
            $testComponent = reset($testComponent);
            
            if ($testComponent) {
                echo "<h3>Original Component Data:</h3>";
                echo "<pre>" . print_r($testComponent, true) . "</pre>";
                
                // Test different types of updates
                $testSettings = [
                    'title' => 'Updated Title ' . date('H:i:s'),
                    'subtitle' => 'Updated at ' . date('Y-m-d H:i:s'),
                    'content' => 'Test content updated',
                    'align' => 'center'
                ];
                
                echo "<h3>Test Settings:</h3>";
                echo "<pre>" . print_r($testSettings, true) . "</pre>";
                
                try {
                    $builder->updateComponent($pageId, $testComponentId, $testSettings);
                    echo "<p class='success'>✓ Component updated successfully!</p>";
                    
                    // Fetch updated component
                    $updatedComponents = $builder->getPageComponents($pageId);
                    $updatedComponent = array_filter($updatedComponents, function($c) use ($testComponentId) {
                        return $c['id'] == $testComponentId;
                    });
                    $updatedComponent = reset($updatedComponent);
                    
                    echo "<h3>Updated Component Data:</h3>";
                    echo "<pre>" . print_r($updatedComponent, true) . "</pre>";
                    
                } catch (Exception $e) {
                    echo "<p class='error'>✗ Update failed: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            } else {
                echo "<p class='error'>Component not found!</p>";
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="debug-section">
        <h2>Manual AJAX Test</h2>
        <p>This simulates the exact AJAX request sent by the page builder:</p>
        
        <?php if (!empty($components)): ?>
            <form id="ajax-test-form">
                <p>
                    <label>Component ID:</label>
                    <select name="component_id" id="component_id">
                        <?php foreach ($components as $component): ?>
                            <option value="<?= $component['id'] ?>">
                                <?= htmlspecialchars($component['id'] . ' - ' . ($component['component_type'] ?? $component['block_type'] ?? 'unknown')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <label>Test Title:</label>
                    <input type="text" name="test_title" value="AJAX Test Title <?= date('H:i:s') ?>" />
                </p>
                <p>
                    <label>Test Subtitle:</label>
                    <input type="text" name="test_subtitle" value="AJAX Test Subtitle <?= date('Y-m-d H:i:s') ?>" />
                </p>
                <button type="button" onclick="testAjaxUpdate()">Test AJAX Update</button>
            </form>
            
            <div id="ajax-result"></div>
            
            <script>
                function testAjaxUpdate() {
                    const form = document.getElementById('ajax-test-form');
                    const resultDiv = document.getElementById('ajax-result');
                    
                    const settings = {
                        title: form.test_title.value,
                        subtitle: form.test_subtitle.value
                    };
                    
                    console.log('Testing AJAX update with settings:', settings);
                    
                    const data = new FormData();
                    data.append('action', 'update_component');
                    data.append('component_id', form.component_id.value);
                    data.append('settings', JSON.stringify(settings));
                    
                    resultDiv.innerHTML = '<p>Testing AJAX request...</p>';
                    
                    fetch('/dashboard/page-builder.php?id=<?= $pageId ?>', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: data
                    })
                    .then(response => response.json())
                    .then(result => {
                        console.log('AJAX response:', result);
                        if (result.success) {
                            resultDiv.innerHTML = '<p class="success">✓ AJAX update successful!</p>';
                            // Reload to show changes
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            resultDiv.innerHTML = '<p class="error">✗ AJAX update failed: ' + (result.error || 'Unknown error') + '</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        resultDiv.innerHTML = '<p class="error">✗ AJAX request failed: ' + error.message + '</p>';
                    });
                }
            </script>
        <?php else: ?>
            <p>No components available for testing.</p>
        <?php endif; ?>
    </div>

    <p><a href="page-builder.php?id=<?= $pageId ?>">← Back to Page Builder</a></p>
</body>
</html>
