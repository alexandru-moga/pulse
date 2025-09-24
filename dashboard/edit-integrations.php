<?php
require_once '../core/init.php';
require_once __DIR__ . '/../core/classes/DiscordOAuth.php';
require_once __DIR__ . '/../core/classes/GitHubOAuth.php';
require_once __DIR__ . '/../core/classes/GoogleOAuth.php';
require_once __DIR__ . '/../core/classes/SlackOAuth.php';

global $db, $currentUser, $settings;

if (!isLoggedIn() || !$currentUser) {
    header('Location: /dashboard/login.php');
    exit;
}
if (isset($_SESSION['account_link_success'])) {
    header('Location: edit-integrations.php?success=' . urlencode($_SESSION['account_link_success']));
    unset($_SESSION['account_link_success']);
    exit;
}

if (isset($_SESSION['account_error'])) {
    header('Location: edit-integrations.php?error=' . urlencode($_SESSION['account_error']));
    unset($_SESSION['account_error']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'unlink_google':
            $googleOAuth = new GoogleOAuth($db);
            $googleOAuth->unlinkGoogleAccount($currentUser->id);
            $success = "Google account unlinked successfully";
            break;

        case 'unlink_discord':
            $stmt = $db->prepare("DELETE FROM discord_links WHERE user_id = ?");
            $stmt->execute([$currentUser->id]);
            $success = "Discord account unlinked successfully";
            break;

        case 'unlink_github':
            $stmt = $db->prepare("DELETE FROM github_links WHERE user_id = ?");
            $stmt->execute([$currentUser->id]);
            $success = "GitHub account unlinked successfully";
            break;

        case 'unlink_slack':
            $stmt = $db->prepare("DELETE FROM slack_links WHERE user_id = ?");
            $stmt->execute([$currentUser->id]);
            $success = "Slack account unlinked successfully";
            break;
    }

    if (isset($success)) {
        header('Location: edit-integrations.php?success=' . urlencode($success));
        exit;
    }
}

// Initialize OAuth classes and check configuration
$discord = new DiscordOAuth($db);
$github = new GitHubOAuth($db);
$google = new GoogleOAuth($db);
$slack = new SlackOAuth($db);

// Check if integrations are configured
$discordConfigured = $discord->isConfigured();
$githubConfigured = $github->isConfigured();
$googleConfigured = $google->isConfigured();
$slackConfigured = $slack->isConfigured();

// Get user links
$googleOAuth = new GoogleOAuth($db);
$googleLink = $googleOAuth->getUserGoogleLink($currentUser->id);

$stmt = $db->prepare("SELECT * FROM discord_links WHERE user_id = ?");
$stmt->execute([$currentUser->id]);
$discordLink = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM github_links WHERE user_id = ?");
$stmt->execute([$currentUser->id]);
$githubLink = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM slack_links WHERE user_id = ?");
$stmt->execute([$currentUser->id]);
$slackLink = $stmt->fetch();

$pageTitle = "Edit Integrations";
include 'components/dashboard-header.php';
?>

<?php if (isset($_GET['success'])): ?>
    <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800 dark:text-green-200"><?= htmlspecialchars($_GET['success']) ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800 dark:text-red-200"><?= htmlspecialchars($_GET['error']) ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="space-y-6">
    <?php if ($googleConfigured): ?>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Google</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <?php if ($googleLink): ?>
                                    Connected as <?= htmlspecialchars($googleLink['google_email']) ?>
                                <?php else: ?>
                                    Not connected
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <?php if ($googleLink): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="unlink_google">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded-md" onclick="return confirm('Are you sure you want to unlink your Google account?')">
                                    Unlink
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="../auth/google/?action=link" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-md">
                                Connect Google
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($discordConfigured): ?>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="#5865F2">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515a.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0a12.64 12.64 0 0 0-.617-1.25a.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057a19.9 19.9 0 0 0 5.993 3.03a.078.078 0 0 0 .084-.028a14.09 14.09 0 0 0 1.226-1.994a.076.076 0 0 0-.041-.106a13.107 13.107 0 0 1-1.872-.892a.077.077 0 0 1-.008-.128a10.2 10.2 0 0 0 .372-.292a.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127a12.299 12.299 0 0 1-1.873.892a.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028a19.839 19.839 0 0 0 6.002-3.03a.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.956-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.955-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.946 2.418-2.157 2.418z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Discord</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <?php if ($discordLink): ?>
                                    Connected as <?= htmlspecialchars($discordLink['discord_username']) ?>
                                <?php else: ?>
                                    Not connected
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <?php if ($discordLink): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="unlink_discord">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded-md" onclick="return confirm('Are you sure you want to unlink your Discord account?')">
                                    Unlink
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="../auth/discord/?action=link" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 px-4 rounded-md">
                                Connect Discord
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($githubConfigured): ?>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8" fill="#181717" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">GitHub</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <?php if ($githubLink): ?>
                                    Connected as <?= htmlspecialchars($githubLink['github_username']) ?>
                                <?php else: ?>
                                    Not connected
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <?php if ($githubLink): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="unlink_github">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded-md" onclick="return confirm('Are you sure you want to unlink your GitHub account?')">
                                    Unlink
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="../auth/github/?action=link" class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-4 rounded-md">
                                Connect GitHub
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($slackConfigured): ?>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8" viewBox="0 0 24 24">
                                <path fill="#E01E5A" d="M5.042 15.165a2.528 2.528 0 0 1-2.52 2.523A2.528 2.528 0 0 1 0 15.165a2.527 2.527 0 0 1 2.522-2.52h2.52v2.52z" />
                                <path fill="#E01E5A" d="M6.313 15.165a2.527 2.527 0 0 1 2.521-2.52 2.527 2.527 0 0 1 2.521 2.52v6.313A2.528 2.528 0 0 1 8.834 24a2.528 2.528 0 0 1-2.521-2.522v-6.313z" />
                                <path fill="#36C5F0" d="M8.834 5.042a2.528 2.528 0 0 1-2.521-2.52A2.528 2.528 0 0 1 8.834 0a2.528 2.528 0 0 1 2.521 2.522v2.52H8.834z" />
                                <path fill="#36C5F0" d="M8.834 6.313a2.528 2.528 0 0 1 2.521 2.521 2.528 2.528 0 0 1-2.521 2.521H2.522A2.528 2.528 0 0 1 0 8.834a2.528 2.528 0 0 1 2.522-2.521h6.312z" />
                                <path fill="#2EB67D" d="M18.956 8.834a2.528 2.528 0 0 1 2.522-2.521A2.528 2.528 0 0 1 24 8.834a2.528 2.528 0 0 1-2.522 2.521h-2.522V8.834z" />
                                <path fill="#2EB67D" d="M17.688 8.834a2.528 2.528 0 0 1-2.523 2.521 2.527 2.527 0 0 1-2.52-2.521V2.522A2.527 2.527 0 0 1 15.165 0a2.528 2.528 0 0 1 2.523 2.522v6.312z" />
                                <path fill="#ECB22E" d="M15.165 18.956a2.528 2.528 0 0 1 2.523 2.522A2.528 2.528 0 0 1 15.165 24a2.527 2.527 0 0 1-2.52-2.522v-2.522h2.52z" />
                                <path fill="#ECB22E" d="M15.165 17.688a2.527 2.527 0 0 1-2.52-2.523 2.526 2.526 0 0 1 2.52-2.52h6.313A2.527 2.527 0 0 1 24 15.165a2.528 2.528 0 0 1-2.522 2.523h-6.313z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Slack</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <?php if ($slackLink): ?>
                                    Connected as <?= htmlspecialchars($slackLink['slack_username']) ?>
                                <?php else: ?>
                                    Not connected
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <?php if ($slackLink): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="action" value="unlink_slack">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded-md" onclick="return confirm('Are you sure you want to unlink your Slack workspace?')">
                                    Unlink
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="../auth/slack/?action=link" class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium py-2 px-4 rounded-md">
                                Connect Slack
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

</div>
</main>
</div>
</div>

<script>
    const darkModeToggle = document.getElementById('darkModeToggle');
    const html = document.documentElement;
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.classList.toggle('dark', savedTheme === 'dark');

    darkModeToggle.addEventListener('click', () => {
        html.classList.toggle('dark');
        const isDark = html.classList.contains('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });
</script>
</body>

</html>