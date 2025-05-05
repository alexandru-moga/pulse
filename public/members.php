<?php
require_once '../core/init.php';
$members = User::getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_TITLE ?></title>
    <link rel="stylesheet" href="https://fonts.hackclub.com/api/css?family=Phantom+Sans">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <h2>Club Members</h2>
        <div class="members-grid">
            <?php foreach ($members as $member): ?>
            <div class="member-card">
                <h3><?= $member->username ?></h3>
                <p>Joined: <?= date('M Y', strtotime($member->created_at)) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
