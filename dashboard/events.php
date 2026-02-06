<?php
require_once __DIR__ . '/../core/init.php';
checkActiveOrLimitedAccess();

global $db, $currentUser, $settings;

// Additional safety check for $currentUser
if (!$currentUser) {
    header('Location: /dashboard/login.php');
    exit;
}

$pageTitle = 'Events';
include __DIR__ . '/components/dashboard-header.php';

$today = date('Y-m-d');

// For inactive users, show only events they applied for or participated in
if ($currentUser->role == 'Guest') {
    $stmt = $db->prepare("
        SELECT DISTINCT e.* 
        FROM events e
        INNER JOIN event_applications ea ON e.id = ea.event_id
        WHERE ea.user_id = ?
        ORDER BY e.start_datetime ASC
    ");
    $stmt->execute([$currentUser->id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Active users can see all events
    $events = $db->query("SELECT * FROM events ORDER BY start_datetime ASC")->fetchAll(PDO::FETCH_ASSOC);
}

function getAssignedYsws($db, $event_id)
{
    $stmt = $db->prepare("SELECT ysws_link FROM event_ysws WHERE event_id = ?");
    $stmt->execute([$event_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Events</h2>
                <p class="text-gray-600 mt-1">
                    <?php if (in_array($currentUser->role, ['Leader', 'Co-leader'])): ?>
                        Manage and view all community events
                    <?php else: ?>
                        View all community events
                    <?php endif; ?>
                </p>
            </div>
            <?php if (in_array($currentUser->role, ['Leader', 'Co-leader']) && $currentUser->active_member == 1): ?>
                <a href="<?= $settings['site_url'] ?>/dashboard/manage-events.php"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Manage Events
                </a>
            <?php endif; ?>
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
                <?php if (in_array($currentUser->role, ['Leader', 'Co-leader'])): ?>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new event.</p>
                    <div class="mt-6">
                        <a href="<?= $settings['site_url'] ?>/dashboard/manage-events.php"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Event
                        </a>
                    </div>
                <?php elseif ($currentUser->role == 'Guest'): ?>
                    <p class="mt-1 text-sm text-gray-500">You did not participate in any events yet.</p>
                <?php else: ?>
                    <p class="mt-1 text-sm text-gray-500">No events have been scheduled yet.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($events as $event):
                    $start_datetime = $event['start_datetime'] ?? null;
                    $end_datetime = $event['end_datetime'] ?? null;

                    $isPast = ($end_datetime && $end_datetime < $today);
                    $isUpcoming = !$isPast && ($start_datetime && $start_datetime > $today);
                    $isOngoing = !$isPast && !$isUpcoming;
                    $assignedYsws = getAssignedYsws($db, $event['id']);

                    if ($isPast) {
                        $statusColor = 'bg-gray-100 text-gray-800';
                        $statusText = 'Past';
                    } elseif ($isOngoing) {
                        $statusColor = 'bg-green-100 text-green-800';
                        $statusText = 'Ongoing';
                    } else {
                        $statusColor = 'bg-blue-100 text-blue-800';
                        $statusText = 'Upcoming';
                    }
                ?>
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <h4 class="text-lg font-medium text-gray-900">
                                        <?= htmlspecialchars($event['title']) ?>
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>">
                                        <?= $statusText ?>
                                    </span>
                                </div>

                                <?php if ($event['description']): ?>
                                    <p class="mt-2 text-gray-600">
                                        <?= nl2br(htmlspecialchars($event['description'])) ?>
                                    </p>
                                <?php endif; ?>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div class="flex items-center text-gray-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>
                                            <?= $start_datetime ? date('M j, Y', strtotime($start_datetime)) : 'No start date' ?>
                                            <?php if ($end_datetime && date('Y-m-d', strtotime($end_datetime)) !== date('Y-m-d', strtotime($start_datetime))): ?>
                                                - <?= date('M j, Y', strtotime($end_datetime)) ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>

                                    <?php if ($event['location']): ?>
                                        <div class="flex items-center text-gray-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span><?= htmlspecialchars($event['location']) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($assignedYsws)): ?>
                                        <div class="flex items-center text-gray-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                            </svg>
                                            <span><?= count($assignedYsws) ?> YSWS project(s)</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($assignedYsws)): ?>
                                    <div class="mt-4">
                                        <h5 class="text-sm font-medium text-gray-900 mb-2">Associated YSWS Projects:</h5>
                                        <div class="flex flex-wrap gap-2">
                                            <?php foreach ($assignedYsws as $ysws_link): ?>
                                                <a href="<?= htmlspecialchars($ysws_link) ?>" target="_blank"
                                                    class="inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                    </svg>
                                                    YSWS Project
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="mt-4">
                                    <?php if (!empty($event['apply_link'])): ?>
                                        <a href="<?= htmlspecialchars($event['apply_link']) ?>" target="_blank"
                                            class="inline-flex items-center px-3 py-1.5 border border-primary shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-600 mr-2">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Apply for Event
                                        </a>
                                    <?php endif; ?>
                                    <?php
                                    // Check if user is set to going for this event
                                    $isGoing = $db->prepare("SELECT status FROM event_attendance WHERE event_id = ? AND user_id = ? AND status = 'going'");
                                    $isGoing->execute([$event['id'], $currentUser->id]);
                                    if ($isGoing->fetchColumn() && !empty($event['event_link'])): ?>
                                        <a href="<?= htmlspecialchars($event['event_link']) ?>" target="_blank"
                                            class="inline-flex items-center px-3 py-1.5 border border-green-600 shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 mr-2">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m-6 0h6"></path>
                                            </svg>
                                            Go to Event
                                        </a>
                                    <?php endif; ?>
                                    <button class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary add-to-calendar-btn"
                                        data-title="<?= htmlspecialchars($event['title']) ?>"
                                        data-start="<?= $start_datetime ?>"
                                        data-end="<?= $end_datetime ?? $start_datetime ?>"
                                        data-desc="<?= htmlspecialchars($event['description'] ?? '') ?>"
                                        data-location="<?= htmlspecialchars($event['location'] ?? '') ?>"
                                        data-event-id="<?= $event['id'] ?>">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Add to Calendar
                                    </button>
                                </div>
                            </div>

                            <?php if (in_array($currentUser->role, ['Leader', 'Co-leader'])): ?>
                                <div class="ml-6 flex-shrink-0">
                                    <a href="<?= $settings['site_url'] ?>/dashboard/manage-events.php?edit=<?= $event['id'] ?>"
                                        class="text-primary hover:text-red-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/add-to-calendar-button@2" async defer></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.add-to-calendar-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                AddToCalendarButton({
                    name: btn.dataset.title,
                    description: btn.dataset.desc,
                    startDate: btn.dataset.start,
                    endDate: btn.dataset.end,
                    location: btn.dataset.location,
                });
                // Mark calendar as added for this event/user
                fetch('mark-calendar-added.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        event_id: btn.dataset.eventId
                    })
                });
            });
        });
    });
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>