<?php
require_once 'core/init.php';
checkMaintenanceMode();
?>
<?php include 'components/layout/header.php'; ?>

<head>
    <link rel="stylesheet" href="css/main.css">
</head>

<main>
    <?php foreach ($pageStructure['components'] as $component): ?>
        <?= $pageManager->renderComponent($component) ?>
    <?php endforeach; ?>
    <?php include 'components/effects/mouse.php'; ?>
    <?php include 'components/effects/globe.php'; ?>
    <?php include 'components/effects/grid.php'; ?>
</main>

<?php include 'components/layout/footer.php'; ?>