<?php
// Stickers Component Template
$stickerTitle = $title ?? 'Sticker Credits Available!';
$stickerDescription = $description ?? 'Thanks to JukeBox for our custom stickers!';
$partnerUrl = $partner_url ?? 'https://www.jukeboxprint.com/custom-stickers';
?>

<section class="py-16 bg-black">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-gray-900 rounded-xl shadow-2xl p-8 text-center border border-gray-700 hover:shadow-3xl hover:scale-105 transition-all duration-300">
            <div class="text-4xl mb-4">🏷️</div>
            <h3 class="text-xl font-semibold text-white mb-4">
                <?= htmlspecialchars($stickerTitle) ?>
            </h3>
            <p class="text-lg text-gray-300">
                <?= htmlspecialchars($stickerDescription) ?>
                <?php if ($partnerUrl): ?>
                    <a href="<?= htmlspecialchars($partnerUrl) ?>"
                        class="text-red-500 hover:text-red-400 font-medium underline decoration-2 underline-offset-4 transition-colors"
                        target="_blank" rel="noopener">custom stickers</a>!
                <?php endif; ?>
            </p>
        </div>
    </div>
</section>