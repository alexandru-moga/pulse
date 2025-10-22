<?php
// Note: We don't actually use TCPDF for generation, just for type hinting if needed
// The diploma generation works by direct PDF text replacement

class DiplomaGenerator {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Generate diploma from template for event participation
     */
    public function generateEventDiploma($userId, $eventId, $templateId = null) {
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
        
        // Generate PDF from template
        return $this->generateFromTemplate($template['template_file'], $user['first_name'], $user['last_name']);
    }
    
    /**
     * Generate certificate from template for project completion
     */
    public function generateProjectCertificate($userId, $projectId, $templateId = null) {
        // Get user and project data
        $stmt = $this->db->prepare("
            SELECT u.first_name, u.last_name, u.email, p.title, p.description, 
                   pa.status, pa.pizza_grant
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
            // Try to find template for this specific project
            $stmt = $this->db->prepare("
                SELECT * FROM diploma_templates 
                WHERE template_type = 'project' AND (related_id = ? OR related_id IS NULL) AND enabled = 1
                ORDER BY related_id DESC LIMIT 1
            ");
            $stmt->execute([$projectId]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if (!$template) {
            throw new Exception('No certificate template available for this project');
        }
        
        // Track certificate download
        $this->trackDiplomaDownload($userId, $projectId, $template['id'], 'project_accepted');
        
        // Generate PDF from template
        return $this->generateFromTemplate($template['template_file'], $data['first_name'], $data['last_name']);
    }
    
    /**
     * Generate PDF from template by replacing text
     * Uses multiple methods to ensure text replacement works
     */
    private function generateFromTemplate($templatePath, $firstName, $lastName) {
        $fullPath = __DIR__ . '/../../' . $templatePath;
        
        if (!file_exists($fullPath)) {
            throw new Exception('Template file not found at path: ' . $fullPath . ' (relative: ' . $templatePath . ')');
        }
        
        // Check if file is readable
        if (!is_readable($fullPath)) {
            throw new Exception('Template file is not readable: ' . $fullPath);
        }
        
        // Read the PDF content as binary
        $pdfContent = file_get_contents($fullPath);
        
        if ($pdfContent === false) {
            throw new Exception('Could not read template file: ' . $fullPath);
        }
        
        if (strlen($pdfContent) === 0) {
            throw new Exception('Template file is empty: ' . $fullPath);
        }
        
        // Try TCPDF-based method first (more reliable)
        try {
            return $this->generateWithTCPDF($fullPath, $firstName, $lastName);
        } catch (Exception $e) {
            error_log("TCPDF generation failed, falling back to binary replacement: " . $e->getMessage());
        }
        
        // Fallback: Try direct binary replacement (works for simple PDFs)
        $originalLength = strlen($pdfContent);
        
        // Method 1: Replace text placeholders in PDF content streams
        $pdfContent = $this->replacePDFText($pdfContent, 'First Name', $firstName);
        $pdfContent = $this->replacePDFText($pdfContent, 'Last Name', $lastName);
        
        // Also handle uppercase versions
        $pdfContent = $this->replacePDFText($pdfContent, 'FIRST NAME', strtoupper($firstName));
        $pdfContent = $this->replacePDFText($pdfContent, 'LAST NAME', strtoupper($lastName));
        
        // Handle variations without spaces
        $pdfContent = $this->replacePDFText($pdfContent, 'FirstName', $firstName);
        $pdfContent = $this->replacePDFText($pdfContent, 'LastName', $lastName);
        
        // Handle with underscores (common in form fields)
        $pdfContent = $this->replacePDFText($pdfContent, 'First_Name', $firstName);
        $pdfContent = $this->replacePDFText($pdfContent, 'Last_Name', $lastName);
        
        // Handle with brackets (form fields)
        $pdfContent = str_replace('[First Name]', $firstName, $pdfContent);
        $pdfContent = str_replace('[Last Name]', $lastName, $pdfContent);
        $pdfContent = str_replace('[FirstName]', $firstName, $pdfContent);
        $pdfContent = str_replace('[LastName]', $lastName, $pdfContent);
        
        // Return the modified PDF content
        return $pdfContent;
    }
    
    /**
     * Replace text in PDF accounting for PDF encoding
     */
    private function replacePDFText($pdfContent, $search, $replace) {
        // Ensure replacement text is same length by padding with spaces
        // This prevents PDF corruption when text lengths differ
        $searchLen = strlen($search);
        $replaceLen = strlen($replace);
        
        if ($replaceLen < $searchLen) {
            // Pad with spaces to match original length
            $replace = str_pad($replace, $searchLen, ' ', STR_PAD_RIGHT);
        } elseif ($replaceLen > $searchLen) {
            // Truncate if too long (better than corruption)
            $replace = substr($replace, 0, $searchLen);
        }
        
        // Replace in regular text
        $pdfContent = str_replace($search, $replace, $pdfContent);
        
        // Replace in hex-encoded text (common in PDFs)
        $searchHex = $this->stringToHex($search);
        $replaceHex = $this->stringToHex($replace);
        $pdfContent = str_replace($searchHex, $replaceHex, $pdfContent);
        
        // Replace in text with PDF escape sequences
        $pdfContent = str_replace('(' . $search . ')', '(' . $replace . ')', $pdfContent);
        
        return $pdfContent;
    }
    
    /**
     * Convert string to hex for PDF hex encoding
     */
    private function stringToHex($string) {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $hex .= sprintf('%02X', ord($string[$i]));
        }
        return $hex;
    }
    
    /**
     * Generate PDF using TCPDF with template as background
     * This is more reliable than binary replacement
     */
    private function generateWithTCPDF($templatePath, $firstName, $lastName) {
        require_once __DIR__ . '/../../lib/tcpdf/tcpdf.php';
        
        // Create new PDF document
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Daydream Timisoara');
        $pdf->SetAuthor('Daydream Timisoara');
        $pdf->SetTitle('Certificate');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins to 0 for full page background
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        
        // Add a page
        $pdf->AddPage();
        
        // Get page dimensions
        $pageWidth = $pdf->getPageWidth();
        $pageHeight = $pdf->getPageHeight();
        
        // Import the template PDF as background
        // Note: TCPDF doesn't support importing PDFs directly
        // So we'll use setImageScale and Image if template is converted to image
        
        // For now, we need to parse the template to find text positions
        // This is complex, so we'll throw an exception to fall back to binary method
        throw new Exception('TCPDF method requires template position mapping');
    }
    
    /**
     * Get available diplomas for user
     */
    public function getAvailableDiplomas($userId) {
        $diplomas = [
            'events' => [],
            'projects' => []
        ];
        
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
                SELECT DISTINCT p.id, p.title, p.created_at, 'project' as type,
                       dt.id as template_id, dt.title as template_title
                FROM projects p
                JOIN project_assignments pa ON p.id = pa.project_id
                LEFT JOIN diploma_templates dt ON (dt.template_type = 'project' AND (dt.related_id = p.id OR dt.related_id IS NULL) AND dt.enabled = 1)
                WHERE pa.user_id = ? AND pa.status IN ('accepted', 'completed')
                HAVING template_id IS NOT NULL
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$userId]);
            $diplomas['projects'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error fetching diplomas: " . $e->getMessage());
            // Return empty arrays on error instead of partial data
        }
        
        return $diplomas;
    }
    
    /**
     * Check if user is eligible for event diploma
     */
    public function isEligibleForEventDiploma($userId, $eventId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM event_attendance ea
                JOIN diploma_templates dt ON (dt.template_type = 'event' AND (dt.related_id = ea.event_id OR dt.related_id IS NULL) AND dt.enabled = 1)
                WHERE ea.user_id = ? AND ea.event_id = ? AND ea.status IN ('going', 'participated')
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
                SELECT COUNT(*) FROM project_assignments pa
                JOIN diploma_templates dt ON (dt.template_type = 'project' AND (dt.related_id = pa.project_id OR dt.related_id IS NULL) AND dt.enabled = 1)
                WHERE pa.user_id = ? AND pa.project_id = ? AND pa.status IN ('accepted', 'completed')
            ");
            $stmt->execute([$userId, $projectId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    private function trackDiplomaDownload($userId, $relatedId, $templateId, $type) {
        try {
            if ($type === 'event_diploma') {
                $stmt = $this->db->prepare("
                    INSERT INTO certificate_downloads (user_id, event_id, diploma_template_id, certificate_type, download_count) 
                    VALUES (?, ?, ?, ?, 1) 
                    ON DUPLICATE KEY UPDATE 
                    download_count = download_count + 1, 
                    downloaded_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$userId, $relatedId, $templateId, $type]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO certificate_downloads (user_id, project_id, diploma_template_id, certificate_type, download_count) 
                    VALUES (?, ?, ?, ?, 1) 
                    ON DUPLICATE KEY UPDATE 
                    download_count = download_count + 1, 
                    downloaded_at = CURRENT_TIMESTAMP
                ");
                $stmt->execute([$userId, $relatedId, $templateId, $type]);
            }
        } catch (PDOException $e) {
            error_log("Diploma download tracking failed: " . $e->getMessage());
        }
    }
}
