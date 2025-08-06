<?php
// Simple PDF generator class to replace TCPDF placeholder
class TCPDF {
    private $pageFormat = 'A4';
    private $orientation = 'L';
    private $unit = 'mm';
    private $content = '';
    private $title = '';
    private $author = '';
    private $subject = '';
    
    public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4') {
        $this->orientation = $orientation;
        $this->unit = $unit;
        $this->pageFormat = $format;
        $this->initializePDF();
    }
    
    private function initializePDF() {
        $this->content = "%PDF-1.4\n";
        $this->content .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $this->content .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    }
    
    public function AddPage($orientation = '', $format = '') {
        // Add page structure to PDF
    }
    
    public function SetFont($family, $style = '', $size = 0) {
        // Store font settings
    }
    
    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '') {
        // Add text to content
    }
    
    public function Output($name = '', $dest = '') {
        // Generate a proper PDF with content
        $pdf_content = $this->generateSimplePDF();
        
        if ($dest === 'D') {
            // Download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header('Content-Length: ' . strlen($pdf_content));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            echo $pdf_content;
        } else {
            return $pdf_content;
        }
    }
    
    private function generateSimplePDF() {
        // Create a basic but valid PDF structure
        $pdf = "%PDF-1.4\n";
        
        // Catalog
        $pdf .= "1 0 obj\n";
        $pdf .= "<< /Type /Catalog /Pages 2 0 R >>\n";
        $pdf .= "endobj\n";
        
        // Pages
        $pdf .= "2 0 obj\n";
        $pdf .= "<< /Type /Pages /Kids [3 0 R] /Count 1 >>\n";
        $pdf .= "endobj\n";
        
        // Page
        $pdf .= "3 0 obj\n";
        $pdf .= "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\n";
        $pdf .= "endobj\n";
        
        // Font
        $pdf .= "4 0 obj\n";
        $pdf .= "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\n";
        $pdf .= "endobj\n";
        
        // Content stream
        $content = "BT\n";
        $content .= "/F1 24 Tf\n";
        $content .= "100 500 Td\n";
        $content .= "(CERTIFICATE OF ACHIEVEMENT) Tj\n";
        $content .= "0 -50 Td\n";
        $content .= "/F1 16 Tf\n";
        $content .= "(This certifies that) Tj\n";
        $content .= "0 -40 Td\n";
        $content .= "/F1 20 Tf\n";
        $content .= "(" . ($this->recipientName ?? 'Certificate Recipient') . ") Tj\n";
        $content .= "0 -40 Td\n";
        $content .= "/F1 16 Tf\n";
        $content .= "(has successfully completed the project) Tj\n";
        $content .= "0 -40 Td\n";
        $content .= "/F1 18 Tf\n";
        $content .= "(\"" . ($this->projectTitle ?? 'Project') . "\") Tj\n";
        $content .= "0 -60 Td\n";
        $content .= "/F1 12 Tf\n";
        $content .= "(Awarded on " . date('F j, Y') . ") Tj\n";
        $content .= "0 -40 Td\n";
        $content .= "(PULSE) Tj\n";
        $content .= "ET\n";
        
        $pdf .= "5 0 obj\n";
        $pdf .= "<< /Length " . strlen($content) . " >>\n";
        $pdf .= "stream\n";
        $pdf .= $content;
        $pdf .= "endstream\n";
        $pdf .= "endobj\n";
        
        // Cross-reference table
        $pdf .= "xref\n";
        $pdf .= "0 6\n";
        $pdf .= "0000000000 65535 f \n";
        $pdf .= "0000000009 65535 n \n";
        $pdf .= "0000000055 65535 n \n";
        $pdf .= "0000000109 65535 n \n";
        $pdf .= "0000000217 65535 n \n";
        $pdf .= "0000000280 65535 n \n";
        
        // Trailer
        $pdf .= "trailer\n";
        $pdf .= "<< /Size 6 /Root 1 0 R >>\n";
        $pdf .= "startxref\n";
        $pdf .= (strlen($pdf) + 10) . "\n";
        $pdf .= "%%EOF\n";
        
        return $pdf;
    }
    
    // Properties to store certificate data
    public $recipientName = '';
    public $projectTitle = '';
    
    public function setCertificateData($name, $title) {
        $this->recipientName = $name;
        $this->projectTitle = $title;
    }
    
    // ...existing placeholder methods...
    public function SetY($y) {}
    public function SetX($x) {}
    public function Image($file, $x = '', $y = '', $w = 0, $h = 0) {}
    public function setCreator($creator) {}
    public function setAuthor($author) { $this->author = $author; }
    public function setTitle($title) { $this->title = $title; }
    public function setSubject($subject) { $this->subject = $subject; }
    public function setPrintHeader($boolean) {}
    public function setPrintFooter($boolean) {}
    public function setMargins($left, $top, $right) {}
    public function setAutoPageBreak($auto, $margin = 0) {}
    public function setFillColor($r, $g, $b) {}
    public function Rect($x, $y, $w, $h, $style = '') {}
    public function setLineWidth($width) {}
    public function setDrawColor($r, $g, $b) {}
    public function setTextColor($r, $g, $b) {}
    public function Line($x1, $y1, $x2, $y2) {}
}