<?php
// Members Grid Component Template
$gridTitle = $title ?? 'Our Team';
$gridSubtitle = $subtitle ?? 'Meet the PULSE community';
?>

<div class="ddb-members-grid py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($gridTitle) ?></h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-300"><?= htmlspecialchars($gridSubtitle) ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            global $db;
            if (isset($db)) {
                try {
                    $stmt = $db->prepare("SELECT * FROM users WHERE role IN ('Leader', 'Co-leader', 'Member') AND active_member = 1 ORDER BY role DESC, first_name ASC");
                    $stmt->execute();
                    $members = $stmt->fetchAll();

                    foreach ($members as $member):
                        $discord_id = $member['discord_id'] ?? '';
                        $discord_avatar = $member['discord_avatar'] ?? '';
                        $avatar = !empty($discord_id) && !empty($discord_avatar)
                            ? "https://cdn.discordapp.com/avatars/{$discord_id}/{$discord_avatar}.png?size=128"
                            : '/images/default-avatar.png';

                        $roleColors = [
                            'Leader' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                            'Co-leader' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                            'Member' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                        ];
                        $roleColor = $roleColors[$member['role'] ?? ''] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
            ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 text-center">
                            <img src="<?= htmlspecialchars($avatar) ?>"
                                alt="<?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?>"
                                class="w-16 h-16 rounded-full mx-auto mb-4 object-cover"
                                onerror="this.src='/images/default-avatar.png'">

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?>
                            </h3>

                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $roleColor ?> mt-2">
                                <?= htmlspecialchars($member['role'] ?? '') ?>
                            </span>

                            <?php if (!empty($member['school'] ?? '')): ?>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    <?= htmlspecialchars($member['school'] ?? '') ?>
                                </p>
                            <?php endif; ?>
                        </div>
            <?php
                    endforeach;
                } catch (Exception $e) {
                    echo '<p class="text-red-500">Error loading members: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
            } else {
                echo '<p class="text-gray-500">No database connection available</p>';
            }
            ?>
        </div>
    </div>
</div>