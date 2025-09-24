<?php
// Image Component Template
$imageSrc = $src ?? '';
$imageAlt = $alt ?? '';
$imageCaption = $caption ?? '';
$imageWidth = $width ?? '100';
$imageAlign = $align ?? 'center';

$alignClass = [
    'left' => 'text-left',
    'center' => 'text-center',
    'right' => 'text-right'
][$imageAlign] ?? 'text-center';

$widthClass = 'w-full';
if ($imageWidth !== '100') {
    $widthClass = "w-{$imageWidth}/4";
}
?>

<div class="ddb-image <?= $alignClass ?> my-4">
    <?php if ($imageSrc): ?>
        <img src="<?= htmlspecialchars($imageSrc) ?>"
            alt="<?= htmlspecialchars($imageAlt) ?>"
            class="<?= $widthClass ?> h-auto mx-auto max-w-full">

        <?php if ($imageCaption): ?>
            <p class="text-sm text-gray-600 mt-2 italic"><?= htmlspecialchars($imageCaption) ?></p>
        <?php endif; ?>
    <?php else: ?>
        <div class="<?= $widthClass ?> h-48 mx-auto bg-gray-200 rounded-lg flex items-center justify-center">
            <span class="text-gray-500">No image selected</span>
        </div>
    <?php endif; ?>
</div>