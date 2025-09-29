<?php
// Core Values Component Template
require_once __DIR__ . '/../helpers/image_emoji_helper.php';

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
?>
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