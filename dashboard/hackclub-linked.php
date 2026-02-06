<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/classes/HackClubOAuth.php';
checkLoggedIn();

global $db, $currentUser, $settings;

$hackclub = new HackClubOAuth($db);
$hackclubLink = $hackclub->getLinkedAccount($currentUser->id);

if (!$hackclubLink) {
    header('Location: /dashboard/index.php');
    exit();
}

$pageTitle = "Hack Club Connected";
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Success Message -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                    </p>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Connected Account Card -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                Hack Club Account Connected
            </h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hack Club ID</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($hackclubLink['hackclub_id']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <p class="mt-1 text-sm text-gray-900">
                        <?= htmlspecialchars($hackclubLink['first_name'] . ' ' . $hackclubLink['last_name']) ?>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($hackclubLink['email']) ?></p>
                </div>
                <?php if ($hackclubLink['slack_id']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Slack ID</label>
                    <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($hackclubLink['slack_id']) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($hackclubLink['verification_status']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Verification Status</label>
                    <p class="mt-1">
                        <?php
                        $statusColors = [
                            'verified' => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'needs_submission' => 'bg-gray-100 text-gray-800',
                            'ineligible' => 'bg-red-100 text-red-800'
                        ];
                        $statusColor = $statusColors[$hackclubLink['verification_status']] ?? 'bg-gray-100 text-gray-800';
                        ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>">
                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $hackclubLink['verification_status']))) ?>
                        </span>
                    </p>
                </div>
                <?php endif; ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">YSWS Eligible</label>
                    <p class="mt-1">
                        <?php if ($hackclubLink['ysws_eligible']): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Yes
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                No
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-600 mb-4">
                    Your Hack Club account is now connected. You can use it to sign in to your account.
                </p>
                <div class="flex space-x-3">
                    <a href="/dashboard/index.php" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Go to Dashboard
                    </a>
                    <a href="/dashboard/profile-edit.php" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        View Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">About Hack Club Authentication</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>You can now use your Hack Club account to sign in. Your account information will be kept in sync automatically.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
