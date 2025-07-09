<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Email Settings';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $id => $value) {
        $stmt = $db->prepare("UPDATE settings SET value=? WHERE id=?");
        $stmt->execute([$value, $id]);
    }
    $success = "Email settings updated successfully!";
}

$email_settings = $db->query("SELECT * FROM settings WHERE name LIKE 'smtp_%' ORDER BY id ASC")->fetchAll();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Email Settings</h2>
                <p class="text-gray-600 mt-1">Configure SMTP settings for sending emails</p>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/settings.php" 
               class="text-primary hover:text-red-600 text-sm font-medium">
                ← Back to Settings
            </a>
        </div>
    </div>

    <!-- Notifications -->
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

    <!-- Email Settings Form -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">SMTP Configuration</h3>
            <p class="text-sm text-gray-500 mt-1">Configure your email server settings for sending notifications</p>
        </div>
        
        <form method="post" class="p-6 space-y-6">
            <?php foreach ($email_settings as $setting): ?>
                <div>
                    <label for="setting-<?= $setting['id'] ?>" class="block text-sm font-medium text-gray-700 mb-1">
                        <?= htmlspecialchars(str_replace('smtp_', 'SMTP ', ucwords(str_replace('_', ' ', $setting['name'])))) ?>
                    </label>
                    <?php if (strpos($setting['name'], 'password') !== false): ?>
                        <input type="password" 
                               name="settings[<?= $setting['id'] ?>]" 
                               id="setting-<?= $setting['id'] ?>"
                               value="<?= htmlspecialchars($setting['value']) ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <?php elseif ($setting['name'] === 'smtp_port'): ?>
                        <input type="number" 
                               name="settings[<?= $setting['id'] ?>]" 
                               id="setting-<?= $setting['id'] ?>"
                               value="<?= htmlspecialchars($setting['value']) ?>"
                               min="1" max="65535"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <?php elseif ($setting['name'] === 'smtp_secure'): ?>
                        <select name="settings[<?= $setting['id'] ?>]" id="setting-<?= $setting['id'] ?>"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                            <option value="" <?= $setting['value'] == '' ? 'selected' : '' ?>>None</option>
                            <option value="tls" <?= $setting['value'] == 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= $setting['value'] == 'ssl' ? 'selected' : '' ?>>SSL</option>
                        </select>
                    <?php else: ?>
                        <input type="text" 
                               name="settings[<?= $setting['id'] ?>]" 
                               id="setting-<?= $setting['id'] ?>"
                               value="<?= htmlspecialchars($setting['value']) ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    <?php endif; ?>
                    
                    <?php if ($setting['name'] === 'smtp_host'): ?>
                        <p class="mt-1 text-sm text-gray-500">Your SMTP server hostname (e.g., smtp.gmail.com)</p>
                    <?php elseif ($setting['name'] === 'smtp_port'): ?>
                        <p class="mt-1 text-sm text-gray-500">Usually 587 for TLS, 465 for SSL, or 25 for no encryption</p>
                    <?php elseif ($setting['name'] === 'smtp_username'): ?>
                        <p class="mt-1 text-sm text-gray-500">Your email address or SMTP username</p>
                    <?php elseif ($setting['name'] === 'smtp_password'): ?>
                        <p class="mt-1 text-sm text-gray-500">Your email password or app-specific password</p>
                    <?php elseif ($setting['name'] === 'smtp_secure'): ?>
                        <p class="mt-1 text-sm text-gray-500">Encryption method used by your email provider</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"></path>
                    </svg>
                    Save Email Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
