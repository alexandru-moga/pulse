<?php
// Scroll Arrow Component Template
$arrowColor = $color ?? 'rgba(255,255,255,0.7)';
$arrowSize = $size ?? 24;
?>

<div class="scroll-indicator animate-fade-in-longest-delayed">
    <div class="bounce">
        <svg width="<?= $arrowSize ?>" height="<?= $arrowSize ?>" viewBox="0 0 24 24" fill="none" stroke="<?= htmlspecialchars($arrowColor) ?>"
            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </div>
</div>