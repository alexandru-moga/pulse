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
        $stmt = $this->db->query("SELECT name, value FROM settings WHERE name LIKE 'certificate_%'");
        $this->settings = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $setting) {
            $this->settings[$setting['name']] = $setting['value'];
        }
    }
    
    public function generateProjectCertificate($userId, $projectId) {
        // Get user and project data
        $stmt = $this->db->prepare("
            SELECT u.first_name, u.last_name, u.email, p.title, p.description, 
                   pa.status, pa.pizza_grant, pa.updated_at
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
        $stmt = $this->db->prepare("
            INSERT INTO certificate_downloads (user_id, project_id, certificate_type, download_count) 
            VALUES (?, ?, ?, 1) 
            ON DUPLICATE KEY UPDATE 
            download_count = download_count + 1, 
            downloaded_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$userId, $projectId, $type]);
    }
    
    private function createPDF($data) {
        // Create new PDF document in landscape orientation
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('PULSE Certificate System');
        $pdf->SetAuthor($this->settings['certificate_org_name'] ?? 'PULSE');
        $pdf->SetTitle('Certificate of Achievement');
        $pdf->SetSubject('Project Completion Certificate');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(false, 0);
        
        // Add a page
        $pdf->AddPage();
        
        // Set background color (light cream)
        $pdf->SetFillColor(255, 250, 240);
        $pdf->Rect(0, 0, 297, 210, 'F');
        
        // Add decorative border
        $pdf->SetLineWidth(2);
        $pdf->SetDrawColor(220, 53, 69); // Primary red color
        $pdf->Rect(10, 10, 277, 190);
        
        $pdf->SetLineWidth(1);
        $pdf->SetDrawColor(255, 140, 55); // Accent color
        $pdf->Rect(15, 15, 267, 180);
        
        // Title
        $pdf->SetFont('helvetica', 'B', 28);
        $pdf->SetTextColor(220, 53, 69);
        $pdf->SetY(40);
        $pdf->Cell(0, 15, 'CERTIFICATE OF ACHIEVEMENT', 0, 1, 'C');
        
        // Subtitle
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetY(60);
        $pdf->Cell(0, 8, 'This certifies that', 0, 1, 'C');
        
        // Recipient name
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(51, 51, 51);
        $pdf->SetY(75);
        $fullName = $data['first_name'] . ' ' . $data['last_name'];
        $pdf->Cell(0, 12, strtoupper($fullName), 0, 1, 'C');
        
        // Achievement text
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetY(95);
        $pdf->Cell(0, 8, 'has successfully completed the project', 0, 1, 'C');
        
        // Project title
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->SetTextColor(220, 53, 69);
        $pdf->SetY(110);
        $pdf->Cell(0, 10, '"' . $data['title'] . '"', 0, 1, 'C');
        
        // Additional text
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetY(130);
        $status = $data['status'] === 'completed' ? 'completed' : 'accepted';
        if ($data['pizza_grant'] === 'received') {
            $pdf->Cell(0, 6, 'Project ' . $status . ' with Pizza Grant recognition', 0, 1, 'C');
        } else {
            $pdf->Cell(0, 6, 'Project ' . $status . ' successfully', 0, 1, 'C');
        }
        
        // Date
        $pdf->SetY(145);
        $date = date('F j, Y', strtotime($data['updated_at']));
        $pdf->Cell(0, 6, 'Awarded on ' . $date, 0, 1, 'C');
        
        // Organization name
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(220, 53, 69);
        $pdf->SetY(165);
        $orgName = $this->settings['certificate_org_name'] ?? 'PULSE';
        $pdf->Cell(0, 8, $orgName, 0, 1, 'C');
        
        // Signature line
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetY(180);
        $signatureName = $this->settings['certificate_signature_name'] ?? 'Leadership Team';
        $signatureTitle = $this->settings['certificate_signature_title'] ?? 'Director';
        $pdf->Cell(0, 4, $signatureName, 0, 1, 'C');
        $pdf->Cell(0, 4, $signatureTitle, 0, 1, 'C');
        
        // Add decorative elements (simple lines)
        $pdf->SetLineWidth(0.5);
        $pdf->SetDrawColor(220, 53, 69);
        // Top decorative line
        $pdf->Line(100, 55, 197, 55);
        // Bottom decorative line
        $pdf->Line(100, 175, 197, 175);
        
        return $pdf;
    }
    
    public function getCertificateStats($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_downloads,
                   COUNT(DISTINCT project_id) as unique_projects
            FROM certificate_downloads 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
