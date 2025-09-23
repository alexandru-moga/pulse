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
    $pageTitle = $pageStructure['meta']['title'] ?? ($settings['site_title'] ?? 'Site');
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

function isActiveUser() {
    global $currentUser;
    return $currentUser && $currentUser->active_member == 1;
}

function isInactiveUser() {
    global $currentUser;
    return $currentUser && $currentUser->active_member == 0;
}

function isAdmin() {
    global $currentUser;
    return $currentUser && in_array($currentUser->role, ['Leader', 'Co-leader']);
}

function checkLoggedIn() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /dashboard/login.php");
        exit();
    }
}

function checkActiveUser() {
    global $currentUser;
    if (!$currentUser || $currentUser->active_member == 0) {
        header("HTTP/1.1 403 Forbidden");
        exit("Access denied - Account not active");
    }
}

function checkActiveOrLimitedAccess() {
    global $currentUser;
    if (!$currentUser) {
        header("Location: /dashboard/login.php");
        exit();
    }
    
    // Allow inactive users limited access to their own data
    if ($currentUser->active_member == 0) {
        // Define allowed pages for inactive users
        $allowedPages = [
            'index.php', 'profile-edit.php', 'change-password.php', 'logout.php',
            'certificates.php', 'download-manual-certificate.php', 'projects.php', 'events.php'
        ];
        
        $currentPage = basename($_SERVER['SCRIPT_NAME']);
        if (!in_array($currentPage, $allowedPages)) {
            header("HTTP/1.1 403 Forbidden");
            exit("Access denied - Limited access for inactive accounts. Contact administrator to reactivate your account.");
        }
    }
}

function checkRole($allowedRoles) {
    global $currentUser;
    if (!$currentUser || !in_array($currentUser->role, (array)$allowedRoles)) {
        header("HTTP/1.1 403 Forbidden");
        exit("Access denied");
    }
}

function getRequestScheme() {
    if (
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
        (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false) ||
        (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
        (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') ||
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    ) {
        return 'https';
    }
    
    if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'dev.alexandru-moga.me') !== false) {
        return 'https';
    }
    
    return 'http';
}

function checkMaintenanceMode() {
    global $settings, $currentUser;
    
    if (!isset($settings['maintenance_mode']) || $settings['maintenance_mode'] !== '1') {
        return;
    }
    
    if ($currentUser && in_array($currentUser->role, ['Leader', 'Co-leader'])) {
        return;
    }
    
    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Maintenance Mode - <?= htmlspecialchars($settings['site_title'] ?? 'PULSE') ?></title>
        <link rel="icon" type="image/x-icon" href="<?= $settings['site_url'] ?>/images/favicon.ico">
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            primary: '#ef4444'
                        }
                    }
                }
            }
        </script>
    </head>
    <body class="bg-gray-50">
        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8 text-center">
                <div>
                    <img src="<?= $settings['site_url'] ?>/images/logo.svg" alt="<?= htmlspecialchars($settings['site_title'] ?? 'Site') ?>" class="mx-auto h-16 w-16">
                    <h1 class="mt-6 text-3xl font-extrabold text-gray-900">Maintenance Mode</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        We're currently performing scheduled maintenance to improve your experience.
                    </p>
                </div>
                
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 dark:bg-yellow-900 rounded-full mb-4">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Site Under Maintenance</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Our website is temporarily unavailable while we make some improvements. 
                        Please check back shortly.
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Expected completion: Shortly
                    </p>
                </div>
                
                <div class="text-center">
                    <a href="<?= $settings['site_url'] ?>/dashboard/login.php" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Admin Login
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}
