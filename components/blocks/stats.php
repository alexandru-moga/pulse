<?php
// Default values
$title = $title ?? 'Our Achievements';
$subtitle = $subtitle ?? 'Numbers that speak for themselves';
$background_color = $background_color ?? '#f8fafc';
$stats = $stats ?? [
    ['number' => '500+', 'label' => 'Happy Clients', 'icon' => '👥'],
    ['number' => '150+', 'label' => 'Projects Done', 'icon' => '🚀'],
    ['number' => '10+', 'label' => 'Years Experience', 'icon' => '⭐'],
    ['number' => '24/7', 'label' => 'Support', 'icon' => '🛟']
];
?>

<section class="stats-section py-16" style="background-color: <?= htmlspecialchars($background_color) ?>">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                <?= htmlspecialchars($title) ?>
            </h2>
            <p class="text-xl text-gray-600">
                <?= htmlspecialchars($subtitle) ?>
            </p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <?php foreach ($stats as $stat): ?>
                <div class="text-center">
                    <?php if (!empty($stat['icon'])): ?>
                        <div class="text-4xl mb-4"><?= htmlspecialchars($stat['icon']) ?></div>
                    <?php endif; ?>
                    <div class="text-3xl md:text-4xl font-bold text-blue-600 mb-2">
                        <?= htmlspecialchars($stat['number']) ?>
                    </div>
                    <div class="text-gray-700 font-medium">
                        <?= htmlspecialchars($stat['label']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
