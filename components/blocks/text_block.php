<?php
// Default values
$title = $title ?? '';
$content = $content ?? '<p>Add your text content here...</p>';
$text_align = $text_align ?? 'left';
$background_color = $background_color ?? '#ffffff';
?>

<section class="text-block" style="background-color: <?= htmlspecialchars($background_color) ?>; text-align: <?= htmlspecialchars($text_align) ?>;">
    <?php if ($title): ?>
        <h2><?= htmlspecialchars($title) ?></h2>
    <?php endif; ?>
    
    <div class="content">
        <?= $content ?>
    </div>
</section>
            <?= $content ?>
        </div>
    </div>
</section>
        
        <div class="prose prose-lg max-w-none <?= $alignClass ?> text-gray-700">
            <?= $content ?>
        </div>
    </div>
</section>
