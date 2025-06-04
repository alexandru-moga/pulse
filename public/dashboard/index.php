<?php

require_once '../../core/init.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageStructure = $pageManager->getPageStructure('dashboard');
include '../components/layout/header.php';
?>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<main class="index-page">
    <?php foreach ($pageStructure['components'] as $component): ?>
        <?= $pageManager->renderComponent($component) ?>
    <?php endforeach; ?>
</main>


<?php 
include '../components/effects/grid.php';
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
?>
