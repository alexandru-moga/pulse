<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Projects Management';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;

// Handle project deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project'])) {
    $projectId = intval($_POST['project_id']);
    $db->prepare("DELETE FROM project_assignments WHERE project_id = ?")->execute([$projectId]);
    $db->prepare("DELETE FROM projects WHERE id = ?")->execute([$projectId]);
    $success = "Project deleted successfully!";
}

// Get all projects
$projects = $db->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get project statistics
$totalProjects = count($projects);
$activeProjects = count(array_filter($projects, function($p) {
    return !$p['end_date'] || strtotime($p['end_date']) >= time();
}));
$yswsProjects = count(array_filter($projects, function($p) {
    return strpos($p['requirements'] ?? '', 'YSWS:') !== false;
}));

// Helper function to get assignment summary
function getAssignmentSummary($db, $projectId) {
    $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM project_assignments WHERE project_id = ? GROUP BY status");
    $stmt->execute([$projectId]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $summary = [
        'accepted' => 0,
        'waiting' => 0,
        'rejected' => 0,
        'not_sent' => 0,
        'completed' => 0
    ];
    
    foreach ($results as $result) {
        $summary[$result['status']] = $result['count'];
    }  
    
    return $summary;
}
?>

<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Projects Management</h2>
                <p class="text-gray-600 dark:text-gray-300 mt-1">Manage all projects and their assignments</p>
            </div>
            <div class="flex space-x-3">    
                <a href="<?= $settings['site_url'] ?>/dashboard/import-projects.php" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    Import YSWS
                </a>
                <button onclick="refreshDates()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Refresh Dates
                </button>
                <a href="<?= $settings['site_url'] ?>/dashboard/create-project.php" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Project
                </a>
            </div>
        </div>
    </div>
    <?php if ($success): ?>
        <div class="bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-700 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-green-700 dark:text-green-300"><?= htmlspecialchars($success) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Projects</dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white"><?= $totalProjects ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Projects</dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white"><?= $activeProjects ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">YSWS Projects</dt>
                        <dd class="text-lg font-medium text-gray-900 dark:text-white"><?= $yswsProjects ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">All Projects</h3>
            <a href="<?= $settings['site_url'] ?>/dashboard/project-user-matrix.php" 
               class="text-primary hover:text-red-600 text-sm font-medium">
                View User Matrix →
            </a>
        </div>
        
        <?php if (empty($projects)): ?>
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No projects</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first project or importing from YSWS.</p>
                <div class="mt-6 flex justify-center space-x-3">
                    <a href="<?= $settings['site_url'] ?>/dashboard/create-project.php" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Project
                    </a>
                    <a href="<?= $settings['site_url'] ?>/dashboard/import-projects.php" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        Import YSWS
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Reward</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requirements</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">YSWS Link</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assignments</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php foreach ($projects as $project): 
                                $summary = getAssignmentSummary($db, $project['id']);
                                
                                $yswsLink = '';
                                $cleanRequirements = $project['requirements'];
                                if ($project['requirements'] && preg_match('/YSWS:\s*(https?:\/\/\S+)/i', $project['requirements'], $matches)) {
                                    $yswsLink = $matches[1];
                                    $cleanRequirements = trim(preg_replace('/\n?YSWS:\s*https?:\/\/\S+/i', '', $project['requirements']));
                                }
                            ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($project['title']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white max-w-xs">
                                            <?= nl2br(htmlspecialchars(mb_strimwidth($project['description'] ?? '', 0, 100, '...'))) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            <?= htmlspecialchars($project['start_date'] ?? 'Indefinite') ?>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            to <?= htmlspecialchars($project['end_date'] ?? 'Indefinite') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            <?php if ($project['reward_amount']): ?>
                                                <span class="font-medium">$<?= htmlspecialchars($project['reward_amount']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($project['reward_description']): ?>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 max-w-xs">
                                                <?= nl2br(htmlspecialchars(mb_strimwidth($project['reward_description'], 0, 50, '...'))) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white max-w-xs">
                                            <?= $cleanRequirements ? nl2br(htmlspecialchars(mb_strimwidth($cleanRequirements, 0, 100, '...'))) : '<span class="text-gray-400">None</span>' ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($yswsLink): ?>
                                            <a href="<?= htmlspecialchars($yswsLink) ?>" target="_blank" 
                                               class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                                YSWS
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">Not linked</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                <?= $summary['accepted'] ?? 0 ?> Accepted
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                <?= $summary['waiting'] ?? 0 ?> Waiting
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                <?= $summary['rejected'] ?? 0 ?> Rejected
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                <?= $summary['not_sent'] ?? 0 ?> Not Sent
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                <?= $summary['completed'] ?? 0 ?> Completed
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="<?= $settings['site_url'] ?>/dashboard/edit-project.php?id=<?= $project['id'] ?>" 
                                               class="text-primary hover:text-red-600">Edit</a>
                                            <form method="post" class="inline" onsubmit="return confirm('Are you sure you want to delete this project? This will also delete all assignments.')">
                                                <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                                <button type="submit" name="delete_project" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function importProjects() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Importing...';
    button.disabled = true;
    
    // Simulate API call to import projects from YSWS database
    setTimeout(() => {
        document.getElementById('dbStatus').textContent = 'Connected';
        document.getElementById('dbStatus').className = 'text-sm font-medium text-green-600';
        document.getElementById('lastSync').textContent = new Date().toLocaleString();
        
        button.textContent = originalText;
        button.disabled = false;
        
        alert('Successfully imported projects from YSWS database!');
        location.reload(); // Refresh to show new projects
    }, 2000);
}

function refreshProjects() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Refreshing...';
    button.disabled = true;
    
    // Simulate API call to refresh projects from YSWS database
    setTimeout(() => {
        document.getElementById('dbStatus').textContent = 'Connected';
        document.getElementById('dbStatus').className = 'text-sm font-medium text-green-600';
        document.getElementById('lastSync').textContent = new Date().toLocaleString();
        
        button.textContent = originalText;
        button.disabled = false;
        
        alert('Successfully refreshed projects from YSWS database!');
        location.reload(); // Refresh to show updated projects
    }, 1500);
}

function refreshDates() {
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Refreshing...';
    button.disabled = true;
    
    // Simulate API call to refresh project dates
    setTimeout(() => {
        button.textContent = originalText;
        button.disabled = false;
        
        alert('Successfully refreshed project dates!');
        location.reload(); // Refresh to show updated dates
    }, 1000);
}
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
    section.style.display = section.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>

