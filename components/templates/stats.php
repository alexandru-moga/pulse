<?php
// Statistics Component Template
$statsTitle = $title ?? 'Our Achievements';
$statsSubtitle = $subtitle ?? 'Numbers that speak for themselves';
$statsData = $stats ?? array();

// Default stats if none provided
if (empty($statsData)) {
    $statsData = array(
        array('value' => '500+', 'label' => 'Happy Clients'),
        array('value' => '150+', 'label' => 'Projects Done'),
        array('value' => '10+', 'label' => 'Years Experience'),
        array('value' => '24/7', 'label' => 'Support')
    );
}

if (!is_array($statsData)) {
    $statsData = array();
}
?>

<section class="container mx-auto py-12">
  <?php if ($statsTitle || $statsSubtitle): ?>
  <div class="text-center mb-12">
    <?php if ($statsTitle): ?>
    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
      <?= htmlspecialchars($statsTitle) ?>
    </h2>
    <?php endif; ?>
    <?php if ($statsSubtitle): ?>
    <p class="text-xl text-gray-600">
      <?= htmlspecialchars($statsSubtitle) ?>
    </p>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  
  <div class="stats-grid">
    <?php foreach ($statsData as $stat): ?>
    <div class="stat-card">
      <div class="stat-content">
        <div class="stat-number"><?= htmlspecialchars($stat['value'] ?? '') ?>+</div>
        <div class="stat-label"><?= htmlspecialchars($stat['label'] ?? '') ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
