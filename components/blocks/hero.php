<?php
// Default values
$title = $title ?? 'Welcome to Our Website';
$subtitle = $subtitle ?? 'We provide amazing services for your business';
$button_text = $button_text ?? 'Get Started';
$button_link = $button_link ?? '#';
$background_image = $background_image ?? '';
$text_color = $text_color ?? '#ffffff';
$button_color = $button_color ?? '#ef4444';
$height = $height ?? 'large';

$heightClass = 'min-h-screen';
if ($height === 'small') $heightClass = 'h-96';
if ($height === 'fullscreen') $heightClass = 'h-screen';

$backgroundStyle = $background_image ? 
    'background-image: url(' . htmlspecialchars($background_image) . '); background-size: cover; background-position: center;' : 
    'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);';
?>

<section class="hero-section <?= $heightClass ?> relative flex items-center justify-center text-center" style="<?= $backgroundStyle ?>">
    <div class="absolute inset-0 bg-black bg-opacity-40"></div>
    <div class="relative z-10 max-w-4xl mx-auto px-4">
        <h1 class="text-4xl md:text-6xl font-bold mb-6" style="color: <?= htmlspecialchars($text_color) ?>">
            <?= htmlspecialchars($title) ?>
        </h1>
        <p class="text-xl md:text-2xl mb-8 opacity-90" style="color: <?= htmlspecialchars($text_color) ?>">
            <?= htmlspecialchars($subtitle) ?>
        </p>
        <a href="<?= htmlspecialchars($button_link) ?>" 
           class="inline-block px-8 py-4 text-lg font-semibold rounded-lg transition-all duration-300 hover:transform hover:scale-105"
           style="background-color: <?= htmlspecialchars($button_color) ?>; color: white;">
            <?= htmlspecialchars($button_text) ?>
        </a>
    </div>
</section>
        <a href="<?= htmlspecialchars($button_link) ?>" 
           class="inline-block px-8 py-4 text-lg font-semibold rounded-lg transition-all duration-300 hover:transform hover:scale-105"
           style="background-color: <?= htmlspecialchars($button_color) ?>; color: white;">
            <?= htmlspecialchars($button_text) ?>
        </a>
    </div>
</section>
                <?= htmlspecialchars($button_text) ?>
            </a>
        <?php endif; ?>
    </div>
</section>
