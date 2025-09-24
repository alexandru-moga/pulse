<?php
// Heading Component Template
$tag = $level ?? 'h2';
$textAlign = $align ?? 'left';
$textColor = $color ?? '#1f2937';
$headingText = $text ?? 'Your Heading';
?>

<<?= $tag ?> class="ddb-heading" style="text-align: <?= $textAlign ?>; color: <?= $textColor ?>; margin: 0.5em 0;">
    <?= htmlspecialchars($headingText) ?>
</<?= $tag ?>>
