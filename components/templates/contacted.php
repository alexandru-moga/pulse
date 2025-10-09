<?php
// Contacted Success Component Template
$successTitle = $title ?? 'Message Received!';
$successMessage = $message ?? 'Your message has been successfully submitted. Our team will review your message and contact you shortly.';
?>

<section class="applied">
    <div class="applied-container">
        <h1 class="applied-title"><?= htmlspecialchars($successTitle) ?></h1>

        <div class="applied-message">
            <?= htmlspecialchars($successMessage) ?>
        </div>

        <div class="applied-next-steps">
            <h2>What Happens Next?</h2>
            <ul>
                <li>We will review your message within 3-5 business days</li>
                <li>Check your email regularly for updates</li>
                <li>Join our Discord community for real-time updates</li>
            </ul>
        </div>

        <a href="/" class="applied-home-btn">
            Return to Homepage
        </a>
    </div>
</section>