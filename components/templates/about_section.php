<?php
// About Section Component Template
$aboutTitle = $title ?? 'About Us';
$aboutDescription = $description ?? 'Learn more about our mission and values.';
$imageUrl = $image_url ?? '';
?>

<section class="about-section container mx-auto py-12">
    <div class="about">
        <?php if ($imageUrl): ?>
            <div class="about-image-container mb-8 text-center">
                <img src="<?= htmlspecialchars($imageUrl) ?>" 
                     alt="<?= htmlspecialchars($aboutTitle) ?>" 
                     class="rounded-lg shadow-lg max-w-full h-auto mx-auto" 
                     style="max-height: 400px;">
            </div>
        <?php endif; ?>
        
        <div class="about-content text-center max-w-4xl mx-auto">
            <h2 class="text-3xl font-bold mb-6"><?= htmlspecialchars($aboutTitle) ?></h2>
            <div class="about-description text-lg leading-relaxed">
                <?= $aboutDescription ?>
            </div>
        </div>
    </div>
</section>
