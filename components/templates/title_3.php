<?php
// Title Style 3 Component Template
$titleText = $text ?? 'Section Title';
$titleLevel = $level ?? 'h2';
?>

<<?= $titleLevel ?> class="title-3">
    <?= htmlspecialchars($titleText) ?>
</<?= $titleLevel ?>>