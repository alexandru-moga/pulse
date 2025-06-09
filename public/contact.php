<?php
require_once '../core/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $fields = $_POST;

    $required = ['name', 'email', 'message'];
    foreach ($required as $field) {
        if (empty(trim($fields[$field] ?? ''))) {
            $errors[$field] = ucfirst($field) . " is required.";
        }
    }
    if (!empty($fields['email']) && !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email address.";
    }

    if (empty($errors)) {
        $_SESSION['form_success'] = "Message sent successfully!";
        header("Location: " . BASE_URL . "contact.php");
        exit();
    } else {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $fields;
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

    <?php include 'components/effects/mouse.php'; ?>
    <?php include 'components/effects/grid.php'; ?>
    <?php include 'components/effects/birds.php'; ?>
</main>

<?php include 'components/layout/footer.php'; ?>
