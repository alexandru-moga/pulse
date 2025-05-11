<?php
require_once __DIR__ . '/config.php';

// core/init.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define root directory once
if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', realpath(__DIR__.'/..'));
}

// Register autoloader
spl_autoload_register(function ($className) {
    $file = ROOT_DIR . '/core/classes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize database
try {
    $db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize PageManager
$pageManager = new PageManager($db);

// Get current page
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');

// Load page structure
try {
    $pageStructure = $pageManager->getPageStructure($currentPage);
    $pageTitle = $pageStructure['meta']['title'] ?? 'PULSE';
    $pageDescription = $pageStructure['meta']['description'] ?? 'Programming University Learning & Software Engineering';
} catch (Exception $e) {
    die("Page load error: " . $e->getMessage());
}

// Session management
session_start();

// Check authenticated user
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = User::getById($_SESSION['user_id']);
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    global $currentUser;
    return $currentUser && $currentUser->role === 'admin';
}
