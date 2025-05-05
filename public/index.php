<?php
require_once '../core/init.php';

$projects = Project::getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_TITLE; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <div class="container">
                <a href="/" class="logo">
                    <img src="images/hackclub-logo.svg" alt="Hack Club">
                    <span>Neighborhood</span>
                </a>
                <div class="nav-links">
                    <a href="/apply.php">Apply</a>
                    <a href="/dashboard.php">Dashboard</a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="content">
                <img src="images/hack-club-logo.svg" alt="Hack Club" class="hero-logo">
                <h1>Neighborhood HQ</h1>
                <div class="cta-buttons">
                    <a href="/ysws.php" class="button ysws">YSWS Projects</a>
                    <a href="/join.php" class="button join">Join Club</a>
                </div>
            </div>
        </section>

        <section class="projects">
            <div class="container">
                <?php foreach ($projects as $project): ?>
                <div class="project-card">
                    <img src="<?php echo $project->image; ?>" alt="<?php echo $project->name; ?>" class="project-image">
                    <div class="project-content">
                        <h2><?php echo $project->name; ?></h2>
                        <p><?php echo $project->description; ?></p>
                        <a href="/ysws.php?id=<?php echo $project->id; ?>" class="button view-project">View Project</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_TITLE; ?></p>
        </div>
    </footer>
</body>
</html>