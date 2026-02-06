<?php
if (session_status() === PHP_SESSION_NONE) session_start();
global $db, $currentUser, $settings;

if (!isset($currentUser) || !$currentUser) {
    header('Location: /dashboard/login.php');
    exit;
}

$isLoggedIn = isset($currentUser) && $currentUser;
$role = $isLoggedIn ? ($currentUser->role ?? 'Guest') : 'guest';

function page_is_visible($page, $role)
{
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
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard - ' . $settings['site_title']) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= $settings['site_url'] ?>/images/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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

        .dark .custom-scrollbar::-webkit-scrollbar-track {
            background: #1e293b;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #475569;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Ensure all backgrounds are properly themed */
        .dark .bg-white {
            background-color: #1f2937 !important;
        }

        .dark .text-gray-900 {
            color: #f9fafb !important;
        }

        .dark .text-gray-600 {
            color: #d1d5db !important;
        }

        .dark .text-gray-500 {
            color: #9ca3af !important;
        }

        .dark .border-gray-200 {
            border-color: #374151 !important;
        }

        .dark .bg-gray-50 {
            background-color: #111827 !important;
        }

        .dark .bg-gray-100 {
            background-color: #1f2937 !important;
        }

        .dark .hover\:bg-gray-50:hover {
            background-color: #374151 !important;
        }

        .dark .hover\:bg-gray-100:hover {
            background-color: #374151 !important;
        }

        .dark .bg-green-50 {
            background-color: #064e3b !important;
        }

        .dark .bg-red-50 {
            background-color: #7f1d1d !important;
        }

        .dark .bg-yellow-50 {
            background-color: #78350f !important;
        }

        .dark .bg-blue-50 {
            background-color: #1e3a8a !important;
        }

        .dark .text-green-800 {
            color: #10b981 !important;
        }

        .dark .text-red-800 {
            color: #f87171 !important;
        }

        .dark .text-yellow-800 {
            color: #fbbf24 !important;
        }

        .dark .text-blue-800 {
            color: #60a5fa !important;
        }

        .dark .border-green-200 {
            border-color: #065f46 !important;
        }

        .dark .border-red-200 {
            border-color: #991b1b !important;
        }

        .dark .border-yellow-200 {
            border-color: #92400e !important;
        }

        .dark .border-blue-200 {
            border-color: #1d4ed8 !important;
        }

        /* Sidebar Toggle Styles */
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }

        .sidebar-hidden {
            transform: translateX(-100%);
        }

        /* Hamburger animation */
        #sidebarToggle svg {
            transition: opacity 0.2s ease-in-out;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 50;
            }

            .sidebar-hidden {
                transform: translateX(-100%);
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
                transition: opacity 0.3s ease-in-out;
            }

            .main-content {
                margin-left: 0 !important;
            }
        }

        @media (min-width: 769px) {
            .sidebar {
                position: relative;
                transition: all 0.3s ease-in-out;
                flex-shrink: 0;
                width: 16rem;
                overflow: visible;
            }

            .sidebar-hidden {
                transform: translateX(-100%);
                width: 0;
                min-width: 0;
                overflow: hidden;
            }

            .main-content {
                margin-left: 0;
                transition: margin-left 0.3s ease-in-out;
                flex: 1;
                min-width: 0;
                /* Prevent flex item from overflowing */
            }

            .main-content-expanded {
                margin-left: 0;
            }
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900 h-full">
    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebarOverlay" class="sidebar-overlay hidden opacity-0"></div>

    <div class="flex h-screen">
        <div id="sidebar" class="sidebar w-64 bg-white dark:bg-gray-800 shadow-lg flex flex-col">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <img src="<?= $settings['site_url'] ?>/images/logo.svg" alt="<?= htmlspecialchars($settings['site_title']) ?>" class="h-8 w-8">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($settings['site_title']) ?></h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Dashboard</p>
                        </div>
                    </div>
                    <!-- Close button for mobile -->
                    <button id="sidebarClose" class="md:hidden p-2 text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 custom-scrollbar overflow-y-auto">
                <ul class="space-y-2">
                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/"
                            class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'index.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6a2 2 0 01-2 2H10a2 2 0 01-2-2V5z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/projects.php"
                            class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'projects.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            My Projects
                        </a>
                    </li>

                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/certificates.php"
                            class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'certificates.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            My Certificates
                        </a>
                    </li>

                    <li>
                        <a href="<?= $settings['site_url'] ?>/diplomas.php"
                            class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'diplomas.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                            Certificates & Diplomas
                        </a>
                    </li>

                    <li>
                        <a href="<?= $settings['site_url'] ?>/dashboard/events.php"
                            class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'events.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Events
                        </a>
                    </li>

                    <?php if ($currentUser && $currentUser->role != 'Guest'): ?>
                        <li>
                            <a href="<?= $settings['site_url'] ?>/dashboard/profile-edit.php"
                                class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'profile-edit.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Edit Profile
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (in_array($role, ['Leader', 'Co-leader']) && $currentUser && $currentUser->role != 'Guest'): ?>
                        <li class="pt-4">
                            <div class="px-4 py-2">
                                <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Administration</h3>
                            </div>
                        </li>

                        <li>
                            <a href="<?= $settings['site_url'] ?>/dashboard/projects-management.php"
                                class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= (in_array($currentFile, ['projects-management.php', 'create-project.php', 'edit-project.php', 'project-user-matrix.php'])) ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                Manage Projects
                            </a>
                        </li>

                        <li>
                            <a href="<?= $settings['site_url'] ?>/dashboard/users.php"
                                class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'users.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                Users
                            </a>
                        </li>

                        <li>
                            <a href="<?= $settings['site_url'] ?>/dashboard/applications.php"
                                class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'applications.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Applications
                            </a>
                        </li>

                        <li>
                            <a href="<?= $settings['site_url'] ?>/dashboard/contact_messages.php"
                                class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'contact_messages.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Messages
                            </a>
                        </li>

                        <li>
                            <a href="<?= $settings['site_url'] ?>/dashboard/manage-events.php"
                                class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'manage-events.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Manage Events
                            </a>
                        </li>

                        <li>
                            <a href="<?= $settings['site_url'] ?>/dashboard/settings.php"
                                class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'settings.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Settings
                            </a>
                        </li>

                        <li>
                            <a href="<?= $settings['site_url'] ?>/dashboard/certificate-management.php"
                                class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'certificate-management.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Certificate Management
                            </a>
                        </li>

                        <li>
                            <a href="<?= $settings['site_url'] ?>/dashboard/diploma-templates.php"
                                class="flex items-center px-4 py-2 text-sm font-medium rounded-lg <?= ($currentFile === 'diploma-templates.php') ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                </svg>
                                Certificate Templates
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quick Actions</h3>
                    <ul class="mt-3 space-y-2">
                        <li>
                            <a href="<?= $settings['site_url'] ?>/" target="_blank"
                                class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                View Site
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-3 mb-3">
                    <?php if (!empty($currentUser->profile_image)): ?>
                        <img src="<?= $settings['site_url'] ?>/images/members/<?= htmlspecialchars($currentUser->profile_image) ?>"
                            alt="<?= htmlspecialchars($currentUser->first_name ?? 'User') ?>"
                            class="w-8 h-8 rounded-full object-cover ring-2 ring-primary">
                    <?php else: ?>
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-medium">
                                <?= strtoupper(substr($currentUser->first_name ?? 'U', 0, 1)) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            <?= htmlspecialchars($currentUser->first_name ?? 'User') ?> <?= htmlspecialchars($currentUser->last_name ?? '') ?>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            <?= htmlspecialchars($currentUser->role ?? 'Member') ?>
                        </p>
                    </div>
                </div>
                <a href="<?= $settings['site_url'] ?>/dashboard/logout.php"
                    class="flex items-center w-full px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout
                </a>
            </div>
        </div>

        <div id="mainContent" class="main-content flex-1 flex flex-col overflow-hidden">
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Hamburger Menu Button -->
                            <button id="sidebarToggle" class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100 transition-colors">
                                <svg id="hamburgerIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                                <svg id="closeIcon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
                            </h1>
                        </div>
                        <div class="flex items-center space-x-4">
                            <button id="darkModeToggle" class="p-2 text-gray-400 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-100 transition-colors">
                                <svg id="lightIcon" class="w-6 h-6 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <svg id="darkIcon" class="w-6 h-6 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <?php if (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] === '1'): ?>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                <strong>Maintenance Mode Active:</strong> The website is currently in maintenance mode. Only Leaders and Co-leaders can access the site.
                                <a href="<?= $settings['site_url'] ?>/dashboard/site-settings.php" class="font-medium underline hover:text-yellow-800 dark:hover:text-yellow-100">
                                    Disable maintenance mode
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($currentUser && $currentUser->role == 'Guest'): ?>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Account Inactive</h3>
                            <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                Your account is currently inactive. You have limited access to view your own certificates, projects, and events. Please contact an administrator to reactivate your account for full access.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-900">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const sidebarToggle = document.getElementById('sidebarToggle');
                            const sidebarClose = document.getElementById('sidebarClose');
                            const sidebar = document.getElementById('sidebar');
                            const sidebarOverlay = document.getElementById('sidebarOverlay');
                            const mainContent = document.getElementById('mainContent');
                            const hamburgerIcon = document.getElementById('hamburgerIcon');
                            const closeIcon = document.getElementById('closeIcon');
                            const darkModeToggle = document.getElementById('darkModeToggle');

                            let sidebarOpen = false;

                            // Dark mode functionality
                            function initDarkMode() {
                                const savedTheme = localStorage.getItem('theme');
                                const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                                if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                                    document.documentElement.classList.add('dark');
                                } else {
                                    document.documentElement.classList.remove('dark');
                                }
                            }

                            function toggleDarkMode() {
                                const isDark = document.documentElement.classList.contains('dark');
                                if (isDark) {
                                    document.documentElement.classList.remove('dark');
                                    localStorage.setItem('theme', 'light');
                                } else {
                                    document.documentElement.classList.add('dark');
                                    localStorage.setItem('theme', 'dark');
                                }
                            }

                            // Initialize dark mode
                            initDarkMode();

                            // Dark mode toggle event
                            if (darkModeToggle) {
                                darkModeToggle.addEventListener('click', toggleDarkMode);
                            }

                            // Load saved sidebar state for desktop
                            function loadSidebarState() {
                                if (!isMobile()) {
                                    const savedState = localStorage.getItem('sidebarOpen');
                                    if (savedState !== null) {
                                        sidebarOpen = savedState === 'true';
                                    } else {
                                        sidebarOpen = true; // Default to open on desktop
                                    }
                                } else {
                                    sidebarOpen = false; // Always start closed on mobile
                                }
                            }

                            // Save sidebar state for desktop
                            function saveSidebarState() {
                                if (!isMobile()) {
                                    localStorage.setItem('sidebarOpen', sidebarOpen.toString());
                                }
                            }

                            // Check if we're on mobile
                            function isMobile() {
                                return window.innerWidth <= 768;
                            }

                            // Update hamburger icon
                            function updateHamburgerIcon() {
                                if (sidebarOpen && isMobile()) {
                                    hamburgerIcon.classList.add('hidden');
                                    closeIcon.classList.remove('hidden');
                                } else {
                                    hamburgerIcon.classList.remove('hidden');
                                    closeIcon.classList.add('hidden');
                                }
                            }

                            // Toggle sidebar
                            function toggleSidebar() {
                                sidebarOpen = !sidebarOpen;
                                saveSidebarState(); // Save state for desktop

                                if (isMobile()) {
                                    // Mobile behavior - overlay
                                    if (sidebarOpen) {
                                        sidebar.classList.remove('sidebar-hidden');
                                        sidebarOverlay.classList.remove('hidden');
                                        setTimeout(() => {
                                            sidebarOverlay.classList.remove('opacity-0');
                                        }, 10);
                                        document.body.style.overflow = 'hidden';
                                    } else {
                                        sidebar.classList.add('sidebar-hidden');
                                        sidebarOverlay.classList.add('opacity-0');
                                        setTimeout(() => {
                                            sidebarOverlay.classList.add('hidden');
                                        }, 300);
                                        document.body.style.overflow = '';
                                    }
                                } else {
                                    // Desktop behavior - simply hide/show sidebar with flex layout
                                    if (sidebarOpen) {
                                        sidebar.classList.remove('sidebar-hidden');
                                    } else {
                                        sidebar.classList.add('sidebar-hidden');
                                    }
                                }

                                updateHamburgerIcon();
                            }

                            // Close sidebar
                            function closeSidebar() {
                                if (sidebarOpen) {
                                    toggleSidebar();
                                }
                            }

                            // Event listeners
                            sidebarToggle.addEventListener('click', toggleSidebar);
                            sidebarClose.addEventListener('click', closeSidebar);
                            sidebarOverlay.addEventListener('click', closeSidebar);

                            // Touch events for mobile
                            let touchStartX = 0;
                            let touchEndX = 0;
                            let touchStartY = 0;
                            let touchEndY = 0;
                            let touchStartTime = 0;
                            let isScrolling = false;

                            // Swipe to close sidebar on mobile
                            sidebar.addEventListener('touchstart', function(e) {
                                touchStartX = e.changedTouches[0].screenX;
                                touchStartY = e.changedTouches[0].screenY;
                                touchStartTime = Date.now();
                                isScrolling = false;
                            }, {
                                passive: true
                            });

                            sidebar.addEventListener('touchmove', function(e) {
                                // Detect if user is scrolling vertically
                                const currentY = e.changedTouches[0].screenY;
                                if (Math.abs(currentY - touchStartY) > 10) {
                                    isScrolling = true;
                                }
                            }, {
                                passive: true
                            });

                            sidebar.addEventListener('touchend', function(e) {
                                touchEndX = e.changedTouches[0].screenX;
                                touchEndY = e.changedTouches[0].screenY;

                                // Only close if not scrolling and it's a horizontal swipe
                                if (isMobile() && sidebarOpen && !isScrolling &&
                                    touchStartX - touchEndX > 50 &&
                                    Math.abs(touchEndY - touchStartY) < 100) {
                                    // Swipe left to close
                                    closeSidebar();
                                }
                            }, {
                                passive: true
                            });

                            // Swipe to open sidebar from screen edge
                            let edgeTouchActive = false;

                            document.addEventListener('touchstart', function(e) {
                                if (isMobile() && !sidebarOpen && e.touches[0].clientX < 10) { // Even stricter - only 10px from edge
                                    touchStartX = e.touches[0].screenX;
                                    touchStartY = e.touches[0].screenY;
                                    touchStartTime = Date.now();
                                    isScrolling = false;
                                    edgeTouchActive = true;
                                } else {
                                    edgeTouchActive = false;
                                }
                            }, {
                                passive: true
                            });

                            document.addEventListener('touchmove', function(e) {
                                // Only track if we started at the edge
                                if (edgeTouchActive && touchStartX < 10) { // Match the stricter edge detection
                                    const currentY = e.touches[0].screenY;
                                    const currentX = e.touches[0].screenX;
                                    const verticalMovement = Math.abs(currentY - touchStartY);
                                    const horizontalMovement = Math.abs(currentX - touchStartX);

                                    // Much more strict vertical movement detection
                                    if (verticalMovement > 8 || (verticalMovement > horizontalMovement && verticalMovement > 3)) {
                                        isScrolling = true;
                                        edgeTouchActive = false;
                                    }
                                } else if (touchStartX >= 10) {
                                    // Not an edge touch, definitely not sidebar gesture
                                    isScrolling = true;
                                    edgeTouchActive = false;
                                }
                            }, {
                                passive: true
                            });

                            document.addEventListener('touchend', function(e) {
                                if (isMobile() && !sidebarOpen && edgeTouchActive && !isScrolling && touchStartX < 10) {
                                    touchEndX = e.changedTouches[0].screenX;
                                    touchEndY = e.changedTouches[0].screenY;
                                    const swipeDistance = touchEndX - touchStartX;
                                    const verticalDistance = Math.abs(touchEndY - touchStartY);
                                    const swipeTime = Date.now() - touchStartTime;

                                    // Very strict criteria for opening sidebar
                                    if (swipeDistance > 100 && verticalDistance < 30 && swipeTime < 200) {
                                        toggleSidebar();
                                    }
                                }
                                edgeTouchActive = false;
                            }, {
                                passive: true
                            });

                            // Handle window resize
                            window.addEventListener('resize', function() {
                                const wasMobile = document.body.classList.contains('mobile-view');
                                const isMobileNow = isMobile();

                                if (wasMobile !== isMobileNow) {
                                    // Mode changed
                                    if (isMobileNow) {
                                        // Switching to mobile
                                        document.body.classList.add('mobile-view');
                                        sidebar.classList.add('sidebar-hidden');
                                        sidebarOverlay.classList.add('hidden', 'opacity-0');
                                        document.body.style.overflow = '';
                                        sidebarOpen = false;
                                    } else {
                                        // Switching to desktop
                                        document.body.classList.remove('mobile-view');
                                        sidebarOverlay.classList.add('hidden', 'opacity-0');
                                        document.body.style.overflow = '';

                                        // Load desktop state
                                        loadSidebarState();
                                        if (sidebarOpen) {
                                            sidebar.classList.remove('sidebar-hidden');
                                        } else {
                                            sidebar.classList.add('sidebar-hidden');
                                        }
                                    }
                                    updateHamburgerIcon();
                                }
                            });

                            // Initialize sidebar state
                            loadSidebarState();

                            // Track mobile state for resize handling
                            if (isMobile()) {
                                document.body.classList.add('mobile-view');
                                sidebar.classList.add('sidebar-hidden');
                                sidebarOverlay.classList.add('hidden', 'opacity-0');
                                sidebarOpen = false; // Always closed on mobile initially
                            } else {
                                document.body.classList.remove('mobile-view');
                                // Apply saved desktop state
                                if (sidebarOpen) {
                                    sidebar.classList.remove('sidebar-hidden');
                                } else {
                                    sidebar.classList.add('sidebar-hidden');
                                }
                            }
                            updateHamburgerIcon();

                            // Escape key to close sidebar on mobile
                            document.addEventListener('keydown', function(e) {
                                if (e.key === 'Escape' && isMobile() && sidebarOpen) {
                                    closeSidebar();
                                }
                            });

                            // Auto-close sidebar when clicking navigation links on mobile
                            const sidebarLinks = sidebar.querySelectorAll('a');
                            sidebarLinks.forEach(link => {
                                link.addEventListener('click', function() {
                                    if (isMobile() && sidebarOpen) {
                                        // Small delay to allow navigation to start before closing
                                        setTimeout(() => {
                                            closeSidebar();
                                        }, 150);
                                    }
                                });
                            });
                        });
                    </script>