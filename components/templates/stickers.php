<?php
// Stickers Component Template
$stickerTitle = $block_content['title'] ?? 'Sticker Credits Available!';
$stickerDescription = $block_content['description'] ?? 'Thanks to JukeBox for our custom stickers!';
$partnerUrl = $block_content['partner_url'] ?? 'https://www.jukeboxprint.com/custom-stickers';
?>

<section class="jukebox-stickers">
    <div class="stickers-content">
        <div class="stickers-header">
            <div class="stickers-icon">🏷️</div>
            <h2><?= htmlspecialchars($stickerTitle) ?></h2>
            <p class="subtitle">
                <?= htmlspecialchars($stickerDescription) ?>
                <?php if ($partnerUrl): ?>
                    <a href="<?= htmlspecialchars($partnerUrl) ?>"
                        class="credit-link"
                        target="_blank" rel="noopener">custom stickers</a>!
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>