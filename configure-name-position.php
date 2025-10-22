<?php
/**
 * Emergency solution: Add name overlay to existing PDF template
 * Use this if you can't modify the original Figma template
 * 
 * You'll need to specify the X,Y coordinates where names should appear
 */
require_once __DIR__ . '/core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

require_once __DIR__ . '/lib/tcpdf/tcpdf.php';

// CONFIGURATION - Adjust these values for your certificate
$TEMPLATE_CONFIG = [
    'template_path' => 'uploads/diploma-templates/template_1761070974_68f7cf7e65b9d.pdf',
    'page_orientation' => 'L', // L for landscape, P for portrait
    'name_position' => [
        'x' => 148.5,  // X position (center of A4 landscape = 148.5mm)
        'y' => 100,    // Y position (adjust based on your design)
        'width' => 100, // Width of text area
        'align' => 'C' // C=Center, L=Left, R=Right
    ],
    'font' => [
        'family' => 'helvetica',
        'style' => 'B', // B=Bold, I=Italic, BI=Bold Italic, '' =Normal
        'size' => 24,
        'color' => [0, 0, 0] // RGB: [0,0,0]=black, [255,0,0]=red, etc.
    ]
];

echo "<!DOCTYPE html>";
echo "<html><head><title>Configure Name Position</title>";
echo "<style>
    body { font-family: Arial; max-width: 900px; margin: 20px auto; padding: 20px; }
    h1 { color: #333; }
    form { background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; }
    label { display: block; margin: 10px 0 5px 0; font-weight: bold; }
    input, select { padding: 8px; width: 100%; max-width: 300px; box-sizing: border-box; }
    button { background: #0066cc; color: white; padding: 10px 30px; border: none; border-radius: 5px; cursor: pointer; margin: 20px 10px 0 0; font-size: 16px; }
    button:hover { background: #0052a3; }
    .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #0066cc; margin: 20px 0; }
    .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
</style>";
echo "</head><body>";

echo "<h1>🎯 Name Position Configurator</h1>";

echo "<div class='info'>";
echo "<strong>📍 How this works:</strong><br>";
echo "1. Adjust the X/Y coordinates to position where names should appear<br>";
echo "2. Choose font size, style, and color<br>";
echo "3. Test the overlay to see if it looks good<br>";
echo "4. Once perfect, the system will use these settings automatically<br><br>";
echo "<strong>Tips:</strong> For A4 Landscape (297x210mm), center X is ~148mm. For Portrait, center X is ~105mm.";
echo "</div>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test'])) {
    // Generate test PDF with overlay
    
    try {
        $templatePath = __DIR__ . '/' . $TEMPLATE_CONFIG['template_path'];
        
        if (!file_exists($templatePath)) {
            throw new Exception("Template not found: $templatePath");
        }
        
        // For TCPDF to import PDFs, we'd need FPDI
        // Since we don't have that, let's create a new PDF with instructions
        
        $pdf = new TCPDF($TEMPLATE_CONFIG['page_orientation'], 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->AddPage();
        
        // Add semi-transparent background text showing this is a test
        $pdf->SetFont('helvetica', '', 40);
        $pdf->SetTextColor(200, 200, 200);
        $pdf->SetXY(50, 50);
        $pdf->Cell(200, 50, 'TEST OVERLAY', 0, 0, 'C');
        
        // Add the name overlay at specified position
        $x = floatval($_POST['x']);
        $y = floatval($_POST['y']);
        $width = floatval($_POST['width']);
        $align = $_POST['align'];
        $fontSize = intval($_POST['font_size']);
        $fontStyle = $_POST['font_style'];
        
        $pdf->SetFont('helvetica', $fontStyle, $fontSize);
        $pdf->SetTextColor(0, 0, 0);
        
        // Position indicator
        $pdf->SetDrawColor(255, 0, 0);
        $pdf->SetLineWidth(0.5);
        $pdf->Line($x, $y-5, $x, $y+5); // Vertical line
        $pdf->Line($x-5, $y, $x+5, $y); // Horizontal line
        
        // Test name
        $pdf->SetXY($x - $width/2, $y);
        $pdf->Cell($width, 10, 'TEST_FIRST TEST_LAST', 0, 0, $align);
        
        // Instructions
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY(10, 10);
        $pdf->MultiCell(100, 5, 
            "Red crosshair shows position ($x, $y)\n" .
            "This is a TEST overlay.\n\n" .
            "In reality, this will be overlaid on your template.",
            0, 'L'
        );
        
        $pdf->Output('test_overlay.pdf', 'D');
        exit;
        
    } catch (Exception $e) {
        echo "<div class='warning'><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Configuration form
echo "<form method='POST'>";
echo "<h2>📐 Position Settings</h2>";

echo "<label>X Position (horizontal, in mm):</label>";
echo "<input type='number' name='x' value='{$TEMPLATE_CONFIG['name_position']['x']}' step='0.5' required>";
echo "<small style='display:block; color:#666; margin-bottom:15px;'>For A4 Landscape: 0-297mm, Center≈148mm</small>";

echo "<label>Y Position (vertical, in mm):</label>";
echo "<input type='number' name='y' value='{$TEMPLATE_CONFIG['name_position']['y']}' step='0.5' required>";
echo "<small style='display:block; color:#666; margin-bottom:15px;'>For A4 Landscape: 0-210mm, Center≈105mm</small>";

echo "<label>Text Width (mm):</label>";
echo "<input type='number' name='width' value='{$TEMPLATE_CONFIG['name_position']['width']}' step='1' required>";

echo "<label>Text Alignment:</label>";
echo "<select name='align'>";
echo "<option value='C' " . ($TEMPLATE_CONFIG['name_position']['align'] == 'C' ? 'selected' : '') . ">Center</option>";
echo "<option value='L' " . ($TEMPLATE_CONFIG['name_position']['align'] == 'L' ? 'selected' : '') . ">Left</option>";
echo "<option value='R' " . ($TEMPLATE_CONFIG['name_position']['align'] == 'R' ? 'selected' : '') . ">Right</option>";
echo "</select>";

echo "<h2>🔤 Font Settings</h2>";

echo "<label>Font Size:</label>";
echo "<input type='number' name='font_size' value='{$TEMPLATE_CONFIG['font']['size']}' min='8' max='72' required>";

echo "<label>Font Style:</label>";
echo "<select name='font_style'>";
echo "<option value='' " . ($TEMPLATE_CONFIG['font']['style'] == '' ? 'selected' : '') . ">Normal</option>";
echo "<option value='B' " . ($TEMPLATE_CONFIG['font']['style'] == 'B' ? 'selected' : '') . ">Bold</option>";
echo "<option value='I' " . ($TEMPLATE_CONFIG['font']['style'] == 'I' ? 'selected' : '') . ">Italic</option>";
echo "<option value='BI' " . ($TEMPLATE_CONFIG['font']['style'] == 'BI' ? 'selected' : '') . ">Bold Italic</option>";
echo "</select>";

echo "<button type='submit' name='test'>🧪 Generate Test Overlay</button>";
echo "</form>";

echo "<div class='warning'>";
echo "<strong>⚠️ Note:</strong> This is a proof-of-concept tool. To actually use position-based overlays, we need to:<br>";
echo "1. Install FPDI library (for importing existing PDFs)<br>";
echo "2. Update DiplomaGenerator.php to use position-based overlays<br>";
echo "3. Store position configurations in database<br><br>";
echo "For now, the best solution is to:<br>";
echo "✅ Add 'First Name' and 'Last Name' text to your Figma template<br>";
echo "✅ Export with text as searchable (not outlined)<br>";
echo "✅ Or use the sample template generator: /generate-sample-template.php";
echo "</div>";

echo "<p><a href='/test-diploma-templates.php'>← Back to Template Verification</a></p>";

echo "</body></html>";
