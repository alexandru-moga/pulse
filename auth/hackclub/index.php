<?php
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/classes/HackClubOAuth.php';

global $db, $settings;

$hackclub = new HackClubOAuth($db);

if (!$hackclub->isConfigured()) {
    $_SESSION['hackclub_error'] = 'Hack Club OAuth is not configured';
    header('Location: /dashboard/login.php');
    exit();
}

// Check for error from Hack Club
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    $errorDescription = $_GET['error_description'] ?? 'Unknown error';
    error_log("Hack Club OAuth error: $error - $errorDescription");
    
    $_SESSION['hackclub_error'] = 'Authentication failed: ' . htmlspecialchars($errorDescription);
    header('Location: /dashboard/login.php');
    exit();
}

// Get the authorization code and state
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

// If no code/state, this is an initial OAuth request (not a callback)
if (empty($code) || empty($state)) {
    $action = $_GET['action'] ?? 'link';
    $isLogin = $action === 'login';
    error_log("Hack Club OAuth: Starting flow, action='$action', isLogin: " . ($isLogin ? 'true' : 'false'));
    error_log("Hack Club OAuth: Current session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
    $authUrl = $hackclub->generateAuthUrl($isLogin);
    error_log("Hack Club OAuth: Generated auth URL, redirecting to Hack Club");
    header('Location: ' . $authUrl);
    exit();
}

try {
    error_log("Hack Club OAuth: Before handleCallback, session hackclub_is_login: " . ($_SESSION['hackclub_is_login'] ?? 'not set'));
    $result = $hackclub->handleCallback($code, $state);
    error_log("Hack Club OAuth: handleCallback result: " . json_encode($result));
    
    // DEBUG: Show result instead of redirecting
    if (isset($_GET['debug'])) {
        header('Content-Type: text/plain');
        echo "=== Hack Club OAuth Debug ===\n\n";
        echo "Result:\n";
        print_r($result);
        echo "\n\nSession user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
        echo "Logged in: " . (isLoggedIn() ? 'YES' : 'NO') . "\n";
        exit();
    }
    
    if ($result['success']) {
        error_log("Hack Club OAuth: Callback successful, action: " . $result['action']);
        if ($result['action'] === 'login') {
            header('Location: ' . $settings['site_url'] . '/dashboard/');
        } else {
            $_SESSION['account_link_success'] = '✅ Hack Club account linked successfully!';
            error_log("Hack Club OAuth: Redirecting to profile-edit.php");
            header('Location: ' . $settings['site_url'] . '/dashboard/profile-edit.php');
        }
    } else {
        error_log("Hack Club OAuth: Callback failed: " . ($result['error'] ?? 'Unknown error'));
        if ($result['action'] === 'login') {
            $_SESSION['hackclub_error'] = $result['error'] ?? 'Authentication failed';
            header('Location: ' . $settings['site_url'] . '/dashboard/login.php');
        } else {
            $_SESSION['account_error'] = $result['error'] ?? 'Failed to link account';
            header('Location: ' . $settings['site_url'] . '/dashboard/profile-edit.php');
        }
    }
    exit();
    
} catch (Exception $e) {
    error_log("Hack Club OAuth callback error: " . $e->getMessage());
    $_SESSION['hackclub_error'] = 'Authentication failed: ' . $e->getMessage();
    header('Location: /dashboard/login.php');
    exit();
}
