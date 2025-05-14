<?php

?>
<section class="container mx-auto py-16">
  <div class="values-grid">
    <?php if (isset($block_content['values']) && is_array($block_content['values'])): ?>
      <?php foreach ($block_content['values'] as $index => $value): ?>
        <div class="value-card">
          <?php if (!empty($value['icon'])): ?>
            <div class="value-icon"><?= $value['icon'] ?></div>
          <?php endif; ?>
          <h3 class="value-title"><?= htmlspecialchars($value['title']) ?></h3>
          <p class="value-description"><?= htmlspecialchars($value['description']) ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
