<?php
// CTA Banner Component Template
$ctaTitle = $title ?? 'Ready to Get Started?';
$ctaDescription = $description ?? 'Join us today and start your journey.';
$buttonText = $button_text ?? 'Get Started';
$buttonLink = $button_link ?? '#';
?>

<section class="cta-banner py-16">
    <div class="container mx-auto px-4">
        <div class="cta-banner-inner bg-gradient-to-r from-primary to-secondary rounded-lg text-center text-white p-12">
            <h2 class="cta-banner-title text-3xl md:text-4xl font-bold mb-4">
                <?= htmlspecialchars($ctaTitle) ?>
            </h2>
            <p class="cta-banner-description text-lg md:text-xl mb-8 opacity-90">
                <?= htmlspecialchars($ctaDescription) ?>
            </p>
            <a href="<?= htmlspecialchars($buttonLink) ?>" 
               class="cta-banner-button inline-block bg-white text-primary font-bold py-3 px-8 rounded-lg hover:bg-gray-100 transition-colors">
                <?= htmlspecialchars($buttonText) ?>
            </a>
        </div>
    </div>
</section>
