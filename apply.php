<?php
require_once 'core/init.php';
checkMaintenanceMode();
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applyForm = new ApplyForm($db);
    $result = $applyForm->processSubmission($_POST);
    
    if($result) {
        $_SESSION['form_success'] = true;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        $_SESSION['form_errors'] = $applyForm->getErrors();
        $_SESSION['form_data'] = $_POST;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

include 'components/layout/header.php'; 
?>

<head>
    <link rel="stylesheet" href="css/main.css">
</head>

<main>
    <?php if(isset($_SESSION['form_success'])): ?>
        <?php
            echo $pageManager->renderComponent([
                'block_type' => 'applied',
                'block_name' => 'applied',
                'order_num' => 999,
                'content' => ''
            ]);
            unset($_SESSION['form_success']);
        ?>
    <?php else: ?>
        <?php foreach ($pageStructure['components'] as $component): ?>
            <?= $pageManager->renderComponent($component) ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php include 'components/effects/mouse.php'; ?>
    <?php include 'components/effects/net.php'; ?>
    <?php include 'components/effects/grid.php'; ?>
</main>

<?php include 'components/layout/footer.php'; ?>
