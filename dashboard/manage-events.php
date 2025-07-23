<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Manage Events';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;

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
    $success = "Event created successfully!";
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
    $success = "Event updated successfully!";
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

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Manage Events</h2>
                <p class="text-gray-600 mt-1">Create and manage events for your community</p>
            </div>
            <div class="text-sm text-gray-500">
                Total Events: <?= count($events) ?>
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
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Create New Event</h3>
        </div>
        <div class="p-6">
            <form method="post" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="title" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                        <input type="text" name="location" id="location"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="start_datetime" class="block text-sm font-medium text-gray-700">Start Date & Time</label>
                        <input type="datetime-local" name="start_datetime" id="start_datetime" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="end_datetime" class="block text-sm font-medium text-gray-700">End Date & Time</label>
                        <input type="datetime-local" name="end_datetime" id="end_datetime"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                    </div>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reminders (before event)</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <input type="text" name="reminders[]" placeholder="e.g. 7 days"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        <input type="text" name="reminders[]" placeholder="e.g. 3 days"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        <input type="text" name="reminders[]" placeholder="e.g. 1 hour"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Examples: "7 days", "3 days", "1 hour", "2 weeks"</p>
                </div>
                
                <div>
                    <label for="ysws_links" class="block text-sm font-medium text-gray-700">Assign YSWS Projects</label>
                    <select name="ysws_links[]" id="ysws_links" multiple
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        <?php foreach ($ysws_projects as $p):
                            if (preg_match('/YSWS:\s*(https?:\/\/\S+)/', $p['requirements'], $m)) $link = $m[1]; else $link = '';
                        ?>
                            <option value="<?= htmlspecialchars($link) ?>"><?= htmlspecialchars($p['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple projects</p>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="create_event"
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">All Events</h3>
        </div>
        
        <?php if (empty($events)): ?>
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No events</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first event.</p>
            </div>
        <?php else: ?>
            <div class="p-6 space-y-6">
                <?php foreach ($events as $event): 
                    $assigned_ysws = getAssignedYsws($db, $event['id']);
                    $reminders = json_decode($event['reminders'], true) ?: [];
                ?>
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($event['title']) ?></h4>
                                <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                    <span><?= htmlspecialchars($event['location']) ?></span>
                                    <span><?= date('M j, Y g:i A', strtotime($event['start_datetime'])) ?></span>
                                    <?php if ($event['end_datetime']): ?>
                                        <span>- <?= date('M j, Y g:i A', strtotime($event['end_datetime'])) ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($event['description']): ?>
                                    <p class="mt-2 text-sm text-gray-700"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <details>
                                <summary class="cursor-pointer text-sm font-medium text-gray-700 hover:text-gray-900">Edit Event</summary>
                                <form method="post" class="mt-4 space-y-4">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Title</label>
                                            <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Location</label>
                                            <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>"
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Start Date & Time</label>
                                            <input type="datetime-local" name="start_datetime" value="<?= date('Y-m-d\TH:i', strtotime($event['start_datetime'])) ?>" required
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">End Date & Time</label>
                                            <input type="datetime-local" name="end_datetime" value="<?= date('Y-m-d\TH:i', strtotime($event['end_datetime'])) ?>"
                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea name="description" rows="2"
                                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm"><?= htmlspecialchars($event['description']) ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Reminders</label>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                            <?php for ($i=0; $i<3; $i++): ?>
                                                <input type="text" name="reminders[]" value="<?= htmlspecialchars($reminders[$i] ?? '') ?>" placeholder="e.g. 7 days"
                                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm">
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">YSWS Projects</label>
                                        <select name="ysws_links[]" multiple
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary text-sm">
                                            <?php foreach ($ysws_projects as $p):
                                                if (preg_match('/YSWS:\s*(https?:\/\/\S+)/', $p['requirements'], $m)) $link = $m[1]; else $link = '';
                                            ?>
                                                <option value="<?= htmlspecialchars($link) ?>" <?= in_array($link, $assigned_ysws) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($p['title']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <button type="submit" name="delete_event" onclick="return confirm('Delete this event?')"
                                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            Delete Event
                                        </button>
                                        <button type="submit" name="edit_event"
                                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </details>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>