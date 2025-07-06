<?php
require_once __DIR__ . '/../core/init.php';
include '../components/layout/header.php';

$ysws_feed_url = "https://ysws.hackclub.com/feed.xml";
$ysws_feed = @simplexml_load_file($ysws_feed_url);
$ysws_projects = [];
if ($ysws_feed && isset($ysws_feed->channel->item)) {
    foreach ($ysws_feed->channel->item as $item) {
        $ysws_projects[] = [
            'title' => (string)$item->title,
            'link' => (string)$item->link,
            'description' => (string)$item->description,
            'pubDate' => (string)$item->pubDate,
        ];
    }
}

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
$unassignedProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$today = date('Y-m-d');
$availableProjects = [];
$pastProjects = [];
foreach ($unassignedProjects as $project) {
    if (empty($project['end_date']) || $project['end_date'] >= $today) {
        $availableProjects[] = $project;
    } else {
        $pastProjects[] = $project;
    }
}

function getYswsLink($requirements) {
    if (preg_match('/YSWS:\s*(https?:\/\/\S+)/i', $requirements ?? '', $m)) {
        return $m[1];
    }
    return null;
}
function formatYswsDescription($descHtml) {
    if (empty($descHtml)) return ['main' => '', 'deadline' => '', 'discussion' => '', 'grant' => ''];
    $dom = new DOMDocument();
    @$dom->loadHTML('<div>' . $descHtml . '</div>');
    $ps = $dom->getElementsByTagName('p');
    $main = '';
    $deadline = '';
    $discussion = '';
    $grant = '';
    foreach ($ps as $p) {
        $text = trim($p->textContent);
        $html = $p->C14N();
        if (stripos($text, 'deadline:') !== false) {
            $deadline = htmlspecialchars($text);
        } elseif (stripos($text, 'grant') !== false && (stripos($text, '$') !== false || stripos($text, 'meal') !== false)) {
            $lines = preg_split('/\n|\r/', $text);
            $grantList = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match('/^\$\d+.*?:/', $line)) {
                    $grantList[] = '<li>' . htmlspecialchars($line) . '</li>';
                }
            }
            if ($grantList) {
                $grant = '<ul class="ysws-grant-list">' . implode('', $grantList) . '</ul>';
            } else {
                $grant = '<div class="ysws-grant-desc">' . htmlspecialchars($text) . '</div>';
            }
        } elseif (stripos($text, 'discussion') !== false || stripos($text, 'slack') !== false || stripos($html, 'href') !== false) {
            $discussion = $html;
        } else {
            $main .= '<div class="ysws-main-desc">' . htmlspecialchars($text) . '</div>';
        }
    }
    return [
        'main' => $main,
        'deadline' => $deadline,
        'discussion' => $discussion,
        'grant' => $grant
    ];
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
        <p class="section-subtitle">All projects you participated in</p>
        <?php if ($myProjects): ?>
            <div class="projects-grid">
                <?php foreach ($myProjects as $project):
                    $virtualStatus = ($project['status'] === 'accepted' && $project['pizza_grant'] === 'received') ? 'accepted_pizza' : $project['status'];
                    $statusLabel = $statusLabels[$virtualStatus] ?? ucfirst($virtualStatus);
                    $statusClass = $statusClasses[$virtualStatus] ?? 'status-not-sent';
                    $rewardTotal = (float)($project['reward_amount'] ?? 0);
                    $yswsLink = getYswsLink($project['requirements']);
                    $isYswsDescription = !empty($project['description']) && strpos($project['description'], '<p>') !== false;
                    $formattedDesc = $isYswsDescription ? formatYswsDescription($project['description']) : null;
                    $deadline = $project['end_date'] ?? '';
                ?>
                <div class="project-card my-project<?= $yswsLink ? ' ysws-linked' : '' ?>">
                    <div class="project-header">
                        <h3><?= htmlspecialchars($project['title']) ?></h3>
                        <div class="project-badges">
                            <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        </div>
                    </div>
                    <?php if ($isYswsDescription && $formattedDesc): ?>
                        <?= $formattedDesc['main'] ?>
                        <?php if ($formattedDesc['grant']): ?>
                            <div class="ysws-section-heading">Grant Amounts:</div>
                            <?= $formattedDesc['grant'] ?>
                        <?php endif; ?>
                        <?php if ($formattedDesc['deadline']): ?>
                            <div class="ysws-deadline"><?= $formattedDesc['deadline'] ?></div>
                        <?php endif; ?>
                        <?php if ($formattedDesc['discussion']): ?>
                            <div class="ysws-section-heading">Discussion:</div>
                            <div class="ysws-discussion"><?= $formattedDesc['discussion'] ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="project-description"><?= htmlspecialchars($project['description'] ?? '') ?></div>
                    <?php endif; ?>
                    <div class="project-details">
                        <div class="detail-item">
                            <span class="detail-label">Reward:</span>
                            <span class="detail-value">
                                <?php if ($rewardTotal > 0): ?>
                                    $<?= number_format($rewardTotal, 2) ?>
                                <?php endif; ?>
                                <?= htmlspecialchars($project['reward_description'] ?? '') ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Requirements:</span>
                            <span class="detail-value">
                                <?= nl2br(htmlspecialchars(trim(preg_replace('/YSWS:\s*https?:\/\/\S+/i', '', $project['requirements'] ?? '')))) ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Deadline:</span>
                            <span class="detail-value"><?= $deadline ?: 'Indefinite' ?></span>
                        </div>
                        <?php if ($yswsLink): ?>
                            <div class="ysws-link-row">
                                <span class="detail-label">YSWS Link:</span>
                                <a href="<?= htmlspecialchars($yswsLink) ?>" target="_blank" class="ysws-link"><?= htmlspecialchars($yswsLink) ?></a>
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
            </div>
        <?php endif; ?>
    </section>

    <section class="projects-section" id="available-projects">
        <h2>Available Projects</h2>
        <p class="section-subtitle">Projects you can join now</p>
        <?php if ($availableProjects): ?>
            <div class="projects-grid">
                <?php foreach ($availableProjects as $project):
                    $rewardTotal = (float)($project['reward_amount'] ?? 0);
                    $yswsLink = getYswsLink($project['requirements']);
                    $isYswsDescription = !empty($project['description']) && strpos($project['description'], '<p>') !== false;
                    $formattedDesc = $isYswsDescription ? formatYswsDescription($project['description']) : null;
                    $deadline = $project['end_date'] ?? '';
                ?>
                <div class="project-card available-project<?= $yswsLink ? ' ysws-linked' : '' ?>">
                    <div class="project-header">
                        <h3><?= htmlspecialchars($project['title']) ?></h3>
                        <div class="project-badges">
                            <span class="status-badge status-available">Available</span>
                        </div>
                    </div>
                    <?php if ($isYswsDescription && $formattedDesc): ?>
                        <?= $formattedDesc['main'] ?>
                        <?php if ($formattedDesc['grant']): ?>
                            <div class="ysws-section-heading">Grant Amounts:</div>
                            <?= $formattedDesc['grant'] ?>
                        <?php endif; ?>
                        <?php if ($formattedDesc['deadline']): ?>
                            <div class="ysws-deadline"><?= $formattedDesc['deadline'] ?></div>
                        <?php endif; ?>
                        <?php if ($formattedDesc['discussion']): ?>
                            <div class="ysws-section-heading">Discussion:</div>
                            <div class="ysws-discussion"><?= $formattedDesc['discussion'] ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="project-description"><?= htmlspecialchars($project['description'] ?? '') ?></div>
                    <?php endif; ?>
                    <div class="project-details">
                        <div class="detail-item">
                            <span class="detail-label">Reward:</span>
                            <span class="detail-value reward-highlight">
                                <?php if ($rewardTotal > 0): ?>
                                    $<?= number_format($rewardTotal, 2) ?>
                                <?php endif; ?>
                                <?= htmlspecialchars($project['reward_description'] ?? '') ?>
                            </span>
                        </div>
                        <?php if (!empty($project['requirements'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Requirements:</span>
                            <span class="detail-value"><?= nl2br(htmlspecialchars(trim(preg_replace('/YSWS:\s*https?:\/\/\S+/i', '', $project['requirements'])))) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <span class="detail-label">Deadline:</span>
                            <span class="detail-value"><?= $deadline ?: 'Indefinite' ?></span>
                        </div>
                        <?php if ($yswsLink): ?>
                            <div class="ysws-link-row">
                                <span class="detail-label">YSWS Link:</span>
                                <a href="<?= htmlspecialchars($yswsLink) ?>" target="_blank" class="ysws-link"><?= htmlspecialchars($yswsLink) ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($yswsLink): ?>
                    <?php else: ?>
                        <form method="post" action="apply-project.php" class="apply-form">
                            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                            <button type="submit" class="apply-button">Join Project</button>
                        </form>
                    <?php endif; ?>
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

    <section class="projects-section" id="past-projects">
        <h2>Past Projects</h2>
        <p class="section-subtitle">Projects whose deadline has passed</p>
        <?php if ($pastProjects): ?>
            <div class="projects-grid">
                <?php foreach ($pastProjects as $project):
                    $rewardTotal = (float)($project['reward_amount'] ?? 0);
                    $yswsLink = getYswsLink($project['requirements']);
                    $isYswsDescription = !empty($project['description']) && strpos($project['description'], '<p>') !== false;
                    $formattedDesc = $isYswsDescription ? formatYswsDescription($project['description']) : null;
                    $deadline = $project['end_date'] ?? '';
                ?>
                <div class="project-card past-project<?= $yswsLink ? ' ysws-linked' : '' ?>">
                    <div class="project-header">
                        <h3><?= htmlspecialchars($project['title']) ?></h3>
                        <div class="project-badges">
                            <span class="status-badge status-past">Past</span>
                        </div>
                    </div>
                    <?php if ($isYswsDescription && $formattedDesc): ?>
                        <?= $formattedDesc['main'] ?>
                        <?php if ($formattedDesc['grant']): ?>
                            <div class="ysws-section-heading">Grant Amounts:</div>
                            <?= $formattedDesc['grant'] ?>
                        <?php endif; ?>
                        <?php if ($formattedDesc['deadline']): ?>
                            <div class="ysws-deadline"><?= $formattedDesc['deadline'] ?></div>
                        <?php endif; ?>
                        <?php if ($formattedDesc['discussion']): ?>
                            <div class="ysws-section-heading">Discussion:</div>
                            <div class="ysws-discussion"><?= $formattedDesc['discussion'] ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="project-description"><?= htmlspecialchars($project['description'] ?? '') ?></div>
                    <?php endif; ?>
                    <div class="project-details">
                        <div class="detail-item">
                            <span class="detail-label">Reward:</span>
                            <span class="detail-value">
                                <?php if ($rewardTotal > 0): ?>
                                    $<?= number_format($rewardTotal, 2) ?>
                                <?php endif; ?>
                                <?= htmlspecialchars($project['reward_description'] ?? '') ?>
                            </span>
                        </div>
                        <?php if (!empty($project['requirements'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Requirements:</span>
                            <span class="detail-value"><?= nl2br(htmlspecialchars(trim(preg_replace('/YSWS:\s*https?:\/\/\S+/i', '', $project['requirements'])))) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <span class="detail-label">Deadline:</span>
                            <span class="detail-value"><?= $deadline ?: 'Indefinite' ?></span>
                        </div>
                        <?php if ($yswsLink): ?>
                            <div class="ysws-link-row">
                                <span class="detail-label">YSWS Link:</span>
                                <a href="<?= htmlspecialchars($yswsLink) ?>" target="_blank" class="ysws-link"><?= htmlspecialchars($yswsLink) ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No past projects</h3>
                <p>All projects are currently open or you have joined all past projects.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include '../components/layout/footer.php'; ?>
