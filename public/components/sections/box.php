<section class="box">
  <div class="container">
    <?php if (isset($block_content['title'])): ?>
      <h2><?= htmlspecialchars($block_content['title']) ?></h2>
    <?php endif; ?>
    <?php if (isset($block_content['content'])): ?>
      <p><?= htmlspecialchars($block_content['content']) ?></p>
    <?php endif; ?>
  </div>
</section>
