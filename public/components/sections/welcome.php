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
</div>
