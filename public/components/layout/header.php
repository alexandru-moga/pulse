<?php

if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'PULSE') ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/main.css">
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/images/favicon.ico">
</head>
<body>
<header class="site-header translucent">
    <div class="header-container">
        <a href="<?= SITE_URL ?>/" class="site-title"><?= htmlspecialchars(SITE_TITLE) ?></a>
        <nav class="nav-links">
            <?php
            $menus = $db->query(
                "SELECT m.id, m.title, p.name AS page_name
                 FROM menus m
                 JOIN pages p ON m.page_id = p.id
                 WHERE m.parent_id IS NULL
                 ORDER BY m.order_num"
            )->fetchAll();
            foreach ($menus as $menu) {
                $url = ($menu['page_name'] === 'index')
                    ? SITE_URL . '/'
                    : SITE_URL . '/' . htmlspecialchars($menu['page_name']) . '.php';
                echo '<a href="' . $url . '">' . htmlspecialchars($menu['title']) . '</a>';
            }
            ?>
        </nav>
    </div>
</header>
