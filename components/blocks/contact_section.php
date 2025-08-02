<?php
// Default values
$title = $title ?? 'Contact Us';
$email = $email ?? 'contact@pulse.com';
$phone = $phone ?? '+1 (555) 123-4567';
$address = $address ?? '123 Main St, City, State 12345';
?>

<section class="contact-section py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                <?= htmlspecialchars($title) ?>
            </h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-8 bg-gray-50 rounded-lg">
                <div class="text-4xl mb-4">📧</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Email</h3>
                <p class="text-gray-600"><?= htmlspecialchars($email) ?></p>
            </div>
            
            <div class="text-center p-8 bg-gray-50 rounded-lg">
                <div class="text-4xl mb-4">📞</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Phone</h3>
                <p class="text-gray-600"><?= htmlspecialchars($phone) ?></p>
            </div>
            
            <div class="text-center p-8 bg-gray-50 rounded-lg">
                <div class="text-4xl mb-4">📍</div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Address</h3>
                <p class="text-gray-600"><?= nl2br(htmlspecialchars($address)) ?></p>
            </div>
        </div>
    </div>
</section>
