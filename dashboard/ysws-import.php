<?php
require_once __DIR__ . '/../core/init.php';

include '../components/layout/header.php';
include '../components/effects/grid.php';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $ysws_link = $_POST['link'];
    $stmt = $db->prepare("INSERT INTO projects (title, description, requirements) VALUES (?, ?, ?)");
    $stmt->execute([$title, $description, "YSWS: $ysws_link"]);
    echo "<div class='ysws-notice ysws-success'>Project imported!</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['link_local'])) {
    $local_id = (int)$_POST['local_project_id'];
    $ysws_link = $_POST['link'];
    $stmt = $db->prepare("UPDATE projects SET requirements = CONCAT(IFNULL(requirements, ''), '\nYSWS: $ysws_link') WHERE id = ?");
    $stmt->execute([$local_id]);
    echo "<div class='ysws-notice ysws-success'>Project linked!</div>";
}

$local_projects = $db->query("SELECT id, title, requirements FROM projects ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
function isLinked($local_projects, $ysws_link) {
    foreach ($local_projects as $proj) {
        if (strpos($proj['requirements'] ?? '', $ysws_link) !== false) return true;
    }
    return false;
}

function formatYswsDescription($descHtml) {
    $dom = new DOMDocument();
    @$dom->loadHTML('<div>' . $descHtml . '</div>');
    $ps = $dom->getElementsByTagName('p');
    $main = '';
    $grant = '';
    $discussion = '';
    foreach ($ps as $p) {
        $text = trim($p->textContent);
        if (stripos($text, 'grant') !== false && (stripos($text, '$') !== false || stripos($text, 'meal') !== false)) {
            $lines = preg_split('/\n|\r/', $text);
            $grantList = [];
            foreach ($lines as $line) {
                if (preg_match('/^\$\d+.*?:/', $line) || preg_match('/^[•\-]\s/', $line)) {
                    $grantList[] = '<li>' . htmlspecialchars(trim($line)) . '</li>';
                }
            }
            if ($grantList) {
                $grant = '<ul class="ysws-grant-list">' . implode('', $grantList) . '</ul>';
            } else {
                $grant = '<div class="ysws-grant-desc">' . htmlspecialchars($text) . '</div>';
            }
        } elseif (stripos($text, 'discussion') !== false || stripos($text, 'slack') !== false) {
            $discussion = $p->C14N();
        } else {
            $main .= '<div class="ysws-main-desc">' . htmlspecialchars($text) . '</div>';
        }
    }
    return [
        'main' => $main,
        'grant' => $grant,
        'discussion' => $discussion
    ];
}
?>

<link rel="stylesheet" href="/css/ysws-import.css">

<div class="ysws-section">
    <h2>Import or Link Hack Club YSWS Projects</h2>
    <p class="ysws-section-subtitle">Browse YSWS projects, import them into your dashboard, or link them to your existing projects.</p>
    <div class="ysws-projects-list">
        <?php foreach ($ysws_projects as $p):
            $info = formatYswsDescription($p['description']);
            $linked = isLinked($local_projects, $p['link']);
        ?>
        <div class="ysws-project-card<?= $linked ? ' ysws-linked' : '' ?>">
            <div class="ysws-header">
                <h3>
                    <a href="<?= htmlspecialchars($p['link']) ?>" target="_blank"><?= htmlspecialchars($p['title']) ?></a>
                </h3>
                <?php if ($linked): ?>
                    <span class="ysws-linked-badge">Linked to YSWS</span>
                <?php endif; ?>
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
            <div class="ysws-actions">
                <form method="post" class="ysws-action-form">
                    <input type="hidden" name="title" value="<?= htmlspecialchars($p['title']) ?>">
                    <input type="hidden" name="description" value="<?= htmlspecialchars($p['description']) ?>">
                    <input type="hidden" name="link" value="<?= htmlspecialchars($p['link']) ?>">
                    <button type="submit" name="import" class="ysws-btn"<?= $linked ? ' disabled' : '' ?>>Import</button>
                </form>
                <form method="post" class="ysws-action-form">
                    <select name="local_project_id" class="ysws-select">
                        <?php foreach ($local_projects as $lp): ?>
                            <option value="<?= $lp['id'] ?>"><?= htmlspecialchars($lp['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="link" value="<?= htmlspecialchars($p['link']) ?>">
                    <button type="submit" name="link_local" class="ysws-btn"<?= $linked ? ' disabled' : '' ?>>Link</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
?>