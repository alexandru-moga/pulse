<?php
// Default values
$title = $title ?? 'Ready to Get Started?';
$description = $description ?? 'Join thousands of satisfied customers today';
$button_text = $button_text ?? 'Get Started Now';
$button_link = $button_link ?? '#';
$secondary_button_text = $secondary_button_text ?? 'Learn More';
$secondary_button_link = $secondary_button_link ?? '#';
$background_color = $background_color ?? '#1f2937';
$text_color = $text_color ?? '#ffffff';
?>

<section class="cta-banner py-16" style="background-color: <?= htmlspecialchars($background_color) ?>">
    <div class="max-w-4xl mx-auto text-center px-4">
        <h2 class="text-3xl md:text-4xl font-bold mb-6" style="color: <?= htmlspecialchars($text_color) ?>">
            <?= htmlspecialchars($title) ?>
        </h2>
        <p class="text-xl mb-8 opacity-90" style="color: <?= htmlspecialchars($text_color) ?>">
            <?= htmlspecialchars($description) ?>
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= htmlspecialchars($button_link) ?>" 
               class="inline-block px-8 py-4 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">
                <?= htmlspecialchars($button_text) ?>
            </a>
            <?php if ($secondary_button_text): ?>
                <a href="<?= htmlspecialchars($secondary_button_link) ?>" 
                   class="inline-block px-8 py-4 border-2 font-semibold rounded-lg hover:bg-white hover:text-gray-900 transition-colors"
                   style="border-color: <?= htmlspecialchars($text_color) ?>; color: <?= htmlspecialchars($text_color) ?>">
                    <?= htmlspecialchars($secondary_button_text) ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
            <?php endif; ?>
        </div>
    </div>
</section>
