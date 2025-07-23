<?php
require_once 'core/init.php';
require_once 'core/classes/ComponentManager.php';

global $db, $settings;

$pageTitle = '{{PAGE_TITLE}}';
$pageName = '{{PAGE_NAME}}';
$tableName = '{{TABLE_NAME}}';

// Get page blocks
$blocks = [];
try {
    $stmt = $db->prepare("SELECT * FROM `$tableName` WHERE is_active = 1 ORDER BY order_num ASC");
    $stmt->execute();
    $blocks = $stmt->fetchAll();
} catch (Exception $e) {
    // Table might not exist yet
}

$componentManager = new ComponentManager($db);
?>
<?php include 'components/layout/header.php'; ?>

<head>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
</head>

<main class="page-content">
    <?php if (empty($blocks)): ?>
        <div class="empty-page">
            <div class="container">
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
                <p>This page is currently being built. Check back soon!</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($blocks as $block): ?>
            <?php
            $content = json_decode($block['content'], true) ?: [];
            echo $componentManager->renderComponent($block['block_type'], $content, $block['id']);
            ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php include 'components/effects/mouse.php'; ?>
    <?php include 'components/effects/globe.php'; ?>
    <?php include 'components/effects/grid.php'; ?>
</main>

<?php include 'components/layout/footer.php'; ?>
