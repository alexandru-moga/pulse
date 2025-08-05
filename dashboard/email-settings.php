<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Email Settings';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['settings'] as $id => $value) {
            $stmt = $db->prepare("UPDATE settings SET value=? WHERE id=?");
            $stmt->execute([$value, $id]);
        }
        $success = "Email settings updated successfully!";
    } catch (Exception $e) {
        $error = "Failed to update settings: " . $e->getMessage();
    }
}

try {
    $email_settings = $db->query("SELECT * FROM settings WHERE name LIKE 'smtp_%' ORDER BY id ASC")->fetchAll();
} catch (Exception $e) {
    $error = "Failed to load email settings: " . $e->getMessage();
    $email_settings = [];
}
?>

<div class="space-y-6">
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
    
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
                    
                    <?php if ($setting['name'] === 'smtp_pass'): ?>
                        <div class="relative">
                            <input type="password" 
                                   name="settings[<?= $setting['id'] ?>]" 
                                   id="setting-<?= $setting['id'] ?>"
                                   value="<?= htmlspecialchars($setting['value']) ?>"
                                   class="mt-1 block w-full px-3 py-2 pr-12 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                            <button type="button" 
                                    onclick="togglePasswordVisibility('setting-<?= $setting['id'] ?>', this)"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg class="h-5 w-5 text-gray-400 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg class="h-5 w-5 text-gray-400 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                </svg>
                            </button>
                        </div>
                    <?php elseif ($setting['name'] === 'smtp_port'): ?>
                        <input type="number" 
                               name="settings[<?= $setting['id'] ?>]" 
                               id="setting-<?= $setting['id'] ?>"
                               value="<?= htmlspecialchars($setting['value']) ?>"
                               min="1" max="65535"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
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
                    <?php elseif ($setting['name'] === 'smtp_user'): ?>
                        <p class="mt-1 text-sm text-gray-500">Your email address or SMTP username</p>
                    <?php elseif ($setting['name'] === 'smtp_pass'): ?>
                        <p class="mt-1 text-sm text-gray-500">Your email password or app-specific password</p>
                    <?php elseif ($setting['name'] === 'smtp_from'): ?>
                        <p class="mt-1 text-sm text-gray-500">The email address emails will be sent from</p>
                    <?php elseif ($setting['name'] === 'smtp_from_name'): ?>
                        <p class="mt-1 text-sm text-gray-500">The name that will appear as the sender</p>
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

<script>
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const eyeOpen = button.querySelector('.eye-open');
    const eyeClosed = button.querySelector('.eye-closed');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
}
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
