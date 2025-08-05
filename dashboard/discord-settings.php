<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$success = $error = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_project_roles'])) {
        $project_id = (int)$_POST['project_id'];
        $accepted_role = $_POST['accepted_role_id'] ?: null;
        $pizza_role = $_POST['pizza_role_id'] ?: null;
        
        $stmt = $db->prepare("UPDATE projects SET discord_accepted_role_id = ?, discord_pizza_role_id = ? WHERE id = ?");
        if ($stmt->execute([$accepted_role, $pizza_role, $project_id])) {
            $success = "Project Discord roles updated successfully!";
        } else {
            $error = "Failed to update project roles.";
        }
    }
    
    if (isset($_POST['update_event_roles'])) {
        $event_id = (int)$_POST['event_id'];
        $participated_role = $_POST['participated_role_id'] ?: null;
        
        $stmt = $db->prepare("UPDATE events SET discord_participated_role_id = ? WHERE id = ?");
        if ($stmt->execute([$participated_role, $event_id])) {
            $success = "Event Discord roles updated successfully!";
        } else {
            $error = "Failed to update event roles.";
        }
    }
    
    if (isset($_POST['update_discord_settings'])) {
        $bot_token = $_POST['bot_token'];
        $guild_id = $_POST['guild_id'];
        
        // Discord OAuth settings
        $client_id = $_POST['client_id'];
        $client_secret = $_POST['client_secret'];
        $redirect_uri = $_POST['redirect_uri'];
        
        // Discord role IDs
        $member_role_id = $_POST['member_role_id'];
        $co_leader_role_id = $_POST['co_leader_role_id'];
        $leader_role_id = $_POST['leader_role_id'];
        
        // Update settings in database
        $settings_to_update = [
            'discord_bot_token' => $bot_token,
            'discord_guild_id' => $guild_id,
            'discord_client_id' => $client_id,
            'discord_client_secret' => $client_secret,
            'discord_redirect_uri' => $redirect_uri,
            'discord_member_role_id' => $member_role_id,
            'discord_co_leader_role_id' => $co_leader_role_id,
            'discord_leader_role_id' => $leader_role_id
        ];
        
        foreach ($settings_to_update as $name => $value) {
            $stmt = $db->prepare("UPDATE settings SET value = ? WHERE name = ?");
            $stmt->execute([$value, $name]);
        }
        
        $success = "Discord settings updated successfully!";
    }
    
    if (isset($_POST['test_discord_connection'])) {
        require_once __DIR__ . '/../core/classes/DiscordBot.php';
        $discordBot = new DiscordBot($db);
        
        $guild_id = $_POST['guild_id'];
        $bot_token = $_POST['bot_token'];
        
        // Test Discord API connection
        $test_result = $discordBot->testConnection($guild_id, $bot_token);
        if ($test_result['success']) {
            $success = "Discord connection successful! Server: " . $test_result['server_name'];
        } else {
            $error = "Discord connection failed: " . $test_result['error'];
        }
    }
}

// Get current Discord settings
$discord_settings = [];
$stmt = $db->prepare("SELECT name, value FROM settings WHERE name LIKE 'discord_%'");
$stmt->execute();
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $setting) {
    $discord_settings[$setting['name']] = $setting['value'];
}

// Get all projects with their Discord role settings
$projects = $db->query("SELECT id, title, discord_accepted_role_id, discord_pizza_role_id FROM projects ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);

// Get all events with their Discord role settings
$events = $db->query("SELECT id, title, discord_participated_role_id FROM events ORDER BY start_datetime DESC")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Discord Settings';
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Discord Settings</h2>
                <p class="text-gray-600 dark:text-gray-300 mt-1">Configure Discord OAuth, bot settings, and role IDs for projects and events</p>
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

    <!-- Discord Bot Settings -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Discord Configuration</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure Discord OAuth, bot, and role settings</p>
        </div>
        <div class="p-6">
            <form method="POST" class="space-y-6">
                <!-- Discord OAuth Settings -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">OAuth Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client ID</label>
                            <input type="text" name="client_id" 
                                   value="<?= htmlspecialchars($discord_settings['discord_client_id'] ?? '') ?>"
                                   placeholder="Your Discord Application Client ID"
                                   class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client Secret</label>
                            <div class="mt-1 relative">
                                <input type="password" name="client_secret" id="client_secret"
                                       value="<?= htmlspecialchars($discord_settings['discord_client_secret'] ?? '') ?>"
                                       placeholder="Your Discord Application Client Secret"
                                       class="block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 pr-10 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                                <button type="button" onclick="toggleVisibility('client_secret')" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Redirect URI</label>
                            <input type="url" name="redirect_uri" 
                                   value="<?= htmlspecialchars($discord_settings['discord_redirect_uri'] ?? '') ?>"
                                   placeholder="https://yourdomain.com/auth/discord/"
                                   class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Discord Bot Settings -->
                <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Bot Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bot Token</label>
                            <div class="mt-1 relative">
                                <input type="password" name="bot_token" id="bot_token"
                                       value="<?= htmlspecialchars($discord_settings['discord_bot_token'] ?? '') ?>"
                                       placeholder="Your Discord Bot Token"
                                       class="block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 pr-10 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                                <button type="button" onclick="toggleVisibility('bot_token')" 
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Guild ID</label>
                            <input type="text" name="guild_id" 
                                   value="<?= htmlspecialchars($discord_settings['discord_guild_id'] ?? '') ?>"
                                   placeholder="Your Discord Server ID"
                                   class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Discord Role IDs -->
                <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">User Role IDs</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Member Role ID</label>
                            <input type="text" name="member_role_id" 
                                   value="<?= htmlspecialchars($discord_settings['discord_member_role_id'] ?? '') ?>"
                                   placeholder="Role ID for Members"
                                   class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Co-leader Role ID</label>
                            <input type="text" name="co_leader_role_id" 
                                   value="<?= htmlspecialchars($discord_settings['discord_co_leader_role_id'] ?? '') ?>"
                                   placeholder="Role ID for Co-leaders"
                                   class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Leader Role ID</label>
                            <input type="text" name="leader_role_id" 
                                   value="<?= htmlspecialchars($discord_settings['discord_leader_role_id'] ?? '') ?>"
                                   placeholder="Role ID for Leaders"
                                   class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">These roles will be automatically assigned based on user roles in the system</p>
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="submit" name="update_discord_settings"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Save All Settings
                    </button>
                    <button type="submit" name="test_discord_connection"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Test Bot Connection
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Project Roles -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Project Discord Roles</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure role IDs for project acceptance and pizza grants</p>
        </div>
        <div class="p-6">
            <?php if (empty($projects)): ?>
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No projects found. Create some projects first.</p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($projects as $project): ?>
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                
                                <div class="flex items-center justify-between">
                                    <h4 class="text-md font-medium text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($project['title']) ?>
                                    </h4>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Accepted Role ID</label>
                                        <input type="text" name="accepted_role_id" 
                                               value="<?= htmlspecialchars($project['discord_accepted_role_id'] ?? '') ?>"
                                               placeholder="Role ID for accepted users"
                                               class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pizza Grant Role ID</label>
                                        <input type="text" name="pizza_role_id" 
                                               value="<?= htmlspecialchars($project['discord_pizza_role_id'] ?? '') ?>"
                                               placeholder="Role ID for pizza grant recipients"
                                               class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white text-sm">
                                    </div>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" name="update_project_roles"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                        Update Role IDs
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Event Roles -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Event Discord Roles</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure role IDs for event participation</p>
        </div>
        <div class="p-6">
            <?php if (empty($events)): ?>
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No events found. Create some events first.</p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($events as $event): ?>
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                
                                <div class="flex items-center justify-between">
                                    <h4 class="text-md font-medium text-gray-900 dark:text-white">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </h4>
                                </div>
                                
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Participated Role ID</label>
                                        <input type="text" name="participated_role_id" 
                                               value="<?= htmlspecialchars($event['discord_participated_role_id'] ?? '') ?>"
                                               placeholder="Role ID for event participants"
                                               class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary dark:bg-gray-700 dark:text-white text-sm">
                                    </div>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" name="update_event_roles"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                        Update Role ID
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Setup Helper -->
    <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-6">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Discord Setup Instructions</h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300 space-y-4">
                    <div>
                        <h4 class="font-medium">OAuth Application Setup:</h4>
                        <ol class="list-decimal list-inside space-y-1 mt-1">
                            <li>Go to <a href="https://discord.com/developers/applications" target="_blank" class="underline">Discord Developer Portal</a></li>
                            <li>Create a new application or select existing one</li>
                            <li>Copy the Client ID and Client Secret from the OAuth2 section</li>
                            <li>Add your redirect URI in the OAuth2 redirects section</li>
                        </ol>
                    </div>
                    <div>
                        <h4 class="font-medium">Bot Setup:</h4>
                        <ol class="list-decimal list-inside space-y-1 mt-1">
                            <li>In the same application, go to the Bot section</li>
                            <li>Create a bot and copy the Bot Token</li>
                            <li>Enable the necessary intents (Server Members Intent, Message Content Intent)</li>
                            <li>Invite the bot to your server with appropriate permissions</li>
                        </ol>
                    </div>
                    <div>
                        <h4 class="font-medium">Getting Role IDs:</h4>
                        <ol class="list-decimal list-inside space-y-1 mt-1">
                            <li>Enable Developer Mode in Discord: User Settings → Advanced → Developer Mode</li>
                            <li>Go to your Discord server → Server Settings → Roles</li>
                            <li>Right-click on any role and select "Copy ID"</li>
                            <li>Paste the ID into the appropriate field above</li>
                        </ol>
                    </div>
                    <div>
                        <h4 class="font-medium">Role Synchronization:</h4>
                        <p class="mt-1">Role synchronization is now handled by a separate Discord bot. Configure the role IDs above and the bot will automatically sync roles based on project assignments and event participation.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
    } else {
        field.type = 'password';
    }
}
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>