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
    <link rel="stylesheet" href="https://icons.hackclub.com/api/icons/0xEC3750/">
    <link rel="stylesheet" href="css/main.css">
    <script src="https://icons.hackclub.com/api/icons.js"></script>
</head>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const toggleSwitch = document.querySelector('#themeToggle');
  const currentTheme = localStorage.getItem('theme') || 'dark';

  // Set initial theme
  document.documentElement.setAttribute('data-theme', currentTheme);
  toggleSwitch.checked = currentTheme === 'dark';

  // Toggle handler
  toggleSwitch.addEventListener('change', function(e) {
    const theme = e.target.checked ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
  });
});
</script>

<body>
    <?php include 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h1>Welcome to <?= SITE_TITLE ?></h1>
                <p class="hero-subtitle">Join a vibrant community of students passionate about technology and innovation</p>
                <div class="cta-buttons">
                    <a href="/join" class="button ysws">Join Now</a>
                    <a href="#projects" class="button secondary">View Projects</a>
                </div>
            </div>
        </section>

        <!-- Core Values -->
        <section class="values">
            <div class="container grid-three">
                <div class="value-card">
                    <hc-icon name="users" size="40"></hc-icon>
                    <h3>Community</h3>
                    <p>Collaborative environment where students learn and grow together</p>
                </div>
                <div class="value-card">
                    <hc-icon name="lightning" size="40"></hc-icon>
                    <h3>Innovation</h3>
                    <p>Build impactful projects using cutting-edge technologies</p>
                </div>
                <div class="value-card">
                    <hc-icon name="growth" size="40"></hc-icon>
                    <h3>Growth</h3>
                    <p>Develop both technical and leadership skills through hands-on experience</p>
                </div>
            </div>
        </section>

        <!-- Projects Grid -->
        <section id="projects" class="projects">
            <div class="container">
                <h2>Featured Projects</h2>
                <div class="ysws-grid">
                    <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <img src="<?= $project->image ?>" alt="<?= $project->name ?>">
                        <div class="project-content">
                            <h3><?= $project->name ?></h3>
                            <p><?= $project->description ?></p>
                            <a href="/ysws/<?= $project->id ?>" class="button view-project">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Join Section -->
        <section class="join-cta">
            <div class="container">
                <h2>Ready to Start Building?</h2>
                <p>Join our community of makers and innovators</p>
                <a href="/join" class="button ysws">Get Started</a>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
