<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $projectId = $_POST['project_id'];
    $db->prepare("DELETE FROM project_assignments WHERE project_id = ?")->execute([$projectId]);
    $db->prepare("DELETE FROM projects WHERE id = ?")->execute([$projectId]);
    $_SESSION['notification'] = ['type' => 'success', 'message' => 'Project deleted successfully.'];
    header("Location: projects-management.php");
    exit;
}

$projects = $db->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Projects Management';
include __DIR__ . '/components/dashboard-header.php';

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

<div class="space-y-6">
    <!-- Notification -->
    <?php if (isset($_SESSION['notification'])): ?>
        <div class="bg-<?= $_SESSION['notification']['type'] === 'success' ? 'green' : 'red' ?>-100 border border-<?= $_SESSION['notification']['type'] === 'success' ? 'green' : 'red' ?>-400 text-<?= $_SESSION['notification']['type'] === 'success' ? 'green' : 'red' ?>-700 px-4 py-3 rounded">
            <?= htmlspecialchars($_SESSION['notification']['message']) ?>
        </div>
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Projects Management</h2>
                <p class="text-gray-600 mt-1">Manage all projects and view assignment statistics</p>
            </div>
            <div class="flex space-x-4">
                <a href="<?= $settings['site_url'] ?>/dashboard/create-project.php" 
                   class="bg-primary text-white px-4 py-2 rounded-md hover:bg-red-600 text-sm font-medium">
                    Create New Project
                </a>
                <a href="<?= $settings['site_url'] ?>/dashboard/project-user-matrix.php" 
                   class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm font-medium">
                    User Matrix
                </a>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">All Projects</h3>
            <p class="text-sm text-gray-500 mt-1"><?= count($projects) ?> total projects</p>
        </div>
        
        <?php if (count($projects) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reward</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requirements</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignments</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($projects as $project): 
                    $summary = getAssignmentSummary($db, $project['id']);
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($project['title']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs">
                                <?= nl2br(htmlspecialchars(mb_strimwidth($project['description'], 0, 100, '...'))) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?= $project['start_date'] ?? 'Indefinite' ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                to <?= $project['end_date'] ?? 'Indefinite' ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <?php if ($project['reward_amount']): ?>
                                    <span class="font-medium">$<?= htmlspecialchars($project['reward_amount']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($project['reward_description']): ?>
                                <div class="text-sm text-gray-500 max-w-xs">
                                    <?= nl2br(htmlspecialchars(mb_strimwidth($project['reward_description'], 0, 50, '...'))) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs">
                                <?= nl2br(htmlspecialchars(mb_strimwidth($project['requirements'], 0, 100, '...'))) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?= $summary['accepted'] ?? 0 ?> Accepted
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <?= $summary['waiting'] ?? 0 ?> Waiting
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <?= $summary['rejected'] ?? 0 ?> Rejected
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <?= $summary['not_participating'] ?? 0 ?> Not Participating
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= $summary['completed'] ?? 0 ?> Completed
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="<?= $settings['site_url'] ?>/dashboard/edit-project.php?id=<?= $project['id'] ?>" 
                                   class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form method="post" action="projects-management.php" style="display:inline;" 
                                      onsubmit="return confirm('Are you sure you want to delete this project? This will also delete all assignments.')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-12">
            <div class="text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v42a2 2 0 002 2h2m0-44h32a2 2 0 012 2v42a2 2 0 01-2 2H9m0-44V7a2 2 0 002 2h28a2 2 0 002-2V5"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No projects</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new project.</p>
                <div class="mt-6">
                    <a href="<?= $settings['site_url'] ?>/dashboard/create-project.php" 
                       class="bg-primary text-white px-4 py-2 rounded-md hover:bg-red-600 text-sm font-medium">
                        Create New Project
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>

