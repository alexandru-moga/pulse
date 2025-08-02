<?php
// Default values
$grid_size = $grid_size ?? 50;
$animation_speed = $animation_speed ?? 3;
$color = $color ?? '#333333';
?>

<div class="grid-background-container relative overflow-hidden" style="min-height: 200px;">
    <style>
        .grid-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(<?= htmlspecialchars($color) ?> 1px, transparent 1px),
                linear-gradient(90deg, <?= htmlspecialchars($color) ?> 1px, transparent 1px);
            background-size: <?= intval($grid_size) ?>px <?= intval($grid_size) ?>px;
            opacity: 0.3;
            animation: gridMove <?= 10 / intval($animation_speed) ?>s linear infinite;
        }
        
        @keyframes gridMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(<?= intval($grid_size) ?>px, <?= intval($grid_size) ?>px); }
        }
    </style>
    
    <div class="grid-bg"></div>
    <div class="relative z-10 p-8 text-center">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Animated Grid Background</h3>
        <p class="text-gray-600 dark:text-gray-400">Dynamic grid pattern with customizable size and animation speed</p>
    </div>
</div>
