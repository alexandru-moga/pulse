<?php
session_start();
require_once 'config/database.php';
require_once 'core/classes/DiplomaGenerator.php';

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
    $generator = new DiplomaGenerator($pdo);
    
    if ($type === 'event') {
        $pdfContent = $generator->generateEventDiploma($userId, $id, $templateId);
        $filename = 'diploma_event_' . $id . '_' . date('Y-m-d') . '.pdf';
    } elseif ($type === 'project') {
        $pdfContent = $generator->generateProjectCertificate($userId, $id, $templateId);
        $filename = 'certificate_project_' . $id . '_' . date('Y-m-d') . '.pdf';
    } else {
        throw new Exception('Invalid type');
    }
    
    // Output the PDF for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdfContent));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    echo $pdfContent;
    
} catch (Exception $e) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
