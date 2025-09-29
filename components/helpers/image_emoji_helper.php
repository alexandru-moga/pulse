<?php
/**
 * Shared helper functions for component templates
 */

if (!function_exists('renderImageOrEmoji')) {
    /**
     * Helper function to render image or emoji
     * Handles multiple formats: emoji:🚀, raw emojis, and image URLs
     */
    function renderImageOrEmoji($value, $class = '') {
        if (empty($value)) return '';
        
        if (strpos($value, 'emoji:') === 0) {
            // It's an emoji with prefix
            $emoji = substr($value, 6);
            return '<span class="' . $class . '">' . htmlspecialchars($emoji) . '</span>';
        } elseif (strpos($value, 'http') === 0 || strpos($value, '/') === 0) {
            // It's an image URL - add responsive sizing for different contexts
            $imageClasses = $class;
            if (strpos($class, 'w-') === false) {
                // Add default sizing if no width class is specified
                if (strpos($class, 'stat-icon') !== false) {
                    $imageClasses .= ' w-10 h-10';
                } elseif (strpos($class, 'value-icon') !== false || strpos($class, 'feature-icon') !== false || strpos($class, 'service-icon') !== false) {
                    $imageClasses .= ' w-12 h-12 mx-auto object-contain';
                } else {
                    $imageClasses .= ' w-full h-auto';
                }
            }
            return '<img src="' . htmlspecialchars($value) . '" alt="" class="' . $imageClasses . '">';
        } else {
            // It's a raw emoji (legacy format)
            return '<span class="' . $class . '">' . htmlspecialchars($value) . '</span>';
        }
    }
}
?>
