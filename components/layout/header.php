<?php
if (session_status() === PHP_SESSION_NONE) session_start();
global $db, $currentUser, $settings;

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
$page_lookup = [];
foreach ($allPages as $p) $page_lookup[$p['id']] = $p;

$mainPages = array_filter($allPages, function($p) use ($role) {
    return ($p['menu_enabled'] ?? 0) && empty($p['parent_id']) && page_is_visible($p, $role);
});

$dashboardPage = null;
foreach ($mainPages as $p) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle ?? 'PULSE') ?></title>
    <link rel="stylesheet" href="<?= $settings['site_url'] ?>/css/main.css">
    <link rel="icon" type="image/x-icon" href="<?= $settings['site_url'] ?>/images/favicon.ico">
</head>
<body>
<header class="site-header translucent">
    <div class="header-container">
        <a href="<?= $settings['site_url'] ?>/" class="site-title"><?= htmlspecialchars($settings['site_title']) ?></a>
        <nav class="nav-links">
            <?php
            foreach ($mainPages as $page) {
                $url = ($page['name'] === 'index')
                    ? $settings['site_url'] . '/'
                    : $settings['site_url'] . '/' . htmlspecialchars($page['name']) . '.php';

                if ($dashboardPageId && $page['id'] == $dashboardPageId && count($dashboardChildren) > 0) {
                    echo '<div class="dropdown">';
                    echo '<button class="dropbtn">' . htmlspecialchars($page['title']) . '</button>';
                    echo '<div class="dropdown-content">';
                    foreach ($dashboardChildren as $child) {
                        $childUrl = $settings['site_url'] . '/' . htmlspecialchars($child['name']) . '.php';
                        echo '<a href="' . $childUrl . '">' . htmlspecialchars($child['title']) . '</a>';
                    }
                    echo '</div></div>';
                } else {
                    echo '<a href="' . $url . '">' . htmlspecialchars($page['title']) . '</a>';
                }
            }
            ?>
        </nav>
    </div>
</header>
