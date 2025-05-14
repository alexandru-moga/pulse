<?php

$title = $block_content['title'] ?? 'Welcome to <span class="text-red-500">Suceava Hacks</span>';
$subtitle = $block_content['subtitle'] ?? 'STUDENT-LED TECH COMMUNITY';
$description = $block_content['description'] ?? 'Join a vibrant community of students passionate about technology and innovation. We build, learn, and grow together through hackathons, workshops, and collaborative projects.';
$primaryButton = $block_content['primaryButton'] ?? ['text' => 'Get Involved', 'url' => '/join.php'];
$secondaryButton = $block_content['secondaryButton'] ?? ['text' => 'Learn More', 'url' => '/about.php'];
?>

<div class="welcome-container">
  <div class="welcome-content">
    <div class="badge animate-fade-in">
      <span class="badge-text"><?= htmlspecialchars($subtitle) ?></span>
    </div>
    
    <h1 class="welcome-heading animate-slide-in">
      <?= $title ?>
    </h1>

    <p class="welcome-description animate-fade-in-delayed">
      <?= htmlspecialchars($description) ?>
    </p>
    
    <div class="welcome-buttons animate-fade-in-long-delayed">
      <a href="<?= htmlspecialchars($primaryButton['url']) ?>" class="primary-button">
        <?= htmlspecialchars($primaryButton['text']) ?>
      </a>
      
      <a href="<?= htmlspecialchars($secondaryButton['url']) ?>" class="secondary-button">
        <?= htmlspecialchars($secondaryButton['text']) ?>
      </a>
    </div>
  </div>
  
  <div class="scroll-indicator animate-fade-in-longest-delayed">
    <div class="bounce">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9"></polyline>
      </svg>
    </div>
  </div>
</div>
