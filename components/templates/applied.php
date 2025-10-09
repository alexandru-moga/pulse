<?php
// Applied Success Component Template
$successTitle = $title ?? 'Application Received!';
$successMessage = $message ?? 'Your application has been successfully submitted. Our team will review your submission and contact you shortly.';
$nextSteps = $next_steps ?? array();

// Default next steps if none provided
if (empty($nextSteps)) {
    $nextSteps = array(
        array('step' => 'We will review your application within 3-5 business days'),
        array('step' => 'Check your email regularly for updates'),
        array('step' => 'Join our Discord community for real-time updates')
    );
}
?>

<section class="applied">
    <div class="applied-container">
        <h1 class="applied-title"><?= htmlspecialchars($successTitle) ?></h1>

        <div class="applied-message">
            <?= htmlspecialchars($successMessage) ?>
        </div>

        <?php if (!empty($nextSteps)): ?>
            <div class="applied-next-steps">
                <h2>What Happens Next?</h2>
                <ul>
                    <?php foreach ($nextSteps as $step): ?>
                        <li><?= htmlspecialchars($step['step'] ?? '') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <a href="/" class="applied-home-btn">
            Return to Homepage
        </a>
    </div>
</section>