<?php
if (session_status() === PHP_SESSION_NONE) session_start();
global $db, $currentUser, $settings;

if (!isset($currentUser) || !$currentUser) {
    header('Location: /dashboard/login.php');
    exit;
}

$isLoggedIn = isset($currentUser) && $currentUser;
$role = $isLoggedIn ? $currentUser->role : 'guest';

function page_is_visible($page, $role) {
    if (empty($page['visibility'])) return true;
    foreach (explode(',', $page['visibility']) as $vis) {
        if (strcasecmp(trim($vis), $role) === 0) return true;
    }
    return false;
}

$allPages = $db->query("SELECT * FROM pages ORDER BY id ASC")->fetchAll();

$dashboardPage = null;
foreach ($allPages as $p) {
    if ($p['name'] === 'dashboard') {
        $dashboardPage = $p;
        break;
    }
}
$dashboardPageId = $dashboardPage['id'] ?? null;

$dashboardChildren = [];
if ($dashboardPageId) {
    foreach ($allPages as $p) {
        if (($p['parent_id'] ?? null) == $dashboardPageId && page_is_visible($p, $role)) {
            $dashboardChildren[] = $p;
        }
    }
}

$currentPath = $_SERVER['REQUEST_URI'];
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard - PULSE') ?></title>
    <link rel="icon" type="image/x-icon" href="<?= $settings['site_url'] ?>/images/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#ef4444',
                        secondary: '#1f2937',
                        accent: '#f59e0b'
                    }
                }
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <div class="w-64 bg-white shadow-lg flex flex-col">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <img src="<?= $settings['site_url'] ?>/images/logo.svg" alt="PULSE" class="h-8 w-8">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">PULSE</h1>
                        <p class="text-sm text-gray-500">Dashboard</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 custom-scrollbar overflow-y-auto">
                <ul class="space-y-2">
                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/" 
                           class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'index.php') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6a2 2 0 01-2 2H10a2 2 0 01-2-2V5z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/projects.php" 
                           class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'projects.php') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            My Projects
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/events.php" 
                           class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'events.php') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Events
                        </a>
                    </li>

                    <?php if (in_array($role, ['Leader', 'Co-leader'])): ?>
                    <li class="pt-4">
                        <div class="px-4 py-2">
                            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Administration</h3>
                        </div>
                    </li>
                    
                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/projects-management.php" 
                           class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= (in_array($currentFile, ['projects-management.php', 'create-project.php', 'edit-project.php', 'project-user-matrix.php'])) ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Manage Projects
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/import-projects.php" 
                           class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'import-projects.php') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            Import YSWS
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/users.php" 
                           class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'users.php') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Users
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/applications.php" 
                           class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'applications.php') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Applications
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/contact_messages.php" 
                           class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'contact_messages.php') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Messages
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/manage-events.php" 
                           class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'manage-events.php') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Manage Events
                        </a>
                    </li>
                    
                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/settings.php" 
                           class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'settings.php') ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Settings
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Quick Actions</h3>
                    <ul class="mt-3 space-y-2">
                        <li>
                            <a href="<?= $settings['site_url'] ?>/" target="_blank"
                               class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                View Site
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium">
                            <?= strtoupper(substr($currentUser->first_name ?? 'U', 0, 1)) ?>
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            <?= htmlspecialchars($currentUser->first_name ?? 'User') ?> <?= htmlspecialchars($currentUser->last_name ?? '') ?>
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            <?= htmlspecialchars($currentUser->role ?? 'Member') ?>
                        </p>
                    </div>
                </div>
                <a href="<?= $settings['site_url'] ?>/dashboard/logout.php" 
                   class="flex items-center w-full px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout
                </a>
            </div>
        </div>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-gray-900">
                            <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
                        </h1>
                        <div class="flex items-center space-x-4">
                            <button class="p-2 text-gray-400 hover:text-gray-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6a2 2 0 012 2v10a2 2 0 01-2 2H9a2 2 0 01-2-2V9a2 2 0 012-2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <?php if (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] === '1'): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Maintenance Mode Active:</strong> The website is currently in maintenance mode. Only Leaders and Co-leaders can access the site.
                                <a href="<?= $settings['site_url'] ?>/dashboard/site-settings.php" class="font-medium underline hover:text-yellow-800">
                                    Disable maintenance mode
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
