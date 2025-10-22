<?php
/**
 * Advanced PDF text replacement using decompression
 * This handles compressed PDF streams (FlateDecode)
 */

class AdvancedPDFReplacer {
    
    /**
     * Replace text in a PDF, handling compressed streams
     */
    public static function replaceText($pdfPath, $replacements, $outputPath = null) {
        $content = file_get_contents($pdfPath);
        
        if ($content === false) {
            throw new Exception("Could not read PDF file: $pdfPath");
        }
        
        // Try to decompress streams and replace text
        $modified = self::processCompressedStreams($content, $replacements);
        
        // If no output path specified, return the content
        if ($outputPath === null) {
            return $modified;
        }
        
        // Save to file
        $result = file_put_contents($outputPath, $modified);
        if ($result === false) {
            throw new Exception("Could not write to output file: $outputPath");
        }
        
        return $modified;
    }
    
    /**
     * Process compressed streams in PDF
     */
    private static function processCompressedStreams($content, $replacements) {
        $replacementCount = 0;
        
        // Pattern to find stream objects with their object numbers
        $pattern = '/(\d+\s+\d+\s+obj.*?)stream\r?\n(.*?)\r?\nendstream/s';
        
        $modified = preg_replace_callback($pattern, function($matches) use ($replacements, &$replacementCount) {
            $objectHeader = $matches[1];
            $streamData = $matches[2];
            
            // Try multiple decompression methods
            $decompressed = false;
            
            // Method 1: gzuncompress (zlib format)
            $decompressed = @gzuncompress($streamData);
            
            // Method 2: gzinflate (raw deflate - most common in PDFs)
            if ($decompressed === false) {
                $decompressed = @gzinflate($streamData);
            }
            
            // Method 3: Try with different zlib/deflate headers
            if ($decompressed === false) {
                // Add zlib header if missing
                $decompressed = @gzinflate(substr($streamData, 2));
            }
            
            if ($decompressed !== false) {
                $modified = false;
                
                // Successfully decompressed, now replace text
                foreach ($replacements as $search => $replace) {
                    // Check if this stream contains the search text
                    if (strpos($decompressed, $search) === false) {
                        continue;
                    }
                    
                    // Ensure same length to prevent PDF corruption
                    $searchLen = strlen($search);
                    $replaceLen = strlen($replace);
                    
                    if ($replaceLen < $searchLen) {
                        $replace = str_pad($replace, $searchLen, ' ');
                    } elseif ($replaceLen > $searchLen) {
                        $replace = substr($replace, 0, $searchLen);
                    }
                    
                    // Replace in various PDF text formats
                    $before = $decompressed;
                    $decompressed = str_replace("($search)", "($replace)", $decompressed);
                    $decompressed = str_replace("<" . bin2hex($search) . ">", "<" . bin2hex($replace) . ">", $decompressed);
                    $decompressed = str_replace($search, $replace, $decompressed);
                    
                    if ($before !== $decompressed) {
                        $modified = true;
                        $replacementCount++;
                    }
                }
                
                if ($modified) {
                    // Recompress using deflate (matching PDF FlateDecode format)
                    $recompressed = gzcompress($decompressed, 9);
                    // Remove zlib header (first 2 bytes) and trailer (last 4 bytes)
                    $recompressed = substr($recompressed, 2, -4);
                    
                    return $objectHeader . 'stream' . "\n" . $recompressed . "\n" . 'endstream';
                }
            }
            
            // Could not decompress or no modifications needed, return original
            return $matches[0];
        }, $content);
        
        error_log("AdvancedPDFReplacer: Made $replacementCount replacements in compressed streams");
        
        return $modified;
    }
    
    /**
     * Extract all text from a PDF (for debugging)
     */
    public static function extractText($pdfPath) {
        $content = file_get_contents($pdfPath);
        $text = [];
        
        // Find text in parentheses
        preg_match_all('/\((.*?)\)/s', $content, $matches);
        if (!empty($matches[1])) {
            $text = array_merge($text, $matches[1]);
        }
        
        // Try to decompress streams and extract text
        preg_match_all('/stream\s+(.*?)\s+endstream/s', $content, $streamMatches);
        foreach ($streamMatches[1] as $stream) {
            $decompressed = @gzuncompress($stream);
            if ($decompressed !== false) {
                preg_match_all('/\((.*?)\)/s', $decompressed, $textMatches);
                if (!empty($textMatches[1])) {
                    $text = array_merge($text, $textMatches[1]);
                }
            }
        }
        
        return array_unique($text);
    }
}
