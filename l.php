<?php
/**
 * Regenerate Links Script
 * 
 * This script regenerates all link indexes from scratch by:
 * 1. Backing up the current links folder
 * 2. Scanning all meditations across specified devotion folders
 * 3. Creating new link indexes (verse-level and chapter-level)
 * 4. Deleting the backup folder upon successful completion
 * 
 * Usage: Run from command line or browser
 * php l.php
 */

// Detect if running in browser or CLI
$isBrowser = php_sapi_name() !== 'cli';

// Set appropriate line break
$br = $isBrowser ? '<br>' : "\n";

// If browser, set content type and start pre-formatted output
if ($isBrowser) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<html><head><meta charset="utf-8"><title>Link Regeneration</title>';
    echo '<style>body { font-family: monospace; background: #ffffffff; color: #000b00ff; padding: 20px; }</style>';
    echo '</head><body><pre>';
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

$languages = ['தமிழ்', 'English', 'German', 'తెలుగు', 'ಕನ್ನಡ', 'മലയാളം'];

// Statistics tracking
$stats = [
    'total_meditations' => 0,
    'total_verses' => 0,
    'total_chapters' => 0,
    'by_language' => [],
    'by_brand' => [],
    'errors' => []
];

echo "=== Link Index Regeneration Script ===$br$br";

// Step 1: Backup current links folder
echo "Step 1: Backing up current links folder...$br";
$linksDir = __DIR__ . '/links';
$backupDir = __DIR__ . '/links_backup_' . date('Y-m-d_H-i-s');

if (is_dir($linksDir)) {
    if (!recursiveCopy($linksDir, $backupDir)) {
        die("ERROR: Failed to create backup. Aborting.$br");
    }
    echo "✓ Backup created at: $backupDir$br$br";
} else {
    echo "ℹ Links folder doesn't exist yet. Skipping backup.$br$br";
}

// Step 2: Remove current links folder (except this script)
echo "Step 2: Clearing current links folder...$br";
if (is_dir($linksDir)) {
    foreach (scandir($linksDir) as $item) {
        if ($item === '.' || $item === '..' || 
            $item === 'migrate-links.php' || $item === 'README.md') {
            continue;
        }
        $itemPath = $linksDir . '/' . $item;
        if (is_dir($itemPath)) {
            recursiveDelete($itemPath);
        } elseif (is_file($itemPath)) {
            unlink($itemPath);
        }
    }
    echo "✓ Links folder cleared$br$br";
}

// Step 3: Scan and generate links
echo "Step 3: Scanning meditations and generating links...$br";

foreach ($devotionBrands as $brand) {
    echo "$br--- Processing Brand: $brand ---$br";
    
    $brandPath = __DIR__ . '/' . $brand;
    
    if (!is_dir($brandPath)) {
        echo "⚠ Warning: Brand folder not found at $brandPath$br";
        continue;
    }
    
    $meditationsPath = $brandPath . '/meditations';
    
    if (!is_dir($meditationsPath)) {
        echo "⚠ Warning: No meditations folder found at $meditationsPath$br";
        continue;
    }
    
    // Initialize brand stats
    if (!isset($stats['by_brand'][$brand])) {
        $stats['by_brand'][$brand] = [
            'meditations' => 0,
            'verses' => 0,
            'chapters' => 0
        ];
    }
    
    // Process each language
    foreach ($languages as $language) {
        $languagePath = $meditationsPath . '/' . $language;
        
        if (!is_dir($languagePath)) {
            continue;
        }
        
        echo "  Language: $language$br";
        
        // Initialize language stats
        if (!isset($stats['by_language'][$language])) {
            $stats['by_language'][$language] = [
                'meditations' => 0,
                'verses' => 0,
                'chapters' => 0,
                'chapters_list' => []
            ];
        }
        
        $files = glob($languagePath . '/*.json');
        $meditationCount = 0;
        $verseCount = 0;
        
        foreach ($files as $file) {
            $filename = basename($file);
            
            // Skip all-meditations.json
            if ($filename === 'all-meditations.json') {
                continue;
            }
            
            $data = json_decode(file_get_contents($file), true);
            
            if (!$data) {
                $stats['errors'][] = "Failed to parse: $brand/$language/$filename";
                continue;
            }
            
            // Extract key verse
            $keyVerse = $data['key_verse'] ?? '';
            $title = $data['title'] ?? '';
            
            if (empty($keyVerse)) {
                $stats['errors'][] = "Missing key_verse: $brand/$language/$filename";
                continue;
            }
            
            // Save link information
            $result = saveLinkInfo($keyVerse, $title, $language, $filename, $brand, $linksDir);
            
            if ($result['success']) {
                $meditationCount++;
                $verseCount++;
                
                // Track chapters
                if (isset($result['chapter']) && !in_array($result['chapter'], $stats['by_language'][$language]['chapters_list'])) {
                    $stats['by_language'][$language]['chapters_list'][] = $result['chapter'];
                }
            } else {
                $stats['errors'][] = $result['error'];
            }
        }
        
        echo "    ✓ Processed $meditationCount meditations$br";
        echo "    ✓ Created $verseCount verse links$br";
        
        // Update stats
        $stats['by_language'][$language]['meditations'] += $meditationCount;
        $stats['by_language'][$language]['verses'] += $verseCount;
        $stats['by_brand'][$brand]['meditations'] += $meditationCount;
        $stats['by_brand'][$brand]['verses'] += $verseCount;
        $stats['total_meditations'] += $meditationCount;
        $stats['total_verses'] += $verseCount;
    }
}

// Calculate chapter counts
foreach ($stats['by_language'] as $language => $data) {
    $stats['by_language'][$language]['chapters'] = count($data['chapters_list']);
    $stats['total_chapters'] += count($data['chapters_list']);
}

// Step 4: Display summary
echo "$br$br=== SUMMARY ===$br";
echo "{$br}Overall Statistics:$br";
echo "  Total Meditations: {$stats['total_meditations']}$br";
echo "  Total Verse Links: {$stats['total_verses']}$br";
echo "  Total Chapter Indexes: {$stats['total_chapters']}$br";

echo "$br{$br}By Language:$br";
foreach ($stats['by_language'] as $language => $data) {
    echo "  $language:$br";
    echo "    Meditations: {$data['meditations']}$br";
    echo "    Verse Links: {$data['verses']}$br";
    echo "    Chapter Indexes: {$data['chapters']}$br";
}

echo "$br{$br}By Brand:$br";
foreach ($stats['by_brand'] as $brand => $data) {
    echo "  $brand:$br";
    echo "    Meditations: {$data['meditations']}$br";
    echo "    Verse Links: {$data['verses']}$br";
}

if (!empty($stats['errors'])) {
    echo "$br{$br}Errors (" . count($stats['errors']) . "):$br";
    foreach ($stats['errors'] as $error) {
        echo "  ⚠ $error$br";
    }
}

// Step 5: Delete backup if successful
if (empty($stats['errors']) || count($stats['errors']) < 10) {
    echo "$br{$br}Step 4: Cleaning up backup...$br";
    if (is_dir($backupDir)) {
        recursiveDelete($backupDir);
        echo "✓ Backup deleted successfully$br";
    }
} else {
    echo "$br{$br}⚠ WARNING: Too many errors encountered. Backup retained at: $backupDir$br";
    echo "Please review the errors and restore from backup if needed.$br";
}

echo "$br=== Link Regeneration Complete ===$br";

// Close HTML tags if browser
if ($isBrowser) {
    echo '</pre></body></html>';
}

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Save link information for a meditation
 */
function saveLinkInfo($keyVerse, $title, $language, $filename, $brand, $linksDir) {
    if (empty($keyVerse)) {
        return ['success' => false, 'error' => "Empty key verse for $brand/$language/$filename"];
    }
    
    // Parse verse reference (e.g., "58_3:15" or "58_3:15-20")
    // Format: book_number_chapter:verse_start or book_number_chapter:verse_start-verse_end
    $versePattern = '/^(\d+)_(\d+):(\d+)(?:-(\d+))?$/';
    if (!preg_match($versePattern, trim($keyVerse), $matches)) {
        return ['success' => false, 'error' => "Invalid verse format: $keyVerse in $brand/$language/$filename"];
    }
    
    $book = $matches[1]; // Book number
    $chapter = $matches[2];
    $verseStart = $matches[3];
    $verseEnd = $matches[4] ?? $verseStart;
    
    // Create language directory
    $languageDir = $linksDir . '/' . $language;
    if (!is_dir($languageDir)) {
        mkdir($languageDir, 0755, true);
    }
    
    // Create verses directory
    $versesDir = $languageDir . '/verses';
    if (!is_dir($versesDir)) {
        mkdir($versesDir, 0755, true);
    }
    
    // Create chapters directory
    $chaptersDir = $languageDir . '/chapters';
    if (!is_dir($chaptersDir)) {
        mkdir($chaptersDir, 0755, true);
    }
    
    // Save verse-level link(s)
    // If verse range spans multiple verses, create separate links for each verse
    $verseStartNum = (int)$verseStart;
    $verseEndNum = (int)$verseEnd;
    
    for ($verse = $verseStartNum; $verse <= $verseEndNum; $verse++) {
        $individualVerseKey = "{$book}_{$chapter}:{$verse}";
        $verseLinkFile = $versesDir . '/' . sanitizeFilename($individualVerseKey) . '.json';
        $verseLinks = [];
        if (file_exists($verseLinkFile)) {
            $verseLinks = json_decode(file_get_contents($verseLinkFile), true) ?: [];
        }
        
        // Check if this meditation already exists for this verse
        $meditationExists = false;
        foreach ($verseLinks as $existingLink) {
            if ($existingLink['brand'] === $brand && 
                $existingLink['filename'] === $filename) {
                $meditationExists = true;
                break;
            }
        }
        
        // Only add if it doesn't already exist
        if (!$meditationExists) {
            $verseLinks[] = [
                'brand' => $brand,
                'title' => $title,
                'filename' => $filename
            ];
            
            file_put_contents($verseLinkFile, json_encode($verseLinks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
    
    // Save chapter-level index
    $chapterKey = "{$book}_{$chapter}"; // e.g., "58_3"
    $chapterLinkFile = $chaptersDir . '/' . sanitizeFilename($chapterKey) . '.json';
    $chapterLinks = [];
    if (file_exists($chapterLinkFile)) {
        $chapterLinks = json_decode(file_get_contents($chapterLinkFile), true) ?: [];
    }
    
    // Check if this verse already exists in chapter index
    $verseExists = false;
    foreach ($chapterLinks as &$link) {
        if ($link['verse'] === $keyVerse) {
            // Add this meditation to existing verse entry
            $link['meditations'][] = [
                'brand' => $brand,
                'title' => $title,
                'filename' => $filename
            ];
            $verseExists = true;
            break;
        }
    }
    
    if (!$verseExists) {
        $chapterLinks[] = [
            'verse' => $keyVerse,
            'meditations' => [
                [
                    'brand' => $brand,
                    'title' => $title,
                    'filename' => $filename
                ]
            ]
        ];
    }
    
    // Sort chapter links by verse number
    usort($chapterLinks, function($a, $b) {
        preg_match('/:(\d+)/', $a['verse'], $matchA);
        preg_match('/:(\d+)/', $b['verse'], $matchB);
        $verseA = isset($matchA[1]) ? (int)$matchA[1] : 0;
        $verseB = isset($matchB[1]) ? (int)$matchB[1] : 0;
        return $verseA - $verseB;
    });
    
    file_put_contents($chapterLinkFile, json_encode($chapterLinks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    return [
        'success' => true,
        'chapter' => $chapterKey
    ];
}

/**
 * Sanitize filename
 */
function sanitizeFilename($filename) {
    $filename = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $filename);
    return trim($filename);
}

/**
 * Recursively copy directory
 */
function recursiveCopy($src, $dst) {
    if (!is_dir($src)) {
        return false;
    }
    
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    
    $dir = opendir($src);
    if (!$dir) {
        return false;
    }
    
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        
        if (is_dir($srcPath)) {
            recursiveCopy($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }
    
    closedir($dir);
    return true;
}

/**
 * Recursively delete directory
 */
function recursiveDelete($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            recursiveDelete($path);
        } else {
            unlink($path);
        }
    }
    
    return rmdir($dir);
}
