<?php
require_once '../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser;

$pageTitle = "Contact Messages";
include '../components/layout/header.php';
include '../components/effects/grid.php';

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_update_id'])) {
    $id = intval($_POST['status_update_id']);
    $status = ($_POST['status'] === 'solved') ? 'solved' : 'waiting';
    $db->prepare("UPDATE contact_messages SET status=? WHERE id=?")->execute([$status, $id]);
    $success = "Status updated.";
}

$messages = $db->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
?>

<head>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/applications.css">
</head>
<div class="projects-management-section">
    <h2>Projects Management</h2>
    <a href="create-project.php" class="cta-button" style="margin-bottom:1.5em;">Create New Project</a>
    <div class="table-responsive">
        <table class="dashboard-table projects-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Dates</th>
                    <th>Reward</th>
                    <th>Requirements</th>
                    <th>Assignments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
<?php foreach ($projects as $project): ?>
    <tr>
        <td><?= htmlspecialchars($project['title']) ?></td>
        <td><?= nl2br(htmlspecialchars($project['description'])) ?></td>
        <td><?= $project['start_date'] ?? 'Indefinite' ?> - <?= $project['end_date'] ?? 'Indefinite' ?></td>
        <td>
            $<?= htmlspecialchars($project['reward_amount']) ?><br>
            <?= nl2br(htmlspecialchars($project['reward_description'])) ?>
        </td>
        <td><?= nl2br(htmlspecialchars($project['requirements'])) ?></td>
        <td>
            <?php
            // Fetch assignments for this project
            $stmt = $db->prepare(
                "SELECT pa.*, u.first_name, u.last_name FROM project_assignments pa
                 JOIN users u ON u.id = pa.user_id WHERE pa.project_id = ?");
            $stmt->execute([$project['id']]);
            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $showCount = 3; // Show first 3 assignments, collapse the rest
            $totalAssignments = count($assignments);
            ?>
            <div class="assignments-list">
                <?php foreach ($assignments as $i => $a): ?>
                    <?php if ($i < $showCount): ?>
                        <div class="assignment-row">
                            <span class="assignment-name"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></span>
                            <span class="assignment-status status-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span>
                            <form method="post" action="projects-management.php" class="inline-form">
                                <input type="hidden" name="action" value="remove_assignment">
                                <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
                                <button type="submit" class="btn-small btn-danger" title="Remove">&#10005;</button>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if ($totalAssignments > $showCount): ?>
                    <button type="button" class="btn-small assignments-toggle" onclick="this.nextElementSibling.classList.toggle('expanded');this.style.display='none';">+<?= $totalAssignments - $showCount ?> more</button>
                    <div class="assignments-collapsed">
                        <?php foreach ($assignments as $i => $a): ?>
                            <?php if ($i >= $showCount): ?>
                                <div class="assignment-row">
                                    <span class="assignment-name"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></span>
                                    <span class="assignment-status status-<?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span>
                                    <form method="post" action="projects-management.php" class="inline-form">
                                        <input type="hidden" name="action" value="remove_assignment">
                                        <input type="hidden" name="assignment_id" value="<?= $a['id'] ?>">
                                        <button type="submit" class="btn-small btn-danger" title="Remove">&#10005;</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Assign new user -->
            <form method="post" action="projects-management.php" class="assign-user-form">
                <input type="hidden" name="action" value="assign_user">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                <select name="user_id">
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-small">Assign</button>
            </form>
        </td>
        <td>
            <form method="post" action="projects-management.php">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                <button type="submit" class="btn-small btn-danger" onclick="return confirm('Delete this project?')">Delete</button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
<?php
include '../components/layout/footer.php';
include '../components/effects/mouse.php';
?>
