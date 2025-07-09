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
            $blocks = $db->query("SELECT * FROM `$tableName` ORDER BY order_num ASC")->fetchAll();
        }
    }
}
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <?php if ($pageId && $page): ?>
                    <h2 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($page['title']) ?> Page Elements</h2>
                    <p class="text-gray-600 mt-1">Manage blocks and content for this page</p>
                <?php else: ?>
                    <h2 class="text-xl font-semibold text-gray-900">Page Management</h2>
                    <p class="text-gray-600 mt-1">Manage your website pages and their content</p>
                <?php endif; ?>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/settings.php" 
               class="text-primary hover:text-red-600 text-sm font-medium">
                ← Back to Settings
            </a>
        </div>
    </div>

    <?php if ($pageId && $page): ?>
        <!-- Single Page View -->
        <?php if ($tableName === ''): ?>
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">No table name set for this page.</p>
                    </div>
                </div>
            </div>
        <?php elseif (!$tableExists): ?>
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">The table <strong><?= htmlspecialchars($tableName) ?></strong> does not exist in the database.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Page Blocks Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Page Blocks</h3>
                    <a href="<?= $settings['site_url'] ?>/dashboard/add-block.php?id=<?= $pageId ?>" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add New Block
                    </a>
                </div>
                
                <?php if (empty($blocks)): ?>
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No blocks</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding a new block to this page.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Block Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content Preview</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($blocks as $block): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($block['id'] ?? '') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($block['block_name'] ?? '') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($block['block_type'] ?? '') ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="max-w-xs">
                                                <pre class="text-xs whitespace-pre-wrap truncate"><?= htmlspecialchars(mb_strimwidth($block['content'] ?? '', 0, 120, '...')) ?></pre>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($block['order_num'] ?? '') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php
                                            $statusColor = ($block['is_active'] ?? 0) ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                            $statusText = ($block['is_active'] ?? 0) ? 'Active' : 'Inactive';
                                            ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>">
                                                <?= $statusText ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                <a href="<?= $settings['site_url'] ?>/dashboard/edit-block.php?id=<?= $pageId ?>&block_id=<?= $block['id'] ?>" 
                                                   class="text-primary hover:text-red-600">
                                                    Edit
                                                </a>
                                                <a href="<?= $settings['site_url'] ?>/dashboard/delete-block.php?id=<?= $pageId ?>&block_id=<?= $block['id'] ?>" 
                                                   onclick="return confirm('Delete this block?')"
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
    <?php else: ?>
        <!-- All Pages View -->
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
            $filteredPages = array_filter($pages, function($pg) {
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
