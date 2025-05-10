<?php
require_once '../core/init.php';
include 'includes/header.php';
include 'includes/page-title.php';
?>

<head>
      <link rel="stylesheet" href="css/main.css">

</head>

  <main>
    <section class="page-title">
      <div class="container">
        <h1><?= SITE_WELCOME_TITLE ?></h1>
        <p class="subtitle"><?= SITE_WELCOME_DESCRIPTION ?></p>
      </div>
    </section>

    <?php 
    include 'includes/about-section.php';
    include 'includes/stats-section.php';
    include 'includes/mission-section.php';
    include 'includes/upcoming-events-section.php';
    ?>
    
  </main>

<?php include 'includes/footer.php'; ?>
