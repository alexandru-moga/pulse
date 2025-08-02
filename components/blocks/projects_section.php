<?php
// Default values
$title = $title ?? 'Our Projects';
$subtitle = $subtitle ?? 'Check out what we\'ve been working on';
$projects = $projects ?? array();
?>

<section class="projects-section py-16 bg-white dark:bg-gray-800">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4"><?= htmlspecialchars($title) ?></h2>
            <p class="text-xl text-gray-600 dark:text-gray-400"><?= htmlspecialchars($subtitle) ?></p>
        </div>
        
        <?php if (empty($projects)): ?>
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🚀</div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No projects yet</h3>
                <p class="text-gray-600 dark:text-gray-400">Projects will be displayed here when added.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($projects as $project): ?>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition-shadow">
                        <?php if (!empty($project['image'])): ?>
                            <img src="<?= htmlspecialchars($project['image']) ?>" alt="<?= htmlspecialchars($project['name']) ?>" 
                                 class="w-full h-48 object-cover">
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                                <?php if (!empty($project['url'])): ?>
                                    <a href="<?= htmlspecialchars($project['url']) ?>" target="_blank" class="hover:text-blue-600">
                                        <?= htmlspecialchars($project['name']) ?>
                                    </a>
                                <?php else: ?>
                                    <?= htmlspecialchars($project['name']) ?>
                                <?php endif; ?>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars($project['description']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
