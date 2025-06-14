<?php
require_once __DIR__ . '/../core/init.php';
include '../components/layout/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("INSERT INTO projects (title, description, reward_amount, reward_description, requirements, start_date, end_date)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['title'],
        $_POST['description'],
        $_POST['reward_amount'] ?: null,
        $_POST['reward_description'],
        $_POST['requirements'],
        $_POST['start_date'] ?: null,
        $_POST['end_date'] ?: null
    ]);
    header("Location: projects-management.php");
    exit;
}
?>

<div class="contact-form-section">
    <h2>Create New Project</h2>
    <form method="post" class="dashboard-form">
        <label>Title:<br>
            <input type="text" name="title" required>
        </label><br>
        <label>Description:<br>
            <textarea name="description"></textarea>
        </label><br>
        <label>Reward Amount ($):<br>
            <input type="number" step="0.01" name="reward_amount">
        </label><br>
        <label>Other Rewards:<br>
            <textarea name="reward_description"></textarea>
        </label><br>
        <label>Requirements:<br>
            <textarea name="requirements"></textarea>
        </label><br>
        <label>Start Date:<br>
            <input type="date" name="start_date">
        </label><br>
        <label>End Date:<br>
            <input type="date" name="end_date">
        </label><br>
        <button type="submit" class="btn btn-primary">Create Project</button>
        <a href="projects-management.php" class="btn">Cancel</a>
    </form>
</div>

<?php include '../components/layout/footer.php'; ?>
<?php include '../components/effects/grid.php'; ?>
<?php include '../components/effects/mouse.php'; ?>
