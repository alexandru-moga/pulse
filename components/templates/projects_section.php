<?php
// Projects Section Component Template
require_once __DIR__ . '/../helpers/image_emoji_helper.php';

$projectsTitle = $title ?? 'Our Projects';
$projectsSubtitle = $subtitle ?? 'Check out what we\'ve been working on';
$projects = $projects ?? array();

// Parse projects if it's a JSON string
if (is_string($projects)) {
    $decoded = json_decode($projects, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $projects = $decoded;
    }
}

// Default projects if none provided
if (empty($projects)) {
    $projects = array(
        array(
            'name' => 'PULSE Website',
            'description' => 'Modern, responsive website built with cutting-edge technologies.',
            'image' => '/images/project-placeholder.jpg',
            'url' => 'https://pulse.com'
        ),
        array(
            'name' => 'Community Platform',
            'description' => 'A platform to connect and engage our community members.',
            'image' => '/images/project-placeholder.jpg',
            'url' => 'https://community.pulse.com'
        )
    );
}
?>
?>

<section class="projects-section py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                <?= htmlspecialchars($projectsTitle) ?>
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-300">
                <?= htmlspecialchars($projectsSubtitle) ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($projects as $project): ?>
                <div class="project-card bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <?php if (!empty($project['image'])): ?>
                        <div class="project-image">
                            <?= renderImageOrEmoji($project['image'], 'w-full h-48 object-cover') ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="project-content p-6">
                        <h3 class="project-title text-xl font-semibold mb-3 text-gray-900 dark:text-white">
                            <?= htmlspecialchars($project['name'] ?? '') ?>
                        </h3>
                        
                        <p class="project-description text-gray-600 dark:text-gray-300 mb-4">
                            <?= htmlspecialchars($project['description'] ?? '') ?>
                        </p>
                        
                        <?php if (!empty($project['url'])): ?>
                            <a href="<?= htmlspecialchars($project['url']) ?>" 
                               target="_blank"
                               class="project-link inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                                View Project
                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
