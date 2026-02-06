<?php
require_once '../core/init.php';

global $db, $settings;

$token = $_GET['token'] ?? '';

if (!$token) {
    $_SESSION['error'] = 'No login token provided.';
    header('Location: login.php');
    exit();
}

// Check if token exists and is valid
// Use UTC_TIMESTAMP() to match PHP's UTC timezone
$stmt = $db->prepare("SELECT * FROM email_login_tokens WHERE token = ? AND expires_at > UTC_TIMESTAMP() AND used = 0");
$stmt->execute([$token]);
$tokenData = $stmt->fetch();

if (!$tokenData) {
    $_SESSION['error'] = 'Invalid or expired login link. Please request a new one.';
    header('Location: login.php');
    exit();
}

// Get user information
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$tokenData['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'User not found.';
    header('Location: login.php');
    exit();
}

// Mark token as used
$db->prepare("UPDATE email_login_tokens SET used = 1 WHERE id = ?")
    ->execute([$tokenData['id']]);

// Delete old/expired tokens for this user
$db->prepare("DELETE FROM email_login_tokens WHERE user_id = ? AND (expires_at < UTC_TIMESTAMP() OR used = 1)")
    ->execute([$user['id']]);

// Log the user in
$_SESSION['user_id'] = $user['id'];
error_log("Email login successful for user " . $user['id'] . " (active: " . $user['active_member'] . ")");

// Check for redirect after login
if (isset($_SESSION['redirect_after_login'])) {
    $redirect = $_SESSION['redirect_after_login'];
    error_log("Redirect after login found: " . $redirect);
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . $redirect);
} else {
    error_log("No redirect after login, going to dashboard index");
    header('Location: index.php');
}
exit();
