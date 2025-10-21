<?php
require_once __DIR__ . '/core/init.php';
checkLoggedIn();

global $db, $currentUser;

require_once __DIR__ . '/core/classes/CertificateGenerator.php';

$userId = $currentUser->id;
$projectId = $_GET['project_id'] ?? null;

if (!$projectId) {
    http_response_code(400);
    echo json_encode(['error' => 'Project ID required']);
    exit;
}

try {
    $generator = new CertificateGenerator($db);
    $pdf = $generator->generateProjectCertificate($userId, $projectId);
    
    $filename = 'certificate_project_' . $projectId . '_' . date('Y-m-d') . '.pdf';
    $pdf->Output($filename, 'D'); // 'D' forces download
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
