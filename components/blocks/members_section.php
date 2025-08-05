<?php
// Default values
$title = $title ?? 'Our Team';
$subtitle = $subtitle ?? 'Meet the amazing people behind ' . htmlspecialchars($settings['site_title'] ?? 'our organization');
$show_all_members = $show_all_members ?? true;
$members_per_row = $members_per_row ?? '3';

$gridCols = [
    '2' => 'md:grid-cols-2',
    '3' => 'md:grid-cols-3', 
    '4' => 'md:grid-cols-4'
];
$gridClass = $gridCols[$members_per_row] ?? 'md:grid-cols-3';

// Sample members data
$members = $members ?? [
    ['name' => 'John Doe', 'position' => 'CEO & Founder', 'bio' => 'Leading the team with vision and passion.', 'image' => ''],
    ['name' => 'Jane Smith', 'position' => 'CTO', 'bio' => 'Tech innovator driving our digital transformation.', 'image' => ''],
    ['name' => 'Mike Johnson', 'position' => 'Lead Developer', 'bio' => 'Building amazing digital experiences.', 'image' => '']
];
?>

<section class="members-section py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                <?= htmlspecialchars($title) ?>
            </h2>
            <p class="text-xl text-gray-600">
                <?= htmlspecialchars($subtitle) ?>
            </p>
        </div>
        
        <div class="grid grid-cols-1 <?= $gridClass ?> gap-8">
            <?php foreach ($members as $member): ?>
                <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                    <?php if (!empty($member['image'])): ?>
                        <img src="<?= htmlspecialchars($member['image']) ?>" alt="<?= htmlspecialchars($member['name']) ?>" 
                             class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">
                    <?php else: ?>
                        <div class="w-24 h-24 rounded-full mx-auto mb-4 bg-gray-300 flex items-center justify-center">
                            <span class="text-2xl text-gray-600">👤</span>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        <?= htmlspecialchars($member['name']) ?>
                    </h3>
                    <p class="text-sm text-blue-600 mb-3">
                        <?= htmlspecialchars($member['position']) ?>
                    </p>
                    <p class="text-gray-600 text-sm">
                        <?= htmlspecialchars($member['bio']) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
