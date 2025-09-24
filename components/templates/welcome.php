<?php
// Welcome Section Component Template  
$welcomeTitle = $title ?? 'Welcome to PULSE';
$welcomeSubtitle = $subtitle ?? 'STUDENT-LED TECH COMMUNITY';
$welcomeDescription = $description ?? 'Join a vibrant community of students passionate about technology.';
$primaryButtonText = $primary_button_text ?? 'Get Involved';
$primaryButtonUrl = $primary_button_url ?? '#';
$secondaryButtonText = $secondary_button_text ?? 'Contact us';
$secondaryButtonUrl = $secondary_button_url ?? '#';
?>

<div class="welcome-container">
  <div class="welcome-content">
    <div class="badge animate-fade-in">
      <span class="badge-text"><?= htmlspecialchars($welcomeSubtitle) ?></span>
    </div>
    
    <h1 class="welcome-heading animate-slide-in">
      <?= $welcomeTitle ?>
    </h1>

    <p class="welcome-description animate-fade-in-delayed">
      <?= htmlspecialchars($welcomeDescription) ?>
    </p>
    
    <div class="welcome-buttons animate-fade-in-long-delayed">
      <?php if ($primaryButtonText): ?>
      <a href="<?= htmlspecialchars($primaryButtonUrl) ?>" 
         class="primary-button">
        <?= htmlspecialchars($primaryButtonText) ?>
      </a>
      <?php endif; ?>
      
      <?php if ($secondaryButtonText): ?>
      <a href="<?= htmlspecialchars($secondaryButtonUrl) ?>" 
         class="secondary-button">
        <?= htmlspecialchars($secondaryButtonText) ?>
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>
