<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/classes/DiscordBot.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Settings';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;


// Get statistics for display
$stats = [
    'total_users' => $db->query("SELECT COUNT(*) FROM users WHERE active_member = 1")->fetchColumn(),
    'discord_linked_users' => $db->query("SELECT COUNT(DISTINCT dl.user_id) FROM discord_links dl JOIN users u ON dl.user_id = u.id WHERE u.active_member = 1")->fetchColumn(),
    'projects_with_roles' => $db->query("SELECT COUNT(*) FROM projects WHERE discord_accepted_role_id IS NOT NULL OR discord_pizza_role_id IS NOT NULL")->fetchColumn(),
    'events_with_roles' => $db->query("SELECT COUNT(*) FROM events WHERE discord_participated_role_id IS NOT NULL")->fetchColumn()
];

// Calculate unlinked users
$unlinked_users = $stats['total_users'] - $stats['discord_linked_users'];
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Settings</h2>
                <p class="text-gray-600 dark:text-gray-300 mt-1">Manage site configuration and integrations</p>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <?php if ($success): ?>
        <div class="bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-700 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700 dark:text-green-300"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-700 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700 dark:text-red-300"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Discord Role Management -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Discord Role Management</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sync and manage Discord roles for projects and events</p>
        </div>
        <div class="p-6">
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white"><?= $stats['total_users'] ?></div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Users</div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?= $stats['discord_linked_users'] ?></div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Discord Linked</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-4">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400"><?= $stats['projects_with_roles'] ?></div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Projects with Roles</div>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/30 rounded-lg p-4">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400"><?= $stats['events_with_roles'] ?></div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Events with Roles</div>
                </div>
            </div>

            <!-- Sync Actions -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <form method="POST" class="flex-1">
                    </form>
                    
                    <form method="POST" class="flex-1">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Site Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Site Settings</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Configure general site settings, maintenance mode, and appearance</p>
            <a href="<?= $settings['site_url'] ?>/dashboard/site-settings.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                Configure
            </a>
        </div>

        <!-- Page Management -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Page Management</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Create and manage website pages, navigation, and content</p>
            <a href="<?= $settings['site_url'] ?>/dashboard/page-settings.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Manage Pages
            </a>
        </div>

        <!-- Footer Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Footer Settings</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Configure footer content, links, and layout</p>
            <a href="<?= $settings['site_url'] ?>/dashboard/footer-settings.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Edit Footer
            </a>
        </div>

        <!-- Discord Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-indigo-600 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Discord Settings</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Configure Discord bot, roles, and OAuth integration</p>
            <a href="<?= $settings['site_url'] ?>/dashboard/discord-settings.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Configure Discord
            </a>
        </div>

        <!-- Email Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Email Settings</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Configure SMTP settings for email notifications</p>
            <a href="<?= $settings['site_url'] ?>/dashboard/email-settings.php" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                Configure Email
            </a>
        </div>

        <!-- Integration Settings -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Integrations</h3>
            </div>
            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Configure GitHub, Google, Slack and other integrations</p>
            <div class="space-y-2">
                <a href="<?= $settings['site_url'] ?>/dashboard/github-settings.php" 
                   class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    GitHub
                </a>
                <a href="<?= $settings['site_url'] ?>/dashboard/google-settings.php" 
                   class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Google
                </a>
                <a href="<?= $settings['site_url'] ?>/dashboard/slack-settings.php" 
                   class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Slack
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
        darkIcon.classList.add('hidden');
    }
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            const isDark = document.documentElement.classList.contains('dark');
            
            if (isDark) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
                lightIcon.classList.remove('hidden');
                darkIcon.classList.add('hidden');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                lightIcon.classList.add('hidden');
                darkIcon.classList.remove('hidden');
            }
        });
    }
});
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
