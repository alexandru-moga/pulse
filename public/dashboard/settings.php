<?php
require_once '../../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

include '../components/layout/header.php';
?>

<head>
    <link rel="stylesheet" href="../css/main.css">
</head>

<main class="contact-form-section">
    <h2>Settings</h2>
    <div class="settings-buttons">
        <a href="email-settings.php" class="cta-button">Email Settings</a>
        <a href="site-settings.php" class="cta-button">Website Settings</a>
        <a href="page-settings.php" class="cta-button">Pages Settings</a>
        <a href="footer-settings.php" class="cta-button">Footer Settings</a>
    </div>
</main>

<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
include '../components/effects/grid.php';
?>
