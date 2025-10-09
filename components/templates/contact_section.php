<?php
// Contact Section Component Template
$sectionTitle = $title ?? 'Contact Us';
$contactEmail = $email ?? 'contact@pulse.com';
$contactPhone = $phone ?? '+1 (555) 123-4567';
$contactAddress = $address ?? '123 Main St, City, State 12345';
?>

<section class="contact-section py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                <?= htmlspecialchars($sectionTitle) ?>
            </h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="contact-item text-center p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                <div class="contact-icon text-3xl mb-4">📧</div>
                <h3 class="contact-label text-lg font-semibold mb-2 text-gray-900 dark:text-white">Email</h3>
                <a href="mailto:<?= htmlspecialchars($contactEmail) ?>" 
                   class="contact-value text-primary hover:text-secondary transition-colors">
                    <?= htmlspecialchars($contactEmail) ?>
                </a>
            </div>
            
            <div class="contact-item text-center p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                <div class="contact-icon text-3xl mb-4">📞</div>
                <h3 class="contact-label text-lg font-semibold mb-2 text-gray-900 dark:text-white">Phone</h3>
                <a href="tel:<?= htmlspecialchars($contactPhone) ?>" 
                   class="contact-value text-primary hover:text-secondary transition-colors">
                    <?= htmlspecialchars($contactPhone) ?>
                </a>
            </div>
            
            <div class="contact-item text-center p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                <div class="contact-icon text-3xl mb-4">📍</div>
                <h3 class="contact-label text-lg font-semibold mb-2 text-gray-900 dark:text-white">Address</h3>
                <p class="contact-value text-gray-600 dark:text-gray-300">
                    <?= nl2br(htmlspecialchars($contactAddress)) ?>
                </p>
            </div>
        </div>
    </div>
</section>
