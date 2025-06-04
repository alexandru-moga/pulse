<?php

?>
<div class="welcome-container">
  <div class="welcome-content">
    <div class="badge animate-fade-in">
      <span class="badge-text"><?= htmlspecialchars($block_content['subtitle'] ?? '') ?></span>
    </div>
    
    <h1 class="welcome-heading animate-slide-in">
      <?= $block_content['title'] ?? '' ?>
    </h1>

    <p class="welcome-description animate-fade-in-delayed">
      <?= htmlspecialchars($block_content['description'] ?? '') ?>
    </p>
    
    <div class="welcome-buttons animate-fade-in-long-delayed">
      <?php if (isset($block_content['primaryButton'])): ?>
      <a href="<?= htmlspecialchars($block_content['primaryButton']['url'] ?? '#') ?>" 
         class="primary-button">
        <?= htmlspecialchars($block_content['primaryButton']['text'] ?? 'Get Involved') ?>
      </a>
      <?php endif; ?>
      
      <?php if (isset($block_content['secondaryButton'])): ?>
      <a href="<?= htmlspecialchars($block_content['secondaryButton']['url'] ?? '#') ?>" 
         class="secondary-button">
        <?= htmlspecialchars($block_content['secondaryButton']['text'] ?? 'Contact us') ?>
      </a>
      <?php endif; ?>
    </div>
  </div>
  
  <div class="scroll-indicator animate-fade-in-longest-delayed">
    <div class="bounce">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" 
           stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 12 15 18 9"></polyline>
      </svg>
    </div>
  </div>
</div>
