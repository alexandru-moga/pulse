<?php
// Feature Grid Component Template
$gridTitle = $title ?? 'Our Features';
$gridSubtitle = $subtitle ?? 'What makes us special';
$columns = $columns ?? '3';
$features = $features ?? array();

// Parse features if it's a JSON string
if (is_string($features)) {
    $decoded = json_decode($features, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $features = $decoded;
    }
}

// Default features if none provided
if (empty($features)) {
    $features = array(
        array('icon' => 'emoji:🚀', 'title' => 'Fast Performance', 'description' => 'Lightning fast performance for all your needs.'),
        array('icon' => 'emoji:🔒', 'title' => 'Secure', 'description' => 'Top-notch security to protect your data.'),
        array('icon' => 'emoji:💡', 'title' => 'Innovative', 'description' => 'Cutting-edge solutions for modern challenges.')
    );
}

// Helper function to render image or emoji
function renderImageOrEmoji($value, $class = '') {
    if (empty($value)) return '';
    
    if (strpos($value, 'emoji:') === 0) {
        // It's an emoji with prefix
        $emoji = substr($value, 6);
        return '<span class="' . $class . '">' . htmlspecialchars($emoji) . '</span>';
    } elseif (strpos($value, 'http') === 0 || strpos($value, '/') === 0) {
        // It's an image URL
        return '<img src="' . htmlspecialchars($value) . '" alt="" class="' . $class . ' w-12 h-12 mx-auto object-contain">';
    } else {
        // It's a raw emoji (legacy format)
        return '<span class="' . $class . '">' . htmlspecialchars($value) . '</span>';
    }
}

$gridClass = "grid-cols-1 md:grid-cols-{$columns}";
?>

<section class="feature-grid py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                <?= htmlspecialchars($gridTitle) ?>
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-300">
                <?= htmlspecialchars($gridSubtitle) ?>
            </p>
        </div>
        
        <div class="grid <?= $gridClass ?> gap-8">
            <?php foreach ($features as $feature): ?>
                <div class="feature-card text-center p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                    <div class="feature-icon text-4xl mb-4">
                        <?= renderImageOrEmoji($feature['icon'] ?? '', 'feature-icon-img') ?>
                    </div>
                    <h3 class="feature-title text-xl font-semibold mb-3 text-gray-900 dark:text-white">
                        <?= htmlspecialchars($feature['title'] ?? '') ?>
                    </h3>
                    <p class="feature-description text-gray-600 dark:text-gray-300">
                        <?= htmlspecialchars($feature['description'] ?? '') ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
