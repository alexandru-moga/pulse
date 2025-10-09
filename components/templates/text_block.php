<?php
// Text Block Component Template
$blockTitle = $title ?? '';
$blockContent = $content ?? '<p>Your content goes here.</p>';
$textAlign = $text_align ?? 'left';
?>

<section class="text-block-section py-8">
    <div class="container mx-auto px-4">
        <div class="text-block" style="text-align: <?= htmlspecialchars($textAlign) ?>;">
            <?php if ($blockTitle): ?>
                <h2 class="text-block-title text-2xl font-bold mb-4 text-gray-900 dark:text-white">
                    <?= htmlspecialchars($blockTitle) ?>
                </h2>
            <?php endif; ?>
            <div class="text-block-content prose prose-lg max-w-none dark:prose-invert">
                <?= $blockContent ?>
            </div>
        </div>
    </div>
</section>
