<?php
require_once __DIR__ . '/../../core/init.php';
require_once __DIR__ . '/../../core/classes/HackClubOAuth.php';

global $db;

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
    $isLogin = ($_GET['action'] ?? 'link') === 'login';
    error_log("Hack Club OAuth: Starting flow, isLogin: " . ($isLogin ? 'true' : 'false'));
    $authUrl = $hackclub->generateAuthUrl($isLogin);
    header('Location: ' . $authUrl);
    exit();
}

try {
    error_log("Hack Club OAuth: Before handleCallback, session hackclub_is_login: " . ($_SESSION['hackclub_is_login'] ?? 'not set'));
    $result = $hackclub->handleCallback($code, $state);
    error_log("Hack Club OAuth: handleCallback result: " . json_encode($result));
    
    if ($result === null) {
        // Login failed, error message already set in session
        error_log("Hack Club OAuth: Result is null, redirecting to login");
        header('Location: /dashboard/login.php');
        exit();
    }
    
    // Check if this was a login or account linking
    if (isset($result['success']) && $result['success']) {
        // Account linking successful
        error_log("Hack Club OAuth: Account linking successful");
        $_SESSION['account_link_success'] = 'Hack Club account linked successfully!';
        header('Location: /dashboard/profile-edit.php');
    } else {
        // Login successful
        error_log("Hack Club OAuth: Login successful, redirecting to dashboard");
        header('Location: /dashboard/index.php');
    }
    exit();
    
} catch (Exception $e) {
    error_log("Hack Club OAuth callback error: " . $e->getMessage());
    $_SESSION['hackclub_error'] = 'Authentication failed: ' . $e->getMessage();
    header('Location: /dashboard/login.php');
    exit();
}
