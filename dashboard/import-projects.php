<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Import YSWS Projects';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;

$feed_url = "https://ysws.hackclub.com/feed.xml";
$rss = @simplexml_load_file($feed_url);
if ($rss === false) {
    die("<div class='ysws-notice ysws-error'>Failed to load YSWS feed.</div>");
}
$ysws_projects = [];
foreach ($rss->channel->item as $item) {
    $ysws_projects[] = [
        'title' => (string)$item->title,
        'link' => (string)$item->link,
        'description' => (string)$item->description,
        'pubDate' => (string)$item->pubDate,
    ];
}

function extractDeadline($descHtml) {
    $text = html_entity_decode(strip_tags($descHtml));
    
    $patterns = [
        '/Deadline:\s*([A-Za-z]+\s+\d{1,2},\s*\d{4})/i',
        '/Deadline:\s*(\d{1,2}\/\d{1,2}\/\d{4})/i',
        '/Deadline:\s*(\d{4}-\d{2}-\d{2})/i',
        '/Deadline:\s*(\d{1,2}-\d{1,2}-\d{4})/i',
        '/due\s+(?:on\s+)?([A-Za-z]+\s+\d{1,2},\s*\d{4})/i',
        '/due\s+(?:by\s+)?(\d{1,2}\/\d{1,2}\/\d{4})/i',
        '/deadline\s+(?:is\s+)?([A-Za-z]+\s+\d{1,2},\s*\d{4})/i'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $m)) {
            $dateStr = trim($m[1]);
            $timestamp = strtotime($dateStr);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }
    }
    
    return null;
}
function extractReward($descHtml) {
    $reward_amount = null;
    $reward_desc = '';
    if (preg_match_all('/\$(\d+(\.\d+)?)/', $descHtml, $matches)) {
        $reward_amount = max(array_map('floatval', $matches[1]));
    }
    if (preg_match('/receive (.+?)\./i', $descHtml, $m)) {
        $reward_desc = trim($m[1]);
    } elseif (preg_match('/grant for (.+?)\./i', $descHtml, $m)) {
        $reward_desc = trim($m[1]);
    }
    return [$reward_amount, $reward_desc];
}
function formatYswsDescription($descHtml) {
    if (empty($descHtml)) return ['main' => '', 'discussion' => '', 'grant' => ''];
    $dom = new DOMDocument();
    @$dom->loadHTML('<div>' . $descHtml . '</div>');
    $ps = $dom->getElementsByTagName('p');
    $main = '';
    $discussion = '';
    $grant = '';
    foreach ($ps as $p) {
        $text = trim($p->textContent);
        $html = $p->C14N();
        if (stripos($text, 'grant') !== false && (stripos($text, '$') !== false || stripos($text, 'meal') !== false)) {
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
        'discussion' => $discussion,
        'grant' => $grant
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $ysws_link = $_POST['link'];
    $pubDate = $_POST['pubDate'] ?? null;
    $start_date = $pubDate ? date('Y-m-d', strtotime($pubDate)) : null;
    $end_date = extractDeadline($description);
    list($reward_amount, $reward_desc) = extractReward($description);

    $stmt = $db->prepare("INSERT INTO projects (title, description, requirements, start_date, end_date, reward_amount, reward_description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, "YSWS: $ysws_link", $start_date, $end_date, $reward_amount, $reward_desc]);
    $success = "Project imported successfully!";
    $local_projects = $db->query("SELECT id, title, requirements FROM projects ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['refresh_dates'])) {
    $local_projects = $db->query("SELECT id, title, requirements FROM projects WHERE requirements LIKE '%YSWS:%'")->fetchAll(PDO::FETCH_ASSOC);
    $updated = 0;
    $not_found = 0;
    
    foreach ($local_projects as $proj) {
        if (preg_match('/YSWS:\s*(https?:\/\/\S+)/i', $proj['requirements'], $m)) {
            $link = $m[1];
            $found = false;
            
            foreach ($ysws_projects as $ysws) {
                if ($ysws['link'] === $link) {
                    $new_start = $ysws['pubDate'] ? date('Y-m-d', strtotime($ysws['pubDate'])) : null;
                    $new_end = extractDeadline($ysws['description']);
                    list($reward_amount, $reward_desc) = extractReward($ysws['description']);
                    
                    $stmt = $db->prepare("UPDATE projects SET start_date = ?, end_date = ?, reward_amount = ?, reward_description = ? WHERE id = ?");
                    $stmt->execute([$new_start, $new_end, $reward_amount, $reward_desc, $proj['id']]);
                    $updated++;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $not_found++;
            }
        }
    }
    
    $message = "Refreshed dates for $updated YSWS project(s).";
    if ($not_found > 0) {
        $message .= " $not_found project(s) not found in current feed (may be archived).";
    }
    $success = $message;
    $local_projects = $db->query("SELECT id, title, requirements FROM projects ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_local'])) {
    $local_id = (int)$_POST['local_project_id'];
    $ysws_link = $_POST['link'];
    $stmt = $db->prepare("SELECT requirements FROM projects WHERE id = ?");
    $stmt->execute([$local_id]);
    $current_requirements = $stmt->fetchColumn();
    if (strpos($current_requirements, 'YSWS:') === false) {
        $new_requirements = $current_requirements ? $current_requirements . "\nYSWS: $ysws_link" : "YSWS: $ysws_link";
        $stmt = $db->prepare("UPDATE projects SET requirements = ? WHERE id = ?");
        $stmt->execute([$new_requirements, $local_id]);
        $success = "Project linked to YSWS!";
        $local_projects = $db->query("SELECT id, title, requirements FROM projects ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = "Project is already linked to YSWS!";
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlink'])) {
    $local_id = (int)$_POST['local_project_id'];
    $stmt = $db->prepare("SELECT requirements FROM projects WHERE id = ?");
    $stmt->execute([$local_id]);
    $current_requirements = $stmt->fetchColumn();
    $new_requirements = preg_replace('/\n?YSWS:\s*https?:\/\/\S+/i', '', $current_requirements);
    $new_requirements = trim($new_requirements);
    $stmt = $db->prepare("UPDATE projects SET requirements = ? WHERE id = ?");
    $stmt->execute([$new_requirements ?: null, $local_id]);
    $success = "Project unlinked from YSWS!";
    $local_projects = $db->query("SELECT id, title, requirements FROM projects ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
}

$local_projects = $db->query("SELECT id, title, requirements FROM projects ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

function isLinkedToYsws($local_projects, $ysws_link) {
    foreach ($local_projects as $proj) {
        if (strpos($proj['requirements'] ?? '', $ysws_link) !== false) {
            return $proj;
        }
    }
    return false;
}
function getLinkedYswsUrl($requirements) {
    if (preg_match('/YSWS:\s*(https?:\/\/\S+)/', $requirements ?? '', $matches)) {
        return $matches[1];
    }
    return null;
}
?>

<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="<?= $settings['site_url'] ?>/dashboard/projects-management.php" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Projects
                </a>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Import YSWS Projects</h2>
                    <p class="text-gray-600 dark:text-gray-300 mt-1">Import projects from YSWS or link existing projects</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <form method="post" class="inline">
                </form>
            </div>
        </div>
    </div>
    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Available YSWS Projects</h3>
            <p class="text-sm text-gray-500 mt-1">Import new projects or link to existing ones</p>
        </div>
        
        <div class="p-6 space-y-6">
            <?php foreach ($ysws_projects as $p):
                $linkedProject = isLinkedToYsws($local_projects, $p['link']);
                $end_date = extractDeadline($p['description']);
                $start_date = $p['pubDate'] ? date('Y-m-d', strtotime($p['pubDate'])) : 'Indefinite';
                list($reward_amount, $reward_desc) = extractReward($p['description']);
                $info = formatYswsDescription($p['description']);
                
                $end_date_display = $end_date ? date('M j, Y', strtotime($end_date)) : 'No deadline specified';
            ?>
            <div class="border border-gray-200 rounded-lg p-6 <?= $linkedProject ? 'bg-green-50 border-green-200' : '' ?>">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3">
                            <h4 class="text-lg font-medium text-gray-900">
                                <a href="<?= htmlspecialchars($p['link']) ?>" target="_blank" 
                                   class="text-primary hover:text-red-600">
                                    <?= htmlspecialchars($p['title']) ?>
                                </a>
                            </h4>
                            <?php if ($linkedProject): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Linked to: <?= htmlspecialchars($linkedProject['title']) ?>
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Available
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="text-sm text-gray-500 mt-1">
                            Published: <?= date('F j, Y', strtotime($p['pubDate'])) ?>
                        </p>
                        
                        <div class="mt-3 text-sm text-gray-700">
                            <?= $info['main'] ?>
                        </div>
                        
                        <?php if ($info['grant']): ?>
                            <div class="mt-3">
                                <h5 class="text-sm font-medium text-gray-900">Grant Amounts:</h5>
                                <div class="text-sm text-gray-700"><?= $info['grant'] ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($info['discussion']): ?>
                            <div class="mt-3">
                                <h5 class="text-sm font-medium text-gray-900">Discussion:</h5>
                                <div class="text-sm text-gray-700"><?= $info['discussion'] ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-500">Start:</span>
                                <div class="text-gray-900"><?= $start_date ?></div>
                            </div>
                            <div>
                                <span class="font-medium text-gray-500">Deadline:</span>
                                <div class="text-gray-900"><?= $end_date_display ?></div>
                            </div>
                            <div>
                                <span class="font-medium text-gray-500">Reward:</span>
                                <div class="text-gray-900"><?= $reward_amount ? '$' . $reward_amount : 'N/A' ?></div>
                            </div>
                            <div>
                                <span class="font-medium text-gray-500">Type:</span>
                                <div class="text-gray-900"><?= htmlspecialchars($reward_desc ?: 'N/A') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 flex items-center space-x-3">
                    <form method="post" class="inline">
                        <input type="hidden" name="title" value="<?= htmlspecialchars($p['title']) ?>">
                        <input type="hidden" name="description" value="<?= htmlspecialchars($p['description']) ?>">
                        <input type="hidden" name="link" value="<?= htmlspecialchars($p['link']) ?>">
                        <input type="hidden" name="pubDate" value="<?= htmlspecialchars($p['pubDate']) ?>">
                        <button type="submit" name="import" <?= $linkedProject ? 'disabled' : '' ?>
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:bg-gray-300 disabled:cursor-not-allowed">
                            <?= $linkedProject ? 'Already Imported' : 'Import as New' ?>
                        </button>
                    </form>
                    
                    <?php if ($linkedProject): ?>
                        <form method="post" class="inline">
                            <input type="hidden" name="local_project_id" value="<?= $linkedProject['id'] ?>">
                            <button type="submit" name="unlink" 
                                    onclick="return confirm('Are you sure you want to unlink this project?')"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Unlink
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" class="inline flex items-center space-x-2">
                            <select name="local_project_id" required
                                    class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                                <option value="">Select project to link...</option>
                                <?php foreach ($local_projects as $lp): 
                                    $alreadyLinked = getLinkedYswsUrl($lp['requirements']);
                                ?>
                                    <option value="<?= $lp['id'] ?>" <?= $alreadyLinked ? 'disabled' : '' ?>>
                                        <?= htmlspecialchars($lp['title']) ?><?= $alreadyLinked ? ' (Already linked)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="link" value="<?= htmlspecialchars($p['link']) ?>">
                            <button type="submit" name="link_local" 
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Link
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Your Local Projects</h3>
            <p class="text-sm text-gray-500 mt-1">Manage YSWS links for your existing projects</p>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($local_projects as $project):
                    $yswsLink = getLinkedYswsUrl($project['requirements']);
                ?>
                    <div class="border border-gray-200 rounded-lg p-4 <?= $yswsLink ? 'bg-green-50 border-green-200' : '' ?>">
                        <h4 class="font-medium text-gray-900"><?= htmlspecialchars($project['title']) ?></h4>
                        
                        <?php if ($yswsLink): ?>
                            <div class="mt-2">
                                <span class="text-xs font-medium text-green-600">Linked to YSWS:</span>
                                <div class="flex items-center space-x-2 mt-1">
                                    <a href="<?= htmlspecialchars($yswsLink) ?>" target="_blank" 
                                       class="text-sm text-primary hover:text-red-600 truncate">
                                        <?= htmlspecialchars(parse_url($yswsLink, PHP_URL_HOST)) ?>
                                    </a>
                                    <form method="post" class="inline">
                                        <input type="hidden" name="local_project_id" value="<?= $project['id'] ?>">
                                        <button type="submit" name="unlink" 
                                                onclick="return confirm('Unlink this project from YSWS?')"
                                                class="text-red-600 hover:text-red-800 text-sm" title="Unlink from YSWS">
                                            ×
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 mt-2">Not linked to any YSWS project</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
