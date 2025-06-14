<?php
require_once __DIR__ . '/../core/init.php'; 

$stmt = $db->prepare(
    "SELECT p.*, pa.status, pa.pizza_grant
     FROM projects p
     JOIN project_assignments pa ON pa.project_id = p.id
     WHERE pa.user_id = ?"
);
$stmt->execute([$currentUser->id]);
$myProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<h2>My Projects</h2>
<?php if ($myProjects): ?>
    <ul>
    <?php foreach ($myProjects as $project): ?>
        <li>
            <strong><?= htmlspecialchars($project['title']) ?></strong><br>
            <?= htmlspecialchars($project['description']) ?><br>
            Status: <?= htmlspecialchars($project['status']) ?><br>
            Reward: $<?= htmlspecialchars($project['reward_amount']) ?> <?= htmlspecialchars($project['reward_description']) ?><br>
            Requirements: <?= nl2br(htmlspecialchars($project['requirements'])) ?><br>
            Start: <?= $project['start_date'] ?? 'Indefinite' ?> |
            End: <?= $project['end_date'] ?? 'Indefinite' ?><br>
            Pizza Grant: <?= htmlspecialchars($project['pizza_grant']) ?>
        </li>
    <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>You have no assigned projects.</p>
<?php endif; ?>
