<?php
require_once __DIR__ . '/../core/init.php';
include '../components/layout/header.php';

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
    echo "<div class='ysws-notice ysws-success'>Project imported successfully!</div>";
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
    echo "<div class='ysws-notice ysws-success'>$message</div>";
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
        echo "<div class='ysws-notice ysws-success'>Project linked to YSWS!</div>";
    } else {
        echo "<div class='ysws-notice ysws-warning'>Project is already linked to YSWS!</div>";
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
    echo "<div class='ysws-notice ysws-success'>Project unlinked from YSWS!</div>";
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

<link rel="stylesheet" href="/css/ysws-import.css">

<div class="ysws-section">
    <div class="ysws-header">
        <h2>Import or Link Hack Club YSWS Projects</h2>
        <p class="ysws-section-subtitle">Browse YSWS projects, import them into your dashboard, or link/unlink them to your existing projects.</p>
        <form method="post" style="margin-bottom:2em;">
            <button type="submit" name="refresh_dates" class="ysws-btn ysws-btn-link">🔄 Refresh Dates from Feed</button>
        </form>
    </div>
    <div class="ysws-projects-list">
        <?php foreach ($ysws_projects as $p):
            $linkedProject = isLinkedToYsws($local_projects, $p['link']);
            $end_date = extractDeadline($p['description']);
            $start_date = $p['pubDate'] ? date('Y-m-d', strtotime($p['pubDate'])) : 'Indefinite';
            list($reward_amount, $reward_desc) = extractReward($p['description']);
            $info = formatYswsDescription($p['description']);
            
            $end_date_display = $end_date ? date('M j, Y', strtotime($end_date)) : 'No deadline specified';
            
            echo "<!-- DEBUG: " . htmlspecialchars($p['description']) . " -->";
            echo "<!-- EXTRACTED DATE: " . ($end_date ?: 'NULL') . " -->";
        ?>
        <div class="ysws-project-card<?= $linkedProject ? ' ysws-linked' : '' ?>">
            <div class="ysws-header">
                <h3>
                    <a href="<?= htmlspecialchars($p['link']) ?>" target="_blank"><?= htmlspecialchars($p['title']) ?></a>
                </h3>
                <div class="ysws-badges">
                    <?php if ($linkedProject): ?>
                        <span class="ysws-linked-badge">Linked to: <?= htmlspecialchars($linkedProject['title']) ?></span>
                    <?php else: ?>
                        <span class="ysws-available-badge">Available</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ysws-pubdate"><?= date('F j, Y', strtotime($p['pubDate'])) ?></div>
            <?= $info['main'] ?>
            <?php if ($info['grant']): ?>
                <div class="ysws-section-heading">Grant Amounts:</div>
                <?= $info['grant'] ?>
            <?php endif; ?>
            <?php if ($info['discussion']): ?>
                <div class="ysws-section-heading">Discussion:</div>
                <div class="ysws-discussion"><?= $info['discussion'] ?></div>
            <?php endif; ?>
            <div class="ysws-meta">
                <div><strong>Start date:</strong> <?= $start_date ?></div>
                <div><strong>End date:</strong> <?= $end_date_display ?></div>
                <div><strong>Reward amount:</strong> <?= $reward_amount ? '$' . $reward_amount : 'N/A' ?></div>
                <div><strong>Reward description:</strong> <?= htmlspecialchars($reward_desc) ?></div>
            </div>
            <div class="ysws-actions">
                <form method="post" class="ysws-action-form">
                    <input type="hidden" name="title" value="<?= htmlspecialchars($p['title']) ?>">
                    <input type="hidden" name="description" value="<?= htmlspecialchars($p['description']) ?>">
                    <input type="hidden" name="link" value="<?= htmlspecialchars($p['link']) ?>">
                    <input type="hidden" name="pubDate" value="<?= htmlspecialchars($p['pubDate']) ?>">
                    <button type="submit" name="import" class="ysws-btn ysws-btn-import"<?= $linkedProject ? ' disabled' : '' ?>>
                        <?= $linkedProject ? 'Already Imported' : 'Import as New' ?>
                    </button>
                </form>
                <?php if ($linkedProject): ?>
                    <form method="post" class="ysws-action-form">
                        <input type="hidden" name="local_project_id" value="<?= $linkedProject['id'] ?>">
                        <button type="submit" name="unlink" class="ysws-btn ysws-btn-unlink" onclick="return confirm('Are you sure you want to unlink this project?')">
                            Unlink
                        </button>
                    </form>
                <?php else: ?>
                    <form method="post" class="ysws-action-form">
                        <select name="local_project_id" class="ysws-select" required>
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
                        <button type="submit" name="link_local" class="ysws-btn ysws-btn-link">Link</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="local-projects-section">
        <h3>Your Local Projects</h3>
        <div class="local-projects-grid">
            <?php foreach ($local_projects as $project):
                $yswsLink = getLinkedYswsUrl($project['requirements']);
            ?>
                <div class="local-project-card<?= $yswsLink ? ' has-ysws-link' : '' ?>">
                    <h4><?= htmlspecialchars($project['title']) ?></h4>
                    <?php if ($yswsLink): ?>
                        <div class="ysws-link-info">
                            <span class="ysws-link-label">Linked to YSWS:</span>
                            <a href="<?= htmlspecialchars($yswsLink) ?>" target="_blank" class="ysws-link">
                                <?= htmlspecialchars(parse_url($yswsLink, PHP_URL_HOST)) ?>
                            </a>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="local_project_id" value="<?= $project['id'] ?>">
                                <button type="submit" name="unlink" class="ysws-unlink-btn" onclick="return confirm('Unlink this project from YSWS?')" title="Unlink from YSWS">
                                    ×
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p class="no-link">Not linked to any YSWS project</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php 
include '../components/effects/grid.php';
include '../components/effects/mouse.php';
include '../components/layout/footer.php';
?>
