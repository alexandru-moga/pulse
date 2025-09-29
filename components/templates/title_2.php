<?php
// Title 2 Component Template - Two-part title with different colors
$firstText = $first ?? 'OUR';
$secondText = $second ?? 'MISSION';
?>
<div class="title-2-container">
    <h2 class="title-2">
        <span class="title-2-grey"><?= htmlspecialchars($firstText) ?></span>
        <span class="title-2-red"><?= htmlspecialchars($secondText) ?></span>
    </h2>
</div>