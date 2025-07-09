<?php
require_once '../core/init.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

global $currentUser, $db;

$pageTitle = 'Dashboard';
include __DIR__ . '/components/dashboard-header.php';

$totalProjects = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE active_member = 1")->fetchColumn();
$totalApplications = $db->query("SELECT COUNT(*) FROM applications WHERE status = 'waiting'")->fetchColumn();
$totalMessages = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'waiting'")->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM project_assignments WHERE user_id = ?");
$stmt->execute([$currentUser->id]);
$myProjectsCount = $stmt->fetchColumn();

$recentProjects = $db->query("SELECT title, id FROM projects ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

$success = $_SESSION['profile_success'] ?? null;
$errors = $_SESSION['profile_errors'] ?? [];
unset($_SESSION['profile_success'], $_SESSION['profile_errors']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dashboard_profile_update'])) {
    $newFirst = trim($_POST['first_name'] ?? '');
    $newLast = trim($_POST['last_name'] ?? '');
    $newDesc = trim($_POST['description'] ?? '');
    $newGithub = trim($_POST['github_username'] ?? '');

    $updateErrors = [];
    if ($newFirst === '') $updateErrors[] = "First name cannot be empty.";
    if ($newLast === '') $updateErrors[] = "Last name cannot be empty.";

    if (empty($updateErrors)) {
        $stmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ?, description = ?, github_username = ? WHERE id = ?");
        $stmt->execute([$newFirst, $newLast, $newDesc, $newGithub, $currentUser->id]);
        $_SESSION['profile_success'] = "Profile updated successfully!";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['profile_errors'] = $updateErrors;
        header("Location: index.php");
        exit();
    }
}
?>

<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    Welcome back, <?= htmlspecialchars($currentUser->first_name) ?>!
                </h1>
                <p class="text-gray-600 mt-1">Here's what's happening with your community</p>
            </div>
            <div class="flex space-x-3">
                <a href="<?= $settings['site_url'] ?>/dashboard/projects.php" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600">
                    View Projects
                </a>
                <a href="<?= $settings['site_url'] ?>/dashboard/import-projects.php" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Import YSWS
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Projects -->
        <div class="bg-white rounded-lg shadow p-6">
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
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Projects</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $totalProjects ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- My Projects -->
        <div class="bg-white rounded-lg shadow p-6">
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
                        <dt class="text-sm font-medium text-gray-500 truncate">My Projects</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $myProjectsCount ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Active Members -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Active Members</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $totalUsers ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Pending Items -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Pending Reviews</dt>
                        <dd class="text-lg font-medium text-gray-900"><?= $totalApplications + $totalMessages ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Management -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">My Profile</h3>
            </div>
            <div class="p-6">
                <?php if ($success): ?>
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($errors): ?>
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        <?php foreach ($errors as $error): ?>
                            <div><?= htmlspecialchars($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="index.php" class="space-y-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                        <input type="text" id="first_name" name="first_name"
                               value="<?= htmlspecialchars($currentUser->first_name ?? '') ?>" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                        <input type="text" id="last_name" name="last_name"
                               value="<?= htmlspecialchars($currentUser->last_name ?? '') ?>" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="github_username" class="block text-sm font-medium text-gray-700">GitHub Username</label>
                        <input type="text" id="github_username" name="github_username"
                               value="<?= htmlspecialchars($currentUser->github_username ?? '') ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary"><?= htmlspecialchars($currentUser->description ?? '') ?></textarea>
                    </div>
                    <button type="submit" name="dashboard_profile_update"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Update Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <a href="<?= $settings['site_url'] ?>/dashboard/import-projects.php" 
                       class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <svg class="w-5 h-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Import YSWS Projects</p>
                            <p class="text-xs text-gray-500">Browse and import Hack Club YSWS projects</p>
                        </div>
                    </a>

                    <a href="<?= $settings['site_url'] ?>/dashboard/projects.php" 
                       class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <svg class="w-5 h-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">View My Projects</p>
                            <p class="text-xs text-gray-500">See all your joined and available projects</p>
                        </div>
                    </a>

                    <?php if (in_array($currentUser->role, ['Leader', 'Co-leader'])): ?>
                    <a href="<?= $settings['site_url'] ?>/dashboard/applications.php" 
                       class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <svg class="w-5 h-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Review Applications</p>
                            <p class="text-xs text-gray-500"><?= $totalApplications ?> pending applications</p>
                        </div>
                    </a>
                    <?php endif; ?>

                    <a href="<?= $settings['site_url'] ?>/dashboard/events.php" 
                       class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <svg class="w-5 h-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">View Events</p>
                            <p class="text-xs text-gray-500">See upcoming and past community events</p>
                        </div>
                    </a>

                    <?php if (!empty($recentProjects)): ?>
                    <div class="pt-3 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Recent Projects</h4>
                        <div class="space-y-1">
                            <?php foreach (array_slice($recentProjects, 0, 3) as $project): ?>
                                <div class="text-sm text-gray-600"><?= htmlspecialchars($project['title']) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
