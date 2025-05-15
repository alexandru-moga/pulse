<?php
require_once '../core/init.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applyForm = new ApplyForm($db);
    $result = $applyForm->processSubmission($_POST);
    
    if($result) {
        $_SESSION['form_success'] = "Application submitted successfully!";
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
        <div class="form-success">
            <?= $_SESSION['form_success'] ?>
            <?php unset($_SESSION['form_success']); ?>
        </div>
    <?php endif; ?>

    <?php foreach ($pageStructure['components'] as $component): ?>
        <?= $pageManager->renderComponent($component) ?>
    <?php endforeach; ?>
    
    <?php include 'components/effects/mouse.php'; ?>
    <?php include 'components/effects/net.php'; ?>
    <?php include 'components/effects/grid.php'; ?>
</main>

<?php include 'components/layout/footer.php'; ?>
