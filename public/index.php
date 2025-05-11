<?php
require_once '../core/init.php';
?>
<?php include 'components/layout/header.php'; ?>

<head>
    <link rel="stylesheet" href="css/main.css">
</head>

<main>
    <?php foreach ($pageStructure['components'] as $component): ?>
        <?= $pageManager->renderComponent($component) ?>
    <?php endforeach; ?>
</main>

<?php include 'components/layout/footer.php'; ?>
