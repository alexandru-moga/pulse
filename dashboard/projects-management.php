<?php
require_once __DIR__ . '/../core/init.php';
include '../components/layout/header.php';

$projects = $db->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

function getAssignmentSummary($db, $projectId) {
    $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM project_assignments WHERE project_id = ? GROUP BY status");
    $stmt->execute([$projectId]);
    $summary = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $summary[$row['status']] = $row['count'];
    }
    return $summary;
}
?>
<div class="projects-management-section">
    <h2>Projects Management</h2>
    <a href="create-project.php" class="cta-button">Create New Project</a>
    <a href="project-user-matrix.php" class="cta-button">Project User Matrix</a>

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
<?php foreach ($projects as $project): 
    $summary = getAssignmentSummary($db, $project['id']);
    ?>
    <tr>
        <td><?= htmlspecialchars($project['title']) ?></td>
        <td><?= nl2br(htmlspecialchars($project['description'])) ?></td>
        <td><?= $project['start_date'] ?? 'Indefinite' ?> - <?= $project['end_date'] ?? 'Indefinite' ?></td>
        <td><?= htmlspecialchars($project['reward_amount'] ?? '') ?><br>
            <?= nl2br(htmlspecialchars($project['reward_description'] ?? '')) ?>
        </td>
        <td><?= nl2br(htmlspecialchars($project['requirements'])) ?></td>
        <td>
            <span class="badge badge-green"><?= $summary['accepted'] ?? 0 ?> Accepted</span>
            <span class="badge badge-orange"><?= $summary['waiting'] ?? 0 ?> Waiting</span>
            <span class="badge badge-red"><?= $summary['rejected'] ?? 0 ?> Rejected</span>
            <span class="badge badge-grey"><?= $summary['not_participating'] ?? 0 ?> Not Participating</span>
            <span class="badge badge-blue"><?= $summary['completed'] ?? 0 ?> Completed</span>
        </td>
        <td>
            <a href="edit-project.php?id=<?= $project['id'] ?>" class="btn-small">Edit</a>
            <form method="post" action="projects-management.php" style="display:inline;">
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
<?php include '../components/layout/footer.php'; ?>
<?php include '../components/effects/grid.php'; ?>
<?php include '../components/effects/mouse.php'; ?>
