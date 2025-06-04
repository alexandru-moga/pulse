<?php

$db = new Database();
$members = $db->query(
    "SELECT id, first_name, last_name, role, description, github_username, discord_id
     FROM users
     WHERE active_member = 1
     ORDER BY FIELD(role, 'Leader', 'Co-leader', 'Member'), join_date DESC"
)->fetchAll();

function getMemberImage($id) {
    $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/images/members/';
    $baseUrl = BASE_URL . 'images/members/';
    $extensions = ['jpg', 'jpeg', 'png', 'webp'];
    foreach ($extensions as $ext) {
        $file = $baseDir . $id . '.' . $ext;
        if (file_exists($file)) {
            return $baseUrl . $id . '.' . $ext;
        }
    }
    return $baseUrl . 'default.webp';
}
?>

<section class="members-grid">
    <div class="grid-container">
        <?php foreach ($members as $member):
            $firstName = isset($member['first_name']) ? $member['first_name'] : '';
            $lastName = isset($member['last_name']) ? $member['last_name'] : '';
            $fullName = trim($firstName . ' ' . $lastName);
            if ($fullName === '') $fullName = 'Community Member';

            $role = isset($member['role']) ? strtolower($member['role']) : 'member';
            $roleClass = 'role-' . preg_replace('/[^a-z0-9]/', '-', $role);

            $description = isset($member['description']) ? $member['description'] : '';
            $github = isset($member['github_username']) ? $member['github_username'] : '';

            $imagePath = getMemberImage($member['id']);
        ?>
        <div class="member-card <?= $roleClass ?>">
            <div class="member-image">
                <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($fullName) ?>">
            </div>
            <div class="member-info">
                <h3 class="member-name"><?= htmlspecialchars($fullName) ?></h3>
                <?php if (!empty($member['role'])): ?>
                    <p class="member-role"><?= htmlspecialchars(ucfirst($member['role'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($description)): ?>
                    <p class="member-desc"><?= htmlspecialchars($description) ?></p>
                <?php endif; ?>
                <div class="member-social">
                    <?php if (!empty($github)): ?>
                        <a href="https://github.com/<?= htmlspecialchars($github) ?>"
                           target="_blank"
                           class="github-button"
                           title="GitHub Profile">
                            <i class="fab fa-github"></i> GitHub
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
