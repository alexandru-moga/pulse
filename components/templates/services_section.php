<?php
// Services Section Component Template
$servicesTitle = $title ?? 'Our Services';
$servicesSubtitle = $subtitle ?? 'What we can do for you';
$services = $services ?? array();

// Parse services if it's a JSON string
if (is_string($services)) {
    $decoded = json_decode($services, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $services = $decoded;
    }
}

// Default services if none provided
if (empty($services)) {
    $services = array(
        array('icon' => 'emoji:🛠️', 'name' => 'Development', 'description' => 'Custom software development solutions.', 'price' => '$99/hour'),
        array('icon' => 'emoji:🎨', 'name' => 'Design', 'description' => 'Creative and modern design services.', 'price' => '$75/hour'),
        array('icon' => 'emoji:📊', 'name' => 'Consulting', 'description' => 'Expert consultation and strategy.', 'price' => '$150/hour')
    );
}

// Helper function to render image or emoji
function renderImageOrEmoji($value, $class = '') {
    if (empty($value)) return '';
    
    if (strpos($value, 'emoji:') === 0) {
        // It's an emoji
        $emoji = substr($value, 6);
        return '<span class="' . $class . '">' . htmlspecialchars($emoji) . '</span>';
    } else {
        // It's an image URL
        return '<img src="' . htmlspecialchars($value) . '" alt="" class="' . $class . ' w-12 h-12 mx-auto object-contain">';
    }
}
?>

<section class="services-section py-16 bg-gray-50 dark:bg-gray-900">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                <?= htmlspecialchars($servicesTitle) ?>
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-300">
                <?= htmlspecialchars($servicesSubtitle) ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($services as $service): ?>
                <div class="service-card bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow">
                    <?php if (!empty($service['icon'])): ?>
                        <div class="service-icon text-4xl mb-4">
                            <?= renderImageOrEmoji($service['icon'], 'service-icon-img') ?>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="service-title text-xl font-semibold mb-3 text-gray-900 dark:text-white">
                        <?= htmlspecialchars($service['name'] ?? '') ?>
                    </h3>
                    
                    <p class="service-description text-gray-600 dark:text-gray-300 mb-4">
                        <?= htmlspecialchars($service['description'] ?? '') ?>
                    </p>
                    
                    <?php if (!empty($service['price'])): ?>
                        <div class="service-price text-lg font-semibold text-blue-600 dark:text-blue-400">
                            <?= htmlspecialchars($service['price']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
