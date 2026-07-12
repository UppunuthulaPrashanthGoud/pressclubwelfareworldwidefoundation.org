<?php
/**
 * Text utility functions for certificate management
 */

/**
 * Clean HTML content for display in tables and lists
 * @param string $html HTML content
 * @param int $length Maximum length (optional)
 * @return string Cleaned text
 */
function cleanHtmlForDisplay($html, $length = null) {
    if (empty($html)) {
        return '';
    }
    
    // Decode HTML entities first
    $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Strip HTML tags
    $clean = strip_tags($decoded);
    
    // Remove extra whitespace
    $clean = preg_replace('/\s+/', ' ', $clean);
    $clean = trim($clean);
    
    // Truncate if length specified
    if ($length && strlen($clean) > $length) {
        $clean = substr($clean, 0, $length) . '...';
    }
    
    return $clean;
}

/**
 * Clean HTML content for certificate generation
 * @param string $html HTML content
 * @return string Cleaned text suitable for certificates
 */
function cleanHtmlForCertificate($html) {
    if (empty($html)) {
        return '';
    }
    
    // Decode HTML entities
    $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // Strip HTML tags but preserve line breaks
    $clean = strip_tags($decoded);
    
    // Normalize whitespace but preserve paragraph breaks
    $clean = preg_replace('/\n\s*\n/', "\n\n", $clean);
    $clean = preg_replace('/[ \t]+/', ' ', $clean);
    $clean = trim($clean);
    
    return $clean;
}

/**
 * Sanitize input while preserving Hindi text
 * @param string $input Input text
 * @return string Sanitized text
 */
function sanitizeHindiText($input) {
    if (empty($input)) {
        return '';
    }
    
    // Remove dangerous HTML tags but allow basic formatting
    $allowed_tags = '<p><br><strong><b><em><i>';
    $clean = strip_tags($input, $allowed_tags);
    
    // Remove script and style content
    $clean = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $clean);
    $clean = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $clean);
    
    return trim($clean);
}

/**
 * Format date in Hindi
 * @param string $date Date string
 * @return string Formatted Hindi date
 */
function formatHindiDate($date) {
    if (!$date) return '';
    
    $hindiMonths = [
        1 => 'जनवरी', 2 => 'फरवरी', 3 => 'मार्च', 4 => 'अप्रैल',
        5 => 'मई', 6 => 'जून', 7 => 'जुलाई', 8 => 'अगस्त',
        9 => 'सितंबर', 10 => 'अक्टूबर', 11 => 'नवंबर', 12 => 'दिसंबर'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = (int)date('m', $timestamp);
    $year = date('Y', $timestamp);
    
    return $day . ' ' . $hindiMonths[$month] . ' ' . $year;
}

/**
 * Clean text for certificate display (removes HTML and formats properly)
 * @param string $text Text to clean
 * @return string Cleaned text
 */
function cleanTextForCertificate($text) {
    if (empty($text)) {
        return '';
    }
    
    // Get text content without HTML tags
    $cleanText = strip_tags($text);
    
    // Replace multiple spaces with single space
    $cleanText = preg_replace('/\s+/', ' ', $cleanText);
    
    // Trim whitespace
    $cleanText = trim($cleanText);
    
    return $cleanText;
}
?>
