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

if (empty($code) || empty($state)) {
    $_SESSION['hackclub_error'] = 'Missing authorization code or state';
    header('Location: /dashboard/login.php');
    exit();
}

try {
    $result = $hackclub->handleCallback($code, $state);
    
    if ($result === null) {
        // Login failed, error message already set in session
        header('Location: /dashboard/login.php');
        exit();
    }
    
    // Check if this was a login or account linking
    if (isset($result['success']) && $result['success']) {
        // Account linking successful
        $_SESSION['account_link_success'] = 'Hack Club account linked successfully!';
        header('Location: /dashboard/profile-edit.php');
    } else {
        // Login successful
        header('Location: /dashboard/index.php');
    }
    exit();
    
} catch (Exception $e) {
    error_log("Hack Club OAuth callback error: " . $e->getMessage());
    $_SESSION['hackclub_error'] = 'Authentication failed: ' . $e->getMessage();
    header('Location: /dashboard/login.php');
    exit();
}
