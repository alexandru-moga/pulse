<?php
require_once __DIR__ . '/../../lib/tcpdf/tcpdf.php';

class CertificateGenerator {
    private $db;
    private $settings;
    
    public function __construct($db) {
        $this->db = $db;
        $this->loadSettings();
    }
    
    private function loadSettings() {
        try {
            $stmt = $this->db->query("SELECT name, value FROM settings WHERE name LIKE 'certificate_%'");
            $this->settings = [];
            if ($stmt) {
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $setting) {
                    $this->settings[$setting['name']] = $setting['value'];
                }
            }
        } catch (PDOException $e) {
            // If settings table doesn't exist, use defaults
            $this->settings = [
                'certificate_org_name' => 'PULSE',
                'certificate_signature_name' => 'Leadership Team',
                'certificate_signature_title' => 'Director',
                'certificate_enabled' => '1'
            ];
        }
    }
    
    public function generateProjectCertificate($userId, $projectId) {
        // Check if certificates are enabled
        if (!isset($this->settings['certificate_enabled']) || $this->settings['certificate_enabled'] !== '1') {
            throw new Exception('Certificate generation is currently disabled');
        }
        
        // Get user and project data
        $stmt = $this->db->prepare("
            SELECT u.first_name, u.last_name, u.email, p.title, p.description, 
                   pa.status, pa.pizza_grant, 
                   COALESCE(pa.updated_at, pa.created_at, NOW()) as updated_at
            FROM users u 
            JOIN project_assignments pa ON u.id = pa.user_id 
            JOIN projects p ON pa.project_id = p.id 
            WHERE u.id = ? AND p.id = ? AND pa.status IN ('accepted', 'completed')
        ");
        $stmt->execute([$userId, $projectId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            throw new Exception('Certificate not available for this project');
        }
        
        // Track certificate download
        $this->trackDownload($userId, $projectId, 'project_accepted');
        
        // Generate PDF
        return $this->createPDF($data);
    }
    
    private function trackDownload($userId, $projectId, $type) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO certificate_downloads (user_id, project_id, certificate_type, download_count) 
                VALUES (?, ?, ?, 1) 
                ON DUPLICATE KEY UPDATE 
                download_count = download_count + 1, 
                downloaded_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$userId, $projectId, $type]);
        } catch (PDOException $e) {
            // Log error but don't fail certificate generation
            error_log("Certificate download tracking failed: " . $e->getMessage());
        }
    }
    
    private function createPDF($data) {
        try {
            // Clean any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Create new PDF document in landscape orientation
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
            
            // Set certificate data
            $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            if (empty($fullName)) {
                $fullName = 'Certificate Recipient';
            }
            $projectTitle = $data['title'] ?? 'Project';
            
            $pdf->setCertificateData($fullName, $projectTitle);
            
            // Set document information
            $pdf->setCreator('PULSE Certificate System');
            $pdf->setAuthor($this->settings['certificate_org_name'] ?? 'PULSE');
            $pdf->setTitle('Certificate of Achievement - ' . $projectTitle);
            $pdf->setSubject('Project Completion Certificate');
            
            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Set margins
            $pdf->setMargins(20, 20, 20);
            $pdf->setAutoPageBreak(false, 0);
            
            // Add a page
            $pdf->AddPage();
            
            // Certificate styling and content
            $pdf->setFillColor(255, 255, 255);
            $pdf->Rect(0, 0, 297, 210, 'F');
            
            // Title
            $pdf->setFont('helvetica', 'B', 24);
            $pdf->setTextColor(220, 53, 69);
            $pdf->SetY(40);
            $pdf->Cell(0, 15, 'CERTIFICATE OF ACHIEVEMENT', 0, 1, 'C');
            
            // Subtitle
            $pdf->setFont('helvetica', '', 16);
            $pdf->setTextColor(100, 100, 100);
            $pdf->SetY(65);
            $pdf->Cell(0, 8, 'This certifies that', 0, 1, 'C');
            
            // Recipient name
            $pdf->setFont('helvetica', 'B', 20);
            $pdf->setTextColor(0, 0, 0);
            $pdf->SetY(80);
            $pdf->Cell(0, 12, strtoupper($fullName), 0, 1, 'C');
            
            // Achievement text
            $pdf->setFont('helvetica', '', 14);
            $pdf->setTextColor(100, 100, 100);
            $pdf->SetY(95);
            $pdf->Cell(0, 8, 'has successfully completed the project', 0, 1, 'C');
            
            // Project title
            $pdf->setFont('helvetica', 'B', 18);
            $pdf->setTextColor(220, 53, 69);
            $pdf->SetY(110);
            $pdf->Cell(0, 10, '"' . $projectTitle . '"', 0, 1, 'C');
            
            // Additional text
            $pdf->setFont('helvetica', '', 12);
            $pdf->setTextColor(100, 100, 100);
            $pdf->SetY(130);
            $status = ($data['status'] ?? 'accepted') === 'completed' ? 'completed' : 'accepted';
            if (($data['pizza_grant'] ?? '') === 'received') {
                $pdf->Cell(0, 6, 'Project ' . $status . ' with Pizza Grant recognition', 0, 1, 'C');
            } else {
                $pdf->Cell(0, 6, 'Project ' . $status . ' successfully', 0, 1, 'C');
            }
            
            // Date
            $pdf->SetY(145);
            $date = date('F j, Y', strtotime($data['updated_at'] ?? 'now'));
            $pdf->Cell(0, 6, 'Awarded on ' . $date, 0, 1, 'C');
            
            // Organization name
            $pdf->setFont('helvetica', 'B', 16);
            $pdf->setTextColor(220, 53, 69);
            $pdf->SetY(165);
            $orgName = $this->settings['certificate_org_name'] ?? 'PULSE';
            $pdf->Cell(0, 8, $orgName, 0, 1, 'C');
            
            // Signature line
            $pdf->setFont('helvetica', '', 10);
            $pdf->setTextColor(100, 100, 100);
            $pdf->SetY(180);
            $signatureName = $this->settings['certificate_signature_name'] ?? 'Leadership Team';
            $signatureTitle = $this->settings['certificate_signature_title'] ?? 'Director';
            $pdf->Cell(0, 4, $signatureName, 0, 1, 'C');
            $pdf->Cell(0, 4, $signatureTitle, 0, 1, 'C');
            
            // Add decorative elements (simple lines)
            $pdf->setLineWidth(0.5);
            $pdf->setDrawColor(220, 53, 69);
            // Top decorative line
            $pdf->Line(100, 55, 197, 55);
            // Bottom decorative line
            $pdf->Line(100, 175, 197, 175);
            
            return $pdf;
        } catch (Exception $e) {
            throw new Exception('Failed to generate certificate: ' . $e->getMessage());
        }
    }
    
    public function getCertificateStats($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_downloads,
                       COUNT(DISTINCT project_id) as unique_projects
                FROM certificate_downloads 
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_downloads' => 0, 'unique_projects' => 0];
        } catch (PDOException $e) {
            return ['total_downloads' => 0, 'unique_projects' => 0];
        }
    }
}
