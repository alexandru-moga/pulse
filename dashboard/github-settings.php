<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'GitHub Settings';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $id => $value) {
        $stmt = $db->prepare("UPDATE settings SET value=? WHERE id=?");
        $stmt->execute([$value, $id]);
    }
    $success = "GitHub settings updated successfully!";
}

$github_settings = $db->query("SELECT * FROM settings WHERE name LIKE 'github_%' ORDER BY id ASC")->fetchAll();
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">GitHub Settings</h2>
                <p class="text-gray-600 mt-1">Configure GitHub OAuth and repository integration</p>
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
            <h3 class="text-lg font-medium text-gray-900">GitHub OAuth Configuration</h3>
            <p class="text-sm text-gray-500 mt-1">Configure your GitHub OAuth app settings</p>
        </div>
        
        <form method="post" class="p-6 space-y-6">
            <?php foreach ($github_settings as $setting): ?>
                <div>
                    <label for="setting-<?= $setting['id'] ?>" class="block text-sm font-medium text-gray-700 mb-1">
                        <?= htmlspecialchars(str_replace('github_', '', ucwords(str_replace('_', ' ', $setting['name'])))) ?>
                    </label>
                    <?php if (strpos($setting['name'], 'secret') !== false): ?>
                        <input type="password" 
                               name="settings[<?= $setting['id'] ?>]" 
                               id="setting-<?= $setting['id'] ?>"
                               value="<?= htmlspecialchars($setting['value']) ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <?php else: ?>
                        <input type="text" 
                               name="settings[<?= $setting['id'] ?>]" 
                               id="setting-<?= $setting['id'] ?>"
                               value="<?= htmlspecialchars($setting['value']) ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <?php endif; ?>
                    
                    <?php if ($setting['name'] === 'github_client_id'): ?>
                        <p class="mt-1 text-sm text-gray-500">Your GitHub OAuth app's Client ID</p>
                    <?php elseif ($setting['name'] === 'github_client_secret'): ?>
                        <p class="mt-1 text-sm text-gray-500">Your GitHub OAuth app's Client Secret</p>
                    <?php elseif ($setting['name'] === 'github_redirect_uri'): ?>
                        <p class="mt-1 text-sm text-gray-500">OAuth redirect URI: <?= $settings['site_url'] ?>/auth/github/</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save GitHub Settings
                </button>
            </div>
        </form>
    </div>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-medium text-blue-900 mb-4">Setup Instructions</h3>
        <div class="text-sm text-blue-800 space-y-2">
            <p><strong>1.</strong> Go to <a href="https://github.com/settings/applications/new" target="_blank" class="underline">GitHub Developer Settings</a></p>
            <p><strong>2.</strong> Create a new OAuth app with these settings:</p>
            <ul class="list-disc list-inside ml-4 space-y-1">
                <li><strong>Application name:</strong> Your site name</li>
                <li><strong>Homepage URL:</strong> <?= htmlspecialchars($settings['site_url']) ?></li>
                <li><strong>Authorization callback URL:</strong> <?= htmlspecialchars($settings['site_url']) ?>/auth/github/</li>
            </ul>
            <p><strong>3.</strong> Copy the Client ID and Client Secret to the form above</p>
            <p><strong>4.</strong> Save the settings to enable GitHub integration</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>