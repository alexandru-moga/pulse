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
$type = $_GET['type'] ?? null;
$id = $_GET['id'] ?? null;
$templateId = $_GET['template_id'] ?? null;

if (!$type || !$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Type and ID required']);
    exit;
}

try {
    $generator = new CertificateGenerator($pdo);
    
    if ($type === 'event') {
        $pdf = $generator->generateEventDiploma($userId, $id, $templateId);
        $filename = 'diploma_event_' . $id . '_' . date('Y-m-d') . '.pdf';
    } elseif ($type === 'project') {
        $pdf = $generator->generateProjectCertificate($userId, $id);
        $filename = 'certificate_project_' . $id . '_' . date('Y-m-d') . '.pdf';
    } else {
        throw new Exception('Invalid type');
    }
    
    $pdf->Output($filename, 'D'); // 'D' forces download
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
