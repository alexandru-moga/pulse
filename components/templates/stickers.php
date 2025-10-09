<?php
// Stickers Component Template
$stickerTitle = $block_content['title'] ?? 'Sticker Credits Available!';
$stickerDescription = $block_content['description'] ?? 'Thanks to JukeBox for our custom stickers!';
$partnerUrl = $block_content['partner_url'] ?? 'https://www.jukeboxprint.com/custom-stickers';
?>

<section class="stickers-section">
    <div class="container mx-auto py-16">
        <div class="stickers-card">
            <div class="stickers-icon">🏷️</div>
            <h3 class="stickers-title"><?= htmlspecialchars($stickerTitle) ?></h3>
            <p class="stickers-description">
                <?= htmlspecialchars($stickerDescription) ?>
                <?php if ($partnerUrl): ?>
                    <a href="<?= htmlspecialchars($partnerUrl) ?>"
                        class="stickers-link"
                        target="_blank" rel="noopener">custom stickers</a>!
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>