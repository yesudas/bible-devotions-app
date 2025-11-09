<?php
/**
 * Generate Master Devotions Data File
 * 
 * This script reads translations.js files from all devotion brands
 * and creates a master JSON file with devotion metadata organized by language.
 * 
 * Output: data/devotions.json
 * 
 * Usage: 
 *   php d.php (command line)
 *   Or open in browser: http://localhost/d.php
 * 
 * When to run:
 *   - After updating any translations.js file in any devotion brand
 *   - After adding a new devotion brand
 *   - After modifying brand metadata (author, email, etc.)
 * 
 * Structure of output file:
 * {
 *   "devotions": {
 *     "English": {
 *       "brands": [{
 *         "name": "3-Minute Meditation",
 *         "appFolder": "3-minute-meditation",
 *         "labels": { ... all label translations ... },
 *         "author": "Pr. Maria Joseph",
 *         "email": "mjosephnj@gmail.com",
 *         "phone": "+919243183231",
 *         "whatsapp": "919243183231",
 *         "website": "https://wordofgod.in"
 *       }]
 *     },
 *     "தமிழ்": { ... },
 *     ...
 *   },
 *   "generatedAt": "2025-11-09 12:34:56",
 *   "version": "1.0"
 * }
 */

// Detect if running in browser or CLI
$isCLI = php_sapi_name() === 'cli';
$nl = $isCLI ? "\n" : "<br>\n";
$nl2 = $isCLI ? "\n\n" : "<br><br>\n";

// Helper function for colored output
function output($text, $class = '') {
    global $isCLI;
    if ($isCLI || empty($class)) {
        return $text;
    }
    return "<span class='$class'>$text</span>";
}

// If browser, set content type and add basic styling
if (!$isCLI) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Devotions Data Generator</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .warning { color: #ffc107; }
        .error { color: #dc3545; }
        .header { font-weight: bold; font-size: 1.2em; }
    </style>
</head>
<body>
<div class="container">
';
}

// Configuration
$devotionBrands = [
    '3-minute-meditation',
    'அனுதின-மன்னா',
    'faiths-check-book',
    'antantulla-appam',
    'சத்திய-வசனம்',
    'நாளுக்கொரு-நல்ல-பங்கு'
];

// Brand metadata (add author, contact info here)
$brandMetadata = [
    '3-minute-meditation' => [
        'author' => 'Pr. Maria Joseph',
        'email' => 'mjosephnj@gmail.com',
        'phone' => '+919243183231',
        'whatsapp' => '919243183231',
        'website' => 'https://wordofgod.in'
    ],
    'அனுதின-மன்னா' => [
        'author' => 'Gladys Sugandhi Hazlitt',
        'email' => 'simonhm@gmail.com',
        'phone' => '+91919901470809',
        'whatsapp' => '919901470809',
        'website' => 'https://wordofgod.in'
    ],
    'faiths-check-book' => [
        'author' => 'Charles H. Spurgeon',
        'email' => '',
        'phone' => '',
        'whatsapp' => '',
        'website' => 'https://wordofgod.in'
    ],
    'antantulla-appam' => [
        'author' => 'Sam Jebadurai',
        'email' => 'support@elimgrc.com',
        'phone' => '',
        'whatsapp' => '',
        'website' => 'https://elimgrc.com'
    ],
    'சத்திய-வசனம்' => [
        'author' => 'சத்திய வசனம்',
        'email' => 'svmadurai@yahoo.co.in',
        'phone' => '',
        'whatsapp' => '',
        'website' => 'https://SathiyaVasanam.in'
    ],
    'நாளுக்கொரு-நல்ல-பங்கு' => [
        'author' => 'போஸ் பொன்ராஜ்',
        'email' => '',
        'phone' => '',
        'whatsapp' => '',
        'website' => 'https://www.tamilbible.org/'
    ]
];

/**
 * Parse JavaScript translations file and extract label data
 */
function parseTranslationsFile($filePath) {
    if (!file_exists($filePath)) {
        return null;
    }
    
    $content = file_get_contents($filePath);
    
    // Remove single-line comments (but preserve strings)
    $lines = explode("\n", $content);
    $cleanedLines = [];
    foreach ($lines as $line) {
        // Remove comment if it's not inside a string
        if (preg_match('/^([^\'"]*)\/\//', $line, $matches)) {
            $cleanedLines[] = $matches[1];
        } else {
            $cleanedLines[] = $line;
        }
    }
    $content = implode("\n", $cleanedLines);
    
    // Extract the labelTranslations object
    if (preg_match('/const\s+labelTranslations\s*=\s*(\{[\s\S]*\});?\s*$/m', $content, $matches)) {
        $jsObject = trim($matches[1]);
        
        // Convert JavaScript object to JSON
        // Strategy: Replace single quotes with double quotes, except for apostrophes inside strings
        
        // First, temporarily replace escaped quotes
        $jsonString = str_replace("\\'", "<<<ESCAPED_QUOTE>>>", $jsObject);
        
        // Replace single quotes that are property delimiters (key: 'value')
        // This regex looks for: quote, any chars except quotes, quote followed by comma or closing brace
        $jsonString = preg_replace("/'([^']*)'(\s*[,}:])/", '"$1"$2', $jsonString);
        
        // Restore escaped quotes as regular quotes
        $jsonString = str_replace("<<<ESCAPED_QUOTE>>>", "'", $jsonString);
        
        // Remove trailing commas
        $jsonString = preg_replace('/,(\s*[}\]])/m', '$1', $jsonString);
        
        // Try to decode
        $data = json_decode($jsonString, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        } else {
            echo output("JSON decode error for $filePath: " . json_last_error_msg(), 'error') . $GLOBALS['nl'];
            // Uncomment for debugging:
            // echo "Converted JSON sample:" . $GLOBALS['nl'] . substr($jsonString, 0, 800) . "..." . $GLOBALS['nl2'];
            return null;
        }
    }
    
    return null;
}

/**
 * Build the master devotions data structure
 */
function buildDevotionsData($devotionBrands, $brandMetadata) {
    $devotionsByLanguage = [];
    
    foreach ($devotionBrands as $brandFolder) {
        $translationsPath = __DIR__ . '/' . $brandFolder . '/js/translations.js';
        
        echo "Processing: $brandFolder" . $GLOBALS['nl'];
        
        $translations = parseTranslationsFile($translationsPath);
        
        if ($translations === null) {
            echo "  " . output("⚠ Warning: Could not parse translations for $brandFolder", 'warning') . $GLOBALS['nl2'];
            continue;
        }
        
        // Get metadata for this brand
        $metadata = $brandMetadata[$brandFolder] ?? [];
        
        // Process each language in the translations
        foreach ($translations as $language => $labels) {
            // Initialize language if not exists
            if (!isset($devotionsByLanguage[$language])) {
                $devotionsByLanguage[$language] = [
                    'brands' => []
                ];
            }
            
            // Get app name from translations
            $appName = $labels['app_name'] ?? $brandFolder;
            
            // Create brand entry for this language
            $brandEntry = [
                'name' => $appName,
                'appFolder' => $brandFolder,
                'labels' => $labels
            ];
            
            // Add metadata if available
            if (!empty($metadata['author'])) {
                $brandEntry['author'] = $metadata['author'];
            }
            if (!empty($metadata['email'])) {
                $brandEntry['email'] = $metadata['email'];
            }
            if (!empty($metadata['phone'])) {
                $brandEntry['phone'] = $metadata['phone'];
            }
            if (!empty($metadata['whatsapp'])) {
                $brandEntry['whatsapp'] = $metadata['whatsapp'];
            }
            if (!empty($metadata['website'])) {
                $brandEntry['website'] = $metadata['website'];
            }
            
            $devotionsByLanguage[$language]['brands'][] = $brandEntry;
        }
        
        echo "  " . output("✓ Processed successfully", 'success') . $GLOBALS['nl2'];
    }
    
    return $devotionsByLanguage;
}

/**
 * Main execution
 */
echo output("=== Devotions Data Generator ===", 'header') . $nl2;

// Build the data structure
$devotionsData = buildDevotionsData($devotionBrands, $brandMetadata);

// Wrap in devotions object
$output = [
    'devotions' => $devotionsData,
    'generatedAt' => date('Y-m-d H:i:s'),
    'version' => '1.0'
];

// Create data directory if it doesn't exist
$dataDir = __DIR__ . '/data';
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
    echo "Created data directory" . $nl2;
}

// Write to file
$outputPath = $dataDir . '/devotions.json';
$jsonOutput = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents($outputPath, $jsonOutput)) {
    echo $nl . output("✓ Successfully generated: $outputPath", 'success') . $nl;
    echo "File size: " . number_format(strlen($jsonOutput)) . " bytes" . $nl;
    
    // Display summary
    echo $nl . "Summary:" . $nl;
    foreach ($devotionsData as $language => $data) {
        $brandCount = count($data['brands']);
        echo "  - $language: $brandCount brands" . $nl;
    }
} else {
    echo $nl . output("✗ Error: Could not write to $outputPath", 'error') . $nl;
    exit(1);
}

echo $nl . output("=== Done ===", 'header') . $nl;

// Close HTML if in browser
if (!$isCLI) {
    echo '</div></body></html>';
}
