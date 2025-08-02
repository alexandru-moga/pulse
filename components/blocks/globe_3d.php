<?php
// Default values
$show_labels = $show_labels ?? true;
$rotation_speed = $rotation_speed ?? 5;
?>

<div class="globe-3d-container py-16 bg-black text-white text-center">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-6xl mb-8 animate-spin" style="animation-duration: <?= 10 - intval($rotation_speed) ?>s;">🌍</div>
        <h3 class="text-2xl font-bold mb-4">Interactive 3D Globe</h3>
        <p class="text-gray-400">Globe component placeholder - Advanced 3D globe would require Three.js implementation</p>
        <?php if ($show_labels): ?>
            <div class="mt-8 grid grid-cols-3 gap-4 text-sm">
                <div>🇺🇸 USA</div>
                <div>🇬🇧 UK</div>
                <div>🇩🇪 Germany</div>
            </div>
        <?php endif; ?>
    </div>
</div>
