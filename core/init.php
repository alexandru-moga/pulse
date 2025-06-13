<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'pulse');
define('DB_USER', 'root');
define('DB_PASS', '');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', realpath(__DIR__.'/..'));
}

spl_autoload_register(function ($className) {
    $file = ROOT_DIR . '/core/classes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

try {
    $db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$settings = [];
foreach ($db->query("SELECT name, value FROM settings") as $row) {
    $settings[$row['name']] = $row['value'];
}

$pageManager = new PageManager($db);

$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');

try {
    $pageStructure = $pageManager->getPageStructure($currentPage);
    $pageTitle = $pageStructure['meta']['title'] ?? 'PULSE';
    $pageDescription = $pageStructure['meta']['description'] ?? 'Programming University Learning & Software Engineering';
} catch (Exception $e) {
    die("Page load error: " . $e->getMessage());
}

session_start();

$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $currentUser = User::getById($_SESSION['user_id']);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    global $currentUser;
    return $currentUser && $currentUser->role === 'admin';
}

function checkLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /dashboard/login.php");
        exit();
    }
}

function checkRole($allowedRoles) {
    global $currentUser;
    if (!$currentUser || !in_array($currentUser->role, (array)$allowedRoles)) {
        header("HTTP/1.1 403 Forbidden");
        exit("Access denied");
    }
}
