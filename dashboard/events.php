<?php
require_once __DIR__ . '/../core/init.php';

$today = date('Y-m-d');
$events = $db->query("SELECT * FROM events ORDER BY start_date ASC")->fetchAll(PDO::FETCH_ASSOC);

function getAssignedYsws($db, $event_id) {
    $stmt = $db->prepare("SELECT ysws_link FROM event_ysws WHERE event_id = ?");
    $stmt->execute([$event_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<link rel="stylesheet" href="/css/events.css">

<div class="events-section">
    <h2>Events</h2>
    <div class="events-list">
        <?php foreach ($events as $event):
            $isPast = ($event['end_date'] && $event['end_date'] < $today);
            $ysws_links = getAssignedYsws($db, $event['id']);
        ?>
        <div class="event-card<?= $isPast ? ' event-past' : '' ?>">
            <h3><?= htmlspecialchars($event['title']) ?></h3>
            <div class="event-meta">
                <?= htmlspecialchars($event['location']) ?> | <?= $event['start_date'] ?> - <?= $event['end_date'] ?>
            </div>
            <div class="event-desc"><?= nl2br(htmlspecialchars($event['description'])) ?></div>
            <?php if ($ysws_links): ?>
                <div class="event-ysws">
                    <strong>YSWS Projects:</strong>
                    <ul>
                        <?php foreach ($ysws_links as $link): ?>
                            <li><a href="<?= htmlspecialchars($link) ?>" target="_blank"><?= htmlspecialchars($link) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <button class="add-to-calendar-btn"
                data-title="<?= htmlspecialchars($event['title']) ?>"
                data-start="<?= $event['start_date'] ?>"
                data-end="<?= $event['end_date'] ?>"
                data-desc="<?= htmlspecialchars($event['description']) ?>"
                data-location="<?= htmlspecialchars($event['location']) ?>">
                Add to Calendar
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/add-to-calendar-button@2" async defer></script>
<script>
document.querySelectorAll('.add-to-calendar-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        AddToCalendarButton({
            name: btn.dataset.title,
            description: btn.dataset.desc,
            startDate: btn.dataset.start,
            endDate: btn.dataset.end,
            location: btn.dataset.location,
        });
    });
});
</script>
