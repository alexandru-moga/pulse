<?php
// Members Grid Component Template
$gridTitle = $block_content['title'] ?? 'Our Team';
$gridSubtitle = $block_content['subtitle'] ?? 'Meet the PULSE community';
?>

<div class="container" style="padding: 2rem 0;">
    <div style="text-align: center; margin: 2rem 0 3rem 0;">
        <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; background: linear-gradient(45deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent;">
            <?= htmlspecialchars($gridTitle) ?>
        </h2>
        <p style="font-size: 1.2rem; color: rgba(255, 255, 255, 0.8); font-weight: 400;">
            <?= htmlspecialchars($gridSubtitle) ?>
        </p>
    </div>

    <div style="width: 100%;">
        <?php
        global $db;
        if (isset($db)) {
            try {
                // Fetch leaders separately
                $leaderStmt = $db->prepare("SELECT * FROM users WHERE role = 'Leader' AND active_member = 1 AND profile_public = 1 ORDER BY first_name ASC");
                $leaderStmt->execute();
                $leaders = $leaderStmt->fetchAll();
                
                // Fetch co-leaders separately  
                $coLeaderStmt = $db->prepare("SELECT * FROM users WHERE role = 'Co-leader' AND active_member = 1 AND profile_public = 1 ORDER BY first_name ASC");
                $coLeaderStmt->execute();
                $coLeaders = $coLeaderStmt->fetchAll();
                
                // Fetch regular members
                $memberStmt = $db->prepare("SELECT * FROM users WHERE role = 'Member' AND active_member = 1 AND profile_public = 1 ORDER BY first_name ASC");
                $memberStmt->execute();
                $members = $memberStmt->fetchAll();

                // Display Leaders Section
                if (!empty($leaders)):
        ?>
                    <div style="width: 100%; margin-bottom: 3rem;">
                        <h3 style="font-size: 2rem; font-weight: 700; text-align: center; margin-bottom: 2rem; background: linear-gradient(45deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent;">
                            Leadership
                        </h3>
                        <div class="members-grid" style="max-width: 800px; margin: 0 auto 2rem auto;">
                            <?php foreach ($leaders as $member):
                                // Handle profile image - prioritize custom upload over Discord avatar
                                $profile_image = $member['profile_image'] ?? '';
                                $discord_id = $member['discord_id'] ?? '';
                                $discord_avatar = $member['discord_avatar'] ?? '';

                                if (!empty($profile_image)) {
                                    $avatar = '/images/members/' . $profile_image;
                                } elseif (!empty($discord_id) && !empty($discord_avatar)) {
                                    $avatar = "https://cdn.discordapp.com/avatars/{$discord_id}/{$discord_avatar}.png?size=128";
                                } else {
                                    $avatar = '/images/default-avatar.svg';
                                }

                                $roleColor = '#ec3750'; // Leader color
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
                            <?php endforeach; ?>
                        </div>
                    </div>
        <?php
                endif;
                
                // Display Co-Leaders Section
                if (!empty($coLeaders)):
        ?>
                    <div style="width: 100%; margin-bottom: 3rem;">
                        <h3 style="font-size: 2rem; font-weight: 700; text-align: center; margin-bottom: 2rem; background: linear-gradient(45deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent;">
                            Co-Leaders
                        </h3>
                        <div class="members-grid">
                            <?php foreach ($coLeaders as $member):
                                // Handle profile image - prioritize custom upload over Discord avatar
                                $profile_image = $member['profile_image'] ?? '';
                                $discord_id = $member['discord_id'] ?? '';
                                $discord_avatar = $member['discord_avatar'] ?? '';

                                if (!empty($profile_image)) {
                                    $avatar = '/images/members/' . $profile_image;
                                } elseif (!empty($discord_id) && !empty($discord_avatar)) {
                                    $avatar = "https://cdn.discordapp.com/avatars/{$discord_id}/{$discord_avatar}.png?size=128";
                                } else {
                                    $avatar = '/images/default-avatar.svg';
                                }

                                $roleColor = '#ff8c37'; // Co-leader color
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
                            <?php endforeach; ?>
                        </div>
                    </div>
        <?php
                endif;
                
                // Display Members Section
                if (!empty($members)):
        ?>
                    <div style="width: 100%;">
                        <h3 style="font-size: 2rem; font-weight: 700; text-align: center; margin-bottom: 2rem; background: linear-gradient(45deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; color: transparent;">
                            Members
                        </h3>
                        <div class="members-grid">
                            <?php foreach ($members as $member):
                                // Handle profile image - prioritize custom upload over Discord avatar
                                $profile_image = $member['profile_image'] ?? '';
                                $discord_id = $member['discord_id'] ?? '';
                                $discord_avatar = $member['discord_avatar'] ?? '';

                                if (!empty($profile_image)) {
                                    $avatar = '/images/members/' . $profile_image;
                                } elseif (!empty($discord_id) && !empty($discord_avatar)) {
                                    $avatar = "https://cdn.discordapp.com/avatars/{$discord_id}/{$discord_avatar}.png?size=128";
                                } else {
                                    $avatar = '/images/default-avatar.svg';
                                }

                                $roleColor = '#33d6a6'; // Member color
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
                            <?php endforeach; ?>
                        </div>
                    </div>
        <?php
                endif;
            } catch (Exception $e) {
                echo '<div style="color: #ec3750; text-align: center; padding: 2rem;">Error loading members: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } else {
            echo '<div style="color: #8492a6; text-align: center; padding: 2rem;">No database connection available</div>';
        }
        ?>
    </div>
</div>