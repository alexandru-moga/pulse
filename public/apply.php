<?php
require_once __DIR__.'/../core/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $submitApp = new SubmitApp($db);
        $applicationId = $submitApp->handleSubmission($_POST);
        
        header('Location: /apply-success.php');
        exit();
    } catch (Exception $e) {
        error_log($e->getMessage());
        header('Location: /apply.php?error='.urlencode($e->getMessage()));
        exit();
    }
}

try {
    $pageStructure = $pageManager->getPageStructure('apply');
    $components = $pageStructure['components'];
} catch (Exception $e) {
    die("Error loading page: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $pageStructure['meta']['title'] ?></title>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>
    <?php foreach ($components as $component): ?>
        <?= $pageManager->renderComponent($component) ?>
    <?php endforeach; ?>
</body>
</html>
