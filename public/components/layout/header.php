<?php
if (session_status() === PHP_SESSION_NONE) session_start();
global $db, $currentUser;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'PULSE') ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/main.css">
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/images/favicon.ico">
    <?php if (!empty($extraCss)) echo $extraCss; ?>
</head>
<body>
<header class="site-header translucent">
    <div class="header-container">
        <a href="<?= SITE_URL ?>/" class="site-title"><?= htmlspecialchars(SITE_TITLE) ?></a>
        <nav class="nav-links">
            <?php
            $isLoggedIn = isset($currentUser) && $currentUser;
            $role = $isLoggedIn ? $currentUser->role : 'guest';

            $menus = $db->query(
                "SELECT m.id, m.title, p.name AS page_name
                 FROM menus m
                 JOIN pages p ON m.page_id = p.id
                 WHERE m.parent_id IS NULL AND p.name != 'dashboard'
                 AND (m.visibility IS NULL OR m.visibility = '' OR FIND_IN_SET('$role', REPLACE(m.visibility, ' ', '')))
                 ORDER BY m.order_num"
            )->fetchAll();

            foreach ($menus as $menu) {
            $url = ($menu['page_name'] === 'index')
                ? SITE_URL . '/'
                : SITE_URL . '/' . htmlspecialchars($menu['page_name']) . '.php';
                echo '<a href="' . $url . '">' . htmlspecialchars($menu['title']) . '</a>';
            }

            $dashboardParent = $db->prepare(
                "SELECT id FROM menus WHERE title='Dashboard' AND parent_id IS NULL AND page_id = (SELECT id FROM pages WHERE name='dashboard') LIMIT 1"
            );
            $dashboardParent->execute();
            $dashboardParentId = $dashboardParent->fetchColumn();

            if ($dashboardParentId) {
                $childrenStmt = $db->prepare(
                    "SELECT m.title, p.name AS page_name
                     FROM menus m
                     JOIN pages p ON m.page_id = p.id
                     WHERE m.parent_id = ? AND (m.visibility IS NULL OR m.visibility = '' OR FIND_IN_SET(?, REPLACE(m.visibility, ' ', '')))
                     ORDER BY m.order_num"
                );
                $childrenStmt->execute([$dashboardParentId, $role]);
                $dashboardLinks = $childrenStmt->fetchAll();

                if ($dashboardLinks) {
                    echo '<div class="dropdown">';
                    echo '<button class="dropbtn">Dashboard</button>';
                    echo '<div class="dropdown-content">';
                    foreach ($dashboardLinks as $item) {
                        $url = SITE_URL . '/' . htmlspecialchars($item['page_name']) . '.php';
                        echo '<a href="' . $url . '">' . htmlspecialchars($item['title']) . '</a>';
                    }
                    echo '</div></div>';
                }
            }
            ?>
        </nav>
    </div>
</header>
