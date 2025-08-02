<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/classes/DiscordBot.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discordBot = new DiscordBot($db);
    
    if (isset($_POST['sync_project_roles'])) {
        $project_id = (int)$_POST['project_id'];
        $result = $discordBot->syncProjectRoles($project_id);
        
        if ($result['success']) {
            $success = "Project roles synced successfully! Assigned " . $result['assigned_count'] . " roles.";
        } else {
            $error = "Failed to sync project roles: " . $result['error'];
        }
    }
    
    if (isset($_POST['sync_event_roles'])) {
        $event_id = (int)$_POST['event_id'];
        $result = $discordBot->syncEventRoles($event_id);
        
        if ($result['success']) {
            $success = "Event roles synced successfully! Assigned " . $result['assigned_count'] . " roles.";
        } else {
            $error = "Failed to sync event roles: " . $result['error'];
        }
    }
    
    if (isset($_POST['sync_all_roles'])) {
        $result = $discordBot->syncAllRoles();
        
        if ($result['success']) {
            $success = "All roles synced successfully! Projects: " . $result['projects_synced'] . ", Events: " . $result['events_synced'];
        } else {
            $error = "Failed to sync all roles: " . $result['error'];
        }
    }
}

$stmt = $db->prepare("
    SELECT p.*, COUNT(pa.user_id) as total_assignments,
           SUM(CASE WHEN pa.status = 'accepted' THEN 1 ELSE 0 END) as accepted_count,
           SUM(CASE WHEN pa.pizza_grant = 'received' THEN 1 ELSE 0 END) as pizza_count
    FROM projects p 
    LEFT JOIN project_assignments pa ON p.id = pa.project_id 
    WHERE p.discord_accepted_role_id IS NOT NULL OR p.discord_pizza_role_id IS NOT NULL
    GROUP BY p.id 
    ORDER BY p.title
");
$stmt->execute();
$projects_with_roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("
    SELECT * FROM events 
    WHERE discord_participated_role_id IS NOT NULL 
    ORDER BY start_datetime DESC
");
$stmt->execute();
$events_with_roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Discord Role Sync';
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Discord Role Sync</h2>
                <p class="text-gray-600 dark:text-gray-300 mt-1">Manually sync Discord roles for projects and events</p>
            </div>
            <form method="POST" class="inline">
                <button type="submit" name="sync_all_roles" 
                        onclick="return confirm('This will sync ALL Discord roles. Continue?')"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Sync All Roles
                </button>
            </form>
        </div>
    </div>

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

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Project Discord Roles</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sync roles for accepted users and pizza grant recipients</p>
        </div>
        <div class="p-6">
            <?php if (empty($projects_with_roles)): ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No projects with Discord roles</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure Discord role IDs in your projects first.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($projects_with_roles as $project): ?>
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="text-md font-medium text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($project['title']) ?>
                                    </h4>
                                    <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span><?= $project['accepted_count'] ?> accepted users</span>
                                        <span><?= $project['pizza_count'] ?> pizza grant recipients</span>
                                    </div>
                                    <div class="mt-2 flex items-center space-x-2 text-xs">
                                        <?php if ($project['discord_accepted_role_id']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                Accepted Role: <?= htmlspecialchars($project['discord_accepted_role_id']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($project['discord_pizza_role_id']): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                Pizza Role: <?= htmlspecialchars($project['discord_pizza_role_id']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <form method="POST" class="ml-4">
                                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                    <button type="submit" name="sync_project_roles"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Sync Roles
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Event Discord Roles</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sync roles for event participants</p>
        </div>
        <div class="p-6">
            <?php if (empty($events_with_roles)): ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No events with Discord roles</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure Discord role IDs in your events first.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($events_with_roles as $event): ?>
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="text-md font-medium text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </h4>
                                    <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span><?= date('M j, Y g:i A', strtotime($event['start_datetime'])) ?></span>
                                        <span><?= htmlspecialchars($event['location']) ?></span>
                                    </div>
                                    <div class="mt-2 text-xs">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                            Participated Role: <?= htmlspecialchars($event['discord_participated_role_id']) ?>
                                        </span>
                                    </div>
                                </div>
                                <form method="POST" class="ml-4">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <button type="submit" name="sync_event_roles"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Sync Roles
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-6">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">How Discord Role Sync Works</h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <ul class="list-disc list-inside space-y-1">
                        <li><strong>Project Sync:</strong> Assigns "accepted" role to users with status "accepted", and "pizza" role to users with pizza_grant "received"</li>
                        <li><strong>Event Sync:</strong> Assigns "participated" role to users linked to events through YSWS projects</li>
                        <li><strong>Requirements:</strong> Users must have Discord linked in their profile</li>
                        <li><strong>Manual Process:</strong> Click sync buttons to update Discord roles on demand</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
