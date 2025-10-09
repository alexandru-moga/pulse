<?php
require_once 'core/init.php';
checkMaintenanceMode();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = $_POST;
    $errors = [];

    $name = trim($fields['name'] ?? '');
    $email = trim($fields['email'] ?? '');
    $message = trim($fields['message'] ?? '');

    if ($name === '') $errors['name'] = "Name is required.";
    if ($email === '') $errors['email'] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email address.";
    if ($message === '') $errors['message'] = "Message is required.";

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);

        $_SESSION['form_success'] = true;
        header("Location: " . "contact.php");
        exit();
    } else {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $fields;
        header("Location: " . "contact.php");
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
    <?php if (isset($_SESSION['form_success'])): ?>
        <?php
        echo $pageManager->renderComponent([
            'block_type' => 'contacted',
            'block_name' => 'contacted',
            'order_num' => 999,
            'content' => ''
        ]);
        unset($_SESSION['form_success']);
        ?>
    <?php else: ?>
        <?php
        foreach ($pageStructure['components'] as $component) {
            echo $pageManager->renderComponent($component);
        }
        ?>
    <?php endif; ?>
    <?php $effectsManager->renderPageEffects('contact'); ?>
</main>

<?php include 'components/layout/footer.php'; ?>