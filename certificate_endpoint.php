<?php
session_start();
require_once 'config/database.php';
require_once 'core/classes/CertificateGenerator.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$projectId = $_GET['project_id'] ?? null;

if (!$projectId) {
    http_response_code(400);
    echo json_encode(['error' => 'Project ID required']);
    exit;
}

try {
    $generator = new CertificateGenerator($pdo);
    $pdf = $generator->generateProjectCertificate($userId, $projectId);
    
    $filename = 'certificate_project_' . $projectId . '_' . date('Y-m-d') . '.pdf';
    $pdf->Output($filename, 'D'); // 'D' forces download
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
