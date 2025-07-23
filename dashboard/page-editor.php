<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/classes/ComponentManager.php';
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

$componentManager = new ComponentManager($db);
$tableName = $page['table_name'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_component':
            $componentType = $_POST['component_type'] ?? '';
            $orderNum = intval($_POST['order_num'] ?? 0);
            
            $component = $componentManager->getComponent($componentType);
            if ($component) {
                $defaultData = [];
                foreach ($component['fields'] as $fieldName => $field) {
                    $defaultData[$fieldName] = $field['default'] ?? '';
                }
                
                $stmt = $db->prepare("INSERT INTO `$tableName` (block_name, block_type, content, order_num, is_active) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([
                    $component['name'],
                    $componentType,
                    json_encode($defaultData),
                    $orderNum
                ]);
                
                echo json_encode(['success' => true, 'block_id' => $db->lastInsertId()]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid component type']);
            }
            exit;
            
        case 'update_component':
            $blockId = intval($_POST['block_id'] ?? 0);
            $data = $_POST['data'] ?? [];
            
            $stmt = $db->prepare("UPDATE `$tableName` SET content = ? WHERE id = ?");
            $stmt->execute([json_encode($data), $blockId]);
            
            echo json_encode(['success' => true]);
            exit;
            
        case 'delete_component':
            $blockId = intval($_POST['block_id'] ?? 0);
            
            $stmt = $db->prepare("DELETE FROM `$tableName` WHERE id = ?");
            $stmt->execute([$blockId]);
            
            echo json_encode(['success' => true]);
            exit;
            
        case 'reorder_components':
            $order = $_POST['order'] ?? [];
            
            foreach ($order as $index => $blockId) {
                $stmt = $db->prepare("UPDATE `$tableName` SET order_num = ? WHERE id = ?");
                $stmt->execute([$index, intval($blockId)]);
            }
            
            echo json_encode(['success' => true]);
            exit;
    }
}

// Get existing blocks
$blocks = $db->query("SELECT * FROM `$tableName` ORDER BY order_num ASC")->fetchAll();

$pageTitle = 'Visual Page Editor';
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="page-editor">
    <div class="editor-header bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Editing: <?= htmlspecialchars($page['title']) ?>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Drag and drop components to build your page
                </p>
            </div>
            <div class="flex space-x-3">
                <button id="previewBtn" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Preview
                </button>
                <a href="<?= $settings['site_url'] ?>/dashboard/page-settings.php?id=<?= $pageId ?>" 
                   class="px-4 py-2 bg-primary text-white rounded-md text-sm font-medium hover:bg-red-600">
                    Done Editing
                </a>
            </div>
        </div>
    </div>

    <div class="editor-content flex">
        <!-- Component Sidebar -->
        <div class="component-sidebar w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 p-4 overflow-y-auto">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Components</h3>
            
            <?php
            $categories = ['header', 'content', 'media', 'forms'];
            $categoryLabels = [
                'header' => 'Headers',
                'content' => 'Content',
                'media' => 'Media',
                'forms' => 'Forms'
            ];
            
            foreach ($categories as $category):
                $components = $componentManager->getComponentsByCategory($category);
                if (empty($components)) continue;
            ?>
                <div class="component-category mb-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wider">
                        <?= $categoryLabels[$category] ?>
                    </h4>
                    <div class="space-y-2">
                        <?php foreach ($components as $type => $component): ?>
                            <div class="component-item p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                 data-component-type="<?= $type ?>">
                                <div class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($component['name']) ?></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?= htmlspecialchars($component['description']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Main Editor Area -->
        <div class="editor-main flex-1 bg-gray-50 dark:bg-gray-900">
            <div class="page-canvas max-w-4xl mx-auto p-6">
                <div id="component-list" class="space-y-4">
                    <?php if (empty($blocks)): ?>
                        <div class="empty-state text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No components yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding a component from the sidebar.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($blocks as $block): ?>
                            <?= renderEditableBlock($block, $componentManager) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="add-component-zone mt-6 p-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-center">
                    <p class="text-gray-500 dark:text-gray-400">Drop a component here to add it to your page</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Component Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="modalTitle">Edit Component</h3>
            </div>
            <div class="p-6">
                <form id="componentForm" class="space-y-4">
                    <div id="formFields"></div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" id="cancelEdit" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md text-sm font-medium hover:bg-red-600">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
function renderEditableBlock($block, $componentManager) {
    $content = json_decode($block['content'], true) ?: [];
    $component = $componentManager->getComponent($block['block_type']);
    
    ob_start();
    ?>
    <div class="editable-block border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 relative group" 
         data-block-id="<?= $block['id'] ?>" 
         data-component-type="<?= htmlspecialchars($block['block_type']) ?>">
        
        <div class="block-controls absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
            <div class="flex space-x-1">
                <button class="edit-block p-1 bg-blue-500 text-white rounded hover:bg-blue-600" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </button>
                <button class="drag-handle p-1 bg-gray-500 text-white rounded hover:bg-gray-600 cursor-move" title="Drag">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                </button>
                <button class="delete-block p-1 bg-red-500 text-white rounded hover:bg-red-600" title="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="block-content p-4">
            <?php
            // Render the actual component instead of using ComponentManager
            $componentFile = __DIR__ . '/../components/blocks/' . $block['block_type'] . '.php';
            if (file_exists($componentFile)) {
                // Set up variables for the component
                foreach ($content as $key => $value) {
                    $$key = $value;
                }
                
                // Set block data for component access
                $blockData = $block;
                
                // Set editor flag
                $_GET['editor'] = true;
                
                include $componentFile;
            } else {
                echo '<div class="text-center p-8 text-gray-500">';
                echo '<h3 class="text-lg font-medium">' . htmlspecialchars($component['name'] ?? $block['block_type']) . '</h3>';
                echo '<p class="text-sm">Component file not found</p>';
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="block-label absolute bottom-2 left-2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity">
            <?= htmlspecialchars($component['name'] ?? $block['block_type']) ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<style>
.page-editor {
    height: calc(100vh - 64px);
    display: flex;
    flex-direction: column;
}

.editor-content {
    flex: 1;
    overflow: hidden;
}

.component-sidebar {
    height: 100%;
    overflow-y: auto;
}

.editor-main {
    flex: 1;
    overflow-y: auto;
}

.component-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.editable-block {
    transition: all 0.2s ease;
}

.editable-block:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.editable-block.dragging {
    opacity: 0.5;
}

.add-component-zone.drag-over {
    border-color: #ef4444;
    background-color: rgba(239, 68, 68, 0.05);
}

#component-list.sortable-ghost {
    opacity: 0.5;
}

/* Dark mode adjustments */
.dark .editable-block:hover {
    box-shadow: 0 4px 12px rgba(255,255,255,0.1);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const componentList = document.getElementById('component-list');
    const componentItems = document.querySelectorAll('.component-item');
    const addZone = document.querySelector('.add-component-zone');
    const editModal = document.getElementById('editModal');
    const componentForm = document.getElementById('componentForm');
    const formFields = document.getElementById('formFields');
    const modalTitle = document.getElementById('modalTitle');
    
    let currentEditingBlock = null;
    let currentComponentType = null;
    
    // Make component list sortable
    new Sortable(componentList, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function(evt) {
            const order = Array.from(componentList.children).map(el => el.dataset.blockId);
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    action: 'reorder_components',
                    order: order
                })
            });
        }
    });
    
    // Make components draggable from sidebar
    componentItems.forEach(item => {
        item.draggable = true;
        
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.dataset.componentType);
        });
        
        item.addEventListener('click', function() {
            addComponent(this.dataset.componentType);
        });
    });
    
    // Make add zone droppable
    addZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('drag-over');
    });
    
    addZone.addEventListener('dragleave', function(e) {
        this.classList.remove('drag-over');
    });
    
    addZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        const componentType = e.dataTransfer.getData('text/plain');
        addComponent(componentType);
    });
    
    // Add component function
    function addComponent(componentType) {
        const orderNum = componentList.children.length;
        
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                action: 'add_component',
                component_type: componentType,
                order_num: orderNum
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show new component
            } else {
                alert('Error adding component: ' + (data.error || 'Unknown error'));
            }
        });
    }
    
    // Edit component handlers
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-block')) {
            const block = e.target.closest('.editable-block');
            editComponent(block);
        }
        
        if (e.target.closest('.delete-block')) {
            const block = e.target.closest('.editable-block');
            deleteComponent(block);
        }
    });
    
    function editComponent(blockElement) {
        currentEditingBlock = blockElement;
        currentComponentType = blockElement.dataset.componentType;
        const blockId = blockElement.dataset.blockId;
        
        // Get component definition and current data
        fetch('get-component-data.php?block_id=' + blockId)
            .then(response => response.json())
            .then(data => {
                showEditModal(data.component, data.currentData);
            });
    }
    
    function deleteComponent(blockElement) {
        if (confirm('Are you sure you want to delete this component?')) {
            const blockId = blockElement.dataset.blockId;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    action: 'delete_component',
                    block_id: blockId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    blockElement.remove();
                }
            });
        }
    }
    
    function showEditModal(component, currentData) {
        modalTitle.textContent = 'Edit ' + component.name;
        formFields.innerHTML = '';
        
        // Generate form fields based on component definition
        Object.entries(component.fields).forEach(([fieldName, field]) => {
            const fieldContainer = document.createElement('div');
            fieldContainer.className = 'form-field';
            
            const label = document.createElement('label');
            label.textContent = field.label;
            label.className = 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1';
            
            let input;
            const currentValue = currentData[fieldName] || field.default || '';
            
            switch (field.type) {
                case 'text':
                    input = document.createElement('input');
                    input.type = 'text';
                    input.value = currentValue;
                    break;
                    
                case 'textarea':
                    input = document.createElement('textarea');
                    input.rows = 3;
                    input.value = currentValue;
                    break;
                    
                case 'wysiwyg':
                    input = document.createElement('textarea');
                    input.rows = 6;
                    input.value = currentValue;
                    break;
                    
                case 'select':
                    input = document.createElement('select');
                    field.options.forEach(option => {
                        const optionEl = document.createElement('option');
                        optionEl.value = option;
                        optionEl.textContent = option;
                        optionEl.selected = option === currentValue;
                        input.appendChild(optionEl);
                    });
                    break;
                    
                case 'checkbox':
                    input = document.createElement('input');
                    input.type = 'checkbox';
                    input.checked = !!currentValue;
                    break;
                    
                case 'range':
                    input = document.createElement('input');
                    input.type = 'range';
                    input.min = field.min || 0;
                    input.max = field.max || 100;
                    input.value = currentValue;
                    break;
                    
                default:
                    input = document.createElement('input');
                    input.type = 'text';
                    input.value = currentValue;
            }
            
            input.name = fieldName;
            input.className = 'mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white';
            
            fieldContainer.appendChild(label);
            fieldContainer.appendChild(input);
            formFields.appendChild(fieldContainer);
        });
        
        editModal.classList.remove('hidden');
    }
    
    // Modal handlers
    document.getElementById('cancelEdit').addEventListener('click', function() {
        editModal.classList.add('hidden');
    });
    
    componentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                action: 'update_component',
                block_id: currentEditingBlock.dataset.blockId,
                data: JSON.stringify(data)
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                editModal.classList.add('hidden');
                location.reload(); // Reload to show changes
            }
        });
    });
    
    // Preview button functionality
    document.getElementById('previewBtn').addEventListener('click', function() {
        const previewUrl = window.location.href + '&preview=1';
        window.open(previewUrl, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
    });
});
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
