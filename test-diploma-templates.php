<?php
/**
 * Test script to verify diploma templates
 * This will help debug template file paths and permissions
 */
require_once __DIR__ . '/core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

echo "<!DOCTYPE html>";
echo "<html><head><title>Template Verification</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 0 20px; }
    h1 { color: #333; }
    .template { 
        border: 1px solid #ddd; 
        padding: 15px; 
        margin: 10px 0; 
        border-radius: 5px; 
        background: #f9f9f9;
    }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: #666; font-size: 0.9em; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    td { padding: 5px; border-bottom: 1px solid #eee; }
    td:first-child { font-weight: bold; width: 200px; }
</style>";
echo "</head><body>";

echo "<h1>Diploma Template Verification</h1>";

// Handle test download
if (isset($_GET['test_template']) && isset($_GET['template_id'])) {
    $templateId = $_GET['template_id'];
    
    try {
        require_once __DIR__ . '/core/classes/DiplomaGenerator.php';
        
        $stmt = $db->prepare("SELECT * FROM diploma_templates WHERE id = ?");
        $stmt->execute([$templateId]);
        $template = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$template) {
            throw new Exception('Template not found');
        }
        
        $fullPath = __DIR__ . '/' . $template['template_file'];
        $pdfContent = file_get_contents($fullPath);
        
        // Test with sample names
        $firstName = "TEST_FIRST";
        $lastName = "TEST_LAST";
        
        $generator = new DiplomaGenerator($db);
        // Use reflection to call private method for testing
        $reflection = new ReflectionClass($generator);
        $method = $reflection->getMethod('generateFromTemplate');
        $method->setAccessible(true);
        
        $result = $method->invoke($generator, $template['template_file'], $firstName, $lastName);
        
        // Output the PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="test_template_' . $templateId . '.pdf"');
        header('Content-Length: ' . strlen($result));
        echo $result;
        exit;
        
    } catch (Exception $e) {
        echo "<div style='padding:20px; background:#ffcccc; border:2px solid red; margin:20px 0;'>";
        echo "<strong>Test Download Failed:</strong> " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
}

// Get all templates from database
$stmt = $db->prepare("
    SELECT dt.*, 
           CASE 
               WHEN dt.template_type = 'event' THEN e.title
               WHEN dt.template_type = 'project' THEN p.title
               ELSE NULL
           END as related_name
    FROM diploma_templates dt
    LEFT JOIN events e ON dt.template_type = 'event' AND dt.related_id = e.id
    LEFT JOIN projects p ON dt.template_type = 'project' AND dt.related_id = p.id
    ORDER BY dt.created_at DESC
");
$stmt->execute();
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($templates)) {
    echo "<p class='warning'>No templates found in database.</p>";
} else {
    echo "<p>Found " . count($templates) . " template(s) in database.</p>";
    
    foreach ($templates as $template) {
        echo "<div class='template'>";
        echo "<h2>{$template['title']} ";
        echo "<a href='?test_template=1&template_id={$template['id']}' style='font-size:0.7em; padding:5px 15px; background:#0066cc; color:white; text-decoration:none; border-radius:3px; margin-left:10px;'>🧪 Test Download</a>";
        echo "</h2>";
        
        echo "<table>";
        echo "<tr><td>ID:</td><td>{$template['id']}</td></tr>";
        echo "<tr><td>Type:</td><td>{$template['template_type']}</td></tr>";
        
        if ($template['related_id']) {
            echo "<tr><td>Related To:</td><td>{$template['related_name']} (ID: {$template['related_id']})</td></tr>";
        } else {
            echo "<tr><td>Related To:</td><td><em>Generic (applies to all)</em></td></tr>";
        }
        
        echo "<tr><td>Enabled:</td><td>" . ($template['enabled'] ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</td></tr>";
        echo "<tr><td>Template Path:</td><td>{$template['template_file']}</td></tr>";
        
        // Check if file exists
        $fullPath = __DIR__ . '/' . $template['template_file'];
        echo "<tr><td>Full Path:</td><td class='info'>{$fullPath}</td></tr>";
        
        if (file_exists($fullPath)) {
            echo "<tr><td>File Exists:</td><td class='success'>✓ Yes</td></tr>";
            
            // Check if readable
            if (is_readable($fullPath)) {
                echo "<tr><td>Readable:</td><td class='success'>✓ Yes</td></tr>";
                
                // Get file size
                $fileSize = filesize($fullPath);
                $fileSizeKB = number_format($fileSize / 1024, 2);
                echo "<tr><td>File Size:</td><td>{$fileSizeKB} KB ({$fileSize} bytes)</td></tr>";
                
                // Check if it's actually a PDF
                $handle = fopen($fullPath, 'r');
                $header = fread($handle, 4);
                fclose($handle);
                
                if ($header === '%PDF') {
                    echo "<tr><td>Valid PDF:</td><td class='success'>✓ Yes</td></tr>";
                } else {
                    echo "<tr><td>Valid PDF:</td><td class='error'>✗ No (header: " . bin2hex($header) . ")</td></tr>";
                }
                
                // Check for placeholders
                $content = file_get_contents($fullPath);
                
                // Also check in decompressed streams if PDF is compressed
                $decompressedText = '';
                $hasCompression = strpos($content, '/FlateDecode') !== false;
                
                if ($hasCompression) {
                    // Try to decompress streams and extract text
                    preg_match_all('/stream\s+(.*?)\s+endstream/s', $content, $streamMatches);
                    foreach ($streamMatches[1] as $stream) {
                        $decompressed = @gzuncompress($stream);
                        if ($decompressed !== false) {
                            $decompressedText .= $decompressed;
                        }
                    }
                }
                
                // Combine both raw and decompressed content for searching
                $searchContent = $content . ' ' . $decompressedText;
                
                $hasFirstName = strpos($searchContent, 'First Name') !== false || strpos($searchContent, 'FirstName') !== false || strpos($searchContent, 'FIRST NAME') !== false;
                $hasLastName = strpos($searchContent, 'Last Name') !== false || strpos($searchContent, 'LastName') !== false || strpos($searchContent, 'LAST NAME') !== false;
                
                // Detailed placeholder analysis
                $placeholders = [
                    'First Name' => strpos($searchContent, 'First Name') !== false,
                    'Last Name' => strpos($searchContent, 'Last Name') !== false,
                    'FirstName' => strpos($searchContent, 'FirstName') !== false,
                    'LastName' => strpos($searchContent, 'LastName') !== false,
                    'FIRST NAME' => strpos($searchContent, 'FIRST NAME') !== false,
                    'LAST NAME' => strpos($searchContent, 'LAST NAME') !== false,
                    'First_Name' => strpos($searchContent, 'First_Name') !== false,
                    'Last_Name' => strpos($searchContent, 'Last_Name') !== false,
                    '[First Name]' => strpos($searchContent, '[First Name]') !== false,
                    '[Last Name]' => strpos($searchContent, '[Last Name]') !== false,
                ];
                
                echo "<tr><td colspan='2' style='background:#f0f0f0; font-weight:bold; padding:10px;'>Placeholder Detection</td></tr>";
                
                $foundAny = false;
                foreach ($placeholders as $placeholder => $found) {
                    if ($found) {
                        echo "<tr><td>Found '{$placeholder}':</td><td class='success'>✓ Yes</td></tr>";
                        $foundAny = true;
                    }
                }
                
                if (!$foundAny) {
                    echo "<tr><td colspan='2'><span class='error'>✗ No standard placeholders found in PDF</span></td></tr>";
                    echo "<tr><td colspan='2'><span class='warning'>⚠ This PDF may use compressed streams or form fields. Text replacement may not work.</span></td></tr>";
                }
                
                // Check PDF structure
                echo "<tr><td colspan='2' style='background:#f0f0f0; font-weight:bold; padding:10px;'>PDF Structure Analysis</td></tr>";
                
                // Check for compression
                $hasFlate = strpos($content, '/FlateDecode') !== false;
                $hasFilter = strpos($content, '/Filter') !== false;
                echo "<tr><td>Compressed Streams:</td><td>" . ($hasFlate ? '<span class="warning">⚠ Yes (FlateDecode)</span>' : '<span class="success">✓ No</span>') . "</td></tr>";
                
                // Check for form fields
                $hasAcroForm = strpos($content, '/AcroForm') !== false;
                echo "<tr><td>Has Form Fields:</td><td>" . ($hasAcroForm ? '<span class="warning">⚠ Yes (may need form filling)</span>' : '<span class="success">✓ No</span>') . "</td></tr>";
                
                // Check for fonts
                $hasFonts = strpos($content, '/Font') !== false;
                echo "<tr><td>Has Fonts:</td><td>" . ($hasFonts ? '<span class="success">✓ Yes</span>' : '<span class="warning">⚠ No</span>') . "</td></tr>";
                
                // Check PDF version
                preg_match('/%PDF-([\d.]+)/', $content, $versionMatch);
                if ($versionMatch) {
                    echo "<tr><td>PDF Version:</td><td>{$versionMatch[1]}</td></tr>";
                }
                
                // Check for text objects
                $textObjects = substr_count($content, 'BT') + substr_count($content, 'ET');
                echo "<tr><td>Text Objects:</td><td>{$textObjects}</td></tr>";
                
                // Try to extract some visible text for preview
                echo "<tr><td colspan='2' style='background:#f0f0f0; font-weight:bold; padding:10px;'>Sample Text Extract</td></tr>";
                
                // Extract readable text (very basic)
                $sampleText = '';
                
                // Try from decompressed content first
                if (!empty($decompressedText)) {
                    preg_match_all('/\((.*?)\)/', substr($decompressedText, 0, 10000), $textMatches);
                    if (!empty($textMatches[1])) {
                        $sampleText = implode(' ', array_slice($textMatches[1], 0, 15));
                    }
                }
                
                // Fallback to raw content
                if (empty($sampleText)) {
                    preg_match_all('/\((.*?)\)/', substr($content, 0, 5000), $textMatches);
                    if (!empty($textMatches[1])) {
                        $sampleText = implode(' ', array_slice($textMatches[1], 0, 10));
                    }
                }
                
                if (!empty($sampleText)) {
                    $sampleText = substr($sampleText, 0, 500);
                    echo "<tr><td colspan='2'><pre style='white-space:pre-wrap; font-size:0.85em; background:#fff; padding:10px; border:1px solid #ddd;'>" . htmlspecialchars($sampleText) . "</pre></td></tr>";
                } else {
                    echo "<tr><td colspan='2'><span class='warning'>⚠ Could not extract readable text</span></td></tr>";
                }
                
                // Recommendation
                echo "<tr><td colspan='2' style='background:#ffffcc; padding:10px;'>";
                if ($foundAny && !$hasCompression) {
                    echo "<strong style='color:green;'>✓ This template should work with text replacement!</strong>";
                } elseif ($foundAny && $hasCompression) {
                    echo "<strong style='color:green;'>✓ Template has placeholders in compressed streams!</strong><br>";
                    echo "The system will automatically decompress, replace text, and recompress. This should work!";
                } elseif ($hasAcroForm) {
                    echo "<strong style='color:orange;'>⚠ This PDF uses form fields instead of text.</strong><br>";
                    echo "The system needs to be updated to fill form fields, or re-create the template with plain text.";
                } else {
                    echo "<strong style='color:red;'>✗ No placeholders detected. This template won't work.</strong><br>";
                    echo "Create a new template with 'First Name' and 'Last Name' as searchable text. See TEMPLATE_CREATION_GUIDE.md for help.";
                }
                echo "</td></tr>";
                
                echo "<tr><td>Contains 'First Name':</td><td>" . ($hasFirstName ? '<span class="success">✓ Yes</span>' : '<span class="warning">✗ No</span>') . "</td></tr>";
                echo "<tr><td>Contains 'Last Name':</td><td>" . ($hasLastName ? '<span class="success">✓ Yes</span>' : '<span class="warning">✗ No</span>') . "</td></tr>";
                
                if (!$hasFirstName || !$hasLastName) {
                    echo "<tr><td colspan='2'><span class='warning'>⚠ Warning: Template may not contain the required placeholders 'First Name' and 'Last Name'</span></td></tr>";
                }
                
            } else {
                echo "<tr><td>Readable:</td><td class='error'>✗ No (permission issue)</td></tr>";
            }
        } else {
            echo "<tr><td>File Exists:</td><td class='error'>✗ No</td></tr>";
            
            // Check if directory exists
            $dir = dirname($fullPath);
            if (file_exists($dir)) {
                echo "<tr><td>Directory Exists:</td><td class='success'>✓ Yes</td></tr>";
                echo "<tr><td colspan='2'><span class='error'>Error: Template file not found at expected location</span></td></tr>";
            } else {
                echo "<tr><td>Directory Exists:</td><td class='error'>✗ No</td></tr>";
                echo "<tr><td colspan='2'><span class='error'>Error: Upload directory does not exist</span></td></tr>";
            }
        }
        
        echo "</table>";
        echo "</div>";
    }
}

// Check upload directory
echo "<h2>Upload Directory Check</h2>";
$uploadDir = __DIR__ . '/uploads/diploma-templates';
echo "<div class='template'>";
echo "<table>";
echo "<tr><td>Upload Directory:</td><td>{$uploadDir}</td></tr>";

if (file_exists($uploadDir)) {
    echo "<tr><td>Exists:</td><td class='success'>✓ Yes</td></tr>";
    
    if (is_writable($uploadDir)) {
        echo "<tr><td>Writable:</td><td class='success'>✓ Yes</td></tr>";
    } else {
        echo "<tr><td>Writable:</td><td class='error'>✗ No (permission issue)</td></tr>";
    }
    
    // List files in directory
    $files = scandir($uploadDir);
    $pdfFiles = array_filter($files, function($file) use ($uploadDir) {
        return is_file($uploadDir . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'pdf';
    });
    
    echo "<tr><td>PDF Files:</td><td>" . count($pdfFiles) . " file(s)</td></tr>";
    
    if (!empty($pdfFiles)) {
        echo "<tr><td>Files:</td><td>";
        echo "<ul style='margin: 5px 0; padding-left: 20px;'>";
        foreach ($pdfFiles as $file) {
            $fileSize = filesize($uploadDir . '/' . $file);
            $fileSizeKB = number_format($fileSize / 1024, 2);
            echo "<li>{$file} ({$fileSizeKB} KB)</li>";
        }
        echo "</ul>";
        echo "</td></tr>";
    }
    
} else {
    echo "<tr><td>Exists:</td><td class='error'>✗ No</td></tr>";
    echo "<tr><td colspan='2'><span class='error'>Error: Upload directory needs to be created</span></td></tr>";
}

echo "</table>";
echo "</div>";

echo "<p style='margin-top: 30px;'><a href='/dashboard/diploma-templates.php'>← Back to Template Management</a></p>";

echo "<p><a href='?'>Refresh Page</a> | <a href='/dashboard/diploma-templates.php'>← Back to Template Management</a></p>";

echo "<div style='background:#e7f3ff; padding:15px; margin:20px 0; border-left:4px solid #0066cc;'>";
echo "<strong>ℹ️ How to use this tool:</strong><br>";
echo "1. Check if your templates exist and are readable<br>";
echo "2. Verify that placeholders are detected<br>";
echo "3. Click 'Test Download' to generate a sample PDF with TEST_FIRST TEST_LAST<br>";
echo "4. Open the downloaded PDF - if you see the test names, the template works!<br>";
echo "5. If not, follow the recommendations or see TEMPLATE_CREATION_GUIDE.md";
echo "</div>";

echo "</body></html>";
