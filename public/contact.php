<?php
require_once '../core/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_TITLE ?></title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/images/favicon.ico">
    <script src="https://icons.hackclub.com/api/icons.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <form class="contact-form" action="/contact-submit" method="POST">
            <h2>Contact Us</h2>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="button">Send Message</button>
        </form>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
