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
 * php regenerate-links.php
 */

// Configuration
$devotionBrands = [
    '3-minute-meditation',
    'அனுதின-மன்னா',
    'antantulla-appam'
];

$languages = ['தமிழ்', 'English'];

// Statistics tracking
$stats = [
    'total_meditations' => 0,
    'total_verses' => 0,
    'total_chapters' => 0,
    'by_language' => [],
    'by_brand' => [],
    'errors' => []
];

echo "=== Link Index Regeneration Script ===\n\n";

// Step 1: Backup current links folder
echo "Step 1: Backing up current links folder...\n";
$linksDir = __DIR__;
$backupDir = dirname($linksDir) . '/links_backup_' . date('Y-m-d_H-i-s');

if (is_dir($linksDir)) {
    if (!recursiveCopy($linksDir, $backupDir)) {
        die("ERROR: Failed to create backup. Aborting.\n");
    }
    echo "✓ Backup created at: $backupDir\n\n";
} else {
    echo "ℹ Links folder doesn't exist yet. Skipping backup.\n\n";
}

// Step 2: Remove current links folder (except this script)
echo "Step 2: Clearing current links folder...\n";
if (is_dir($linksDir)) {
    foreach (scandir($linksDir) as $item) {
        if ($item === '.' || $item === '..' || $item === 'regenerate-links.php' || 
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
    echo "✓ Links folder cleared\n\n";
}

// Step 3: Scan and generate links
echo "Step 3: Scanning meditations and generating links...\n";

foreach ($devotionBrands as $brand) {
    echo "\n--- Processing Brand: $brand ---\n";
    
    $brandPath = dirname($linksDir) . '/' . $brand;
    
    if (!is_dir($brandPath)) {
        echo "⚠ Warning: Brand folder not found at $brandPath\n";
        continue;
    }
    
    $meditationsPath = $brandPath . '/meditations';
    
    if (!is_dir($meditationsPath)) {
        echo "⚠ Warning: No meditations folder found at $meditationsPath\n";
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
        
        echo "  Language: $language\n";
        
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
        
        echo "    ✓ Processed $meditationCount meditations\n";
        echo "    ✓ Created $verseCount verse links\n";
        
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
echo "\n\n=== SUMMARY ===\n";
echo "\nOverall Statistics:\n";
echo "  Total Meditations: {$stats['total_meditations']}\n";
echo "  Total Verse Links: {$stats['total_verses']}\n";
echo "  Total Chapter Indexes: {$stats['total_chapters']}\n";

echo "\n\nBy Language:\n";
foreach ($stats['by_language'] as $language => $data) {
    echo "  $language:\n";
    echo "    Meditations: {$data['meditations']}\n";
    echo "    Verse Links: {$data['verses']}\n";
    echo "    Chapter Indexes: {$data['chapters']}\n";
}

echo "\n\nBy Brand:\n";
foreach ($stats['by_brand'] as $brand => $data) {
    echo "  $brand:\n";
    echo "    Meditations: {$data['meditations']}\n";
    echo "    Verse Links: {$data['verses']}\n";
}

if (!empty($stats['errors'])) {
    echo "\n\nErrors (" . count($stats['errors']) . "):\n";
    foreach ($stats['errors'] as $error) {
        echo "  ⚠ $error\n";
    }
}

// Step 5: Delete backup if successful
if (empty($stats['errors']) || count($stats['errors']) < 10) {
    echo "\n\nStep 4: Cleaning up backup...\n";
    if (is_dir($backupDir)) {
        recursiveDelete($backupDir);
        echo "✓ Backup deleted successfully\n";
    }
} else {
    echo "\n\n⚠ WARNING: Too many errors encountered. Backup retained at: $backupDir\n";
    echo "Please review the errors and restore from backup if needed.\n";
}

echo "\n=== Link Regeneration Complete ===\n";

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
    
    // Parse verse reference (e.g., "John 3:16" or "John 3:16-17")
    $versePattern = '/^([0-9]?\s?[A-Za-z]+)\s+(\d+):(\d+)(?:-(\d+))?$/';
    if (!preg_match($versePattern, trim($keyVerse), $matches)) {
        return ['success' => false, 'error' => "Invalid verse format: $keyVerse in $brand/$language/$filename"];
    }
    
    $book = trim($matches[1]);
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
    
    // Save verse-level link
    $verseLinkFile = $versesDir . '/' . sanitizeFilename($keyVerse) . '.json';
    $verseLinks = [];
    if (file_exists($verseLinkFile)) {
        $verseLinks = json_decode(file_get_contents($verseLinkFile), true) ?: [];
    }
    
    $verseLinks[] = [
        'brand' => $brand,
        'title' => $title,
        'filename' => $filename
    ];
    
    file_put_contents($verseLinkFile, json_encode($verseLinks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // Save chapter-level index
    $chapterKey = "$book $chapter";
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
