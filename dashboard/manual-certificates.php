<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db, $currentUser, $settings;

$pageTitle = 'Assigned Certificate Management';
include __DIR__ . '/components/dashboard-header.php';

$success = $error = null;

// Handle certificate upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_certificate'])) {
    $userId = intval($_POST['user_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($userId) || empty($title)) {
        $error = "User and title are required.";
    } elseif (!isset($_FILES['certificate_file']) || $_FILES['certificate_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a valid certificate file.";
    } else {
        $file = $_FILES['certificate_file'];
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        $maxFileSize = 10 * 1024 * 1024; // 10MB

        if (!in_array($file['type'], $allowedTypes)) {
            $error = "Only PDF, JPEG, and PNG files are allowed.";
        } elseif ($file['size'] > $maxFileSize) {
            $error = "File size must be less than 10MB.";
        } else {
            // Create uploads directory if it doesn't exist
            $uploadDir = __DIR__ . '/../uploads/certificates/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'cert_' . $userId . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            $relativePath = 'uploads/certificates/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Save to database
                $stmt = $db->prepare("
                    INSERT INTO manual_certificates 
                    (user_id, title, description, file_path, original_filename, file_size, mime_type, uploaded_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $userId,
                    $title,
                    $description,
                    $relativePath,
                    $file['name'],
                    $file['size'],
                    $file['type'],
                    $currentUser->id
                ]);

                $success = "Certificate uploaded successfully for user.";
            } else {
                $error = "Failed to upload certificate file.";
            }
        }
    }
}

// Handle certificate deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_certificate'])) {
    $certificateId = intval($_POST['certificate_id']);

    // Get certificate file path before deletion
    $stmt = $db->prepare("SELECT file_path FROM manual_certificates WHERE id = ?");
    $stmt->execute([$certificateId]);
    $certificate = $stmt->fetch();

    if ($certificate) {
        // Delete from database
        $stmt = $db->prepare("DELETE FROM manual_certificates WHERE id = ?");
        $stmt->execute([$certificateId]);

        // Delete file from filesystem
        $fullPath = __DIR__ . '/../' . $certificate['file_path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        $success = "Certificate deleted successfully.";
    } else {
        $error = "Certificate not found.";
    }
}

// Get all users for the dropdown
$stmt = $db->prepare("
    SELECT id, first_name, last_name, email 
    FROM users 
    WHERE role IN ('Member', 'Co-leader', 'Leader', 'Alumni') 
    ORDER BY first_name, last_name
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all manual certificates
$stmt = $db->prepare("
    SELECT mc.*, 
           u.first_name, u.last_name, u.email,
           uploader.first_name as uploader_first_name, uploader.last_name as uploader_last_name
    FROM manual_certificates mc
    JOIN users u ON mc.user_id = u.id
    JOIN users uploader ON mc.uploaded_by = uploader.id
    ORDER BY mc.uploaded_at DESC
");
$stmt->execute();
$certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-gradient-to-r from-primary to-red-600 rounded-lg shadow-lg p-8 text-white">
        <div class="max-w-4xl">
            <h1 class="text-3xl font-bold mb-2">Assigned Certificate Management</h1>
            <p class="text-red-100">Upload and manage certificates for members</p>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Upload Certificate Form -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Upload New Certificate</h2>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Select Member</label>
                    <select name="user_id" id="user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">Choose a member...</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Certificate Title</label>
                    <input type="text" name="title" id="title" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        placeholder="e.g., Certificate of Excellence">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                    placeholder="Additional details about this certificate..."></textarea>
            </div>

            <div>
                <label for="certificate_file" class="block text-sm font-medium text-gray-700 mb-2">Certificate File</label>
                <input type="file" name="certificate_file" id="certificate_file" required
                    accept=".pdf,.jpg,.jpeg,.png"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                <p class="text-sm text-gray-500 mt-1">Accepted formats: PDF, JPEG, PNG. Maximum size: 10MB</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" name="upload_certificate"
                    class="px-6 py-2 bg-primary text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Upload Certificate
                </button>
            </div>
        </form>
    </div>

    <!-- Existing Certificates -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Uploaded Certificates</h2>

        <?php if (empty($certificates)): ?>
            <p class="text-gray-500 text-center py-8">No certificates uploaded yet.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certificate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Downloads</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($certificates as $cert): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($cert['first_name'] . ' ' . $cert['last_name']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($cert['email']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($cert['title']) ?></div>
                                    <?php if ($cert['description']): ?>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($cert['description']) ?></div>
                                    <?php endif; ?>
                                    <div class="text-xs text-gray-400 mt-1">
                                        <?= htmlspecialchars($cert['original_filename']) ?>
                                        (<?= number_format($cert['file_size'] / 1024, 1) ?> KB)
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?= htmlspecialchars($cert['uploader_first_name'] . ' ' . $cert['uploader_last_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= $cert['download_count'] ?> downloads</div>
                                    <?php if ($cert['last_downloaded_at']): ?>
                                        <div class="text-xs text-gray-500">
                                            Last: <?= date('M j, Y g:i A', strtotime($cert['last_downloaded_at'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y g:i A', strtotime($cert['uploaded_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this certificate?')">
                                        <input type="hidden" name="certificate_id" value="<?= $cert['id'] ?>">
                                        <button type="submit" name="delete_certificate"
                                            class="text-red-600 hover:text-red-900 focus:outline-none">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>