<?php
require_once '../core/init.php';
checkActiveOrLimitedAccess();

global $currentUser, $db;

// Additional safety check for $currentUser
if (!$currentUser) {
    header('Location: /dashboard/login.php');
    exit;
}

$pageTitle = 'Dashboard';
include __DIR__ . '/components/dashboard-header.php';

$totalProjects = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE active_member = 1")->fetchColumn();
$totalApplications = $db->query("SELECT COUNT(*) FROM applications WHERE status = 'waiting'")->fetchColumn();
$totalMessages = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'waiting'")->fetchColumn();

# For inactive/guest users, only count projects where status is not 'Not_participating'
if ($currentUser->active_member == 0 || $currentUser->role == 'Guest') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM project_assignments WHERE user_id = ? AND status != 'Not_participating'");
    $stmt->execute([$currentUser->id]);
    $myProjectsCount = $stmt->fetchColumn();

    # Don't show recent projects for inactive/guest users
    $recentProjects = [];
} else {
    $stmt = $db->prepare("SELECT COUNT(*) FROM project_assignments WHERE user_id = ?");
    $stmt->execute([$currentUser->id]);
    $myProjectsCount = $stmt->fetchColumn();

    $recentProjects = $db->query("SELECT title, id FROM projects ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
}

$success = $_SESSION['profile_success'] ?? $_SESSION['account_link_success'] ?? null;
$errors = $_SESSION['profile_errors'] ?? [];
unset($_SESSION['profile_success'], $_SESSION['profile_errors'], $_SESSION['account_link_success']);
?>

<div class="space-y-6">
    <?php if ($currentUser->role == 'Guest'): ?>
        <!-- Guest User Notice -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
            <div class="flex">
                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Guest Account</h3>
                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">You have limited access as a guest. Contact an administrator to upgrade your account for full access to all features.</p>
                </div>
            </div>
        </div>
    <?php elseif ($currentUser->active_member == 0): ?>
        <!-- Inactive User Notice -->
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
            <div class="flex">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Inactive Account</h3>
                    <p class="mt-1 text-sm text-red-700 dark:text-red-300">Your account is currently inactive. You have limited access. Contact an administrator to reactivate your account.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <?php
                // Handle profile image - prioritize custom upload over Discord avatar
                $profile_image = $currentUser->profile_image ?? '';
                $discord_id = $currentUser->discord_id ?? '';
                $discord_avatar = $currentUser->discord_avatar ?? '';

                if (!empty($profile_image)) {
                    // Use custom uploaded image
                    $avatar = '/images/members/' . $profile_image;
                } elseif (!empty($discord_id) && !empty($discord_avatar)) {
                    // Fallback to Discord avatar
                    $avatar = "https://cdn.discordapp.com/avatars/{$discord_id}/{$discord_avatar}.png?size=128";
                } else {
                    // Default avatar
                    $avatar = '/images/default-avatar.svg';
                }
                ?>
                <img src="<?= htmlspecialchars($avatar) ?>"
                    alt="<?= htmlspecialchars($currentUser->first_name ?? '') ?> <?= htmlspecialchars($currentUser->last_name ?? '') ?>"
                    class="w-16 h-16 rounded-full object-cover ring-2 ring-primary"
                    onerror="this.src='/images/default-avatar.svg'">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Welcome back, <?= htmlspecialchars($currentUser->first_name) ?>!
                    </h1>
                    <p class="text-gray-600 dark:text-gray-300 mt-1">Dashboard overview and statistics</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="<?= $settings['site_url'] ?>/dashboard/projects.php"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600">
                    View Projects
                </a>
                <a href="<?= $settings['site_url'] ?>/dashboard/profile-edit.php"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Edit Profile
                </a>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 <?= ($currentUser->active_member == 1 && $currentUser->role != 'Guest') ? 'lg:grid-cols-4' : 'lg:grid-cols-2' ?> gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Projects</dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white"><?= $totalProjects ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">My Projects</dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white"><?= $myProjectsCount ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <?php if ($currentUser->active_member == 1 && $currentUser->role != 'Guest'): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Members</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white"><?= $totalUsers ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Pending Reviews</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white"><?= $totalApplications + $totalMessages ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">My Profile</h3>
            </div>
            <div class="p-6">
                <?php if ($success): ?>
                    <div class="mb-4 bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <div class="mb-4 bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                        <?php foreach ($errors as $error): ?>
                            <div><?= htmlspecialchars($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <?php
                        // Handle profile image - prioritize custom upload over Discord avatar
                        $profile_image = $currentUser->profile_image ?? '';
                        $discord_id = $currentUser->discord_id ?? '';
                        $discord_avatar = $currentUser->discord_avatar ?? '';

                        if (!empty($profile_image)) {
                            // Use custom uploaded image
                            $avatar = '/images/members/' . $profile_image;
                        } elseif (!empty($discord_id) && !empty($discord_avatar)) {
                            // Fallback to Discord avatar
                            $avatar = "https://cdn.discordapp.com/avatars/{$discord_id}/{$discord_avatar}.png?size=128";
                        } else {
                            // Default avatar
                            $avatar = '/images/default-avatar.svg';
                        }
                        ?>
                        <img src="<?= htmlspecialchars($avatar) ?>"
                            alt="<?= htmlspecialchars($currentUser->first_name ?? '') ?> <?= htmlspecialchars($currentUser->last_name ?? '') ?>"
                            class="w-16 h-16 rounded-full object-cover"
                            onerror="this.src='/images/default-avatar.svg'">
                        <div>
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                <?= htmlspecialchars($currentUser->first_name ?? '') ?> <?= htmlspecialchars($currentUser->last_name ?? '') ?>
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($currentUser->role ?? 'Member') ?></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($currentUser->email ?? 'Not provided') ?></p>
                        </div>

                        <?php if ($currentUser->school): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">School</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white"><?= htmlspecialchars($currentUser->school) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($currentUser->description): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">About Me</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white"><?= nl2br(htmlspecialchars($currentUser->description)) ?></p>
                            </div> <?php endif; ?>
                    </div>

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="<?= $settings['site_url'] ?>/dashboard/profile-edit.php"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <a href="<?= $settings['site_url'] ?>/dashboard/projects.php"
                        class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">View My Projects</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">See all your joined and available projects</p>
                        </div>
                    </a>

                    <a href="<?= $settings['site_url'] ?>/dashboard/events.php"
                        class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">View Events</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">See upcoming and past community events</p>
                        </div>
                    </a>

                    <?php if (in_array($currentUser->role, ['Leader', 'Co-leader'])): ?>
                        <a href="<?= $settings['site_url'] ?>/dashboard/applications.php"
                            class="flex items-center p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="w-5 h-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Review Applications</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?= $totalApplications ?> pending applications</p>
                            </div>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($recentProjects)): ?>
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Recent Projects</h4>
                            <div class="space-y-1">
                                <?php foreach (array_slice($recentProjects, 0, 3) as $project): ?>
                                    <div class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($project['title']) ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>