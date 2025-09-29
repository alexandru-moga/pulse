<?php
// Core Values Component Template
$values = $values ?? array();

// Parse values if it's a JSON string
if (is_string($values)) {
    $decoded = json_decode($values, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $values = $decoded;
    }
}

if (!is_array($values)) {
    $values = array();
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
        return '<img src="' . htmlspecialchars($value) . '" alt="" class="' . $class . ' w-12 h-12 object-contain">';
    } else {
        // It's a raw emoji (legacy format)
        return '<span class="' . $class . '">' . htmlspecialchars($value) . '</span>';
    }
}
?>

<section class="container mx-auto py-16">
    <div class="values-grid">
        <?php if (!empty($values)): ?>
            <?php foreach ($values as $index => $value): ?>
                <div class="value-card">
                    <?php if (!empty($value['icon'])): ?>
                        <div class="value-icon"><?= renderImageOrEmoji($value['icon'], 'value-icon-img') ?></div>
                    <?php endif; ?>
                    <h3 class="value-title"><?= htmlspecialchars($value['title'] ?? '') ?></h3>
                    <p class="value-description"><?= htmlspecialchars($value['description'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>