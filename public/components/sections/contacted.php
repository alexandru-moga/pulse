<?php

?>

<section class="applied">
  <div class="applied-container">
    <h1 class="applied-title"><?= htmlspecialchars($block_content['title'] ?? 'Application Received!') ?></h1>
    
    <div class="applied-message">
      <?= nl2br(htmlspecialchars($block_content['message'] ?? 'Your message has been successfully submitted. You will receive a response in 1-2 buisness days.')) ?>
    </div>

    <?php if (!empty($block_content['next_steps'])): ?>
      <div class="applied-next-steps">
        <h2><?= htmlspecialchars($block_content['next_steps_title'] ?? 'What Happens Next?') ?></h2>
        <ul>
          <?php foreach ($block_content['next_steps'] as $step): ?>
            <li><?= htmlspecialchars($step) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if (!empty($block_content['cta'])): ?>
      <a href="<?= htmlspecialchars($block_content['cta']['url'] ?? '/') ?>" class="applied-home-btn">
        <?= htmlspecialchars($block_content['cta']['text'] ?? 'Return to Homepage') ?>
      </a>
    <?php endif; ?>
  </div>
</section>
