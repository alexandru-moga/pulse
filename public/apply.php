<?php
require_once '../core/init.php';
$projects = Project::getAll();
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
        <form class="application-form" action="/modules/YSWS/apply.php" method="POST">
            <h2>Join Our Club</h2>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Select Project</label>
                <select name="project_id" required>
                    <?php foreach ($projects as $project): ?>
                    <option value="<?= $project->id ?>"><?= $project->name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="button">Submit Application</button>
        </form>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
