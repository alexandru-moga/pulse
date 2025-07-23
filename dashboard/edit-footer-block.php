<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$blockId = intval($_GET['id'] ?? 0);
if (!$blockId) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'No block specified.'];
    header('Location: footer-settings.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM footer WHERE id = ?");
$stmt->execute([$blockId]);
$block = $stmt->fetch();

if (!$block) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Block not found.'];
    header('Location: footer-settings.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delete = $db->prepare("DELETE FROM footer WHERE id = ?");
    $delete->execute([$blockId]);
    $_SESSION['notification'] = ['type' => 'success', 'message' => 'Footer block deleted successfully.'];
    header('Location: footer-settings.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'delete')) {
    $section_type = trim($_POST['section_type'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $order_num = intval($_POST['order_num'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$section_type) $errors[] = 'Section type is required.';
    if (!$content) $errors[] = 'Content is required.';

    if (empty($errors)) {
        $update = $db->prepare("UPDATE footer SET section_type = ?, content = ?, order_num = ?, is_active = ? WHERE id = ?");
        $update->execute([$section_type, $content, $order_num, $is_active, $blockId]);
        $_SESSION['notification'] = ['type' => 'success', 'message' => 'Footer block updated successfully.'];
        header('Location: footer-settings.php');
        exit;
    }
}

$pageTitle = 'Edit Footer Block';
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Edit Footer Block</h2>
                <p class="text-gray-600 mt-1">
                    Editing footer section "<?= htmlspecialchars($block['section_type']) ?>"
                </p>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/footer-settings.php" 
               class="text-primary hover:text-red-600 text-sm font-medium">
                ← Back to Footer Settings
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
            <h3 class="text-lg font-medium text-gray-900">Footer Block Details</h3>
        </div>
        
        <form method="post" class="p-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="section_type" class="block text-sm font-medium text-gray-700">Section Type</label>
                    <input type="text" 
                           id="section_type" 
                           name="section_type" 
                           required 
                           value="<?= htmlspecialchars($_POST['section_type'] ?? $block['section_type']) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                           placeholder="e.g., logo, links, cta, credits">
                    <p class="mt-1 text-sm text-gray-500">Examples: logo, links, cta, credits, social, contact</p>
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                    <textarea id="content" 
                              name="content" 
                              rows="6"
                              required
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                              placeholder="Enter the footer section content (JSON or plain text)"><?= htmlspecialchars($_POST['content'] ?? $block['content']) ?></textarea>
                    <p class="mt-1 text-sm text-gray-500">Can be JSON data or plain text, depending on section type.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="order_num" class="block text-sm font-medium text-gray-700">Order Number</label>
                        <input type="number" 
                               id="order_num" 
                               name="order_num" 
                               required 
                               value="<?= htmlspecialchars($_POST['order_num'] ?? $block['order_num']) ?>"
                               min="0"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                        <p class="mt-1 text-sm text-gray-500">Lower numbers appear first in the footer.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1" 
                                       <?= (isset($_POST['is_active']) ? $_POST['is_active'] : $block['is_active']) ? 'checked' : '' ?>
                                       class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-900">Active (visible in footer)</span>
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
                    <a href="<?= $settings['site_url'] ?>/dashboard/footer-settings.php" 
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
    if (confirm('Are you sure you want to delete this footer block? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>