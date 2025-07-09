<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$projectId = $_GET['id'] ?? null;
if (!$projectId) {
    header('Location: projects-management.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$projectId]);
$project = $stmt->fetch();

if (!$project) {
    $_SESSION['notification'] = ['type' => 'error', 'message' => 'Project not found.'];
    header('Location: projects-management.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("UPDATE projects SET title = ?, description = ?, reward_amount = ?, reward_description = ?, requirements = ?, start_date = ?, end_date = ? WHERE id = ?");
    $stmt->execute([
        $_POST['title'],
        $_POST['description'],
        $_POST['reward_amount'] ?: null,
        $_POST['reward_description'],
        $_POST['requirements'],
        $_POST['start_date'] ?: null,
        $_POST['end_date'] ?: null,
        $projectId
    ]);
    $_SESSION['notification'] = ['type' => 'success', 'message' => 'Project updated successfully.'];
    header('Location: projects-management.php');
    exit;
}

$pageTitle = 'Edit Project';
include __DIR__ . '/components/dashboard-header.php';
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Edit Project</h2>
                <p class="text-gray-600 mt-1">Update project details and settings</p>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/projects-management.php" 
               class="text-primary hover:text-red-600 text-sm font-medium">
                ← Back to Projects
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Project Details</h3>
        </div>
        
        <form method="post" class="p-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Project Title</label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           required 
                           value="<?= htmlspecialchars($project['title']) ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                              placeholder="Describe what this project involves..."><?= htmlspecialchars($project['description']) ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" 
                               id="start_date" 
                               name="start_date"
                               value="<?= htmlspecialchars($project['start_date']) ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" 
                               id="end_date" 
                               name="end_date"
                               value="<?= htmlspecialchars($project['end_date']) ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                </div>

                <div>
                    <label for="requirements" class="block text-sm font-medium text-gray-700">Requirements</label>
                    <textarea id="requirements" 
                              name="requirements" 
                              rows="3"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                              placeholder="What skills or qualifications are needed?"><?= htmlspecialchars($project['requirements']) ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="reward_amount" class="block text-sm font-medium text-gray-700">Monetary Reward ($)</label>
                        <input type="number" 
                               id="reward_amount" 
                               name="reward_amount" 
                               step="0.01" 
                               min="0"
                               value="<?= htmlspecialchars($project['reward_amount']) ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                               placeholder="0.00">
                    </div>

                    <div>
                        <label for="reward_description" class="block text-sm font-medium text-gray-700">Other Rewards</label>
                        <textarea id="reward_description" 
                                  name="reward_description" 
                                  rows="2"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary"
                                  placeholder="Certificates, recognition, etc."><?= htmlspecialchars($project['reward_description']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="<?= $settings['site_url'] ?>/dashboard/projects-management.php" 
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Update Project
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
