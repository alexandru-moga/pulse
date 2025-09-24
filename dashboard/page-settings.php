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

// Handle migration from hardcoded effects
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['migrate_effects'])) {
    try {
        $migrationResults = [];
        
        // Define hardcoded effects for each page based on current files
        $hardcodedEffects = [
            'index' => ['mouse', 'globe', 'grid'],
            'members' => ['mouse', 'grid'],
            'apply' => ['mouse', 'net', 'grid'],
            'contact' => ['mouse', 'grid', 'birds'],
            'core/page-template' => ['mouse', 'globe', 'grid'] // For template-based pages
        ];
        
        $pages = $db->query("SELECT * FROM pages")->fetchAll();
        
        foreach ($pages as $p) {
            $pageName = $p['name'];
            $effects = $hardcodedEffects[$pageName] ?? [];
            $effectsJson = json_encode($effects);
            
            $stmt = $db->prepare("UPDATE pages SET effects = ? WHERE id = ?");
            $stmt->execute([$effectsJson, $p['id']]);
            
            $migrationResults[] = "Page '{$p['title']}' migrated with effects: " . implode(', ', $effects ?: ['none']);
        }
        
        $message = 'Migration completed successfully! ' . count($migrationResults) . ' pages updated:<br>' . implode('<br>', $migrationResults);
        $messageType = 'success';
        
        // Refresh current page data if viewing a specific page
        if ($pageId) {
            $stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
            $stmt->execute([$pageId]);
            $page = $stmt->fetch();
        }
    } catch (Exception $e) {
        $message = 'Migration failed: ' . $e->getMessage();
        $messageType = 'error';
    }
}

if ($pageId) {
    $stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$pageId]);
    $page = $stmt->fetch();

    $tableName = isset($page['table_name']) ? trim((string)$page['table_name']) : '';
    if ($page && $tableName !== '') {
        $stmt2 = $db->prepare("SHOW TABLES LIKE ?");
        $stmt2->execute([$tableName]);
        if ($stmt2->fetch()) {
            $tableExists = true;

            // Check if table needs migration
            try {
                $stmt3 = $db->query("DESCRIBE `$tableName`");
                $columns = $stmt3->fetchAll(PDO::FETCH_COLUMN);
                $needsMigration = in_array('block_type', $columns);

                // Determine which ordering column to use
                $orderColumn = 'id'; // Default fallback
                if ($needsMigration && in_array('order_num', $columns)) {
                    $orderColumn = 'order_num';
                } elseif (!$needsMigration && in_array('position', $columns)) {
                    $orderColumn = 'position';
                }

                $blocks = $db->query("SELECT * FROM `$tableName` ORDER BY `$orderColumn` ASC")->fetchAll();
            } catch (Exception $e) {
                // If there's any error, just get all blocks without ordering
                $blocks = $db->query("SELECT * FROM `$tableName`")->fetchAll();
            }
        }
    }
}
?>

<div class="space-y-6">
    <?php if ($message): ?>
        <div class="<?= $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/50 border-green-200 dark:border-green-700 text-green-700 dark:text-green-300' : 'bg-red-50 dark:bg-red-900/50 border-red-200 dark:border-red-700 text-red-700 dark:text-red-300' ?> border rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 <?= $messageType === 'success' ? 'text-green-400' : 'text-red-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?php if ($messageType === 'success'): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    <?php else: ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    <?php endif; ?>
                </svg>
                <div class="ml-3">
                    <p class="text-sm"><?= $message ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <?php if ($pageId && $page): ?>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($page['title']) ?> Page Settings</h2>
                    <p class="text-gray-600 dark:text-gray-300 mt-1">Manage content and structure for this page</p>
                <?php else: ?>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Page Management</h2>
                    <p class="text-gray-600 dark:text-gray-300 mt-1">Manage your website pages and their content</p>
                <?php endif; ?>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/settings.php"
                class="text-primary hover:text-red-600 text-sm font-medium">
                ← Back to Settings
            </a>
        </div>
    </div>

    <!-- Global Effects Migration -->
    <?php if (!$pageId): ?>
        <div class="bg-blue-50 dark:bg-blue-900/50 border border-blue-200 dark:border-blue-700 rounded-md p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-blue-700 dark:text-blue-200">Effects Migration Available</h3>
                    <p class="text-sm text-blue-600 dark:text-blue-300 mt-1">
                        Migrate all hardcoded effects from page files to the database. This will move mouse, grid, globe, birds, and net effects to database control.
                    </p>
                    <div class="mt-3">
                        <form method="post" class="inline">
                            <button type="submit" name="migrate_effects" onclick="return confirm('This will migrate effects from all page files to the database. Continue?')"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-800 dark:text-blue-200 dark:hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Migrate All Effects
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Page Effects Management -->
    <?php if ($pageId && $page): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4 mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Page Effects</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Control visual effects for this page</p>
            </div>
            
            <form method="post" class="space-y-4">
                <input type="hidden" name="update_effects" value="1">
                
                <?php
                $currentEffects = [];
                if (!empty($page['effects'])) {
                    $currentEffects = json_decode($page['effects'], true) ?: [];
                }
                ?>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <?php foreach ($availableEffects as $effect): ?>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="effects[]" 
                                   value="<?= $effect ?>"
                                   id="effect_<?= $effect ?>"
                                   <?= in_array($effect, $currentEffects) ? 'checked' : '' ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="effect_<?= $effect ?>" class="ml-2 text-sm text-gray-900 dark:text-white capitalize">
                                <?= $effect ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="flex items-center justify-between pt-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Current effects: <?= empty($currentEffects) ? 'None' : implode(', ', $currentEffects) ?>
                    </div>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Effects
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($pageId && $page): ?>
        <?php if ($tableName === ''): ?>
            <div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-700 rounded-md p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 dark:text-red-300">No table name set for this page.</p>
                    </div>
                </div>
            </div>
        <?php elseif (!$tableExists): ?>
            <div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-700 rounded-md p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 dark:text-red-300">The table <strong><?= htmlspecialchars($tableName) ?></strong> does not exist in the database.</p>
                    </div>
                </div>
            </div>
        <?php elseif ($needsMigration): ?>
            <div class="bg-orange-50 dark:bg-orange-900/50 border border-orange-200 dark:border-orange-700 rounded-md p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-orange-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-orange-700 dark:text-orange-200">Migration Required</h3>
                        <p class="text-sm text-orange-600 dark:text-orange-300 mt-1">
                            This page uses the old builder format. Please migrate to use the new drag-and-drop builder.
                        </p>
                        <div class="mt-3">
                            <a href="<?= $settings['site_url'] ?>/dashboard/migrate-builder.php"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-orange-700 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 dark:bg-orange-800 dark:text-orange-200 dark:hover:bg-orange-700">
                                Run Migration
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Visual Editor Call-to-Action -->
            <div class="bg-gradient-to-r from-primary to-red-600 rounded-lg shadow p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium">Visual Page Editor</h3>
                        <p class="mt-1 opacity-90">Use our drag-and-drop editor to build your page with pre-designed components</p>
                    </div>
                    <a href="<?= $settings['site_url'] ?>/dashboard/page-builder.php?id=<?= $pageId ?>"
                        class="inline-flex items-center px-4 py-2 border border-white rounded-md text-sm font-medium text-primary bg-white hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Drag & Drop Builder
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Page Components</h3>
                    <div class="flex space-x-3">
                        <a href="<?= $settings['site_url'] ?>/dashboard/page-builder.php?id=<?= $pageId ?>"
                            class="inline-flex items-center px-3 py-2 border border-primary text-primary rounded-md text-sm font-medium hover:bg-primary hover:text-white transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                            Drag & Drop Builder
                        </a>
                    </div>
                </div>

                <?php if (empty($blocks)): ?>
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No components</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by using the visual editor or adding a block.</p>
                        <div class="mt-6">
                            <a href="<?= $settings['site_url'] ?>/dashboard/page-builder.php?id=<?= $pageId ?>"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                Start with Drag & Drop Builder
                            </a>
                        </div>
                    </div>
                <?php else: ?>
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($blocks as $block):
                                        // Support both old and new structures
                                        $componentType = $block['component_type'] ?? $block['block_type'] ?? 'Unknown';
                                        $settings = $block['settings'] ?? $block['content'] ?? '';
                                        $position = $block['position'] ?? $block['order_num'] ?? 0;
                                    ?>
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($block['id'] ?? '') ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($componentType) ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                <div class="max-w-xs">
                                                    <pre class="text-xs whitespace-pre-wrap truncate"><?= htmlspecialchars(mb_strimwidth($settings, 0, 120, '...')) ?></pre>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($position) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                <?php
                                                $statusColor = ($block['is_active'] ?? 0) ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                                $statusText = ($block['is_active'] ?? 0) ? 'Active' : 'Inactive';
                                                ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>">
                                                    <?= $statusText ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex items-center space-x-2">
                                                    <a href="<?= $settings['site_url'] ?>/dashboard/page-builder.php?id=<?= $pageId ?>"
                                                        class="text-primary hover:text-red-600">
                                                        Edit in Builder
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Available Pages</h3>
                <a href="<?= $settings['site_url'] ?>/dashboard/create-page.php"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create New Page
                </a>
            </div>

            <?php
            $pages = $db->query("SELECT * FROM pages ORDER BY id ASC")->fetchAll();
            $filteredPages = array_filter($pages, function ($pg) {
                return !empty(trim((string)$pg['table_name']));
            });
            ?>

            <?php if (empty($filteredPages)): ?>
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No pages</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new page.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($filteredPages as $pg): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($pg['name'] ?? '') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($pg['title'] ?? '') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($pg['table_name'] ?? '') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="<?= $settings['site_url'] ?>/dashboard/page-settings.php?id=<?= $pg['id'] ?>"
                                                class="text-primary hover:text-red-600">
                                                Edit Elements
                                            </a>
                                            <a href="<?= $settings['site_url'] ?>/dashboard/delete-page.php?id=<?= $pg['id'] ?>"
                                                onclick="return confirm('Are you sure you want to delete this page, its PHP file, and its table?')"
                                                class="text-red-600 hover:text-red-800">
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>