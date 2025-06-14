<?php

define('ROOT_DIR', realpath(__DIR__ . '/..'));

require_once __DIR__ . '../../lib/phpdotenv/src/Dotenv.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

spl_autoload_register(function ($class) {
    $dotenvPrefix = 'Dotenv\\';
    $dotenvDir = __DIR__ . '/../lib/phpdotenv/src/';
    if (str_starts_with($class, $dotenvPrefix)) {
        $file = $dotenvDir . str_replace('\\', '/', substr($class, strlen($dotenvPrefix))) . '.php';
        if (file_exists($file)) require $file;
        return;
    }

    $phpOptionPrefix = 'PhpOption\\';
    $phpOptionDir = __DIR__ . '/../lib/php-option/src/PhpOption/';
    if (str_starts_with($class, $phpOptionPrefix)) {
        $file = $phpOptionDir . substr($class, strlen($phpOptionPrefix)) . '.php';
        if (file_exists($file)) require $file;
        return;
    }

    $resultTypePrefix = 'GrahamCampbell\\ResultType\\';
    $resultTypeDir = __DIR__ . '/../lib/result-type/src/ResultType/';
    if (str_starts_with($class, $resultTypePrefix)) {
        $file = $resultTypeDir . substr($class, strlen($resultTypePrefix)) . '.php';
        if (file_exists($file)) require $file;
        return;
    }

    $file = ROOT_DIR . '/core/classes/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);

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
