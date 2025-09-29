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

                    // Get component type to check for special handling
                    $components = $builder->getPageComponents($pageId);
                    $component = array_filter($components, function ($c) use ($componentId) {
                        return $c['id'] == $componentId;
                    });
                    $component = reset($component);

                    if ($component) {
                        $componentType = $component['component_type'] ?? $component['block_type'] ?? 'unknown';

                        // Special handling for statistics component
                        if ($componentType === 'stats' && isset($settings['items'])) {
                            // For backward compatibility, we can save it in the new format with 'items'
                            // The template already handles both formats
                            error_log("Statistics component detected, keeping 'items' structure");
                        }
                    }

                    error_log("Update component - Processed settings: " . var_export($settings, true));

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
                $componentIds = array_map(function ($c) {
                    return $c['id'];
                }, $components);

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

                    // Debug logging for stats component
                    if ($componentType === 'stats') {
                        error_log("Stats component debug:");
                        error_log("Component config: " . json_encode($componentConfig));
                        error_log("Original settings: " . json_encode($settings));
                    }

                    // Special handling for statistics component migration
                    if ($componentType === 'stats') {
                        // Check if the data is in old format (direct array) vs new format (with 'items' field)
                        if (isset($settings[0]) && is_array($settings[0])) {
                            // Old format: settings is directly the array of stats
                            $settings = ['items' => $settings];
                        } elseif (!isset($settings['items'])) {
                            // No items field, create empty array
                            $settings = ['items' => []];
                        }
                        // If it already has 'items' field, leave as is

                        error_log("Migrated settings: " . json_encode($settings));
                    }

                    echo json_encode([
                        'success' => true,
                        'component' => $componentConfig,
                        'settings' => $settings
                    ]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Component not found']);
                }
                break;

            case 'upload_image':
                if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'error' => 'No image file uploaded or upload error']);
                    break;
                }

                $file = $_FILES['image'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 5 * 1024 * 1024; // 5MB

                // Validate file type
                if (!in_array($file['type'], $allowedTypes)) {
                    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
                    break;
                }

                // Validate file size
                if ($file['size'] > $maxSize) {
                    echo json_encode(['success' => false, 'error' => 'File size too large. Maximum size is 5MB.']);
                    break;
                }

                // Create uploads directory if it doesn't exist
                $uploadDir = __DIR__ . '/../uploads/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '.' . $extension;
                $filepath = $uploadDir . $filename;

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Generate URL relative to site root
                    $url = $settings['site_url'] . '/uploads/images/' . $filename;
                    echo json_encode(['success' => true, 'url' => $url]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
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
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Builder - <?= htmlspecialchars($page['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#ef4444',
                        secondary: '#1f2937',
                        accent: '#f59e0b'
                    }
                }
            }
        }
    </script>
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

        .dark .ddb-sidebar {
            background: #1f2937;
            border-right-color: #374151;
        }

        .ddb-canvas {
            flex: 1;
            background: #f9fafb;
            overflow-y: auto;
            position: relative;
        }

        .dark .ddb-canvas {
            background: #111827;
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

        .dark .ddb-settings-panel {
            background: #1f2937;
            border-left-color: #374151;
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

        .dark .ddb-form-label {
            color: #d1d5db;
        }

        .ddb-form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            background: white;
            color: #374151;
        }

        .dark .ddb-form-control {
            background: #374151;
            border-color: #4b5563;
            color: #d1d5db;
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

        .dark .ddb-toolbar {
            background: #1f2937;
            border-bottom-color: #374151;
        }

        .sortable-ghost {
            opacity: 0.4;
        }

        .sortable-chosen {
            transform: scale(1.02);
        }

        /* Repeater Field Styles */
        .ddb-repeater {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            background: #f9fafb;
            margin-bottom: 16px;
        }

        .dark .ddb-repeater {
            border-color: #4b5563;
            background: #374151;
        }

        .ddb-repeater-items {
            margin-bottom: 12px;
        }

        .ddb-repeater-item {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            margin-bottom: 12px;
            padding: 12px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .dark .ddb-repeater-item {
            background: #1f2937;
            border-color: #4b5563;
        }

        .ddb-repeater-item:last-child {
            margin-bottom: 0;
        }

        .ddb-repeater-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-weight: 600;
            color: #374151;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .dark .ddb-repeater-header {
            color: #d1d5db;
            border-bottom-color: #4b5563;
        }

        .ddb-repeater-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .ddb-repeater-field {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            background: white;
            color: #374151;
        }

        .dark .ddb-repeater-field {
            background: #4b5563;
            border-color: #6b7280;
            color: #d1d5db;
        }

        .ddb-repeater-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .ddb-btn-secondary {
            background: #6b7280;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .ddb-btn-secondary:hover {
            background: #4b5563;
        }

        .ddb-btn-danger {
            background: #ef4444;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .ddb-btn-danger:hover {
            background: #dc2626;
        }

        /* Image Field Styles */
        .ddb-image-field {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            background: white;
        }

        .dark .ddb-image-field {
            border-color: #4b5563;
            background: #374151;
        }

        .ddb-image-preview {
            position: relative;
            margin-bottom: 12px;
            display: inline-block;
        }

        .ddb-image-preview img {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .dark .ddb-image-preview img {
            border-color: #4b5563;
        }

        .ddb-btn-remove {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 20px;
            height: 20px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 12px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ddb-btn-remove:hover {
            background: #dc2626;
        }

        .ddb-image-controls {
            margin-top: 8px;
        }

        .ddb-image-buttons {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .ddb-btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .ddb-file-input {
            display: none;
        }

        /* Emoji Picker Styles */
        .ddb-emoji-picker {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ddb-emoji-picker-content {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            width: 400px;
            max-height: 500px;
            overflow: hidden;
        }

        .dark .ddb-emoji-picker-content {
            background: #1f2937;
        }

        .ddb-emoji-picker-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
        }

        .dark .ddb-emoji-picker-header {
            border-bottom-color: #374151;
            color: #d1d5db;
        }

        .ddb-btn-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #6b7280;
            padding: 4px;
            border-radius: 4px;
        }

        .ddb-btn-close:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .dark .ddb-btn-close:hover {
            background: #374151;
            color: #d1d5db;
        }

        .ddb-emoji-grid {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 4px;
            padding: 16px;
            max-height: 400px;
            overflow-y: auto;
        }

        .ddb-emoji-btn {
            background: none;
            border: none;
            font-size: 24px;
            padding: 8px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .ddb-emoji-btn:hover {
            background: #f3f4f6;
        }

        .dark .ddb-emoji-btn:hover {
            background: #374151;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <div class="ddb-builder">
        <!-- Sidebar with Components -->
        <div class="ddb-sidebar">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Components</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">Drag components to build your page</p>
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
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
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
                                        'heading' => '📝',
                                        'text' => '📄',
                                        'hero' => '🎯',
                                        'image' => '🖼️',
                                        'button' => '�',
                                        'spacer' => '↕️',
                                        'columns' => '📊',
                                        'members_grid' => '👥',
                                        'contact_form' => '📧',
                                        'apply_form' => '📝',
                                        'welcome' => '👋',
                                        'title' => '📰',
                                        'title_2' => '�',
                                        'title_3' => '📰',
                                        'stats' => '📊',
                                        'core_values' => '⭐',
                                        'scroll_arrow' => '⬇️',
                                        'applied' => '✅',
                                        'contacted' => '📩',
                                        'stickers' => '🏷️',
                                        'values' => '💎',
                                        'box' => '📦',
                                        'custom' => '�'
                                    ];
                                    echo $icons[$type] ?? '📦';
                                    ?>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($component['name']) ?>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
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
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Editing: <?= htmlspecialchars($page['title']) ?>
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Drag components from the sidebar to build your page</p>
                </div>
                <div class="flex gap-2">
                    <button id="preview-btn" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
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
                <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-sm min-h-[600px]">
                    <div class="p-8">
                        <div id="canvas-content" class="ddb-component-list">
                            <?php if (empty($components)): ?>
                                <div class="ddb-drop-zone" id="initial-drop-zone">
                                    <div class="text-center">
                                        <div class="text-4xl mb-2">🎨</div>
                                        <p class="text-gray-500 dark:text-gray-400">Drop components here to start building</p>
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
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
            <div class="flex justify-between items-center">
                <h3 id="settings-title" class="text-lg font-semibold text-gray-900 dark:text-white">Component Settings</h3>
                <button id="close-settings" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-4">
            <form id="settings-form">
                <div id="settings-fields"></div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" id="cancel-settings" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
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
    <script>
        // Dark mode initialization for page builder
        function initPageBuilderDarkMode() {
            const savedTheme = localStorage.getItem('theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }

        // Initialize dark mode on page load
        initPageBuilderDarkMode();
    </script>
</body>

</html>