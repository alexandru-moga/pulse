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
        // Pattern to find stream objects
        $pattern = '/stream\s+(.*?)\s+endstream/s';
        
        $modified = preg_replace_callback($pattern, function($matches) use ($replacements) {
            $streamData = $matches[1];
            
            // Try to decompress if it's FlateDecode
            $decompressed = @gzuncompress($streamData);
            
            if ($decompressed !== false) {
                // Successfully decompressed, now replace text
                foreach ($replacements as $search => $replace) {
                    // Ensure same length to prevent PDF corruption
                    $searchLen = strlen($search);
                    $replaceLen = strlen($replace);
                    
                    if ($replaceLen < $searchLen) {
                        $replace = str_pad($replace, $searchLen, ' ');
                    } elseif ($replaceLen > $searchLen) {
                        $replace = substr($replace, 0, $searchLen);
                    }
                    
                    // Replace in various PDF text formats
                    $decompressed = str_replace("($search)", "($replace)", $decompressed);
                    $decompressed = str_replace("<" . bin2hex($search) . ">", "<" . bin2hex($replace) . ">", $decompressed);
                    $decompressed = str_replace($search, $replace, $decompressed);
                }
                
                // Recompress
                $recompressed = gzcompress($decompressed);
                
                return 'stream' . "\n" . $recompressed . "\n" . 'endstream';
            }
            
            // Could not decompress, return original
            return $matches[0];
        }, $content);
        
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
