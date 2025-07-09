<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = "Contact Messages";
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_update_id'])) {
    $id = intval($_POST['status_update_id']);
    $status = ($_POST['status'] === 'solved') ? 'solved' : 'waiting';
    $db->prepare("UPDATE contact_messages SET status=? WHERE id=?")->execute([$status, $id]);
    $success = "Status updated.";
}

$messages = $db->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Contact Messages</h2>
                <p class="text-gray-600 mt-1">Review and manage contact form submissions</p>
            </div>
            <div class="text-sm text-gray-500">
                Total Messages: <?= count($messages) ?>
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

    <!-- Messages Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Messages List</h3>
        </div>
        
        <?php if (empty($messages)): ?>
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No messages</h3>
                <p class="mt-1 text-sm text-gray-500">No contact messages have been received yet.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($messages as $msg): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($msg['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($msg['name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="text-primary hover:text-red-600">
                                        <?= htmlspecialchars($msg['email']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="max-w-xs truncate"><?= htmlspecialchars($msg['message']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y g:i A', strtotime($msg['submitted_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <form method="post" class="inline">
                                        <input type="hidden" name="status_update_id" value="<?= $msg['id'] ?>">
                                        <select name="status" onchange="this.form.submit()" 
                                                class="text-sm rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary <?php
                                                    if ($msg['status'] === 'waiting') echo 'bg-yellow-50 text-yellow-800';
                                                    elseif ($msg['status'] === 'solved') echo 'bg-green-50 text-green-800';
                                                ?>">
                                            <option value="waiting" <?= $msg['status']=='waiting'?'selected':'' ?>>Waiting</option>
                                            <option value="solved" <?= $msg['status']=='solved'?'selected':'' ?>>Solved</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if ($msg['status'] === 'waiting'): ?>
                                        <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: Your Contact Message&body=Hello <?= htmlspecialchars($msg['name']) ?>,%0A%0AThank you for your message:%0A%0A<?= htmlspecialchars($msg['message']) ?>%0A%0A" 
                                           class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs leading-4 font-medium rounded text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                            Send Reply
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>