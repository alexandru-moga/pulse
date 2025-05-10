<?php
// Load configuration FIRST
require_once __DIR__ . '/config.php';

// Initialize session with defined name
session_name(SESSION_NAME);
session_start();

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader for classes
spl_autoload_register(function($class) {
    $file = CORE_PATH . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize database connection
$db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Initialize page manager and navigation
$pageManager = new PageManager($db);
$navigation = new Navigation($db);

// Get current page information
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageData = $pageManager->getPageData($currentPage);

// Get page blocks
$pageBlocks = $pageManager->getPageBlocks($currentPage);

// Set default page title and description
if ($pageData) {
    $pageTitle = $pageData['title'] ?? SITE_WELCOME_TITLE;
    $pageDescription = $pageData['description'] ?? SITE_WELCOME_DESCRIPTION;
    $menuEnabled = $pageData['menu_enabled'];
} else {
    // Fallback to constants if defined
    $constantTitle = strtoupper($currentPage . '_TITLE');
    $constantDesc = strtoupper($currentPage . '_DESCRIPTION');
    
    $pageTitle = defined($constantTitle) ? constant($constantTitle) : SITE_WELCOME_TITLE;
    $pageDescription = defined($constantDesc) ? constant($constantDesc) : SITE_WELCOME_DESCRIPTION;
    $menuEnabled = true;
}

// Check logged-in user
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
