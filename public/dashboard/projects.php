<?php
require_once '../../core/init.php';
checkLoggedIn();

global $currentUser, $db;

$canAssign = $currentUser && in_array($currentUser->role, ['Leader', 'Co-leader']);

$createProjectSuccess = $createProjectError = null;
if ($canAssign && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_project'])) {
    $projectTitle = trim($_POST['project_title'] ?? '');
    $projectDescription = trim($_POST['project_description'] ?? '');
    if ($projectTitle === '') {
        $createProjectError = "Project title cannot be empty.";
    } else {
        $db->prepare("INSERT INTO projects (title, description, status) VALUES (?, ?, 'active')")
           ->execute([$projectTitle, $projectDescription]);
        $createProjectSuccess = "Project created successfully!";
    }
}

$deleteProjectSuccess = $deleteProjectError = null;
if ($canAssign && isset($_GET['delete_project'])) {
    $projectId = intval($_GET['delete_project']);
    $db->prepare("DELETE FROM projects WHERE id = ?")->execute([$projectId]);
    $db->prepare("DELETE FROM user_projects WHERE project_id = ?")->execute([$projectId]);
    $deleteProjectSuccess = "Project deleted successfully!";
}

$allProjects = $allUsers = [];
if ($canAssign) {
    $allProjects = $db->query("SELECT * FROM projects")->fetchAll();
    $allUsers = $db->query("SELECT id, first_name, last_name FROM users")->fetchAll();
}

$assignSuccess = $assignError = null;
if ($canAssign && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_project'])) {
    $projectId = intval($_POST['project_id']);
    $userId = intval($_POST['user_id']);
    $exists = $db->prepare("SELECT 1 FROM user_projects WHERE user_id = ? AND project_id = ?");
    $exists->execute([$userId, $projectId]);
    if (!$exists->fetch()) {
        $db->prepare("INSERT INTO user_projects (user_id, project_id, assigned_by) VALUES (?, ?, ?)")
            ->execute([$userId, $projectId, $currentUser->id]);
        $assignSuccess = "Project assigned successfully!";
    } else {
        $assignError = "This user is already assigned to the selected project.";
    }
}

$userProjects = [];
$stmt = $db->prepare(
    "SELECT p.* 
     FROM projects p
     JOIN user_projects up ON p.id = up.project_id
     WHERE up.user_id = ?"
);
$stmt->execute([$currentUser->id]);
$userProjects = $stmt->fetchAll();

include '../components/layout/header.php';
include '../components/effects/mouse.php';
include '../components/effects/grid.php';
?>

<style>
select, select option {
  background: #181b20;
  color: #fff;
}
.project-delete-btn {
  color: #fff;
  background: #d9534f;
  border: none;
  border-radius: 3px;
  padding: 2px 8px;
  margin-left: 8px;
  cursor: pointer;
}
.project-delete-btn:hover {
  background: #c9302c;
}
</style>

<main class="contact-form-section">
    <h2>My Projects</h2>
    <?php if (!empty($userProjects)): ?>
        <ul>
            <?php foreach ($userProjects as $project): ?>
                <li>
                    <strong><?= htmlspecialchars($project['title'] ?? '') ?></strong>
                    <?php if (!empty($project['description'])): ?>
                        <br><?= htmlspecialchars($project['description']) ?>
                    <?php endif; ?>
                    <br>Status: <?= htmlspecialchars($project['status'] ?? '') ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>You have no assigned projects.</p>
    <?php endif; ?>

    <?php if ($canAssign): ?>
        <h3>Create New Project</h3>
        <?php if ($createProjectSuccess): ?>
            <div class="form-success"><?= htmlspecialchars($createProjectSuccess) ?></div>
        <?php elseif ($createProjectError): ?>
            <div class="form-errors"><div class="error"><?= htmlspecialchars($createProjectError) ?></div></div>
        <?php endif; ?>
        <form method="POST" style="margin-bottom:2rem;">
            <div class="form-group">
                <label for="project_title">Project Title</label>
                <input type="text" name="project_title" id="project_title" required>
            </div>
            <div class="form-group">
                <label for="project_description">Description</label>
                <textarea name="project_description" id="project_description"></textarea>
            </div>
            <button type="submit" name="create_project" class="cta-button">Create Project</button>
        </form>

        <h3>All Projects</h3>
        <?php if ($deleteProjectSuccess): ?>
            <div class="form-success"><?= htmlspecialchars($deleteProjectSuccess) ?></div>
        <?php elseif ($deleteProjectError): ?>
            <div class="form-errors"><div class="error"><?= htmlspecialchars($deleteProjectError) ?></div></div>
        <?php endif; ?>
        <ul>
            <?php foreach ($allProjects as $project): ?>
                <li>
                    <strong><?= htmlspecialchars($project['title'] ?? '') ?></strong>
                    <?php if (!empty($project['description'])): ?>
                        <br><?= htmlspecialchars($project['description']) ?>
                    <?php endif; ?>
                    <form method="get" style="display:inline">
                        <input type="hidden" name="delete_project" value="<?= $project['id'] ?>">
                        <button type="submit" class="project-delete-btn" onclick="return confirm('Are you sure you want to delete this project?')">Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <h3>Assign Project to Member</h3>
        <?php if ($assignSuccess): ?>
            <div class="form-success"><?= htmlspecialchars($assignSuccess) ?></div>
        <?php elseif ($assignError): ?>
            <div class="form-errors"><div class="error"><?= htmlspecialchars($assignError) ?></div></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="user_id">Member</label>
                <select name="user_id" id="user_id" required>
                    <option value="">Select member</option>
                    <?php foreach ($allUsers as $user): ?>
                        <option value="<?= $user['id'] ?>">
                            <?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="project_id">Project</label>
                <select name="project_id" id="project_id" required>
                    <option value="">Select project</option>
                    <?php foreach ($allProjects as $project): ?>
                        <?php if (!empty($project['title'])): ?>
                            <option value="<?= $project['id'] ?>">
                                <?= htmlspecialchars($project['title']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="assign_project" class="cta-button">Assign Project</button>
        </form>
    <?php endif; ?>
</main>

<?php include '../components/layout/footer.php'; ?>
