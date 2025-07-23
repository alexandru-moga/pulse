<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageId = isset($_GET['id']) ? intval($_GET['id']) : null;
$blockId = isset($_GET['block_id']) ? intval($_GET['block_id']) : null;

if (!$pageId || !$blockId) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Invalid page or block ID.'];
    header('Location: page-settings.php');
    exit;
}

$page = $db->prepare("SELECT * FROM pages WHERE id = ?");
$page->execute([$pageId]);
$page = $page->fetch();

if (!$page || empty($page['table_name'])) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Invalid page or table name.'];
    header('Location: page-settings.php');
    exit;
}

$tableName = $page['table_name'];
$stmt = $db->prepare("SELECT * FROM `$tableName` WHERE id = ?");
$stmt->execute([$blockId]);
$block = $stmt->fetch();

if (!$block) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Block not found.'];
    header("Location: page-settings.php?id=" . $pageId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delete = $db->prepare("DELETE FROM `$tableName` WHERE id = ?");
    $delete->execute([$blockId]);
    $_SESSION['notification'] = ['type' => 'success', 'message' => 'Block deleted successfully.'];
    header("Location: page-settings.php?id=" . $pageId);
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    $block_name = trim($_POST['block_name'] ?? '');
    $block_type = trim($_POST['block_type'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $order_num = intval($_POST['order_num'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (!$block_name) $errors[] = 'Block name is required.';
    if (!$block_type) $errors[] = 'Block type is required.';
    
    if (empty($errors)) {
        $update = $db->prepare("UPDATE `$tableName` SET block_name = ?, block_type = ?, content = ?, order_num = ?, is_active = ? WHERE id = ?");
        $update->execute([$block_name, $block_type, $content, $order_num, $is_active, $blockId]);
        $_SESSION['notification'] = ['type' => 'success', 'message' => 'Block updated successfully.'];
        header("Location: page-settings.php?id=" . $pageId);
        exit;
    }
}

$pageTitle = 'Edit Block';
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Edit Block</h2>
                <p class="text-gray-600 mt-1">
                    Editing "<?= htmlspecialchars($block['block_name']) ?>" from "<?= htmlspecialchars($page['name']) ?>"
                </p>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/page-settings.php?id=<?= $pageId ?>" 
               class="text-primary hover:text-red-600 text-sm font-medium">
                ← Back to Page Settings
            </a>
        </div>
    </div>
    <?php if ($errors): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Block Details</h3>
        </div>
        
        <form method="post" class="p-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="block_name" class="block text-sm font-medium text-gray-700">Block Name</label>
                    <input type="text" 
                           id="block_name" 
                           name="block_name" 
                           required 
                           value="<?= htmlspecialchars($block['block_name']) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                           placeholder="Enter a descriptive name for this block">
                </div>

                <div>
                    <label for="block_type" class="block text-sm font-medium text-gray-700">Block Type</label>
                    <input type="text" 
                           id="block_type" 
                           name="block_type" 
                           required 
                           value="<?= htmlspecialchars($block['block_type']) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                           placeholder="e.g., heading, text, stats, etc.">
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                    <textarea id="content" 
                              name="content" 
                              rows="6"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                              placeholder="Enter the block content (JSON or plain text)"><?= htmlspecialchars($block['content']) ?></textarea>
                    <p class="mt-1 text-sm text-gray-500">Can be JSON data or plain text, depending on block type.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="order_num" class="block text-sm font-medium text-gray-700">Order Number</label>
                        <input type="number" 
                               id="order_num" 
                               name="order_num" 
                               required 
                               value="<?= htmlspecialchars($block['order_num']) ?>"
                               min="0"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                        <p class="mt-1 text-sm text-gray-500">Lower numbers appear first on the page.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1" 
                                       <?= $block['is_active'] ? 'checked' : '' ?>
                                       class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-900">Active (visible on page)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <button type="button" 
                        onclick="confirmDelete()"
                        class="px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Delete Block
                </button>
                
                <div class="flex space-x-4">
                    <a href="<?= $settings['site_url'] ?>/dashboard/page-settings.php?id=<?= $pageId ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
        <form id="deleteForm" method="post" style="display: none;">
            <input type="hidden" name="action" value="delete">
        </form>
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this block? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
