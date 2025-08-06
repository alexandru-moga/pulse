<?php
require_once __DIR__ . '/../core/init.php';
require_once __DIR__ . '/../core/classes/CertificateGenerator.php';
checkLoggedIn();

global $db, $currentUser, $settings;

$pageTitle = 'My Certificates';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;

// Handle certificate download
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_certificate'])) {
    $projectId = intval($_POST['project_id']);
    
    try {
        $certificateGenerator = new CertificateGenerator($db);
        $pdf = $certificateGenerator->generateProjectCertificate($currentUser->id, $projectId);
        
        // Get project title for filename
        $stmt = $db->prepare("SELECT title FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch();
        
        $filename = 'certificate_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $project['title']) . '.pdf';
        
        $pdf->Output($filename, 'D');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get user's eligible projects (accepted or completed)
$stmt = $db->prepare("
    SELECT p.id, p.title, p.description, p.reward_amount, p.reward_description,
           pa.status, pa.pizza_grant, 
           COALESCE(pa.updated_at, pa.created_at, CURRENT_TIMESTAMP) as updated_at,
           cd.download_count, cd.downloaded_at
    FROM projects p
    JOIN project_assignments pa ON pa.project_id = p.id
    LEFT JOIN certificate_downloads cd ON cd.user_id = pa.user_id AND cd.project_id = p.id
    WHERE pa.user_id = ? AND pa.status IN ('accepted', 'completed')
    ORDER BY COALESCE(pa.updated_at, pa.created_at) DESC
");
$stmt->execute([$currentUser->id]);
$eligibleProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get certificate statistics
try {
    $certificateGenerator = new CertificateGenerator($db);
    $stats = $certificateGenerator->getCertificateStats($currentUser->id);
} catch (Exception $e) {
    $stats = ['total_downloads' => 0, 'unique_projects' => 0];
}
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-primary to-red-600 rounded-lg shadow-lg p-8 text-white">
        <div class="max-w-4xl">
            <h1 class="text-3xl font-bold mb-2">My Certificates</h1>
            <p class="text-red-100 mb-6">Download your achievement certificates for completed projects</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4">
                    <div class="text-2xl font-bold"><?= $stats['unique_projects'] ?></div>
                    <div class="text-red-100 text-sm">Certified Projects</div>
                </div>
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4">
                    <div class="text-2xl font-bold"><?= $stats['total_downloads'] ?></div>
                    <div class="text-red-100 text-sm">Total Downloads</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications -->
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

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Certificates Section -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Available Certificates</h3>
            <p class="text-sm text-gray-500 mt-1">Download certificates for your accepted and completed projects</p>
        </div>
        
        <?php if (empty($eligibleProjects)): ?>
            <div class="p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No certificates available</h3>
                <p class="mt-1 text-sm text-gray-500">Complete some projects to earn certificates!</p>
                <div class="mt-6">
                    <a href="<?= $settings['site_url'] ?>/dashboard/projects.php" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-red-600">
                        Browse Projects
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($eligibleProjects as $project): 
                    $statusBadgeClass = $project['status'] === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800';
                    $statusText = $project['status'] === 'completed' ? 'Completed' : 'Accepted';
                    if ($project['pizza_grant'] === 'received') {
                        $statusBadgeClass = 'bg-purple-100 text-purple-800';
                        $statusText .= ' + Pizza';
                    }
                ?>
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h4 class="text-lg font-medium text-gray-900 mb-2">
                                    <?= htmlspecialchars($project['title']) ?>
                                </h4>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusBadgeClass ?>">
                                    <?= $statusText ?>
                                </span>
                            </div>
                            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                            <?= htmlspecialchars(mb_strimwidth($project['description'] ?? '', 0, 120, '...')) ?>
                        </p>
                        
                        <div class="space-y-2 text-sm text-gray-500 mb-4">
                            <div class="flex justify-between">
                                <span>Completed:</span>
                                <span><?= date('M j, Y', strtotime($project['updated_at'])) ?></span>
                            </div>
                            <?php if ($project['reward_amount']): ?>
                                <div class="flex justify-between">
                                    <span>Reward:</span>
                                    <span>$<?= number_format($project['reward_amount'], 2) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($project['download_count']): ?>
                                <div class="flex justify-between">
                                    <span>Downloads:</span>
                                    <span><?= $project['download_count'] ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Last downloaded:</span>
                                    <span><?= date('M j, Y', strtotime($project['downloaded_at'])) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <form method="post" class="w-full">
                            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                            <button type="submit" name="download_certificate"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download Certificate
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Certificate Info -->
    <div class="bg-blue-50 rounded-lg p-6">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">About Certificates</h3>
                <div class="mt-2 text-sm text-blue-700 space-y-2">
                    <p>🏆 <strong>Certificate Requirements:</strong> Certificates are available for projects with "Accepted" or "Completed" status.</p>
                    <p>📄 <strong>Format:</strong> Certificates are generated as PDF files in professional landscape format.</p>
                    <p>🎖️ <strong>Recognition:</strong> Pizza Grant recipients receive special recognition on their certificates.</p>
                    <p>📊 <strong>Tracking:</strong> Download counts and dates are tracked for your records.</p>
                    <p>🔄 <strong>Re-download:</strong> You can download your certificates multiple times whenever needed.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
