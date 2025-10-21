<?php
require_once '../core/init.php';
checkActiveOrLimitedAccess();

global $currentUser, $db;

// Log for debugging
error_log("discord-linked.php accessed by user: " . ($currentUser ? $currentUser->id : 'none'));
error_log("Session account_link_success: " . ($_SESSION['account_link_success'] ?? 'not set'));

// Check if there's a success message
$success = $_SESSION['account_link_success'] ?? null;

// Don't unset immediately - keep it for the page to use
// unset($_SESSION['account_link_success']);

// If no success message, it means they accessed this page directly or session expired
// Instead of redirecting, just show a generic success message
if (!$success) {
    error_log("No success message in session, checking if user has Discord linked");
    // Check if user has Discord linked
    $stmt = $db->prepare("SELECT discord_id FROM discord_links WHERE user_id = ?");
    $stmt->execute([$currentUser->id]);
    $hasDiscord = $stmt->fetch();

    if (!$hasDiscord) {
        // Not linked, redirect to dashboard
        error_log("User doesn't have Discord linked, redirecting to dashboard");
        header('Location: /dashboard/');
        exit();
    }

    // Has Discord linked, show generic success
    $success = 'Discord account linked successfully!';
}

// Clear the session message after we've used it
unset($_SESSION['account_link_success']);

$pageTitle = 'Discord Linked Successfully';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#ec4a0a',
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6">
                <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                Discord Linked Successfully!
            </h1>

            <!-- Message -->
            <p class="text-gray-600 mb-6">
                Your Discord account has been successfully linked to your Phoenix Club profile. Your roles are being synced automatically.
            </p>

            <!-- Discord Info -->
            <?php
            // Get Discord info if available
            $stmt = $db->prepare("
                SELECT discord_username, discord_id 
                FROM discord_links 
                WHERE user_id = ?
            ");
            $stmt->execute([$currentUser->id]);
            $discordInfo = $stmt->fetch();
            ?>

            <?php if ($discordInfo): ?>
                <div class="bg-indigo-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-center space-x-3">
                        <svg class="h-8 w-8 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515a.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0a12.64 12.64 0 0 0-.617-1.25a.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057a19.9 19.9 0 0 0 5.993 3.03a.078.078 0 0 0 .084-.028a14.09 14.09 0 0 0 1.226-1.994a.076.076 0 0 0-.041-.106a13.107 13.107 0 0 1-1.872-.892a.077.077 0 0 1-.008-.128a10.2 10.2 0 0 0 .372-.292a.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127a12.299 12.299 0 0 1-1.873.892a.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028a19.839 19.839 0 0 0 6.002-3.03a.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.956-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.955-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.946 2.418-2.157 2.418z" />
                        </svg>
                        <div class="text-left">
                            <p class="text-sm font-medium text-indigo-900">
                                <?= htmlspecialchars($discordInfo['discord_username']) ?>
                            </p>
                            <p class="text-xs text-indigo-600">
                                Linked Account
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Close Instructions -->
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-gray-700 font-medium">
                    You can now close this window and return to Discord.
                </p>
                <p class="text-sm text-gray-500 mt-2">
                    Your roles will be synced automatically within a few seconds.
                </p>
            </div>
        </div>
    </div>
</body>

</html>