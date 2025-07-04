<?php
require_once __DIR__ . '/../core/init.php';
include '../components/layout/header.php';

$stmt = $db->prepare(
    "SELECT p.*, pa.status, pa.pizza_grant 
     FROM projects p
     JOIN project_assignments pa ON pa.project_id = p.id
     WHERE pa.user_id = ?"
);
$stmt->execute([$currentUser->id]);
$myProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare(
    "SELECT * FROM projects 
     WHERE id NOT IN (SELECT project_id FROM project_assignments WHERE user_id = ?)
     ORDER BY id ASC"
);
$stmt->execute([$currentUser->id]);
$availableProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalMoney = 0;
$otherRewards = [];
foreach ($myProjects as $project) {
    if (in_array($project['status'], ['accepted', 'accepted_pizza', 'completed'])) {
        $totalMoney += (float)($project['reward_amount'] ?? 0);
        if (!empty($project['reward_description'])) {
            $otherRewards[] = $project['reward_description'];
        }
    }
}

$statusLabels = [
    'accepted' => 'Accepted',
    'accepted_pizza' => 'Accepted+Pizza',
    'waiting' => 'Waiting',
    'rejected' => 'Rejected',
    'not_participating' => 'Not Participating',
    'not_sent' => 'Not Sent',
    'completed' => 'Completed'
];

$statusClasses = [
    'accepted' => 'status-accepted',
    'accepted_pizza' => 'status-accepted-pizza',
    'waiting' => 'status-waiting',
    'rejected' => 'status-rejected',
    'not_participating' => 'status-not-participating',
    'not_sent' => 'status-not-sent',
    'completed' => 'status-completed'
];
?>

<link rel="stylesheet" href="/css/projects-page.css">

<div class="projects-hero">
    <div class="hero-content">
        <h1>You Build, We Reward.</h1>
        <p class="hero-subtitle">Find your next project opportunity and the rewards that come with it.</p>
        
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-number">$<?= number_format($totalMoney, 2) ?></span>
                <span class="stat-label">Total Earned</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= count($myProjects) ?></span>
                <span class="stat-label">Projects Joined</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= count($availableProjects) ?></span>
                <span class="stat-label">Available Projects</span>
            </div>
        </div>
    </div>
</div>

<div class="projects-container">
    
    <section class="projects-section">
        <h2>My Projects</h2>
        <p class="section-subtitle">Track your progress and see what you've earned</p>
        
        <?php if ($myProjects): ?>
            <div class="projects-grid">
                <?php foreach ($myProjects as $project): 
                    $virtualStatus = ($project['status'] === 'accepted' && $project['pizza_grant'] === 'received') ? 'accepted_pizza' : $project['status'];
                    $statusLabel = $statusLabels[$virtualStatus] ?? ucfirst($virtualStatus);
                    $statusClass = $statusClasses[$virtualStatus] ?? 'status-not-sent';
                    $rewardTotal = (float)($project['reward_amount'] ?? 0);
                    $isEarned = in_array($virtualStatus, ['accepted', 'accepted_pizza', 'completed']);
                ?>
                    <div class="project-card my-project">
                        <div class="project-header">
                            <h3><?= htmlspecialchars($project['title']) ?></h3>
                            <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        </div>
                        
                        <p class="project-description"><?= htmlspecialchars($project['description'] ?? '') ?></p>
                        
                        <div class="project-details">
                            <div class="detail-item">
                                <span class="detail-label">Reward:</span>
                                <span class="detail-value">
                                    <?php if ($rewardTotal > 0): ?>
                                        $<?= number_format($rewardTotal, 2) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($project['reward_description'])): ?>
                                        <?= htmlspecialchars($project['reward_description']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <?php if ($isEarned): ?>
                                <div class="detail-item earned">
                                    <span class="detail-label">Earned:</span>
                                    <span class="detail-value earned-amount">
                                        <?php if ($rewardTotal > 0): ?>
                                            $<?= number_format($rewardTotal, 2) ?>
                                        <?php endif; ?>
                                        <?= htmlspecialchars($project['reward_description'] ?? '') ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($project['start_date'] || $project['end_date']): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value">
                                        <?= $project['start_date'] ?? 'Indefinite' ?> - <?= $project['end_date'] ?? 'Indefinite' ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($project['requirements'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Requirements:</span>
                                    <span class="detail-value"><?= nl2br(htmlspecialchars($project['requirements'])) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No projects yet</h3>
                <p>Join your first project to start earning rewards!</p>
                <button onclick="document.getElementById('available-projects').scrollIntoView()" class="cta-button">Browse Projects</button>
            </div>
        <?php endif; ?>
    </section>

    <section class="projects-section" id="available-projects">
        <h2>Available Projects</h2>
        <p class="section-subtitle">Find your next opportunity and join the project</p>
        
        <?php if ($availableProjects): ?>
            <div class="projects-grid">
                <?php foreach ($availableProjects as $project): 
                    $rewardTotal = (float)($project['reward_amount'] ?? 0);
                ?>
                    <div class="project-card available-project">
                        <div class="project-header">
                            <h3><?= htmlspecialchars($project['title']) ?></h3>
                            <span class="status-badge status-available">Available</span>
                        </div>
                        
                        <p class="project-description"><?= htmlspecialchars($project['description'] ?? '') ?></p>
                        
                        <div class="project-details">
                            <div class="detail-item">
                                <span class="detail-label">Reward:</span>
                                <span class="detail-value reward-highlight">
                                    <?php if ($rewardTotal > 0): ?>
                                        $<?= number_format($rewardTotal, 2) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($project['reward_description'])): ?>
                                        <?= htmlspecialchars($project['reward_description']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <?php if ($project['start_date'] || $project['end_date']): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value">
                                        <?= $project['start_date'] ?? 'Indefinite' ?> - <?= $project['end_date'] ?? 'Indefinite' ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($project['requirements'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Requirements:</span>
                                    <span class="detail-value"><?= nl2br(htmlspecialchars($project['requirements'])) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <form method="post" action="apply-project.php" class="apply-form">
                            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                            <button type="submit" class="apply-button">Join Project</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No available projects</h3>
                <p>Check back later for new opportunities!</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include '../components/layout/footer.php'; ?>