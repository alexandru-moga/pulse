<?php
if (!is_array($block_content)) {
    $block_content = [];
}
?>
<section class="container mx-auto py-12">
  <div class="stats-grid">
    <?php foreach ($block_content as $stat): ?>
    <div class="stat-card">
      <div class="stat-content">
        <div class="stat-number"><?= htmlspecialchars($stat['value']) ?>+</div>
        <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
