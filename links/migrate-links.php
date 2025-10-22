<?php
/**
 * Migration Script: Refactor Links to New Structure
 * 
 * This script migrates existing link files from:
 *   /links/[verse].json
 * 
 * To new structure:
 *   /links/[language]/verses/[verse].json (without language attribute)
 *   /links/[language]/chapters/[chapter].json (new chapter-level index)
 * 
 * Run this script once: php migrate-links.php
 */

$links_dir = __DIR__;
$migrated_count = 0;
$error_count = 0;
$backup_dir = $links_dir . '/backup_' . date('Ymd_His');

echo "==================================================\n";
echo "Link Migration Script\n";
echo "==================================================\n\n";

// Create backup directory
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0755, true);
    echo "✓ Created backup directory: $backup_dir\n\n";
}

// Get all JSON files in the links directory (excluding subdirectories)
$files = glob($links_dir . '/*.json');

if (empty($files)) {
    echo "No link files found to migrate.\n";
    exit(0);
}

echo "Found " . count($files) . " files to migrate.\n\n";

foreach ($files as $file) {
    $filename = basename($file);
    $verse_ref = basename($file, '.json');
    
    echo "Processing: $filename\n";
    
    // Parse verse reference to get chapter
    $parts = explode('_', $verse_ref);
    if (count($parts) !== 2) {
        echo "  ⚠ Skipping: Invalid verse reference format\n\n";
        $error_count++;
        continue;
    }
    
    $bookNo = $parts[0];
    $chapterVerse = explode(':', $parts[1]);
    if (count($chapterVerse) !== 2) {
        echo "  ⚠ Skipping: Invalid chapter:verse format\n\n";
        $error_count++;
        continue;
    }
    
    $chapter = $chapterVerse[0];
    $chapterRef = $bookNo . '_' . $chapter;
    
    // Read existing file
    $content = file_get_contents($file);
    $links = json_decode($content, true);
    
    if (!is_array($links)) {
        echo "  ⚠ Skipping: Invalid JSON format\n\n";
        $error_count++;
        continue;
    }
    
    // Backup original file
    copy($file, $backup_dir . '/' . $filename);
    
    // Group links by language
    $links_by_language = [];
    foreach ($links as $link) {
        $language = $link['language'] ?? 'Unknown';
        
        // Extract language from file path if not set
        if ($language === 'Unknown' && isset($link['file'])) {
            if (preg_match('/\/meditations\/([^\/]+)\//', $link['file'], $matches)) {
                $language = $matches[1];
            }
        }
        
        if (!isset($links_by_language[$language])) {
            $links_by_language[$language] = [];
        }
        
        $links_by_language[$language][] = $link;
    }
    
    // Create new structure for each language
    foreach ($links_by_language as $language => $language_links) {
        $lang_dir = $links_dir . '/' . $language;
        $verses_dir = $lang_dir . '/verses';
        $chapters_dir = $lang_dir . '/chapters';
        
        // Create directories
        if (!file_exists($verses_dir)) {
            mkdir($verses_dir, 0755, true);
        }
        if (!file_exists($chapters_dir)) {
            mkdir($chapters_dir, 0755, true);
        }
        
        // Create verse-level file (without language attribute)
        $verse_links = [];
        $chapter_links = [];
        
        foreach ($language_links as $link) {
            // Verse-level link (remove language attribute)
            $verse_link = [
                'brand' => $link['brand'],
                'title' => $link['title'],
                'file' => $link['file']
            ];
            $verse_links[] = $verse_link;
            
            // Chapter-level link
            $chapter_link = [
                'brand' => $link['brand'],
                'title' => $link['title'],
                'verse' => $verse_ref,
                'file' => $link['file']
            ];
            
            // Load existing chapter file
            $chapter_file = $chapters_dir . '/' . $chapterRef . '.json';
            $existing_chapter_links = [];
            if (file_exists($chapter_file)) {
                $existing = json_decode(file_get_contents($chapter_file), true);
                if (is_array($existing)) {
                    $existing_chapter_links = $existing;
                }
            }
            
            // Check if link already exists
            $exists = false;
            foreach ($existing_chapter_links as $existing_link) {
                if ($existing_link['file'] === $chapter_link['file']) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $existing_chapter_links[] = $chapter_link;
            }
            
            // Sort by verse reference
            usort($existing_chapter_links, function($a, $b) {
                return strcmp($a['verse'], $b['verse']);
            });
            
            // Save chapter file
            file_put_contents($chapter_file, json_encode($existing_chapter_links, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        
        // Save verse-level file
        $verse_file = $verses_dir . '/' . $verse_ref . '.json';
        file_put_contents($verse_file, json_encode($verse_links, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        echo "  ✓ Migrated to $language/verses/$filename\n";
        echo "  ✓ Updated $language/chapters/$chapterRef.json\n";
    }
    
    // Delete old file
    unlink($file);
    echo "  ✓ Removed old file\n";
    
    $migrated_count++;
    echo "\n";
}

echo "==================================================\n";
echo "Migration Complete!\n";
echo "==================================================\n";
echo "Successfully migrated: $migrated_count files\n";
echo "Errors encountered: $error_count files\n";
echo "Backup location: $backup_dir\n";
echo "\n";
echo "New structure:\n";
echo "  /links/[language]/verses/[verse].json - Verse-level links\n";
echo "  /links/[language]/chapters/[chapter].json - Chapter-level index\n";
echo "\n";
echo "You can safely delete the backup directory after verifying.\n";
echo "==================================================\n";
