<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/classes/DragDropBuilder.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageId = isset($_GET['id']) ? intval($_GET['id']) : null;
if (!$pageId) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Invalid page ID.'];
    header('Location: page-settings.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$pageId]);
$page = $stmt->fetch();

if (!$page || empty($page['table_name'])) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Invalid page or table name.'];
    header('Location: page-settings.php');
    exit;
}

$builder = new DragDropBuilder($db);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'add_component':
                $componentType = $_POST['component_type'] ?? '';
                $position = $_POST['position'] ?? null;

                // Debug logging
                error_log("Adding component: $componentType, original position: " . var_export($position, true));

                // Convert 'end' or any non-numeric values to null for automatic position calculation
                if ($position === 'end' || $position === '' || !is_numeric($position)) {
                    $position = null;
                } else {
                    // Convert to integer if it's a numeric position
                    $position = intval($position);
                }

                error_log("Final position after conversion: " . var_export($position, true));

                $componentId = $builder->addComponent($pageId, $componentType, [], $position);
                echo json_encode(['success' => true, 'component_id' => $componentId]);
                break;

            case 'update_component':
                try {
                    $componentId = intval($_POST['component_id'] ?? 0);
                    $settings = $_POST['settings'] ?? '[]';

                    error_log("Update component - ID: $componentId, Raw settings: " . var_export($settings, true));

                    // If settings is a JSON string, decode it
                    if (is_string($settings)) {
                        $settings = json_decode($settings, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new Exception('Invalid settings JSON: ' . json_last_error_msg());
                        }
                    }

                    // Ensure settings is always an array
                    if (!is_array($settings)) {
                        $settings = [];
                    }

                    error_log("Update component - Decoded settings: " . var_export($settings, true));

                    $builder->updateComponent($pageId, $componentId, $settings);
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    error_log("Update component error: " . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                }
                break;

            case 'delete_component':
                $componentId = intval($_POST['component_id'] ?? 0);

                $builder->deleteComponent($pageId, $componentId);
                echo json_encode(['success' => true]);
                break;

            case 'reorder_components':
                $componentIds = $_POST['component_ids'] ?? [];

                $builder->reorderComponents($pageId, $componentIds);
                echo json_encode(['success' => true]);
                break;

            case 'move_component':
                $componentId = intval($_POST['component_id'] ?? 0);
                $direction = $_POST['direction'] ?? 'up';

                // Get all components for the page
                $components = $builder->getPageComponents($pageId);
                $currentIndex = -1;
                
                // Find current component index
                foreach ($components as $index => $component) {
                    if ($component['id'] == $componentId) {
                        $currentIndex = $index;
                        break;
                    }
                }

                if ($currentIndex === -1) {
                    echo json_encode(['success' => false, 'error' => 'Component not found']);
                    break;
                }

                // Calculate new position
                $newIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;
                
                // Check bounds
                if ($newIndex < 0 || $newIndex >= count($components)) {
                    echo json_encode(['success' => false, 'error' => 'Cannot move component in that direction']);
                    break;
                }

                // Create new order array
                $componentIds = array_map(function($c) { return $c['id']; }, $components);
                
                // Swap positions
                $temp = $componentIds[$currentIndex];
                $componentIds[$currentIndex] = $componentIds[$newIndex];
                $componentIds[$newIndex] = $temp;

                $builder->reorderComponents($pageId, $componentIds);
                echo json_encode(['success' => true]);
                break;

            case 'get_component_settings':
                $componentId = intval($_POST['component_id'] ?? 0);

                // Get component data
                $components = $builder->getPageComponents($pageId);
                $component = array_filter($components, function ($c) use ($componentId) {
                    return $c['id'] == $componentId;
                });
                $component = reset($component);

                if ($component) {
                    // Handle both old and new component structures
                    $componentType = $component['component_type'] ?? $component['block_type'] ?? 'unknown';
                    $settingsJson = $component['settings'] ?? $component['content'] ?? '{}';

                    $componentConfig = $builder->getComponent($componentType);
                    $settings = json_decode($settingsJson, true) ?: [];

                    echo json_encode([
                        'success' => true,
                        'component' => $componentConfig,
                        'settings' => $settings
                    ]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Component not found']);
                }
                break;

            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

    exit;
}

$components = $builder->getPageComponents($pageId);
$availableComponents = $builder->getComponents();

$pageTitle = 'Drag & Drop Builder';
include __DIR__ . '/components/dashboard-header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Builder - <?= htmlspecialchars($page['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        .ddb-builder {
            height: calc(100vh - 64px);
            display: flex;
        }

        .ddb-sidebar {
            width: 300px;
            min-width: 300px;
            background: white;
            border-right: 1px solid #e5e7eb;
            overflow-y: auto;
        }

        .ddb-canvas {
            flex: 1;
            background: #f9fafb;
            overflow-y: auto;
            position: relative;
        }

        .ddb-component {
            position: relative;
            border: 2px solid transparent;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .ddb-component:hover {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        .ddb-component.selected {
            border-color: #ef4444;
            box-shadow: 0 0 0 2px #ef4444;
        }

        .ddb-component-controls {
            position: absolute;
            top: -32px;
            left: 0;
            background: #1f2937;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ddb-component:hover .ddb-component-controls {
            opacity: 1;
        }

        .ddb-component-actions button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 2px;
            border-radius: 2px;
        }

        .ddb-component-actions button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .ddb-drop-zone {
            min-height: 100px;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 14px;
            margin: 16px 0;
            transition: all 0.2s ease;
        }

        .ddb-drop-zone.dragover {
            border-color: #3b82f6;
            background: #dbeafe;
            color: #1e40af;
        }

        .ddb-component-list {
            list-style: none;
            padding: 0;
            margin: 0;
            min-height: 200px;
        }

        .ddb-component-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin: 8px 0;
            cursor: grab;
            transition: all 0.2s ease;
        }

        .ddb-component-item:hover {
            border-color: #3b82f6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .ddb-component-item:active {
            cursor: grabbing;
        }

        .ddb-settings-panel {
            position: fixed;
            right: -400px;
            top: 64px;
            width: 400px;
            height: calc(100vh - 64px);
            background: white;
            border-left: 1px solid #e5e7eb;
            z-index: 50;
            transition: right 0.3s ease;
            overflow-y: auto;
        }

        .ddb-settings-panel.active {
            right: 0;
        }

        .ddb-form-group {
            margin-bottom: 16px;
        }

        .ddb-form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 4px;
            color: #374151;
        }

        .ddb-form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
        }

        .ddb-form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        .ddb-toolbar {
            position: sticky;
            top: 0;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 16px;
            z-index: 20;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .sortable-ghost {
            opacity: 0.4;
        }

        .sortable-chosen {
            transform: scale(1.02);
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="ddb-builder">
        <!-- Sidebar with Components -->
        <div class="ddb-sidebar">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Components</h2>
                <p class="text-sm text-gray-600">Drag components to build your page</p>
            </div>

            <?php
            $categories = [
                'content' => 'Content',
                'layout' => 'Layout',
                'media' => 'Media',
                'forms' => 'Forms'
            ];

            foreach ($categories as $categoryKey => $categoryName):
                $categoryComponents = $builder->getComponentsByCategory($categoryKey);
                if (empty($categoryComponents)) continue;
            ?>

                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-700 uppercase tracking-wide mb-2">
                        <?= $categoryName ?>
                    </h3>

                    <?php foreach ($categoryComponents as $type => $component): ?>
                        <div class="ddb-component-item"
                            draggable="true"
                            data-component-type="<?= $type ?>">
                            <div class="flex items-center">
                                <div class="text-2xl mr-3">
                                    <?php
                                    $icons = [
                                        'heading' => '📝', 'text' => '📄', 'hero' => '🎯', 'image' => '🖼️', 'button' => '�',
                                        'spacer' => '↕️', 'columns' => '📊', 'members_grid' => '👥', 'contact_form' => '📧',
                                        'apply_form' => '📝', 'welcome' => '👋', 'title' => '📰', 'title_2' => '�',
                                        'title_3' => '📰', 'stats' => '📊', 'core_values' => '⭐', 'scroll_arrow' => '⬇️',
                                        'applied' => '✅', 'contacted' => '📩', 'stickers' => '🏷️', 'values' => '💎',
                                        'box' => '📦', 'custom' => '�'
                                    ];
                                    echo $icons[$type] ?? '📦';
                                    ?>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">
                                        <?= htmlspecialchars($component['name']) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= htmlspecialchars($component['description']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endforeach; ?>
        </div>

        <!-- Main Canvas -->
        <div class="ddb-canvas">
            <div class="ddb-toolbar">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">
                        Editing: <?= htmlspecialchars($page['title']) ?>
                    </h1>
                    <p class="text-sm text-gray-600">Drag components from the sidebar to build your page</p>
                </div>
                <div class="flex gap-2">
                    <button id="preview-btn" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Preview
                    </button>
                    <button id="save-btn" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                        Save
                    </button>
                    <a href="<?= $settings['site_url'] ?>/dashboard/page-settings.php?id=<?= $pageId ?>"
                        class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:bg-red-600">
                        Done
                    </a>
                </div>
            </div>

            <div class="p-6">
                <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm min-h-[600px]">
                    <div class="p-8">
                        <div id="canvas-content" class="ddb-component-list">
                            <?php if (empty($components)): ?>
                                <div class="ddb-drop-zone" id="initial-drop-zone">
                                    <div class="text-center">
                                        <div class="text-4xl mb-2">🎨</div>
                                        <p class="text-gray-500">Drop components here to start building</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($components as $component): ?>
                                    <div class="ddb-component-wrapper">
                                        <?= $builder->renderComponent($component, true) ?>
                                    </div>
                                <?php endforeach; ?>
                                <div class="ddb-drop-zone">
                                    <p>Drop components here to add to the end</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Panel -->
    <div id="settings-panel" class="ddb-settings-panel">
        <div class="p-4 border-b bg-gray-50">
            <div class="flex justify-between items-center">
                <h3 id="settings-title" class="text-lg font-semibold text-gray-900">Component Settings</h3>
                <button id="close-settings" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-4">
            <form id="settings-form">
                <div id="settings-fields"></div>

                <div class="flex justify-end gap-2 pt-4 border-t">
                    <button type="button" id="cancel-settings" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        Apply Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= $settings['site_url'] ?>/js/drag-drop-builder.js"></script>
</body>

</html>