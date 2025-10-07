<?php
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/classes/SlackOAuth.php';

global $db, $settings;

try {
    $slack = new SlackOAuth($db);
    
    if (!$slack->isConfigured()) {
        $_SESSION['account_error'] = 'Slack OAuth is not configured. Please contact an administrator.';
        header('Location: ' . $settings['site_url'] . '/dashboard/');
        exit;
    }
    if (isset($_GET['code']) && isset($_GET['state'])) {
        $result = $slack->handleCallback($_GET['code'], $_GET['state']);
        
        if ($result['success']) {
            if ($result['action'] === 'login') {
                header('Location: ' . $settings['site_url'] . '/dashboard/');
            } else {
                $_SESSION['account_link_success'] = 'Slack account linked successfully!';
                header('Location: ' . $settings['site_url'] . '/dashboard/profile-edit.php');
            }
        } else {
            if (isset($_SESSION['slack_oauth_action']) && $_SESSION['slack_oauth_action'] === 'login') {
                $_SESSION['slack_error'] = $result['error'];
                header('Location: ' . $settings['site_url'] . '/dashboard/login.php');
            } else {
                $_SESSION['account_error'] = $result['error'];
                header('Location: ' . $settings['site_url'] . '/dashboard/profile-edit.php');
            }
        }
        exit;
    }
    if (isset($_GET['error'])) {
        $_SESSION['account_error'] = 'Slack OAuth was cancelled or failed.';
        header('Location: ' . $settings['site_url'] . '/dashboard/profile-edit.php');
        exit;
    }
    $isLogin = ($_GET['action'] ?? 'link') === 'login';
    $authUrl = $slack->generateAuthUrl($isLogin);
    header('Location: ' . $authUrl);
    exit;
    
} catch (Exception $e) {
    error_log('Slack OAuth Error: ' . $e->getMessage());
    $_SESSION['account_error'] = 'An error occurred during Slack authentication.';
    header('Location: ' . $settings['site_url'] . '/dashboard/profile-edit.php');
    exit;
}
?>