<?php
// Values Hero Component Template  
$valuesTitle = $title ?? 'Welcome to <span class="text-red-500">Suceava Hacks</span>';
$valuesSubtitle = $subtitle ?? 'STUDENT-LED TECH COMMUNITY';
$valuesDescription = $description ?? 'Join a vibrant community of students passionate about technology and innovation. We build, learn, and grow together through hackathons, workshops, and collaborative projects.';
$primaryButtonText = $primary_button_text ?? 'Get Involved';
$primaryButtonUrl = $primary_button_url ?? '/join.php';
$secondaryButtonText = $secondary_button_text ?? 'Learn More';
$secondaryButtonUrl = $secondary_button_url ?? '/about.php';
?>

<div class="welcome-container">
    <div class="welcome-content">
        <div class="badge animate-fade-in">
            <span class="badge-text"><?= htmlspecialchars($valuesSubtitle) ?></span>
        </div>

        <h1 class="welcome-heading animate-slide-in">
            <?= $valuesTitle ?>
        </h1>

        <p class="welcome-description animate-fade-in-delayed">
            <?= htmlspecialchars($valuesDescription) ?>
        </p>

        <div class="welcome-buttons animate-fade-in-long-delayed">
            <a href="<?= htmlspecialchars($primaryButtonUrl) ?>" class="primary-button">
                <?= htmlspecialchars($primaryButtonText) ?>
            </a>

            <a href="<?= htmlspecialchars($secondaryButtonUrl) ?>" class="secondary-button">
                <?= htmlspecialchars($secondaryButtonText) ?>
            </a>
        </div>
    </div>

    <div class="welcome-scroll-indicator animate-fade-in-longest-delayed">
        <div class="bounce">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>
    </div>
</div>