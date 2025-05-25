<?php
require_once '../core/init.php';
require_once __DIR__ . '/../core/classes/ContactForm.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactForm = new ContactForm($db);
    
    if($contactForm->processSubmission($_POST)) {
        $_SESSION['form_success'] = "Message sent successfully!";
        header("Location: " . BASE_URL . "contact.php");
        exit();
    } else {
        $_SESSION['form_errors'] = $contactForm->getErrors();
        $_SESSION['form_data'] = $_POST;
        header("Location: " . BASE_URL . "contact.php");
        exit();
    }
}

$pageStructure = $pageManager->getPageStructure('contact');
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
    
    <?php include 'components/effects/birds.php'; ?>
</main>

<?php include 'components/layout/footer.php'; ?>
