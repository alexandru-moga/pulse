<?php
// Core Values Component Template
$values = $values ?? array();
if (!is_array($values)) {
    $values = array();
}
?>

<section class="container mx-auto py-16">
  <div class="values-grid">
    <?php if (!empty($values)): ?>
      <?php foreach ($values as $index => $value): ?>
        <div class="value-card">
          <?php if (!empty($value['icon'])): ?>
            <div class="value-icon"><?= $value['icon'] ?></div>
          <?php endif; ?>
          <h3 class="value-title"><?= htmlspecialchars($value['title'] ?? '') ?></h3>
          <p class="value-description"><?= htmlspecialchars($value['description'] ?? '') ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>
