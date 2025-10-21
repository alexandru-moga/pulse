<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/core/init.php';
checkLoggedIn();

global $db, $currentUser;

require_once __DIR__ . '/core/classes/DiplomaGenerator.php';

$userId = $currentUser->id;
$type = $_GET['type'] ?? null;
$id = $_GET['id'] ?? null;
$templateId = $_GET['template_id'] ?? null;

if (!$type || !$id) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Type and ID required']);
    exit;
}

try {
    $generator = new DiplomaGenerator($db);
    
    if ($type === 'event') {
        $pdfContent = $generator->generateEventDiploma($userId, $id, $templateId);
        $filename = 'diploma_event_' . $id . '_' . date('Y-m-d') . '.pdf';
    } elseif ($type === 'project') {
        $pdfContent = $generator->generateProjectCertificate($userId, $id, $templateId);
        $filename = 'certificate_project_' . $id . '_' . date('Y-m-d') . '.pdf';
    } else {
        throw new Exception('Invalid type');
    }
    
    // Clear any output buffers
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Output the PDF for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdfContent));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    echo $pdfContent;
    exit;
    
} catch (Exception $e) {
    // Clear any output buffers
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
}
