<?php
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/classes/DiscordOAuth.php';

global $db, $settings;

try {
    $discord = new DiscordOAuth($db);
    
    if (!$discord->isConfigured()) {
        throw new Exception('Discord OAuth is not configured');
    }
    
    if (isset($_GET['code']) && isset($_GET['state'])) {
        $result = $discord->handleCallback($_GET['code'], $_GET['state']);
        
        if ($result['success']) {
            if ($result['action'] === 'login') {
                header('Location: ' . $settings['site_url'] . '/dashboard/');
            } else {
                $_SESSION['account_link_success'] = 'Discord account linked successfully!';
                header('Location: ' . $settings['site_url'] . '/dashboard/edit-integrations.php');
            }
        } else {
            if (isset($_SESSION['discord_oauth_action']) && $_SESSION['discord_oauth_action'] === 'login') {
                $_SESSION['discord_error'] = $result['error'];
                header('Location: ' . $settings['site_url'] . '/dashboard/login.php');
            } else {
                $_SESSION['account_error'] = $result['error'];
                header('Location: ' . $settings['site_url'] . '/dashboard/edit-integrations.php');
            }
        }
        exit;
    }
    
    if (isset($_GET['error'])) {
        $_SESSION['account_error'] = 'Discord OAuth was cancelled or failed.';
        header('Location: ' . $settings['site_url'] . '/dashboard/edit-integrations.php');
        exit;
    }
    
    $isLogin = ($_GET['action'] ?? 'link') === 'login';
    $authUrl = $discord->generateAuthUrl($isLogin);
    header('Location: ' . $authUrl);
    exit;
    
} catch (Exception $e) {
    error_log('Discord OAuth Error: ' . $e->getMessage());
    $_SESSION['account_error'] = $e->getMessage();
    
    if (isLoggedIn()) {
        header('Location: ' . $settings['site_url'] . '/dashboard/edit-integrations.php');
    } else {
        header('Location: ' . $settings['site_url'] . '/dashboard/login.php');
    }
    exit;
}
?>
