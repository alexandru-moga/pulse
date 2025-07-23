<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Slack Settings';
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
    
    $success = "Slack settings updated successfully!";
}

$slack_settings = $db->query("SELECT * FROM settings WHERE name LIKE 'slack_%' ORDER BY name ASC")->fetchAll();
$default_slack_settings = [
    'slack_client_id' => 'Client ID',
    'slack_client_secret' => 'Client Secret',
    'slack_redirect_uri' => 'Redirect URI',
    'slack_bot_token' => 'Bot Token',
    'slack_webhook_url' => 'Webhook URL'
];
$existing_settings = [];
foreach ($slack_settings as $setting) {
    $existing_settings[$setting['name']] = $setting;
}
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Slack Settings</h2>
                <p class="text-gray-600 mt-1">Configure Slack OAuth and webhook integration</p>
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
            <h3 class="text-lg font-medium text-gray-900">Slack OAuth Configuration</h3>
            <p class="text-sm text-gray-500 mt-1">Configure your Slack app settings</p>
        </div>
        
        <form method="post" class="p-6 space-y-6">
            <?php if (!empty($slack_settings)): ?>
                <?php foreach ($slack_settings as $setting): ?>
                    <div>
                        <label for="setting-<?= $setting['id'] ?>" class="block text-sm font-medium text-gray-700 mb-1">
                            <?= htmlspecialchars(str_replace('slack_', '', ucwords(str_replace('_', ' ', $setting['name'])))) ?>
                        </label>
                        <?php if (strpos($setting['name'], 'secret') !== false || strpos($setting['name'], 'token') !== false || strpos($setting['name'], 'webhook') !== false): ?>
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
                        
                        <?php if ($setting['name'] === 'slack_client_id'): ?>
                            <p class="mt-1 text-sm text-gray-500">Your Slack app's Client ID</p>
                        <?php elseif ($setting['name'] === 'slack_client_secret'): ?>
                            <p class="mt-1 text-sm text-gray-500">Your Slack app's Client Secret</p>
                        <?php elseif ($setting['name'] === 'slack_bot_token'): ?>
                            <p class="mt-1 text-sm text-gray-500">Your Slack bot token (starts with xoxb-)</p>
                        <?php elseif ($setting['name'] === 'slack_webhook_url'): ?>
                            <p class="mt-1 text-sm text-gray-500">Slack webhook URL for notifications</p>
                        <?php elseif ($setting['name'] === 'slack_redirect_uri'): ?>
                            <p class="mt-1 text-sm text-gray-500">OAuth redirect URI: <?= $settings['site_url'] ?>/auth/slack/</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($default_slack_settings as $name => $label): ?>
                    <div>
                        <label for="new-<?= $name ?>" class="block text-sm font-medium text-gray-700 mb-1">
                            <?= htmlspecialchars($label) ?>
                        </label>
                        <?php if (strpos($name, 'secret') !== false || strpos($name, 'token') !== false || strpos($name, 'webhook') !== false): ?>
                            <input type="password" 
                                   name="new_settings[<?= $name ?>]" 
                                   id="new-<?= $name ?>"
                                   placeholder="Enter your Slack <?= strtolower($label) ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                        <?php else: ?>
                            <input type="text" 
                                   name="new_settings[<?= $name ?>]" 
                                   id="new-<?= $name ?>"
                                   value="<?= $name === 'slack_redirect_uri' ? $settings['site_url'] . '/auth/slack/' : '' ?>"
                                   placeholder="<?= $name === 'slack_redirect_uri' ? $settings['site_url'] . '/auth/slack/' : 'Enter your Slack ' . strtolower($label) ?>"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
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
                    Save Slack Settings
                </button>
            </div>
        </form>
    </div>
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
        <h3 class="text-lg font-medium text-purple-900 mb-4">Setup Instructions</h3>
        <div class="text-sm text-purple-800 space-y-2">
            <p><strong>1.</strong> Go to <a href="https://api.slack.com/apps" target="_blank" class="underline">Slack API Apps</a></p>
            <p><strong>2.</strong> Create a new app with these settings:</p>
            <ul class="list-disc list-inside ml-4 space-y-1">
                <li><strong>App Name:</strong> Your site name</li>
                <li><strong>Development Slack Workspace:</strong> Your workspace</li>
            </ul>
            <p><strong>3.</strong> Configure OAuth & Permissions:</p>
            <ul class="list-disc list-inside ml-4 space-y-1">
                <li><strong>Redirect URLs:</strong> <?= htmlspecialchars($settings['site_url']) ?>/auth/slack/</li>
                <li><strong>Scopes:</strong> identity.basic, identity.email, identity.team</li>
            </ul>
            <p><strong>4.</strong> Copy the Client ID and Client Secret to the form above</p>
            <p><strong>5.</strong> Optionally configure webhooks for notifications</p>
            <p><strong>6.</strong> Save the settings to enable Slack integration</p>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Test Webhook</h3>
            <p class="text-sm text-gray-500 mt-1">Send a test message to verify webhook configuration</p>
        </div>
        
        <div class="p-6">
            <form method="post" action="test-slack-webhook.php" class="space-y-4">
                <div>
                    <label for="test_message" class="block text-sm font-medium text-gray-700 mb-1">Test Message</label>
                    <input type="text" 
                           name="test_message" 
                           id="test_message"
                           value="Slack integration test"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                </div>
                
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    Send Test Message
                </button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>