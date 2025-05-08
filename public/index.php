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
        <!-- Page Title -->
        <section class="page-title">
          <div class="container">
            <h1><?= SITE_WELCOME_TITLE ?></h1>
            <p class="subtitle"><?= SITE_WELCOME_DESCRIPTION ?></p>
          </div>
        </section>
        <!-- About -->
        <section class="about">
          <div class="container">
            <h2><?= INDEX_ABOUT_TITLE ?></h2> 
            <p><?= INDEX_ABOUT_DESCRIPTION ?></p>
          </div>
        </section>

        <!-- Stats Section -->
        <section class="container" style="margin-bottom: 3rem;">
          <div class="stats-row">
            <div class="stats-card">
              <div class="stats-number"><?= INDEX_ACTIVE_STUDENTS?>+</div>
              <div class="stats-label">Active Members</div>
            </div>
            <div class="stats-card">
              <div class="stats-number"><?= INDEX_ACTIVE_PROJECTS?>+</div>
              <div class="stats-label">Projects Active</div>
            </div>
            <div class="stats-card">
              <div class="stats-number"><?= INDEX_COMPLETED_PROJECTS?>+</div>
              <div class="stats-label">Projects Completed</div>
            </div>
          </div>
        </section>


        <!-- Our Mission -->
        <section class="mission">
          <div class="container">
            <h2><?= INDEX_MISSION_TITLE ?></h2> 
            <p><?= INDEX_MISSION_DESCRIPTION ?></p>
          </div>
        </section>

        <!-- Upcoming Events Preview -->
        <section class="container events-section">
          <h2 class="events-title">Upcoming Events</h2>
          <div class="events-row">
            <div class="event-card">
              <h3 class="event-card-title">Intro to Github & Web Development</h3>
              <p class="event-card-date">April 12, 2025 | 14:00 - 17:00</p>
              <p class="event-card-desc">Learn the fundamentals of web development and share your site to get $5 for boba!</p>
              <a class="event-card-link" href="/events">Register Now &rarr;</a>
            </div>
          </div>
        </section>


    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>