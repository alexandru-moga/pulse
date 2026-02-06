<?php
require_once __DIR__ . '/../core/init.php';
checkActiveOrLimitedAccess();

global $db, $currentUser;

// Additional safety check for $currentUser
if (!$currentUser) {
    http_response_code(403);
    die('Access denied - not logged in');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid certificate ID');
}

$certificateId = intval($_GET['id']);

try {
    // Get certificate details and verify ownership
    $stmt = $db->prepare("
        SELECT mc.*, u.first_name, u.last_name 
        FROM manual_certificates mc
        JOIN users u ON mc.user_id = u.id
        WHERE mc.id = ?
    ");
    $stmt->execute([$certificateId]);
    $certificate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$certificate) {
        http_response_code(404);
        die('Certificate not found');
    }

    // Check if user is authorized to download this certificate
    // Only the assigned user or ACTIVE admins can download
    $isAuthorized = false;

    if ($certificate['user_id'] == $currentUser->id) {
        // User is the certificate owner (active or inactive users can download their own)
        $isAuthorized = true;
    } elseif (in_array($currentUser->role, ['Leader', 'Co-leader'])) {
        // User is an active admin
        $isAuthorized = true;
    }

    if (!$isAuthorized) {
        http_response_code(403);
        die('You are not authorized to download this certificate');
    }

    // Build full file path
    $filePath = __DIR__ . '/../' . $certificate['file_path'];

    if (!file_exists($filePath)) {
        http_response_code(404);
        die('Certificate file not found');
    }

    // Update download count and last downloaded date (only for the certificate owner)
    if ($certificate['user_id'] == $currentUser->id) {
        $stmt = $db->prepare("
            UPDATE manual_certificates 
            SET download_count = download_count + 1, 
                last_downloaded_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$certificateId]);
    }

    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    // Prepare filename for download
    $fileExtension = pathinfo($certificate['original_filename'], PATHINFO_EXTENSION);
    $downloadName = 'certificate_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $certificate['title']) . '.' . $fileExtension;

    // Set headers for file download
    header('Content-Type: ' . $certificate['mime_type']);
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    // Output the file
    readfile($filePath);
    exit;
} catch (Exception $e) {
    error_log('Manual certificate download error: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error');
}
