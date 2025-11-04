<?php
/**
 * Meditation Index Generator
 * This script scans all meditation files across all apps and languages,
 * and generates all-meditations.json files for each language.
 * Only includes meditations that have a key_verse field.
 */

// Configuration
$devotionBrands = [
    '3-minute-meditation',
    'அனுதின-மன்னா',
    'faiths-check-book',
    'antantulla-appam',
    'சத்திய-வசனம்',
    'நாளுக்கொரு-நல்ல-பங்கு'
];

// Set headers for browser output
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meditation Index Generator</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        h2 {
            color: #667eea;
            margin-top: 30px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #ffc107;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .app-section {
            background: #f8f9fa;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .language-section {
            margin: 15px 0;
            padding-left: 20px;
        }
        .meditation-list {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📖 Meditation Index Generator</h1>
        <div class="info">
            <strong>Started:</strong> <?php echo date('Y-m-d H:i:s'); ?>
        </div>

<?php

$totalAppsProcessed = 0;
$totalLanguagesProcessed = 0;
$totalMeditations = 0;
$totalMedidationsWithKeyVerse = 0;
$totalMeditationsSkipped = 0;

// Process each devotion brand
foreach ($devotionBrands as $brand) {
    echo "<div class='app-section'>";
    echo "<h2>🔖 Processing: {$brand}</h2>";
    
    $meditationsPath = __DIR__ . "/{$brand}/meditations";
    
    if (!is_dir($meditationsPath)) {
        echo "<div class='error'>❌ Meditations folder not found: {$meditationsPath}</div>";
        echo "</div>";
        continue;
    }
    
    $totalAppsProcessed++;
    
    // Get all language folders
    $languages = array_filter(glob($meditationsPath . '/*'), 'is_dir');
    
    if (empty($languages)) {
        echo "<div class='warning'>⚠️ No language folders found in {$meditationsPath}</div>";
        echo "</div>";
        continue;
    }
    
    // Process each language
    foreach ($languages as $languagePath) {
        $language = basename($languagePath);
        echo "<div class='language-section'>";
        echo "<h3>🌐 Language: {$language}</h3>";
        
        $totalLanguagesProcessed++;
        
        // Get all JSON files in the language folder
        $jsonFiles = glob($languagePath . '/*.json');
        
        // Filter out all-meditations.json
        $jsonFiles = array_filter($jsonFiles, function($file) {
            return basename($file) !== 'all-meditations.json';
        });
        
        if (empty($jsonFiles)) {
            echo "<div class='warning'>⚠️ No meditation JSON files found in {$language}</div>";
            echo "</div>";
            continue;
        }
        
        $meditations = [];
        $skippedCount = 0;
        $includedCount = 0;
        
        // Process each meditation file
        foreach ($jsonFiles as $jsonFile) {
            $filename = basename($jsonFile);
            $content = file_get_contents($jsonFile);
            $meditation = json_decode($content, true);
            
            $totalMeditations++;
            
            if (!$meditation) {
                echo "<div class='error'>⚠️ Invalid JSON in file: {$filename}</div>";
                $skippedCount++;
                $totalMeditationsSkipped++;
                continue;
            }
            
            // Check if key_verse exists
            if (!isset($meditation['key_verse']) || empty($meditation['key_verse'])) {
                $skippedCount++;
                $totalMeditationsSkipped++;
                continue;
            }
            
            $totalMedidationsWithKeyVerse++;
            $includedCount++;
            
            // Extract required fields
            $meditationEntry = [
                'uniqueid' => $meditation['uniqueid'] ?? '',
                'filename' => $filename,
                'title' => $meditation['title'] ?? '',
                'date' => $meditation['date'] ?? '',
                'key_verse' => $meditation['key_verse'] ?? ''
            ];
            
            $meditations[] = $meditationEntry;
        }
        
        // Sort meditations by date
        usort($meditations, function($a, $b) {
            return strcmp($a['date'], $b['date']);
        });
        
        // Write all-meditations.json file
        $outputFile = $languagePath . '/all-meditations.json';
        $jsonContent = json_encode($meditations, JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($outputFile, $jsonContent)) {
            echo "<div class='success'>✅ Successfully created: <code>all-meditations.json</code></div>";
            echo "<div class='meditation-list'>";
            echo "📊 <strong>{$includedCount}</strong> meditations included (with key_verse)<br>";
            if ($skippedCount > 0) {
                echo "⏭️ <strong>{$skippedCount}</strong> meditations skipped (no key_verse)<br>";
            }
            echo "📝 Total files processed: <strong>" . count($jsonFiles) . "</strong>";
            echo "</div>";
        } else {
            echo "<div class='error'>❌ Failed to write: {$outputFile}</div>";
        }
        
        echo "</div>"; // End language-section
    }
    
    echo "</div>"; // End app-section
}

?>

        <h2>📈 Summary Statistics</h2>
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalAppsProcessed; ?></div>
                <div class="stat-label">Apps Processed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalLanguagesProcessed; ?></div>
                <div class="stat-label">Languages Processed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalMedidationsWithKeyVerse; ?></div>
                <div class="stat-label">Meditations Included</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalMeditationsSkipped; ?></div>
                <div class="stat-label">Meditations Skipped</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalMeditations; ?></div>
                <div class="stat-label">Total Files Scanned</div>
            </div>
        </div>

        <div class="success">
            <strong>✅ Process Completed!</strong><br>
            Finished at: <?php echo date('Y-m-d H:i:s'); ?>
        </div>

        <div class="info">
            <strong>ℹ️ Note:</strong> Only meditations with a <code>key_verse</code> field have been included in the generated <code>all-meditations.json</code> files.
        </div>
    </div>
</body>
</html>
