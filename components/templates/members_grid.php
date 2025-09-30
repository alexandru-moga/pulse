<?php
// Members Grid Component Template
$gridTitle = $block_content['title'] ?? 'Our Team';
$gridSubtitle = $block_content['subtitle'] ?? 'Meet the PULSE community';
?>

<div class="container">
    <div style="text-align: center; margin: 4rem 0 2rem 0;">
        <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; background: linear-gradient(45deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent;">
            <?= htmlspecialchars($gridTitle) ?>
        </h2>
        <p style="font-size: 1.2rem; color: rgba(255, 255, 255, 0.8); font-weight: 400;">
            <?= htmlspecialchars($gridSubtitle) ?>
        </p>
    </div>

    <div class="members-grid">
        <?php
        global $db;
        if (isset($db)) {
            try {
                $stmt = $db->prepare("SELECT * FROM users WHERE role IN ('Leader', 'Co-leader', 'Member') AND active_member = 1 AND profile_public = 1 ORDER BY role DESC, first_name ASC");
                $stmt->execute();
                $members = $stmt->fetchAll();

                foreach ($members as $member):
                    // Handle profile image - prioritize custom upload over Discord avatar
                    $profile_image = $member['profile_image'] ?? '';
                    $discord_id = $member['discord_id'] ?? '';
                    $discord_avatar = $member['discord_avatar'] ?? '';

                    if (!empty($profile_image)) {
                        // Use custom uploaded image
                        $avatar = '/uploads/profiles/' . $profile_image;
                    } elseif (!empty($discord_id) && !empty($discord_avatar)) {
                        // Fallback to Discord avatar
                        $avatar = "https://cdn.discordapp.com/avatars/{$discord_id}/{$discord_avatar}.png?size=128";
                    } else {
                        // Default avatar
                        $avatar = '/images/default-avatar.svg';
                    }

                    $roleColors = [
                        'Leader' => '#ec3750',
                        'Co-leader' => '#ff8c37',
                        'Member' => '#33d6a6'
                    ];
                    $roleColor = $roleColors[$member['role'] ?? ''] ?? '#8492a6';
        ?>
                    <div class="member-card">
                        <div class="member-image">
                            <img src="<?= htmlspecialchars($avatar) ?>"
                                alt="<?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?>"
                                onerror="this.src='/images/default-avatar.svg'">
                        </div>
                        <div class="member-info">
                            <div class="member-name">
                                <?= htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?>
                            </div>
                            <div class="member-role" style="color: <?= htmlspecialchars($roleColor) ?>;">
                                <?= htmlspecialchars($member['role'] ?? '') ?>
                            </div>
                            <?php if (!empty($member['bio'] ?? '')): ?>
                                <div class="member-desc">
                                    <?= htmlspecialchars($member['bio'] ?? '') ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($member['school'] ?? '')): ?>
                                <div class="member-desc">
                                    📚 <?= htmlspecialchars($member['school'] ?? '') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
        <?php
                endforeach;
            } catch (Exception $e) {
                echo '<div style="color: #ec3750; text-align: center; padding: 2rem;">Error loading members: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } else {
            echo '<div style="color: #8492a6; text-align: center; padding: 2rem;">No database connection available</div>';
        }
        ?>
    </div>
</div>