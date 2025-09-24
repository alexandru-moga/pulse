<?php
// Button Component Template
$buttonText = $text ?? 'Click Me';
$buttonLink = $link ?? '#';
$buttonStyle = $style ?? 'primary';
$buttonSize = $size ?? 'md';
$buttonAlign = $align ?? 'left';

$styleClasses = [
    'primary' => 'bg-primary hover:bg-red-600 text-white',
    'secondary' => 'bg-gray-600 hover:bg-gray-700 text-white',
    'outline' => 'border-2 border-primary text-primary hover:bg-primary hover:text-white'
][$buttonStyle] ?? 'bg-primary hover:bg-red-600 text-white';

$sizeClasses = [
    'sm' => 'px-4 py-2 text-sm',
    'md' => 'px-6 py-3 text-base',
    'lg' => 'px-8 py-4 text-lg'
][$buttonSize] ?? 'px-6 py-3 text-base';

$alignClass = [
    'left' => 'text-left',
    'center' => 'text-center',
    'right' => 'text-right'
][$buttonAlign] ?? 'text-left';
?>

<div class="ddb-button <?= $alignClass ?> my-4">
    <a href="<?= htmlspecialchars($buttonLink) ?>"
        class="inline-block <?= $styleClasses ?> <?= $sizeClasses ?> rounded-lg font-medium transition-colors">
        <?= htmlspecialchars($buttonText) ?>
    </a>
</div>