<section class="page-title">
  <div class="container">
    <?php if (isset($block_content['title'])): ?>
      <h1><?= htmlspecialchars($block_content['title']) ?></h1>
    <?php endif; ?>
    <?php if (isset($block_content['subtitle'])): ?>
      <p class="subtitle"><?= htmlspecialchars($block_content['subtitle']) ?></p>
    <?php endif; ?>
  </div>
</section>
