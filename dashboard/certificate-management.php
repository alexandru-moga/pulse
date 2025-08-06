<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Certificate Management';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $settingsToUpdate = [
        'certificate_enabled' => $_POST['certificate_enabled'] ? '1' : '0',
        'certificate_title' => $_POST['certificate_title'],
        'certificate_org_name' => $_POST['certificate_org_name'],
        'certificate_signature_name' => $_POST['certificate_signature_name'],
        'certificate_signature_title' => $_POST['certificate_signature_title']
    ];
    
    foreach ($settingsToUpdate as $name => $value) {
        $stmt = $db->prepare("UPDATE settings SET value = ? WHERE name = ?");
        $stmt->execute([$value, $name]);
    }
    
    $success = "Certificate settings updated successfully!";
}

// Get current settings
$certificateSettings = [];
$stmt = $db->prepare("SELECT name, value FROM settings WHERE name LIKE 'certificate_%'");
$stmt->execute();
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $setting) {
    $certificateSettings[$setting['name']] = $setting['value'];
}

// Get certificate statistics
$totalCertificates = $db->query("SELECT COUNT(*) FROM certificate_downloads")->fetchColumn();
$uniqueUsers = $db->query("SELECT COUNT(DISTINCT user_id) FROM certificate_downloads")->fetchColumn();
$uniqueProjects = $db->query("SELECT COUNT(DISTINCT project_id) FROM certificate_downloads")->fetchColumn();

// Get recent downloads
$recentDownloads = $db->query("
    SELECT cd.*, u.first_name, u.last_name, p.title as project_title
    FROM certificate_downloads cd
    JOIN users u ON cd.user_id = u.id
    JOIN projects p ON cd.project_id = p.id
    ORDER BY cd.downloaded_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Certificate Management</h2>
                <p class="text-gray-600 mt-1">Configure certificate settings and view download statistics</p>
            </div>
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

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Downloads</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $totalCertificates ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Certified Users</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $uniqueUsers ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Certified Projects</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $uniqueProjects ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Certificate Settings -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Certificate Settings</h3>
            <p class="text-sm text-gray-500 mt-1">Configure how certificates are generated and displayed</p>
        </div>
        <div class="p-6">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="certificate_enabled" value="1" 
                                   <?= ($certificateSettings['certificate_enabled'] ?? '1') === '1' ? 'checked' : '' ?>
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <span class="ml-2 text-sm font-medium text-gray-700">Enable Certificate Downloads</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">Allow users to download certificates for their projects</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Certificate Title</label>
                        <input type="text" name="certificate_title" 
                               value="<?= htmlspecialchars($certificateSettings['certificate_title'] ?? 'Certificate of Achievement') ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Organization Name</label>
                        <input type="text" name="certificate_org_name" 
                               value="<?= htmlspecialchars($certificateSettings['certificate_org_name'] ?? 'PULSE') ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Signature Name</label>
                        <input type="text" name="certificate_signature_name" 
                               value="<?= htmlspecialchars($certificateSettings['certificate_signature_name'] ?? 'Leadership Team') ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Signature Title</label>
                        <input type="text" name="certificate_signature_title" 
                               value="<?= htmlspecialchars($certificateSettings['certificate_signature_title'] ?? 'Director') ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="update_settings"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Downloads -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Certificate Downloads</h3>
            <p class="text-sm text-gray-500 mt-1">Latest certificate download activity</p>
        </div>
        <div class="overflow-hidden">
            <?php if (empty($recentDownloads)): ?>
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No downloads yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Certificate downloads will appear here</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Downloads</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Downloaded</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($recentDownloads as $download): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($download['first_name'] . ' ' . $download['last_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($download['project_title']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= ucwords(str_replace('_', ' ', $download['certificate_type'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= $download['download_count'] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M j, Y g:i A', strtotime($download['downloaded_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
