<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Google Settings';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['settings'])) {
        foreach ($_POST['settings'] as $id => $value) {
            $stmt = $db->prepare("UPDATE settings SET value=? WHERE id=?");
            $stmt->execute([$value, $id]);
        }
    }
    if (isset($_POST['new_settings'])) {
        foreach ($_POST['new_settings'] as $name => $value) {
            $stmt = $db->prepare("INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
            $stmt->execute([$name, $value, $value]);
        }
    }
    
    $success = "Google settings updated successfully!";
}

$google_settings = $db->query("SELECT * FROM settings WHERE name LIKE 'google_%' ORDER BY name ASC")->fetchAll();
$default_google_settings = [
    'google_client_id' => 'Client ID',
    'google_client_secret' => 'Client Secret',
    'google_redirect_uri' => 'Redirect URI'
];
$existing_settings = [];
foreach ($google_settings as $setting) {
    $existing_settings[$setting['name']] = $setting;
}
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Google Settings</h2>
                <p class="text-gray-600 mt-1">Configure Google OAuth and API integration</p>
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
            <h3 class="text-lg font-medium text-gray-900">Google OAuth Configuration</h3>
            <p class="text-sm text-gray-500 mt-1">Configure your Google Cloud Console application settings</p>
        </div>
        
        <form method="post" class="p-6 space-y-6">
            <?php if (!empty($google_settings)): ?>
                <?php foreach ($google_settings as $setting): ?>
                    <div>
                        <label for="setting-<?= $setting['id'] ?>" class="block text-sm font-medium text-gray-700 mb-1">
                            <?= htmlspecialchars(str_replace('google_', '', ucwords(str_replace('_', ' ', $setting['name'])))) ?>
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
                        
                        <?php if ($setting['name'] === 'google_client_id'): ?>
                            <p class="mt-1 text-sm text-gray-500">Your Google OAuth 2.0 Client ID</p>
                        <?php elseif ($setting['name'] === 'google_client_secret'): ?>
                            <p class="mt-1 text-sm text-gray-500">Your Google OAuth 2.0 Client Secret</p>
                        <?php elseif ($setting['name'] === 'google_redirect_uri'): ?>
                            <p class="mt-1 text-sm text-gray-500">OAuth redirect URI: <?= $settings['site_url'] ?>/auth/google/</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($default_google_settings as $name => $label): ?>
                    <div>
                        <label for="new-<?= $name ?>" class="block text-sm font-medium text-gray-700 mb-1">
                            <?= htmlspecialchars($label) ?>
                        </label>
                        <?php if (strpos($name, 'secret') !== false): ?>
                            <input type="password" 
                                   name="new_settings[<?= $name ?>]" 
                                   id="new-<?= $name ?>"
                                   placeholder="Enter your Google <?= strtolower($label) ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                        <?php else: ?>
                            <input type="text" 
                                   name="new_settings[<?= $name ?>]" 
                                   id="new-<?= $name ?>"
                                   value="<?= $name === 'google_redirect_uri' ? $settings['site_url'] . '/auth/google/' : '' ?>"
                                   placeholder="<?= $name === 'google_redirect_uri' ? $settings['site_url'] . '/auth/google/' : 'Enter your Google ' . strtolower($label) ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                        <?php endif; ?>
                        
                        <?php if ($name === 'google_client_id'): ?>
                            <p class="mt-1 text-sm text-gray-500">Your Google OAuth 2.0 Client ID</p>
                        <?php elseif ($name === 'google_client_secret'): ?>
                            <p class="mt-1 text-sm text-gray-500">Your Google OAuth 2.0 Client Secret</p>
                        <?php elseif ($name === 'google_redirect_uri'): ?>
                            <p class="mt-1 text-sm text-gray-500">OAuth redirect URI: <?= $settings['site_url'] ?>/auth/google/</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Google Settings
                </button>
            </div>
        </form>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <h3 class="text-lg font-medium text-red-900 mb-4">Setup Instructions</h3>
        <div class="text-sm text-red-800 space-y-2">
            <p><strong>1.</strong> Go to <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="underline">Google Cloud Console</a></p>
            <p><strong>2.</strong> Create a new project or select an existing one</p>
            <p><strong>3.</strong> Enable the Google+ API or Google Identity API</p>
            <p><strong>4.</strong> Create OAuth 2.0 credentials with these settings:</p>
            <ul class="list-disc list-inside ml-4 space-y-1">
                <li><strong>Application type:</strong> Web application</li>
                <li><strong>Name:</strong> Your site name</li>
                <li><strong>Authorized JavaScript origins:</strong> <?= rtrim($settings['site_url'], '/') ?></li>
                <li><strong>Authorized redirect URIs:</strong> <?= htmlspecialchars($settings['site_url']) ?>/auth/google/</li>
            </ul>
            <p><strong>5.</strong> Copy the Client ID and Client Secret to the form above</p>
            <p><strong>6.</strong> Save the settings to enable Google integration</p>
            <div class="mt-4 p-3 bg-yellow-100 border border-yellow-300 rounded">
                <p class="text-yellow-800 text-xs"><strong>Note:</strong> JavaScript origins should not include paths or trailing slashes. Use domain only: <code><?= rtrim($settings['site_url'], '/') ?></code></p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>