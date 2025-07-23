<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();

global $db, $currentUser, $settings;

$pageTitle = 'My Projects';
include __DIR__ . '/components/dashboard-header.php';

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
    'not_sent' => 'Not Sent',
    'not_sent' => 'Not Sent',
    'completed' => 'Completed'
];
$statusClasses = [
    'accepted' => 'status-accepted',
    'accepted_pizza' => 'status-accepted-pizza',
    'waiting' => 'status-waiting',
    'rejected' => 'status-rejected',
    'not_sent' => 'status-not-sent',
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

<div class="space-y-6">
    <div class="bg-gradient-to-r from-primary to-red-600 rounded-lg shadow-lg p-8 text-white">
        <div class="max-w-4xl">
            <h1 class="text-3xl font-bold mb-2">You Build, We Reward.</h1>
            <p class="text-red-100 mb-6">Find your next project opportunity and the rewards that come with it.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4">
                    <div class="text-2xl font-bold">$<?= number_format($totalMoney, 2) ?></div>
                    <div class="text-red-100 text-sm">Total Earned</div>
                </div>
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4">
                    <div class="text-2xl font-bold"><?= count($myProjects) ?></div>
                    <div class="text-red-100 text-sm">Projects Joined</div>
                </div>
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4">
                    <div class="text-2xl font-bold"><?= count($availableProjects) ?></div>
                    <div class="text-red-100 text-sm">Available Projects</div>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">My Projects</h3>
            <p class="text-sm text-gray-500 mt-1">All projects you participated in</p>
        </div>
        
        <?php if ($myProjects): ?>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($myProjects as $project):
                    $virtualStatus = ($project['status'] === 'accepted' && $project['pizza_grant'] === 'received') ? 'accepted_pizza' : $project['status'];
                    $statusLabel = $statusLabels[$virtualStatus] ?? ucfirst($virtualStatus);
                    $rewardTotal = (float)($project['reward_amount'] ?? 0);
                    $yswsLink = getYswsLink($project['requirements']);
                    $isYswsDescription = !empty($project['description']) && strpos($project['description'], '<p>') !== false;
                    $formattedDesc = $isYswsDescription ? formatYswsDescription($project['description']) : null;
                    $deadline = $project['end_date'] ?? '';
                    
                    $statusColors = [
                        'accepted' => 'bg-green-100 text-green-800',
                        'accepted_pizza' => 'bg-purple-100 text-purple-800',
                        'waiting' => 'bg-yellow-100 text-yellow-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'completed' => 'bg-blue-100 text-blue-800',
                        'not_sent' => 'bg-gray-100 text-gray-800',
                        'not_sent' => 'bg-gray-100 text-gray-800'
                    ];
                    $statusColor = $statusColors[$virtualStatus] ?? 'bg-gray-100 text-gray-800';
                ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-3">
                        <h4 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($project['title']) ?></h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>">
                            <?= $statusLabel ?>
                        </span>
                    </div>
                    
                    <?php if ($isYswsDescription && $formattedDesc): ?>
                        <div class="text-sm text-gray-700 mb-3">
                            <?= $formattedDesc['main'] ?>
                        </div>
                        <?php if ($formattedDesc['grant']): ?>
                            <div class="mb-2">
                                <h5 class="text-sm font-medium text-gray-900">Grant Amounts:</h5>
                                <div class="text-sm text-gray-700"><?= $formattedDesc['grant'] ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($formattedDesc['deadline']): ?>
                            <div class="text-sm text-gray-600 mb-2"><?= $formattedDesc['deadline'] ?></div>
                        <?php endif; ?>
                        <?php if ($formattedDesc['discussion']): ?>
                            <div class="mb-2">
                                <h5 class="text-sm font-medium text-gray-900">Discussion:</h5>
                                <div class="text-sm text-gray-700"><?= $formattedDesc['discussion'] ?></div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-700 mb-3"><?= htmlspecialchars($project['description'] ?? '') ?></p>
                    <?php endif; ?>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-500">Reward:</span>
                            <span class="text-gray-900">
                                <?php if ($rewardTotal > 0): ?>
                                    $<?= number_format($rewardTotal, 2) ?>
                                <?php endif; ?>
                                <?= htmlspecialchars($project['reward_description'] ?? '') ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-500">Deadline:</span>
                            <span class="text-gray-900"><?= $deadline ?: 'Indefinite' ?></span>
                        </div>
                        <?php if ($yswsLink): ?>
                            <div class="pt-2">
                                <a href="<?= htmlspecialchars($yswsLink) ?>" target="_blank" 
                                   class="text-primary hover:text-red-600 text-sm">
                                    View YSWS Project →
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No projects yet</h3>
                <p class="mt-1 text-sm text-gray-500">Join your first project to start earning rewards!</p>
            </div>
        <?php endif; ?>
    </div>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Available Projects</h3>
            <p class="text-sm text-gray-500 mt-1">Available projects</p>
        </div>
        
        <?php if ($availableProjects): ?>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($availableProjects as $project):
                    $rewardTotal = (float)($project['reward_amount'] ?? 0);
                    $yswsLink = getYswsLink($project['requirements']);
                    $isYswsDescription = !empty($project['description']) && strpos($project['description'], '<p>') !== false;
                    $formattedDesc = $isYswsDescription ? formatYswsDescription($project['description']) : null;
                    $deadline = $project['end_date'] ?? '';
                ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:border-primary transition-colors <?= $yswsLink ? 'border-green-200 bg-green-50' : '' ?>">
                    <div class="flex items-start justify-between mb-3">
                        <h4 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($project['title']) ?></h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Available
                        </span>
                    </div>
                    
                    <?php if ($isYswsDescription && $formattedDesc): ?>
                        <div class="text-sm text-gray-700 mb-3">
                            <?= $formattedDesc['main'] ?>
                        </div>
                        <?php if ($formattedDesc['grant']): ?>
                            <div class="mb-2">
                                <h5 class="text-sm font-medium text-gray-900">Grant Amounts:</h5>
                                <div class="text-sm text-gray-700"><?= $formattedDesc['grant'] ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($formattedDesc['deadline']): ?>
                            <div class="text-sm text-gray-600 mb-2"><?= $formattedDesc['deadline'] ?></div>
                        <?php endif; ?>
                        <?php if ($formattedDesc['discussion']): ?>
                            <div class="mb-2">
                                <h5 class="text-sm font-medium text-gray-900">Discussion:</h5>
                                <div class="text-sm text-gray-700"><?= $formattedDesc['discussion'] ?></div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-700 mb-3"><?= htmlspecialchars($project['description'] ?? '') ?></p>
                    <?php endif; ?>
                    
                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-500">Reward:</span>
                            <span class="text-gray-900 font-medium">
                                <?php if ($rewardTotal > 0): ?>
                                    $<?= number_format($rewardTotal, 2) ?>
                                <?php endif; ?>
                                <?= htmlspecialchars($project['reward_description'] ?? '') ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-500">Deadline:</span>
                            <span class="text-gray-900"><?= $deadline ?: 'Indefinite' ?></span>
                        </div>
                        <?php if ($yswsLink): ?>
                            <div class="pt-2">
                                <a href="<?= htmlspecialchars($yswsLink) ?>" target="_blank" 
                                   class="text-primary hover:text-red-600 text-sm">
                                    View YSWS Project →
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!$yswsLink): ?>
                        <form method="post" action="apply-project.php">
                            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Join Project
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No available projects</h3>
                <p class="mt-1 text-sm text-gray-500">Check back later for new opportunities!</p>
            </div>
        <?php endif; ?>
    </div>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Past Projects</h3>
            <p class="text-sm text-gray-500 mt-1">Projects whose deadline has passed</p>
        </div>
        
        <?php if ($pastProjects): ?>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($pastProjects as $project):
                    $rewardTotal = (float)($project['reward_amount'] ?? 0);
                    $yswsLink = getYswsLink($project['requirements']);
                    $isYswsDescription = !empty($project['description']) && strpos($project['description'], '<p>') !== false;
                    $formattedDesc = $isYswsDescription ? formatYswsDescription($project['description']) : null;
                    $deadline = $project['end_date'] ?? '';
                ?>
                <div class="border border-gray-200 rounded-lg p-4 opacity-75 <?= $yswsLink ? 'border-green-200 bg-green-50' : '' ?>">
                    <div class="flex items-start justify-between mb-3">
                        <h4 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($project['title']) ?></h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            Past
                        </span>
                    </div>
                    
                    <?php if ($isYswsDescription && $formattedDesc): ?>
                        <div class="text-sm text-gray-700 mb-3">
                            <?= $formattedDesc['main'] ?>
                        </div>
                        <?php if ($formattedDesc['grant']): ?>
                            <div class="mb-2">
                                <h5 class="text-sm font-medium text-gray-900">Grant Amounts:</h5>
                                <div class="text-sm text-gray-700"><?= $formattedDesc['grant'] ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($formattedDesc['deadline']): ?>
                            <div class="text-sm text-gray-600 mb-2"><?= $formattedDesc['deadline'] ?></div>
                        <?php endif; ?>
                        <?php if ($formattedDesc['discussion']): ?>
                            <div class="mb-2">
                                <h5 class="text-sm font-medium text-gray-900">Discussion:</h5>
                                <div class="text-sm text-gray-700"><?= $formattedDesc['discussion'] ?></div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-700 mb-3"><?= htmlspecialchars($project['description'] ?? '') ?></p>
                    <?php endif; ?>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-500">Reward:</span>
                            <span class="text-gray-900">
                                <?php if ($rewardTotal > 0): ?>
                                    $<?= number_format($rewardTotal, 2) ?>
                                <?php endif; ?>
                                <?= htmlspecialchars($project['reward_description'] ?? '') ?>
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-500">Deadline:</span>
                            <span class="text-gray-900"><?= $deadline ?: 'Indefinite' ?></span>
                        </div>
                        <?php if ($yswsLink): ?>
                            <div class="pt-2">
                                <a href="<?= htmlspecialchars($yswsLink) ?>" target="_blank" 
                                   class="text-primary hover:text-red-600 text-sm">
                                    View YSWS Project →
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No past projects</h3>
                <p class="mt-1 text-sm text-gray-500">All projects are currently open or you have joined all past projects.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
