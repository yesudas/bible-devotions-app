<?php
/**
 * Sitemap Generator
 * Generates individual sitemaps for each brand and a master sitemap index
 */

/**
 * Dynamically discover all brands and their languages
 */
function discoverBrands() {
    $brands = [];
    $rootDir = __DIR__;
    
    // Get all directories in the root
    $items = scandir($rootDir);
    
    foreach ($items as $item) {
        // Skip hidden files, parent dirs, and non-brand folders
        if ($item === '.' || $item === '..' || $item[0] === '.' || 
            !is_dir($rootDir . '/' . $item)) {
            continue;
        }
        
        // Check if it has a meditations folder (brand indicator)
        $meditationsDir = $rootDir . '/' . $item . '/meditations';
        if (!is_dir($meditationsDir)) {
            continue;
        }
        
        // Discover languages for this brand
        $languages = [];
        $langItems = scandir($meditationsDir);
        
        foreach ($langItems as $langItem) {
            if ($langItem === '.' || $langItem === '..' || $langItem[0] === '.') {
                continue;
            }
            
            $langPath = $meditationsDir . '/' . $langItem;
            
            // Check if it's a directory with all-meditations.json
            if (is_dir($langPath) && file_exists($langPath . '/all-meditations.json')) {
                $languages[] = $langItem;
            }
        }
        
        // Only add if we found at least one language
        if (!empty($languages)) {
            // Get brand name from folder (capitalize and format)
            $brandName = ucwords(str_replace(['-', '_'], ' ', $item));
            
            $brands[$item] = [
                'name' => $brandName,
                'languages' => $languages
            ];
        }
    }
    
    return $brands;
}

// Dynamically load all brands
$brands = discoverBrands();

// Base URL - Update this to your actual domain
$baseUrl = 'https://wordofgod.in/bible-devotions/';

// Today's date (hardcoded as requested)
$today = '2026-01-31';

/**
 * Generate sitemap for a single brand
 */
function generateBrandSitemap($brandFolder, $brandInfo, $baseUrl, $today) {
    $urls = [];
    
    // Add main brand homepage for each language
    foreach ($brandInfo['languages'] as $language) {
        $urls[] = [
            'loc' => $baseUrl . '/' . $brandFolder . '/?lang=' . urlencode($language),
            'lastmod' => $today,
            'changefreq' => 'yearly',
            'priority' => '1.0'
        ];
        
        // Add view all page
        $urls[] = [
            'loc' => $baseUrl . '/' . $brandFolder . '/?view=all&lang=' . urlencode($language),
            'lastmod' => $today,
            'changefreq' => 'yearly',
            'priority' => '0.8'
        ];
        
        // Get all meditations for this language
        $meditationsFile = __DIR__ . '/' . $brandFolder . '/meditations/' . $language . '/all-meditations.json';
        
        if (file_exists($meditationsFile)) {
            $meditations = json_decode(file_get_contents($meditationsFile), true);
            
            if ($meditations && is_array($meditations)) {
                foreach ($meditations as $meditation) {
                    // Skip scheduled meditations
                    if (isset($meditation['scheduled']) && $meditation['scheduled'] === true) {
                        continue;
                    }
                    
                    // Create URL-friendly title slug
                    $titleSlug = createSlug($meditation['title']);
                    
                    $urls[] = [
                        'loc' => $baseUrl . '/' . $brandFolder . '/?mode=latest&id=' . 
                                urlencode($meditation['uniqueid']) . '&lang=' . urlencode($language) . 
                                '&title=' . urlencode($titleSlug),
                        'lastmod' => $today,
                        'changefreq' => 'yearly',
                        'priority' => '0.7'
                    ];
                }
            }
        }
    }
    
    // Generate XML with X-Robots-Tag directive to slow down crawlers
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
    $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
    
    foreach ($urls as $url) {
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . "\n";
        $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . "\n";
        $xml .= '    <priority>' . $url['priority'] . '</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }
    
    $xml .= '</urlset>';
    
    return $xml;
}

/**
 * Create URL-friendly slug from title
 */
function createSlug($title) {
    // Basic slug creation (you can enhance this)
    $slug = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $title);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Generate master sitemap index
 */
function generateMasterSitemap($brands, $baseUrl, $today) {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    foreach ($brands as $brandFolder => $brandInfo) {
        $xml .= '  <sitemap>' . "\n";
        $xml .= '    <loc>' . $baseUrl . '/' . $brandFolder . '/sitemap.xml</loc>' . "\n";
        $xml .= '    <lastmod>' . $today . '</lastmod>' . "\n";
        $xml .= '  </sitemap>' . "\n";
    }
    
    $xml .= '</sitemapindex>';
    
    return $xml;
}

// Main execution
$generatedSitemaps = [];
$totalUrls = 0;
$startTime = microtime(true);

// Buffer output for HTML
ob_start();

// Generate individual brand sitemaps
$brandResults = [];
foreach ($brands as $brandFolder => $brandInfo) {
    $sitemapXml = generateBrandSitemap($brandFolder, $brandInfo, $baseUrl, $today);
    $sitemapPath = __DIR__ . '/' . $brandFolder . '/sitemap.xml';
    $sitemapUrl = $baseUrl . $brandFolder . '/sitemap.xml';
    
    $urlCount = 0;
    $success = false;
    
    if (file_put_contents($sitemapPath, $sitemapXml)) {
        $urlCount = substr_count($sitemapXml, '<url>');
        $totalUrls += $urlCount;
        $generatedSitemaps[] = $brandFolder;
        $success = true;
    }
    
    $brandResults[] = [
        'name' => $brandInfo['name'],
        'folder' => $brandFolder,
        'url' => $sitemapUrl,
        'urlCount' => $urlCount,
        'languages' => $brandInfo['languages'],
        'success' => $success
    ];
}

// Generate master sitemap
$masterSitemap = generateMasterSitemap($brands, $baseUrl, $today);
$masterPath = __DIR__ . '/sitemap.xml';
$masterUrl = rtrim($baseUrl, '/') . '/sitemap.xml';
$masterSuccess = file_put_contents($masterPath, $masterSitemap);

$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

ob_end_clean();

// Output HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitemap Generator - WordOfGod.in</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
        }
        
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 40px;
            background: #f8f9fa;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .icon {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        
        .stat-card .label {
            color: #6c757d;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .brands-section {
            padding: 40px;
        }
        
        .section-title {
            font-size: 1.8em;
            margin-bottom: 30px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .brand-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .brand-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
        }
        
        .brand-card.error {
            border-color: #dc3545;
            background: #fff5f5;
        }
        
        .brand-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .brand-name {
            font-size: 1.4em;
            font-weight: bold;
            color: #333;
        }
        
        .brand-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .brand-status.success {
            background: #d4edda;
            color: #155724;
        }
        
        .brand-status.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .brand-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-item .icon {
            font-size: 1.3em;
        }
        
        .info-item .content {
            flex: 1;
        }
        
        .info-item .label {
            font-size: 0.8em;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .info-item .value {
            font-weight: 600;
            color: #333;
        }
        
        .sitemap-link {
            color: #667eea;
            text-decoration: none;
            word-break: break-all;
            font-size: 0.9em;
        }
        
        .sitemap-link:hover {
            text-decoration: underline;
        }
        
        .languages {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .language-tag {
            background: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85em;
        }
        
        .master-sitemap {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 40px;
            margin: 0;
        }
        
        .master-sitemap h2 {
            font-size: 1.8em;
            margin-bottom: 20px;
        }
        
        .master-sitemap-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.2);
            padding: 15px 25px;
            border-radius: 10px;
            color: white;
            text-decoration: none;
            font-size: 1.1em;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .master-sitemap-link:hover {
            background: rgba(255,255,255,0.3);
            transform: translateX(5px);
        }
        
        .tips {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 30px 40px;
            margin: 0;
        }
        
        .tips h3 {
            color: #856404;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tips ul {
            list-style: none;
            padding-left: 0;
        }
        
        .tips li {
            padding: 8px 0;
            color: #856404;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tips li:before {
            content: "✓";
            color: #28a745;
            font-weight: bold;
            font-size: 1.2em;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }
            
            .brand-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .summary {
                padding: 20px;
            }
            
            .brands-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>
                <span>�️</span>
                <span>Sitemap Generator</span>
            </h1>
            <p>WordOfGod.in - Bible Devotions</p>
        </div>
        
        <!-- Summary Stats -->
        <div class="summary">
            <div class="stat-card">
                <div class="icon">📁</div>
                <div class="value"><?php echo count($generatedSitemaps); ?></div>
                <div class="label">Brands</div>
            </div>
            <div class="stat-card">
                <div class="icon">🔗</div>
                <div class="value"><?php echo number_format($totalUrls); ?></div>
                <div class="label">Total URLs</div>
            </div>
            <div class="stat-card">
                <div class="icon">⚡</div>
                <div class="value"><?php echo $executionTime; ?>s</div>
                <div class="label">Execution Time</div>
            </div>
            <div class="stat-card">
                <div class="icon">📅</div>
                <div class="value"><?php echo $today; ?></div>
                <div class="label">Last Updated</div>
            </div>
        </div>
        
        <!-- Brand Sitemaps -->
        <div class="brands-section">
            <h2 class="section-title">
                <span>📦</span>
                <span>Brand Sitemaps</span>
            </h2>
            
            <?php foreach ($brandResults as $brand): ?>
            <div class="brand-card <?php echo $brand['success'] ? '' : 'error'; ?>">
                <div class="brand-header">
                    <div class="brand-name"><?php echo htmlspecialchars($brand['name']); ?></div>
                    <div class="brand-status <?php echo $brand['success'] ? 'success' : 'error'; ?>">
                        <span><?php echo $brand['success'] ? '✓' : '✗'; ?></span>
                        <span><?php echo $brand['success'] ? 'Generated' : 'Failed'; ?></span>
                    </div>
                </div>
                
                <?php if ($brand['success']): ?>
                <div class="brand-info">
                    <div class="info-item">
                        <div class="icon">🔗</div>
                        <div class="content">
                            <div class="label">URLs</div>
                            <div class="value"><?php echo number_format($brand['urlCount']); ?></div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="icon">🌐</div>
                        <div class="content">
                            <div class="label">Languages</div>
                            <div class="languages">
                                <?php foreach ($brand['languages'] as $lang): ?>
                                    <span class="language-tag"><?php echo htmlspecialchars($lang); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 15px;">
                    <a href="<?php echo htmlspecialchars($brand['url']); ?>" target="_blank" class="sitemap-link">
                        🔗 <?php echo htmlspecialchars($brand['url']); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Master Sitemap -->
        <div class="master-sitemap">
            <h2>🗂️ Master Sitemap Index</h2>
            <p style="margin-bottom: 20px; opacity: 0.9;">
                Submit this URL to Google Search Console and other search engines:
            </p>
            <a href="<?php echo htmlspecialchars($masterUrl); ?>" target="_blank" class="master-sitemap-link">
                <span>🚀</span>
                <span><?php echo htmlspecialchars($masterUrl); ?></span>
            </a>
        </div>
        
        <!-- Tips -->
        <div class="tips">
            <h3>💡 Tips to Reduce Scraper Load</h3>
            <ul>
                <li>changefreq set to 'yearly' (done)</li>
                <li>Upload robots.txt with crawl-delay directives</li>
                <li>Monitor server logs for abusive scrapers</li>
                <li>Use rate limiting at server level (.htaccess)</li>
                <li>Implement PHP rate limiter for bulletproof protection</li>
            </ul>
        </div>
    </div>
</body>
</html>