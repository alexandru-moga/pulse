<?php
require_once __DIR__ . '/../core/init.php';
include '../components/layout/header.php';

if (!isset($currentUser) || !$currentUser->id) {
    die('<div class="notice error">You must be logged in as a leader/co-leader to access this page.</div>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $reminders = json_encode(array_values(array_filter($_POST['reminders'] ?? [])));
    $stmt = $db->prepare("INSERT INTO events (title, description, location, start_datetime, end_datetime, reminders, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $location, $start_datetime, $end_datetime, $reminders, $currentUser->id]);
    $event_id = $db->lastInsertId();
    if (!empty($_POST['ysws_links'])) {
        foreach (array_unique($_POST['ysws_links']) as $ysws_link) {
            try {
                $db->prepare("INSERT IGNORE INTO event_ysws (event_id, ysws_link) VALUES (?, ?)")->execute([$event_id, $ysws_link]);
            } catch (PDOException $e) {}
        }
    }
    echo "<div class='notice success'>Event created!</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_event'])) {
    $event_id = (int)$_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $reminders = json_encode(array_values(array_filter($_POST['reminders'] ?? [])));
    $stmt = $db->prepare("UPDATE events SET title=?, description=?, location=?, start_datetime=?, end_datetime=?, reminders=? WHERE id=?");
    $stmt->execute([$title, $description, $location, $start_datetime, $end_datetime, $reminders, $event_id]);
    $db->prepare("DELETE FROM event_ysws WHERE event_id=?")->execute([$event_id]);
    if (!empty($_POST['ysws_links'])) {
        foreach (array_unique($_POST['ysws_links']) as $ysws_link) {
            try {
                $db->prepare("INSERT IGNORE INTO event_ysws (event_id, ysws_link) VALUES (?, ?)")->execute([$event_id, $ysws_link]);
            } catch (PDOException $e) {}
        }
    }
    echo "<div class='notice success'>Event updated!</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $event_id = (int)$_POST['event_id'];
    $db->prepare("DELETE FROM event_ysws WHERE event_id=?")->execute([$event_id]);
    $db->prepare("DELETE FROM events WHERE id=?")->execute([$event_id]);
    echo "<div class='notice success'>Event deleted!</div>";
}

$events = $db->query("SELECT * FROM events ORDER BY start_datetime DESC")->fetchAll(PDO::FETCH_ASSOC);
$ysws_projects = $db->query("SELECT id, title, requirements FROM projects WHERE requirements LIKE 'YSWS:%'")->fetchAll(PDO::FETCH_ASSOC);
function getAssignedYsws($db, $event_id) {
    $stmt = $db->prepare("SELECT ysws_link FROM event_ysws WHERE event_id = ?");
    $stmt->execute([$event_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

?>
<link rel="stylesheet" href="/css/events.css">

<div class="events-admin-section">
    <h2>Manage Events</h2>

    <details open>
        <summary><strong>Create New Event</strong></summary>
        <form method="post" class="event-form">
            <label>Title: <input type="text" name="title" required></label>
            <label>Description: <textarea name="description"></textarea></label>
            <label>Location: <input type="text" name="location"></label>
            <label>Start Date & Time: <input type="datetime-local" name="start_datetime" required></label>
            <label>End Date & Time: <input type="datetime-local" name="end_datetime"></label>
            <label>Reminders (before event):
                <input type="text" name="reminders[]" placeholder="e.g. 7 days">
                <input type="text" name="reminders[]" placeholder="e.g. 3 days">
                <input type="text" name="reminders[]" placeholder="e.g. 1 hour">
                <span class="hint">Examples: "7 days", "3 days", "1 hour", "2 weeks"</span>
            </label>
            <label>Assign YSWS Projects:
                <select name="ysws_links[]" multiple>
                    <?php foreach ($ysws_projects as $p):
                        if (preg_match('/YSWS:\s*(https?:\/\/\S+)/', $p['requirements'], $m)) $link = $m[1]; else $link = '';
                    ?>
                        <option value="<?= htmlspecialchars($link) ?>"><?= htmlspecialchars($p['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" name="create_event" class="btn btn-accent">Create Event</button>
        </form>
    </details>

    <hr style="margin:2em 0;">

    <?php foreach ($events as $event): 
        $assigned_ysws = getAssignedYsws($db, $event['id']);
        $reminders = json_decode($event['reminders'], true) ?: [];
    ?>
        <details>
        <summary>
            <strong><?= htmlspecialchars($event['title']) ?></strong>
            <span class="event-meta"><?= htmlspecialchars($event['location']) ?> | <?= $event['start_datetime'] ?> - <?= $event['end_datetime'] ?></span>
        </summary>
        <form method="post" class="event-form">
            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
            <label>Title: <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required></label>
            <label>Description: <textarea name="description"><?= htmlspecialchars($event['description']) ?></textarea></label>
            <label>Location: <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>"></label>
            <label>Start Date & Time: <input type="datetime-local" name="start_datetime" value="<?= date('Y-m-d\TH:i', strtotime($event['start_datetime'])) ?>" required></label>
            <label>End Date & Time: <input type="datetime-local" name="end_datetime" value="<?= date('Y-m-d\TH:i', strtotime($event['end_datetime'])) ?>"></label>
            <label>Reminders (before event):
                <?php for ($i=0; $i<3; $i++): ?>
                    <input type="text" name="reminders[]" value="<?= htmlspecialchars($reminders[$i] ?? '') ?>" placeholder="e.g. 7 days">
                <?php endfor; ?>
                <span class="hint">Examples: "7 days", "3 days", "1 hour", "2 weeks"</span>
            </label>
            <label>Assign YSWS Projects:
                <select name="ysws_links[]" multiple>
                    <?php foreach ($ysws_projects as $p):
                        if (preg_match('/YSWS:\s*(https?:\/\/\S+)/', $p['requirements'], $m)) $link = $m[1]; else $link = '';
                    ?>
                        <option value="<?= htmlspecialchars($link) ?>" <?= in_array($link, $assigned_ysws) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" name="edit_event" class="btn">Save Changes</button>
            <button type="submit" name="delete_event" class="btn btn-danger" onclick="return confirm('Delete this event?')">Delete</button>
        </form>
        <form method="post" class="manual-reminder-form" style="margin-top:1em;">
            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
            <button type="submit" name="send_reminder" class="btn btn-accent">Send Reminder Now</button>
        </form>
        </details>
    <?php endforeach; ?>
</div>