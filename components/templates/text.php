<?php
// Text Block Component Template
$textContent = $content ?? 'Enter your text content here...';
$textAlign = $align ?? 'left';
?>

<div class="ddb-text-block" style="text-align: <?= $textAlign ?>;">
    <?= $textContent ?>
</div>
