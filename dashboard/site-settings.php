<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Site Settings';
include __DIR__ . '/components/dashboard-header.php';

$settingNames = [
    'SITE_TITLE',
    'site_url',
    'maintenance_mode'
];

$success = $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $id => $value) {
        $stmt = $db->prepare("UPDATE settings SET value=? WHERE id=?");
        $stmt->execute([$value, $id]);
    }
    $success = "Settings updated successfully!";
}

$inSql = implode(',', array_fill(0, count($settingNames), '?'));
$stmt = $db->prepare("SELECT * FROM settings WHERE name IN ($inSql) ORDER BY id ASC");
$stmt->execute($settingNames);
$site_settings = $stmt->fetchAll();
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Site Settings</h2>
                <p class="text-gray-600 mt-1">Configure your website's basic settings</p>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/settings.php" 
               class="text-primary hover:text-red-600 text-sm font-medium">
                ← Back to Settings
            </a>
        </div>
    </div>
    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Website Configuration</h3>
        </div>
        
        <form method="post" class="p-6 space-y-6">
            <?php foreach ($site_settings as $setting): ?>
                <div>
                    <label for="setting-<?= $setting['id'] ?>" class="block text-sm font-medium text-gray-700 mb-1">
                        <?= htmlspecialchars($setting['name']) ?>
                    </label>
                    <?php if ($setting['name'] === 'maintenance_mode'): ?>
                        <select name="settings[<?= $setting['id'] ?>]" id="setting-<?= $setting['id'] ?>"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                            <option value="0" <?= $setting['value'] == '0' ? 'selected' : '' ?>>Disabled</option>
                            <option value="1" <?= $setting['value'] == '1' ? 'selected' : '' ?>>Enabled</option>
                        </select>
                    <?php else: ?>
                        <input type="text" 
                               name="settings[<?= $setting['id'] ?>]" 
                               id="setting-<?= $setting['id'] ?>"
                               value="<?= htmlspecialchars($setting['value']) ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <?php endif; ?>
                    
                    <?php if ($setting['name'] === 'site_url'): ?>
                        <p class="mt-1 text-sm text-gray-500">The base URL of your website (without trailing slash)</p>
                    <?php elseif ($setting['name'] === 'SITE_TITLE'): ?>
                        <p class="mt-1 text-sm text-gray-500">The title that appears in browser tabs and search results</p>
                    <?php elseif ($setting['name'] === 'maintenance_mode'): ?>
                        <p class="mt-1 text-sm text-gray-500">When enabled, only administrators can access the website</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>