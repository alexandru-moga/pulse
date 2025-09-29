<?php
// Statistics Component Template
$statsData = $items ?? $stats ?? array();

// If items/stats is a JSON string, decode it
if (is_string($statsData)) {
    $decoded = json_decode($statsData, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $statsData = $decoded;
    }
}

// Default stats if none provided
if (empty($statsData)) {
    $statsData = array(
        array('value' => '150', 'label' => 'Active Members'),
        array('value' => '25', 'label' => 'Projects Active'),
        array('value' => '50', 'label' => 'Projects Completed')
    );
}

if (!is_array($statsData)) {
    $statsData = array();
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
        return '<img src="' . htmlspecialchars($value) . '" alt="" class="' . $class . '">';
    } else {
        // It's a raw emoji (legacy format)
        return '<span class="' . $class . '">' . htmlspecialchars($value) . '</span>';
    }
}
?>

<section class="container mx-auto py-12">
    <div class="stats-grid">
        <?php foreach ($statsData as $stat): ?>
            <div class="stat-card">
                <div class="stat-content">
                    <?php if (!empty($stat['icon'])): ?>
                        <div class="stat-icon">
                            <?= renderImageOrEmoji($stat['icon'], 'stat-icon-img') ?>
                        </div>
                    <?php endif; ?>
                    <div class="stat-number">
                        <?= htmlspecialchars($stat['value'] ?? '') ?>
                    </div>
                    <div class="stat-label">
                        <?= htmlspecialchars($stat['label'] ?? '') ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>