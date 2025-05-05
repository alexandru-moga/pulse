<?php

define('CORE_PATH', __DIR__ . '/../core');
require_once __DIR__ . '/config.php';

// Start session
session_name(SESSION_NAME);
session_start();

// Include configuration
require_once 'config.php';

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load core classes
spl_autoload_register(function($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize database connection
$db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check if user is logged in
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = User::getById($_SESSION['user_id']);
}

// Helper functions
function redirect($location) {
    header("Location: $location");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    global $currentUser;
    return $currentUser && $currentUser->role === 'admin';
}