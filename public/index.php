<?php
require_once '../core/init.php';
$projects = Projects::getAll();
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
        <section class="hero">
            <h1>Welcome to <?= SITE_TITLE ?></h1>
            <div class="ysws-grid">
                <?php foreach ($projects as $project): ?>
                <div class="project-card">
                    <img src="<?= $project->image ?>" alt="<?= $project->name ?>">
                    <h3><?= $project->name ?></h3>
                    <p><?= $project->description ?></p>
                    <a href="/ysws/<?= $project->id ?>" class="button">View Project</a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
