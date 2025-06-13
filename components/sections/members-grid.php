<?php
function renderMembersGrid($db) {
    $members = $db->query(
        "SELECT id, first_name, last_name, role, description, github_username, discord_id
         FROM users
         WHERE active_member = 1
         ORDER BY FIELD(role, 'Leader', 'Co-leader', 'Member'), join_date DESC"
    )->fetchAll();

    function getMemberImage($member) {
        if (!empty($member['github_username'])) {
            return 'https://github.com/' . urlencode($member['github_username']) . '.png?size=120';
        }
        return 'images/members/default.webp';
    }
    ?>
    
    <div class="members-grid">
    <?php foreach ($members as $member): ?>
        <?php
            $fullName = $member['first_name'] . ' ' . $member['last_name'];
            $description = $member['description'] ?? '';
        ?>
        <div class="member-card">
            <div class="member-image">
                <img src="<?= htmlspecialchars(getMemberImage($member)) ?>"
                     alt="Profile photo of <?= htmlspecialchars($fullName) ?>"
                     loading="lazy" width="120" height="120">
            </div>
            <div class="member-info">
                <div class="member-name"><?= htmlspecialchars($fullName) ?></div>
                <div class="member-role"><?= htmlspecialchars(ucfirst($member['role'])) ?></div>
                <div class="member-desc"><?= htmlspecialchars($description) ?></div>
                <?php if (!empty($member['github_username'])): ?>
                    <a href="https://github.com/<?= htmlspecialchars($member['github_username']) ?>" target="_blank" rel="noopener">GitHub</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?php
}
?>
