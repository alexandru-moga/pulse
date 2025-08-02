<?php
// Default values
$title = $title ?? 'About Us';
$description = $description ?? 'Learn more about our mission and values.';
$image_url = $image_url ?? '';
?>

<section class="about-section py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                    <?= htmlspecialchars($title) ?>
                </h2>
                <div class="prose prose-lg text-gray-700">
                    <?= $description ?>
                </div>
            </div>
            <?php if ($image_url): ?>
                <div>
                    <img src="<?= htmlspecialchars($image_url) ?>" alt="About us" 
                         class="w-full h-auto rounded-lg shadow-lg">
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
