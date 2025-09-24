<?php
// Hero Section Component Template
$heroTitle = $title ?? 'Welcome to Our Website';
$heroSubtitle = $subtitle ?? 'Discover amazing things with us';
$backgroundImage = $background_image ?? '';
$ctaText = $cta_text ?? 'Get Started';
$ctaLink = $cta_link ?? '#';
$sectionHeight = $height ?? 'medium';

$heightClass = [
    'small' => 'min-h-[300px]',
    'medium' => 'min-h-[500px]',
    'large' => 'min-h-[700px]',
    'full' => 'min-h-screen'
][$sectionHeight] ?? 'min-h-[500px]';

$backgroundStyle = $backgroundImage ? "background-image: url('{$backgroundImage}'); background-size: cover; background-position: center;" : 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);';
?>

<section class="ddb-hero <?= $heightClass ?> flex items-center justify-center text-white relative" style="<?= $backgroundStyle ?>">
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    <div class="container mx-auto px-4 text-center relative z-10">
        <h1 class="text-4xl md:text-6xl font-bold mb-4"><?= htmlspecialchars($heroTitle) ?></h1>
        <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto"><?= htmlspecialchars($heroSubtitle) ?></p>
        <?php if ($ctaText): ?>
            <a href="<?= htmlspecialchars($ctaLink) ?>" class="inline-block bg-primary hover:bg-red-600 text-white font-bold py-3 px-8 rounded-lg text-lg transition-colors">
                <?= htmlspecialchars($ctaText) ?>
            </a>
        <?php endif; ?>
    </div>
</section>
