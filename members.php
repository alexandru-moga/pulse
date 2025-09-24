<?php
require_once 'core/init.php';
checkMaintenanceMode();
global $db;
?>
<?php include 'components/layout/header.php'; ?>


<head>
    <link rel="stylesheet" href="css/main.css">
</head>

<main>
    <?php foreach ($pageStructure['components'] as $component): ?>
        <?= $pageManager->renderComponent($component) ?>
    <?php endforeach; ?>
    <?php
    renderMembersGrid($db);
    ?>
    <?php $effectsManager->renderPageEffects('members'); ?>
</main>

<?php include 'components/layout/footer.php'; ?>
