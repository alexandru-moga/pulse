<?php
// Title Style 2 Component Template
$titleText = $text ?? 'Section Title';
$titleLevel = $level ?? 'h2';
?>

<section class="section-heading-2">
    <div class="container">
        <<?= $titleLevel ?> class="title-style-2">
            <?= htmlspecialchars($titleText) ?>
        </<?= $titleLevel ?>>
    </div>
</section>