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

            // Set document information
            $pdf->setCreator('PULSE Certificate System');
            $pdf->setAuthor($this->settings['certificate_org_name'] ?? 'PULSE');
            $pdf->setTitle('Certificate of Achievement - ' . $projectTitle);
            $pdf->setSubject('Project Completion Certificate');

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Set margins
            $pdf->setMargins(0, 0, 0);
            $pdf->setAutoPageBreak(false, 0);

            // Add a page
            $pdf->AddPage();

            // Light background color (cream/off-white)
            $pdf->Rect(0, 0, 297, 210, 'F', [], [248, 248, 245]);

            // Decorative border frame (outer)
            $pdf->setLineWidth(1.5);
            $pdf->setDrawColor(204, 153, 102); // Gold/bronze color
            $pdf->Rect(15, 15, 267, 180, 'D');

            // Inner decorative border
            $pdf->setLineWidth(0.5);
            $pdf->setDrawColor(204, 153, 102);
            $pdf->Rect(20, 20, 257, 170, 'D');

            // Corner decorative elements (simple geometric pattern)
            $this->addCornerDecorations($pdf);

            // Add Hack Club logo in top left
            $logoPath = __DIR__ . '/../../assets/images/hackclub-logo.png';
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 25, 25, 25, '', '', '', '', false, 300, '', false, false, 0);
            }

            // Top decorative flourish (center)
            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetTextColor(204, 153, 102);
            $pdf->SetX(0);
            $pdf->SetY(35);
            $pdf->Cell(297, 10, '❦ ❦ ❦', 0, 0, 'C');

            // Main title "CERTIFICATE"
            $pdf->SetFont('helvetica', 'B', 36);
            $pdf->SetTextColor(184, 134, 11); // Gold color
            $pdf->SetX(0);
            $pdf->SetY(55);
            $pdf->Cell(297, 20, 'CERTIFICATE', 0, 0, 'C');

            // Subtitle "OF ACHIEVEMENT"
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor(184, 134, 11);
            $pdf->SetX(0);
            $pdf->SetY(75);
            $pdf->Cell(297, 10, '— OF ACHIEVEMENT —', 0, 0, 'C');

            // "This certificate is awarded to" text
            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetX(0);
            $pdf->SetY(95);
            $pdf->Cell(297, 10, 'This certificate is awarded to', 0, 0, 'C');

            // Recipient name (elegant script-like font)
            $pdf->SetFont('helvetica', 'BI', 32);
            $pdf->SetTextColor(51, 51, 51);
            $pdf->SetX(0);
            $pdf->SetY(110);
            $pdf->Cell(297, 20, $fullName, 0, 0, 'C');

            // Achievement description
            $pdf->SetFont('helvetica', '', 13);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->SetX(0);
            $pdf->SetY(135);
            
            // Multi-line achievement text
            $achievementText = "For successfully completing the project\n" . 
                              '"' . $projectTitle . '"\n' .
                              "in Hack Club's development program.";
            
            $lines = explode("\n", $achievementText);
            $lineHeight = 6;
            $startY = 135;
            
            foreach ($lines as $i => $line) {
                $pdf->SetY($startY + ($i * $lineHeight));
                if ($i == 1) { // Project title line
                    $pdf->SetFont('helvetica', 'B', 13);
                    $pdf->SetTextColor(51, 51, 51);
                } else {
                    $pdf->SetFont('helvetica', '', 13);
                    $pdf->SetTextColor(80, 80, 80);
                }
                $pdf->Cell(297, $lineHeight, $line, 0, 0, 'C');
            }

            // Signature section
            $pdf->SetFont('helvetica', 'BI', 16);
            $pdf->SetTextColor(51, 51, 51);
            $pdf->SetX(170);
            $pdf->SetY(170);
            $signatureName = $this->settings['certificate_signature_name'] ?? 'Thomas Stubblefield';
            $pdf->Cell(80, 8, $signatureName, 0, 0, 'C');

            // Signature title
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetTextColor(51, 51, 51);
            $pdf->SetX(170);
            $pdf->SetY(178);
            $signatureTitle = $this->settings['certificate_signature_title'] ?? 'Organiser';
            $pdf->Cell(80, 6, $signatureTitle, 0, 0, 'C');

            // Date (bottom left area)
            $pdf->SetFont('helvetica', '', 11);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetX(30);
            $pdf->SetY(185);
            $date = date('F j, Y', strtotime($data['updated_at'] ?? 'now'));
            $pdf->Cell(100, 6, 'Awarded on ' . $date, 0, 0, 'L');

            // Organization name (bottom)
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(184, 134, 11);
            $pdf->SetX(0);
            $pdf->SetY(185);
            $orgName = $this->settings['certificate_org_name'] ?? 'PULSE';
            $pdf->Cell(297, 6, strtoupper($orgName), 0, 0, 'C');

            return $pdf;
        } catch (Exception $e) {
            throw new Exception('Failed to generate certificate: ' . $e->getMessage());
        }
    }

    private function addCornerDecorations($pdf) {
        $pdf->setLineWidth(0.3);
        $pdf->setDrawColor(204, 153, 102);
        
        // Top-left corner decoration
        for ($i = 0; $i < 3; $i++) {
            $offset = $i * 3;
            $pdf->Line(25 + $offset, 25, 35 + $offset, 25);
            $pdf->Line(25, 25 + $offset, 25, 35 + $offset);
        }
        
        // Top-right corner decoration
        for ($i = 0; $i < 3; $i++) {
            $offset = $i * 3;
            $pdf->Line(272 - $offset, 25, 262 - $offset, 25);
            $pdf->Line(272, 25 + $offset, 272, 35 + $offset);
        }
        
        // Bottom-left corner decoration
        for ($i = 0; $i < 3; $i++) {
            $offset = $i * 3;
            $pdf->Line(25 + $offset, 185, 35 + $offset, 185);
            $pdf->Line(25, 185 - $offset, 25, 175 - $offset);
        }
        
        // Bottom-right corner decoration
        for ($i = 0; $i < 3; $i++) {
            $offset = $i * 3;
            $pdf->Line(272 - $offset, 185, 262 - $offset, 185);
            $pdf->Line(272, 185 - $offset, 272, 175 - $offset);
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
    
    /**
     * Generate event diploma based on template
     */
    public function generateEventDiploma($userId, $eventId, $templateId = null) {
        // Check if certificates are enabled
        if (!isset($this->settings['certificate_enabled']) || $this->settings['certificate_enabled'] !== '1') {
            throw new Exception('Certificate generation is currently disabled');
        }
        
        // Get user data
        $stmt = $this->db->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        // Check event attendance
        $stmt = $this->db->prepare("
            SELECT ea.status, e.title, e.start_datetime, e.end_datetime, e.location
            FROM event_attendance ea
            JOIN events e ON ea.event_id = e.id
            WHERE ea.user_id = ? AND ea.event_id = ? AND ea.status IN ('going', 'participated')
        ");
        $stmt->execute([$userId, $eventId]);
        $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$attendance) {
            throw new Exception('User did not attend this event');
        }
        
        // Get diploma template
        $template = null;
        if ($templateId) {
            $stmt = $this->db->prepare("
                SELECT * FROM diploma_templates 
                WHERE id = ? AND enabled = 1
            ");
            $stmt->execute([$templateId]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Try to find template for this specific event
            $stmt = $this->db->prepare("
                SELECT * FROM diploma_templates 
                WHERE template_type = 'event' AND (related_id = ? OR related_id IS NULL) AND enabled = 1
                ORDER BY related_id DESC LIMIT 1
            ");
            $stmt->execute([$eventId]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if (!$template) {
            throw new Exception('No diploma template available for this event');
        }
        
        // Track diploma download
        $this->trackDiplomaDownload($userId, $eventId, $template['id'], 'event_diploma');
        
        // Merge data
        $data = array_merge($user, $attendance, ['template' => $template]);
        
        // Generate PDF
        return $this->createDiplomaPDF($data);
    }
    
    /**
     * Check if user is eligible for event diploma
     */
    public function isEligibleForEventDiploma($userId, $eventId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM event_attendance 
                WHERE user_id = ? AND event_id = ? AND status IN ('going', 'participated')
            ");
            $stmt->execute([$userId, $eventId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Check if user is eligible for project certificate
     */
    public function isEligibleForProjectCertificate($userId, $projectId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM project_assignments 
                WHERE user_id = ? AND project_id = ? AND status IN ('accepted', 'completed')
            ");
            $stmt->execute([$userId, $projectId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get available diplomas for user
     */
    public function getAvailableDiplomas($userId) {
        $diplomas = [];
        
        try {
            // Get event diplomas
            $stmt = $this->db->prepare("
                SELECT DISTINCT e.id, e.title, e.start_datetime, 'event' as type,
                       dt.id as template_id, dt.title as template_title
                FROM events e
                JOIN event_attendance ea ON e.id = ea.event_id
                LEFT JOIN diploma_templates dt ON (dt.template_type = 'event' AND (dt.related_id = e.id OR dt.related_id IS NULL) AND dt.enabled = 1)
                WHERE ea.user_id = ? AND ea.status IN ('going', 'participated')
                HAVING template_id IS NOT NULL
                ORDER BY e.start_datetime DESC
            ");
            $stmt->execute([$userId]);
            $diplomas['events'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get project certificates
            $stmt = $this->db->prepare("
                SELECT DISTINCT p.id, p.title, p.created_at, 'project' as type
                FROM projects p
                JOIN project_assignments pa ON p.id = pa.project_id
                WHERE pa.user_id = ? AND pa.status IN ('accepted', 'completed')
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$userId]);
            $diplomas['projects'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error fetching diplomas: " . $e->getMessage());
        }
        
        return $diplomas;
    }
    
    private function trackDiplomaDownload($userId, $eventId, $templateId, $type) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO certificate_downloads (user_id, event_id, diploma_template_id, certificate_type, download_count) 
                VALUES (?, ?, ?, ?, 1) 
                ON DUPLICATE KEY UPDATE 
                download_count = download_count + 1, 
                downloaded_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$userId, $eventId, $templateId, $type]);
        } catch (PDOException $e) {
            error_log("Diploma download tracking failed: " . $e->getMessage());
        }
    }
    
    private function createDiplomaPDF($data) {
        try {
            // Clean any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Create new PDF document in landscape orientation
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

            $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            if (empty($fullName)) {
                $fullName = 'Certificate Recipient';
            }
            $eventTitle = $data['title'] ?? 'Event';
            $template = $data['template'];

            // Set document information
            $pdf->setCreator('PULSE Diploma System');
            $pdf->setAuthor($this->settings['certificate_org_name'] ?? 'PULSE');
            $pdf->setTitle('Diploma - ' . $eventTitle);
            $pdf->setSubject('Event Participation Diploma');

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Set margins
            $pdf->setMargins(0, 0, 0);
            $pdf->setAutoPageBreak(false, 0);

            // Add a page
            $pdf->AddPage();

            // Add background image if available
            if ($template['background_image'] && file_exists(__DIR__ . '/../../' . $template['background_image'])) {
                $pdf->Image(__DIR__ . '/../../' . $template['background_image'], 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);
            } else {
                // Default background
                $pdf->Rect(0, 0, 297, 210, 'F', [], [248, 248, 245]);
                $pdf->setLineWidth(1.5);
                $pdf->setDrawColor(204, 153, 102);
                $pdf->Rect(15, 15, 267, 180, 'D');
                $pdf->setLineWidth(0.5);
                $pdf->Rect(20, 20, 257, 170, 'D');
                $this->addCornerDecorations($pdf);
            }

            // Main title "CERTIFICATE"
            $pdf->SetFont('helvetica', 'B', 36);
            $pdf->SetTextColor(184, 134, 11);
            $pdf->SetX(0);
            $pdf->SetY(55);
            $pdf->Cell(297, 20, 'CERTIFICATE', 0, 0, 'C');

            // Subtitle
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetTextColor(184, 134, 11);
            $pdf->SetX(0);
            $pdf->SetY(75);
            $pdf->Cell(297, 10, '— OF PARTICIPATION —', 0, 0, 'C');

            // "This certificate is awarded to"
            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetX(0);
            $pdf->SetY(95);
            $pdf->Cell(297, 10, 'This certificate is awarded to', 0, 0, 'C');

            // Recipient name
            $pdf->SetFont('helvetica', 'BI', 32);
            $pdf->SetTextColor(51, 51, 51);
            $pdf->SetX(0);
            $pdf->SetY(110);
            $pdf->Cell(297, 20, $fullName, 0, 0, 'C');

            // Achievement description
            $pdf->SetFont('helvetica', '', 13);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->SetX(0);
            $pdf->SetY(135);
            
            // Use template certificate text
            $certificateText = $template['certificate_text'] ?? "For participating in\n\"$eventTitle\"";
            
            $lines = explode("\n", $certificateText);
            $lineHeight = 6;
            $startY = 135;
            
            foreach ($lines as $i => $line) {
                $pdf->SetY($startY + ($i * $lineHeight));
                if (strpos($line, '"') !== false) {
                    $pdf->SetFont('helvetica', 'B', 13);
                    $pdf->SetTextColor(51, 51, 51);
                } else {
                    $pdf->SetFont('helvetica', '', 13);
                    $pdf->SetTextColor(80, 80, 80);
                }
                $pdf->Cell(297, $lineHeight, $line, 0, 0, 'C');
            }

            // Signature section
            $pdf->SetFont('helvetica', 'BI', 16);
            $pdf->SetTextColor(51, 51, 51);
            $pdf->SetX(170);
            $pdf->SetY(170);
            $signatureName = $template['signature_name'] ?? $this->settings['certificate_signature_name'] ?? 'Leadership Team';
            $pdf->Cell(80, 8, $signatureName, 0, 0, 'C');

            // Signature title
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetTextColor(51, 51, 51);
            $pdf->SetX(170);
            $pdf->SetY(178);
            $signatureTitle = $template['signature_title'] ?? $this->settings['certificate_signature_title'] ?? 'Event Organizer';
            $pdf->Cell(80, 6, $signatureTitle, 0, 0, 'C');

            // Event date
            $pdf->SetFont('helvetica', '', 11);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetX(30);
            $pdf->SetY(185);
            $date = date('F j, Y', strtotime($data['start_datetime'] ?? 'now'));
            $pdf->Cell(100, 6, 'Event Date: ' . $date, 0, 0, 'L');

            // Organization name
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(184, 134, 11);
            $pdf->SetX(0);
            $pdf->SetY(185);
            $orgName = $this->settings['certificate_org_name'] ?? 'PULSE';
            $pdf->Cell(297, 6, strtoupper($orgName), 0, 0, 'C');

            return $pdf;
        } catch (Exception $e) {
            throw new Exception('Failed to generate diploma: ' . $e->getMessage());
        }
    }
}