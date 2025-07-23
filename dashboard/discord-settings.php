<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Discord Settings';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $id => $value) {
        $stmt = $db->prepare("UPDATE settings SET value=? WHERE id=?");
        $stmt->execute([$value, $id]);
    }
    $success = "Discord settings updated successfully!";
}

$discord_settings = $db->query("SELECT * FROM settings WHERE name LIKE 'discord_%' ORDER BY id ASC")->fetchAll();
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Discord Settings</h2>
                <p class="text-gray-600 mt-1">Configure Discord OAuth and bot integration</p>
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
            <h3 class="text-lg font-medium text-gray-900">Discord OAuth Configuration</h3>
            <p class="text-sm text-gray-500 mt-1">Configure your Discord application settings</p>
        </div>
        
        <form method="post" class="p-6 space-y-6">
            <?php foreach ($discord_settings as $setting): ?>
                <div>
                    <label for="setting-<?= $setting['id'] ?>" class="block text-sm font-medium text-gray-700 mb-1">
                        <?= htmlspecialchars(str_replace('discord_', '', ucwords(str_replace('_', ' ', $setting['name'])))) ?>
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
                    
                    <?php if ($setting['name'] === 'discord_client_id'): ?>
                        <p class="mt-1 text-sm text-gray-500">Your Discord application's Client ID</p>
                    <?php elseif ($setting['name'] === 'discord_client_secret'): ?>
                        <p class="mt-1 text-sm text-gray-500">Your Discord application's Client Secret</p>
                    <?php elseif ($setting['name'] === 'discord_bot_token'): ?>
                        <p class="mt-1 text-sm text-gray-500">Your Discord bot token for server integration</p>
                    <?php elseif ($setting['name'] === 'discord_redirect_uri'): ?>
                        <p class="mt-1 text-sm text-gray-500">OAuth redirect URI (e.g., <?= $settings['site_url'] ?>/auth/discord/callback)</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Discord Settings
                </button>
            </div>
        </form>
    </div>
    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
        <h3 class="text-lg font-medium text-indigo-900 mb-4">Setup Instructions</h3>
        <div class="text-sm text-indigo-800 space-y-2">
            <p><strong>1.</strong> Go to <a href="https://discord.com/developers/applications" target="_blank" class="underline">Discord Developer Portal</a></p>
            <p><strong>2.</strong> Create a new application with these settings:</p>
            <ul class="list-disc list-inside ml-4 space-y-1">
                <li><strong>Name:</strong> Your site name</li>
                <li><strong>Description:</strong> Your site description</li>
            </ul>
            <p><strong>3.</strong> Go to OAuth2 → General and configure:</p>
            <ul class="list-disc list-inside ml-4 space-y-1">
                <li><strong>Redirects:</strong> <?= htmlspecialchars($settings['site_url']) ?>/auth/discord/</li>
            </ul>
            <p><strong>4.</strong> Copy the Client ID and Client Secret to the form above</p>
            <p><strong>5.</strong> (Optional) Create a bot for server integration:</p>
            <ul class="list-disc list-inside ml-4 space-y-1">
                <li>Go to Bot section and create a bot</li>
                <li>Copy the bot token for advanced integration</li>
            </ul>
            <p><strong>6.</strong> Save the settings to enable Discord integration</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>