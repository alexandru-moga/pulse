<?php
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/classes/DiscordOAuth.php';

global $db, $settings;

try {
    $discord = new DiscordOAuth($db);

    if (!$discord->isConfigured()) {
        throw new Exception('Discord OAuth is not configured');
    }

    // Get verification token from URL
    $token = $_GET['token'] ?? '';

    if (empty($token)) {
        $_SESSION['error'] = 'No verification token provided.';
        header('Location: ' . $settings['site_url'] . '/login.php');
        exit;
    }

    // Validate verification token
    $stmt = $db->prepare("
        SELECT discord_id, discord_username, expires_at, used, user_id 
        FROM discord_verification_tokens 
        WHERE token = ?
    ");
    $stmt->execute([$token]);
    $verification = $stmt->fetch();

    if (!$verification) {
        $_SESSION['error'] = 'Invalid verification token.';
        header('Location: ' . $settings['site_url'] . '/login.php');
        exit;
    }

    if ($verification['used']) {
        $_SESSION['error'] = 'This verification token has already been used.';
        header('Location: ' . $settings['site_url'] . '/login.php');
        exit;
    }

    if (strtotime($verification['expires_at']) < time()) {
        $_SESSION['error'] = 'This verification token has expired. Please request a new one from Discord.';
        header('Location: ' . $settings['site_url'] . '/login.php');
        exit;
    }

    // Store verification info in session
    $_SESSION['discord_verification'] = [
        'token' => $token,
        'discord_id' => $verification['discord_id'],
        'discord_username' => $verification['discord_username']
    ];

    // If user is already logged in, proceed to link Discord
    if (isLoggedIn()) {
        // Check if Discord account is already linked
        $stmt = $db->prepare("SELECT user_id FROM discord_links WHERE discord_id = ?");
        $stmt->execute([$verification['discord_id']]);
        $existingLink = $stmt->fetch();

        if ($existingLink) {
            // Already linked
            if ($existingLink['user_id'] == $_SESSION['user_id']) {
                $_SESSION['success'] = 'Your Discord account is already linked!';
            } else {
                $_SESSION['error'] = 'This Discord account is already linked to another user.';
            }
            header('Location: ' . $settings['site_url'] . '/dashboard/profile-edit.php');
            exit;
        }

        // Redirect to Discord OAuth to complete the linking
        $_SESSION['discord_from_verification'] = true;
        header('Location: ' . $settings['site_url'] . '/auth/discord/?action=link&from=verify');
        exit;
    }

    // User is not logged in, redirect to login page
    $_SESSION['info'] = 'Please login to your Phoenix Club account to link your Discord.';
    $_SESSION['redirect_after_login'] = '/auth/discord/?action=link&from=verify';
    header('Location: ' . $settings['site_url'] . '/login.php');
    exit;
} catch (Exception $e) {
    error_log("Discord verification error: " . $e->getMessage());
    $_SESSION['error'] = 'An error occurred during verification: ' . $e->getMessage();
    header('Location: ' . $settings['site_url'] . '/login.php');
    exit;
}
