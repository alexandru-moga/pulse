<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Migrate to New Builder';
include __DIR__ . '/components/dashboard-header.php';

$migrationStatus = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['run_migration'])) {
        try {
            // Get all page tables
            $stmt = $db->query("SHOW TABLES LIKE 'page_%'");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $migratedTables = [];
            
            foreach ($tables as $table) {
                // Check if table has old structure
                $stmt = $db->query("DESCRIBE `$table`");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (in_array('block_type', $columns) && in_array('block_name', $columns)) {
                    // This is an old structure table, migrate it
                    
                    // Step 1: Add new columns if they don't exist
                    if (!in_array('component_type', $columns)) {
                        $db->query("ALTER TABLE `$table` ADD COLUMN `component_type` varchar(50) DEFAULT NULL AFTER `block_type`");
                    }
                    if (!in_array('settings', $columns)) {
                        $db->query("ALTER TABLE `$table` ADD COLUMN `settings` text DEFAULT NULL AFTER `content`");
                    }
                    if (!in_array('position', $columns)) {
                        $db->query("ALTER TABLE `$table` ADD COLUMN `position` int(11) DEFAULT 0 AFTER `order_num`");
                    }
                    
                    // Step 2: Copy data and transform component types
                    $stmt = $db->prepare("UPDATE `$table` SET 
                        `component_type` = CASE 
                            WHEN `block_type` = 'title-3' THEN 'heading'
                            WHEN `block_type` = 'heading-3' THEN 'heading' 
                            WHEN `block_type` = 'contact-form' THEN 'contact_form'
                            WHEN `block_type` = 'apply-form' THEN 'apply_form'
                            WHEN `block_type` = 'members-grid' THEN 'members_grid'
                            ELSE `block_type`
                        END,
                        `settings` = `content`,
                        `position` = `order_num`");
                    $stmt->execute();
                    
                    // Step 3: Drop old columns
                    if (in_array('block_name', $columns)) {
                        $db->query("ALTER TABLE `$table` DROP COLUMN `block_name`");
                    }
                    if (in_array('block_type', $columns)) {
                        $db->query("ALTER TABLE `$table` DROP COLUMN `block_type`");
                    }
                    if (in_array('content', $columns)) {
                        $db->query("ALTER TABLE `$table` DROP COLUMN `content`");
                    }
                    if (in_array('order_num', $columns)) {
                        $db->query("ALTER TABLE `$table` DROP COLUMN `order_num`");
                    }
                    
                    $migratedTables[] = $table;
                }
            }
            
            if (empty($migratedTables)) {
                $migrationStatus = 'No tables needed migration. All tables are already using the new structure.';
            } else {
                $migrationStatus = 'Successfully migrated ' . count($migratedTables) . ' tables: ' . implode(', ', $migratedTables);
            }
            
        } catch (Exception $e) {
            $error = 'Migration failed: ' . $e->getMessage();
        }
    }
}

// Check current status
$needsMigration = [];
$alreadyMigrated = [];

try {
    $stmt = $db->query("SHOW TABLES LIKE 'page_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $stmt = $db->query("DESCRIBE `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('block_type', $columns)) {
            $needsMigration[] = $table;
        } else {
            $alreadyMigrated[] = $table;
        }
    }
} catch (Exception $e) {
    $error = 'Failed to check migration status: ' . $e->getMessage();
}
?>

<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Migrate to New Drag & Drop Builder</h2>
                <p class="text-gray-600 dark:text-gray-300 mt-1">Convert existing page builder content to work with the new drag-and-drop interface</p>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/page-settings.php" 
               class="text-primary hover:text-red-600 text-sm font-medium">
                ← Back to Page Settings
            </a>
        </div>
        
        <?php if (!empty($migrationStatus)): ?>
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 dark:text-green-200"><?= htmlspecialchars($migrationStatus) ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-md">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 001.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 dark:text-red-200"><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Tables that need migration -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 dark:text-white mb-3">
                    <svg class="w-5 h-5 inline-block text-orange-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    Needs Migration (<?= count($needsMigration) ?>)
                </h3>
                <?php if (empty($needsMigration)): ?>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">All tables have been migrated!</p>
                <?php else: ?>
                    <ul class="space-y-1">
                        <?php foreach ($needsMigration as $table): ?>
                            <li class="text-sm text-gray-700 dark:text-gray-300">• <?= htmlspecialchars($table) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <!-- Tables already migrated -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h3 class="font-medium text-gray-900 dark:text-white mb-3">
                    <svg class="w-5 h-5 inline-block text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Already Migrated (<?= count($alreadyMigrated) ?>)
                </h3>
                <?php if (empty($alreadyMigrated)): ?>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">No tables migrated yet.</p>
                <?php else: ?>
                    <ul class="space-y-1">
                        <?php foreach ($alreadyMigrated as $table): ?>
                            <li class="text-sm text-gray-700 dark:text-gray-300">• <?= htmlspecialchars($table) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($needsMigration)): ?>
            <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md">
                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">What this migration will do:</h4>
                <ul class="text-sm text-yellow-700 dark:text-yellow-300 list-disc list-inside space-y-1">
                    <li>Add new columns: <code>component_type</code>, <code>settings</code>, <code>position</code></li>
                    <li>Convert old component types: <code>title-3</code> → <code>heading</code>, <code>heading-3</code> → <code>heading</code></li>
                    <li>Copy <code>content</code> to <code>settings</code> and <code>order_num</code> to <code>position</code></li>
                    <li>Remove old columns: <code>block_name</code>, <code>block_type</code>, <code>content</code>, <code>order_num</code></li>
                </ul>
            </div>
            
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <strong>Warning:</strong> This will modify your database structure. Make sure you have a backup!
                </div>
                <form method="POST" onsubmit="return confirm('Are you sure you want to run the migration? Make sure you have a database backup first!')">
                    <button type="submit" name="run_migration" 
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm transition duration-200">
                        Run Migration
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="mt-6 text-center">
                <div class="text-green-600 dark:text-green-400 text-sm">
                    ✅ All page tables have been migrated to the new builder format!
                </div>
                <div class="mt-4">
                    <a href="<?= $settings['site_url'] ?>/dashboard/page-settings.php" 
                       class="bg-primary hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-md shadow-sm transition duration-200">
                        Go to Page Settings
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
