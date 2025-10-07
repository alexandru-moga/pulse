<?php
// Heading Component Template
$tag = $level ?? 'h2';
$textAlign = $align ?? 'left';
$textColor = $color ?? '#1f2937';
$headingText = $text ?? 'Your Heading';
$styleClass = $style_class ?? '';

// If using heading-3 style (for apply page subtitle)
if ($styleClass === 'heading-3') {
    echo "<{$tag} class=\"heading-3\">" . htmlspecialchars($headingText) . "</{$tag}>";
} else {
    echo "<{$tag} class=\"ddb-heading\" style=\"text-align: {$textAlign}; color: {$textColor}; margin: 0.5em 0;\">" . htmlspecialchars($headingText) . "</{$tag}>";
}
?>