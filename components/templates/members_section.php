<?php
// Members Section Component Template
$sectionTitle = $title ?? 'Our Team';
$sectionSubtitle = $subtitle ?? 'Meet the amazing people behind PULSE';
$showAllMembers = $show_all_members ?? true;
$membersPerRow = $members_per_row ?? '3';
?>

<section class="members-section py-16">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                <?= htmlspecialchars($sectionTitle) ?>
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-300">
                <?= htmlspecialchars($sectionSubtitle) ?>
            </p>
        </div>
        
        <div class="members-grid grid grid-cols-1 md:grid-cols-<?= $membersPerRow ?> gap-8">
            <?php
            // This would typically fetch from database
            // For now, show placeholder content
            global $db;
            if (isset($db)) {
                try {
                    $query = "SELECT * FROM users WHERE role IN ('Leader', 'Co-leader', 'Member') AND active_member = 1 ORDER BY role DESC, first_name ASC";
                    if (!$showAllMembers) {
                        $query .= " LIMIT 6";
                    }
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $members = $stmt->fetchAll();
                    
                    foreach ($members as $member):
                        $profileImage = !empty($member['profile_image']) ? $member['profile_image'] : '/images/default-avatar.png';
                        $fullName = trim($member['first_name'] . ' ' . $member['last_name']);
                        $role = $member['role'] ?? 'Member';
                        $githubUrl = $member['github_username'] ? 'https://github.com/' . $member['github_username'] : '';
                ?>
                        <div class="member-card bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow">
                            <img src="<?= htmlspecialchars($profileImage) ?>" 
                                 alt="<?= htmlspecialchars($fullName) ?>"
                                 class="member-image w-24 h-24 rounded-full mx-auto mb-4 object-cover">
                            <h3 class="member-name text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                <?= htmlspecialchars($fullName) ?>
                            </h3>
                            <p class="member-role text-primary font-medium mb-3">
                                <?= htmlspecialchars($role) ?>
                            </p>
                            <?php if ($githubUrl): ?>
                                <a href="<?= htmlspecialchars($githubUrl) ?>" 
                                   target="_blank" 
                                   class="text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">
                                    <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                <?php 
                    endforeach;
                } catch (Exception $e) {
                    // Fallback content if database fails
                    echo '<div class="col-span-full text-center text-gray-600 dark:text-gray-400">Unable to load team members.</div>';
                }
            } else {
                // Fallback content if no database
                echo '<div class="col-span-full text-center text-gray-600 dark:text-gray-400">Team members will be displayed here.</div>';
            }
            ?>
        </div>
    </div>
</section>
