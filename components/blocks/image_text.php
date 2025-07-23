<div class="image-text-block image-<?= htmlspecialchars($image_position ?? 'left') ?>">
    <div class="image-text-content">
        <div class="image-container">
            <img src="<?= htmlspecialchars($image ?? '') ?>" alt="<?= htmlspecialchars($title ?? '') ?>">
        </div>
        <div class="text-container">
            <?php if (!empty($title)): ?>
                <h3><?= htmlspecialchars($title) ?></h3>
            <?php endif; ?>
            <div class="content"><?= $content ?? '' ?></div>
        </div>
    </div>
</div>
