<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $settings;

$success = $error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hackclub_client_id = trim($_POST['hackclub_client_id'] ?? '');
    $hackclub_client_secret = trim($_POST['hackclub_client_secret'] ?? '');
    $hackclub_redirect_uri = trim($_POST['hackclub_redirect_uri'] ?? '');

    try {
        $db->prepare("INSERT INTO settings (name, value) VALUES ('hackclub_client_id', ?) ON DUPLICATE KEY UPDATE value = ?")
            ->execute([$hackclub_client_id, $hackclub_client_id]);
        
        $db->prepare("INSERT INTO settings (name, value) VALUES ('hackclub_client_secret', ?) ON DUPLICATE KEY UPDATE value = ?")
            ->execute([$hackclub_client_secret, $hackclub_client_secret]);
        
        $db->prepare("INSERT INTO settings (name, value) VALUES ('hackclub_redirect_uri', ?) ON DUPLICATE KEY UPDATE value = ?")
            ->execute([$hackclub_redirect_uri, $hackclub_redirect_uri]);

        $success = "Hack Club OAuth settings saved successfully!";
        
        // Reload settings
        $settings = [];
        foreach ($db->query("SELECT name, value FROM settings") as $row) {
            $settings[$row['name']] = $row['value'];
        }
    } catch (Exception $e) {
        $error = "Failed to save settings: " . $e->getMessage();
    }
}

// Get current settings
$hackclub_client_id = $settings['hackclub_client_id'] ?? '';
$hackclub_client_secret = $settings['hackclub_client_secret'] ?? '';
$hackclub_redirect_uri = $settings['hackclub_redirect_uri'] ?? '';

// Get link statistics
$totalLinks = $db->query("SELECT COUNT(*) FROM hackclub_links")->fetchColumn();
$verifiedLinks = $db->query("SELECT COUNT(*) FROM hackclub_links WHERE verification_status = 'verified'")->fetchColumn();
$yswsEligible = $db->query("SELECT COUNT(*) FROM hackclub_links WHERE ysws_eligible = 1")->fetchColumn();

$pageTitle = "Hack Club OAuth Settings";
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Hack Club OAuth Settings</h2>
                <p class="text-gray-600 mt-1">Configure Hack Club authentication for your application</p>
            </div>
            <a href="https://auth.hackclub.com/developer/apps" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                Hack Club Developer Portal
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Links</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= $totalLinks ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Verified Users</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= $verifiedLinks ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">YSWS Eligible</p>
                    <p class="text-2xl font-semibold text-gray-900"><?= $yswsEligible ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Setup Instructions -->
    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Setup Instructions</h3>
                <div class="mt-2 text-sm text-blue-700 space-y-2">
                    <p><strong>1.</strong> Create an app at <a href="https://auth.hackclub.com/developer/apps/new" target="_blank" class="underline">Hack Club Developer Portal</a></p>
                    <p><strong>2.</strong> Copy your Client ID and Client Secret</p>
                    <p><strong>3.</strong> Set your redirect URI to: <code class="bg-blue-100 px-1 rounded"><?= htmlspecialchars($settings['site_url']) ?>/auth/hackclub/</code></p>
                    <p><strong>4.</strong> Enter the credentials below and save</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">OAuth Configuration</h3>
        </div>
        <form method="POST" class="p-6 space-y-6">
            <div>
                <label for="hackclub_client_id" class="block text-sm font-medium text-gray-700">Client ID</label>
                <input type="text" name="hackclub_client_id" id="hackclub_client_id" 
                    value="<?= htmlspecialchars($hackclub_client_id) ?>"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                    placeholder="Your Hack Club Client ID">
                <p class="mt-1 text-sm text-gray-500">From your Hack Club app settings</p>
            </div>

            <div>
                <label for="hackclub_client_secret" class="block text-sm font-medium text-gray-700">Client Secret</label>
                <input type="password" name="hackclub_client_secret" id="hackclub_client_secret" 
                    value="<?= htmlspecialchars($hackclub_client_secret) ?>"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                    placeholder="Your Hack Club Client Secret">
                <p class="mt-1 text-sm text-gray-500">Keep this secret and secure</p>
            </div>

            <div>
                <label for="hackclub_redirect_uri" class="block text-sm font-medium text-gray-700">Redirect URI</label>
                <input type="url" name="hackclub_redirect_uri" id="hackclub_redirect_uri" 
                    value="<?= htmlspecialchars($hackclub_redirect_uri) ?>"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"
                    placeholder="<?= htmlspecialchars($settings['site_url']) ?>/auth/hackclub/">
                <p class="mt-1 text-sm text-gray-500">Must match the redirect URI in your Hack Club app</p>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
