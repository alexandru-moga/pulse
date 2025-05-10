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
    
    <main>
        <!-- Page Title -->
        <section class="page-title">
          <div class="container">
            <h1><?= MEMBERS_TITLE ?></h1>
            <p class="subtitle"><?= MEMBERS_DESCRIPTION ?></p>
          </div>
        </section>


    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>