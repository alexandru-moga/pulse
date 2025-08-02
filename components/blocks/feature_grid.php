<?php
// Default values
$title = $title ?? 'Our Features';
$subtitle = $subtitle ?? 'What makes us special';
$columns = $columns ?? '3';
$features = $features ?? [
    ['icon' => '⚡', 'title' => 'Fast Performance', 'description' => 'Lightning fast loading times'],
    ['icon' => '🔒', 'title' => 'Secure', 'description' => 'Bank-level security for your data'],
    ['icon' => '📱', 'title' => 'Mobile Friendly', 'description' => 'Looks great on all devices']
];

$gridCols = [
    '2' => 'md:grid-cols-2',
    '3' => 'md:grid-cols-3',
    '4' => 'md:grid-cols-4'
];
$gridClass = $gridCols[$columns] ?? 'md:grid-cols-3';
?>

<section class="feature-grid py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                <?= htmlspecialchars($title) ?>
            </h2>
            <p class="text-xl text-gray-600">
                <?= htmlspecialchars($subtitle) ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 <?= $gridClass ?> gap-8">
            <?php foreach ($features as $feature): ?>
                <div class="text-center p-6 rounded-lg hover:shadow-lg transition-shadow">
                    <?php if (!empty($feature['icon'])): ?>
                        <div class="text-4xl mb-4"><?= htmlspecialchars($feature['icon']) ?></div>
                    <?php endif; ?>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">
                        <?= htmlspecialchars($feature['title']) ?>
                    </h3>
                    <p class="text-gray-600">
                        <?= htmlspecialchars($feature['description']) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
</section>
