<?php
// Default values
$title = $title ?? 'Our Services';
$subtitle = $subtitle ?? 'What we can do for you';
$services = $services ?? array();
?>

<section class="services-section py-16 bg-gray-50 dark:bg-gray-900">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4"><?= htmlspecialchars($title) ?></h2>
            <p class="text-xl text-gray-600 dark:text-gray-400"><?= htmlspecialchars($subtitle) ?></p>
        </div>
        
        <?php if (empty($services)): ?>
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🛠️</div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No services yet</h3>
                <p class="text-gray-600 dark:text-gray-400">Services will be displayed here when added.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($services as $service): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg hover:shadow-xl transition-shadow">
                        <?php if (!empty($service['icon'])): ?>
                            <div class="text-4xl mb-4"><?= htmlspecialchars($service['icon']) ?></div>
                        <?php endif; ?>
                        
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                            <?= htmlspecialchars($service['name']) ?>
                        </h3>
                        
                        <p class="text-gray-600 dark:text-gray-400 mb-4"><?= htmlspecialchars($service['description']) ?></p>
                        
                        <?php if (!empty($service['price'])): ?>
                            <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                <?= htmlspecialchars($service['price']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
