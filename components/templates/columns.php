<?php
// Columns Component Template
$columnCount = $columns ?? '2';
$columnGap = $gap ?? 'md';

$gridClass = "grid-cols-{$columnCount}";
$gapClass = [
    'sm' => 'gap-4',
    'md' => 'gap-6',
    'lg' => 'gap-8'
][$columnGap] ?? 'gap-6';
?>

<div class="ddb-columns grid <?= $gridClass ?> <?= $gapClass ?> my-4">
    <?php for ($i = 1; $i <= $columnCount; $i++): ?>
        <div class="ddb-column min-h-[100px] border-2 border-dashed border-gray-300 rounded-lg p-4">
            <div class="text-center text-gray-500">
                <p>Column <?= $i ?></p>
                <small>Drop components here</small>
            </div>
        </div>
    <?php endfor; ?>
</div>