<?php
require_once '../../core/init.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_TITLE ?></title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/images/favicon.ico">
    <script src="https://icons.hackclub.com/api/icons.js"></script>
    <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>
    <script type="text/babel" src="js/GradientBackground.jsx"></script>
</head>

<body>
<script type="text/babel">
        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<GradientBackground />);
    </script>
    <?php include 'includes/header.php'; ?>
    <main>



    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>