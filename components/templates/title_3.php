<?php
// Title Style 3 Component Template
$titleText = $text ?? 'Section Title';
$titleLevel = $level ?? 'h3';
?>

<section class="section-heading-3">
  <div class="container">
    <<?= $titleLevel ?> class="title-style-3">
      <?= htmlspecialchars($titleText) ?>
    </<?= $titleLevel ?>>
  </div>
</section>
