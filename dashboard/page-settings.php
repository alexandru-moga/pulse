<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Page Settings';
include __DIR__ . '/components/dashboard-header.php';

$pageId = isset($_GET['id']) ? intval($_GET['id']) : null;
$page = null;
$tableName = null;
$blocks = [];
$tableExists = false;
$needsMigration = false;
$message = '';
$messageType = '';

// Available effects
$availableEffects = ['mouse', 'grid', 'globe', 'birds', 'net'];

// Handle form submission for effects
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_effects']) && $pageId) {
    try {
        $selectedEffects = $_POST['effects'] ?? [];
        $effectsJson = json_encode(array_values($selectedEffects));

        $stmt = $db->prepare("UPDATE pages SET effects = ? WHERE id = ?");
        $stmt->execute([$effectsJson, $pageId]);

        $message = 'Effects updated successfully!';
        $messageType = 'success';

        // Refresh page data
        $stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
        $stmt->execute([$pageId]);
        $page = $stmt->fetch();
    } catch (Exception $e) {
        $message = 'Error updating effects: ' . $e->getMessage();
        $messageType = 'error';
    }
}

if ($pageId) {
    // Get page information
    $stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$pageId]);
    $page = $stmt->fetch();

    if ($page) {
        $tableName = $page['table_name'];

        // Check if page table exists
        try {
            $stmt = $db->query("SHOW TABLES LIKE '$tableName'");
            $tableExists = $stmt->rowCount() > 0;

            if ($tableExists) {
                // Check if table uses old structure
                $stmt = $db->query("DESCRIBE $tableName");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $usesOldStructure = in_array('block_type', $columns);

                // Check if migration is needed
                if ($usesOldStructure) {
                    $needsMigration = true;
                } else {
                    // Get components for new structure
                    $stmt = $db->query("SELECT * FROM $tableName WHERE is_active = 1 ORDER BY position ASC");
                    $blocks = $stmt->fetchAll();
                }
            }
        } catch (Exception $e) {
            error_log("Error checking page table: " . $e->getMessage());
        }
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <!-- Success/Error Messages -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 border border-red-400 text-red-700 dark:bg-red-800 dark:text-red-100' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Page Settings</h1>
                <?php if ($page): ?>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Editing: <span class="font-semibold"><?= htmlspecialchars($page['title']) ?></span>
                    </p>
                <?php endif; ?>
            </div>

            <?php if ($page): ?>
                <div class="flex space-x-3">
                    <a href="<?= $settings['site_url'] ?>/<?= $page['name'] ?>.php"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Preview Page
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$page): ?>
        <!-- Page Selector -->
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-4 mb-2">Select a Page to Edit</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-8">Choose which page you want to manage settings for</p>
            </div>

            <?php
            // Get all available pages
            $stmt = $db->query("SELECT * FROM pages ORDER BY id ASC");
            $pages = $stmt->fetchAll();
            ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($pages as $pageOption): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <?php
                                    // Choose icon based on page name
                                    $icon = match ($pageOption['name']) {
                                        'index' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>',
                                        'members' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>',
                                        'apply' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
                                        'contact' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>',
                                        default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>'
                                    };
                                    ?>
                                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?= $icon ?>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($pageOption['title']) ?>
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <?= htmlspecialchars($pageOption['name']) ?>
                                    </p>
                                </div>
                            </div>

                            <?php if ($pageOption['description']): ?>
                                <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 line-clamp-2">
                                    <?= htmlspecialchars($pageOption['description']) ?>
                                </p>
                            <?php endif; ?>

                            <div class="flex space-x-3">
                                <a href="<?= $settings['site_url'] ?>/dashboard/page-settings.php?id=<?= $pageOption['id'] ?>"
                                    class="flex-1 text-center px-4 py-2 bg-primary text-white rounded-md text-sm font-medium hover:bg-red-700 transition-colors">
                                    Manage Settings
                                </a>
                                <a href="<?= $settings['site_url'] ?>/dashboard/page-builder.php?id=<?= $pageOption['id'] ?>"
                                    class="flex-1 text-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    Edit Page
                                </a>
                            </div>

                            <div class="mt-3 flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                                <span>ID: <?= $pageOption['id'] ?></span>
                                <span class="flex items-center">
                                    <span class="w-2 h-2 rounded-full <?= $pageOption['menu_enabled'] ? 'bg-green-400' : 'bg-gray-400' ?> mr-1"></span>
                                    <?= $pageOption['menu_enabled'] ? 'Menu Enabled' : 'Menu Disabled' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-8">
                <a href="<?= $settings['site_url'] ?>/dashboard/"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Page Found -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Page Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Page Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Page Name</dt>
                            <dd class="text-sm text-gray-900 dark:text-white mt-1"><?= htmlspecialchars($page['name']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Page Title</dt>
                            <dd class="text-sm text-gray-900 dark:text-white mt-1"><?= htmlspecialchars($page['title']) ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Table Name</dt>
                            <dd class="text-sm text-gray-900 dark:text-white mt-1"><?= htmlspecialchars($tableName ?: 'Not Set') ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Menu Enabled</dt>
                            <dd class="text-sm text-gray-900 dark:text-white mt-1"><?= $page['menu_enabled'] ? 'Yes' : 'No' ?></dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                            <dd class="text-sm text-gray-900 dark:text-white mt-1"><?= htmlspecialchars($page['description'] ?: 'No description') ?></dd>
                        </div>
                    </dl>
                </div>

                <!-- Page Builder Access -->
                <div class="bg-gradient-to-r from-primary to-red-600 rounded-lg shadow p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-8 h-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <div>
                                <h3 class="text-lg font-medium">Visual Page Editor</h3>
                                <p class="mt-1 opacity-90">Use our drag-and-drop editor to build your page with pre-designed components</p>
                            </div>
                        </div>
                        <a href="<?= $settings['site_url'] ?>/dashboard/page-builder.php?id=<?= $pageId ?>"
                            class="inline-flex items-center px-4 py-2 border border-white rounded-md text-sm font-medium text-primary bg-white hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Open Page Builder
                        </a>
                    </div>
                </div>

                <?php if ($needsMigration): ?>
                    <!-- Migration Warning -->
                    <div class="bg-orange-50 dark:bg-orange-900 border-l-4 border-orange-400 p-4 rounded-lg">
                        <div class="flex">
                            <svg class="h-5 w-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-orange-700 dark:text-orange-200">Migration Required</h3>
                                <p class="text-sm text-orange-600 dark:text-orange-300 mt-1">
                                    This page uses the old builder format. Please migrate to use the new drag-and-drop builder.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Components Table -->
                <?php if (!empty($blocks) && !$needsMigration): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Page Components</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Current components on this page. Use the Page Builder for full editing capabilities.
                            </p>
                        </div>
                        <div class="overflow-hidden">
                            <div class="overflow-x-auto max-h-96">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Component Type</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Settings Preview</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Position</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <?php foreach ($blocks as $block): ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                    <?= $block['id'] ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                                        <?= htmlspecialchars($block['component_type']) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs">
                                                    <div class="truncate">
                                                        <?= htmlspecialchars(substr($block['settings'] ?? '', 0, 100)) ?>
                                                        <?= strlen($block['settings'] ?? '') > 100 ? '...' : '' ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                    <?= $block['position'] ?? 'N/A' ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $block['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100' ?>">
                                                        <?= $block['is_active'] ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="<?= $settings['site_url'] ?>/dashboard/page-builder.php?id=<?= $pageId ?>"
                            class="block w-full text-center px-4 py-2 bg-primary text-white rounded-md text-sm font-medium hover:bg-red-700 transition-colors">
                            Edit with Page Builder
                        </a>
                        <a href="<?= $settings['site_url'] ?>/<?= $page['name'] ?>.php"
                            target="_blank"
                            class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Preview Page
                        </a>
                    </div>
                </div>

                <!-- Page Effects -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Page Effects</h3>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="update_effects" value="1">

                        <div class="space-y-3">
                            <?php
                            $currentEffects = json_decode($page['effects'] ?? '[]', true) ?: [];
                            foreach ($availableEffects as $effect):
                            ?>
                                <label class="flex items-center">
                                    <input type="checkbox"
                                        name="effects[]"
                                        value="<?= $effect ?>"
                                        <?= in_array($effect, $currentEffects) ? 'checked' : '' ?>
                                        class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300 capitalize"><?= $effect ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit"
                            class="w-full px-4 py-2 bg-primary text-white rounded-md text-sm font-medium hover:bg-red-700 transition-colors">
                            Update Effects
                        </button>
                    </form>
                </div>

                <!-- Page Statistics -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Page Stats</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Components</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white"><?= count($blocks) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Table Exists</span>
                            <span class="text-sm font-medium <?= $tableExists ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $tableExists ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Menu Enabled</span>
                            <span class="text-sm font-medium <?= $page['menu_enabled'] ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $page['menu_enabled'] ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>