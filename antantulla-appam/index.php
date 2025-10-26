<?php

// Set session timeout to 5 minutes (300 seconds)
ini_set('session.gc_maxlifetime', 300);
session_set_cookie_params(300);

session_start();

// Add this reset logic
if (isset($_GET['reset'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

include 'counter.php';
include '../detect-app.php';

$version = "2025.10.4";

$languages = ["தமிழ்", "English", "తెలుగు", "ಕನ್ನಡ", "മലയാളം"];

// Language selection logic
$selectedLanguage = $_GET['lang'] ?? $_SESSION['selected_language'] ?? $languages[0];

// Validate selected language
if (!in_array($selectedLanguage, $languages)) {
    $selectedLanguage = $languages[0];
}

// Check if language has changed and reset pagination if needed
if (isset($_SESSION['selected_language']) && $_SESSION['selected_language'] !== $selectedLanguage) {
    // Language changed - reset pagination
    unset($_SESSION['current_meditation_index']);
    unset($_SESSION['random_sequence']);
    unset($_SESSION['random_index']);
}

// Store in session for persistence
$_SESSION['selected_language'] = $selectedLanguage;

// Load app name from translations.js
function getAppName($language) {
    $translationsFile = __DIR__ . '/js/translations.js';
    if (file_exists($translationsFile)) {
        $content = file_get_contents($translationsFile);
        // Extract the translations object
        if (preg_match('/const\s+labelTranslations\s*=\s*({[\s\S]*?});/m', $content, $matches)) {
            // Convert JS object to JSON-parseable format
            $jsObject = $matches[1];
            
            // More careful conversion: replace single quotes around keys and values, but not apostrophes in content
            // First, protect apostrophes inside strings by temporarily replacing them
            $jsObject = preg_replace("/(\w)'(\w)/", "$1<<<APOSTROPHE>>>$2", $jsObject);
            
            // Now replace single quotes with double quotes
            $jsObject = str_replace("'", '"', $jsObject);
            
            // Restore apostrophes
            $jsObject = str_replace('<<<APOSTROPHE>>>', "'", $jsObject);
            
            // Remove trailing commas before closing braces/brackets
            $jsObject = preg_replace('/,(\s*[}\]])/m', '$1', $jsObject);
            
            $translations = json_decode($jsObject, true);
            if ($translations && isset($translations[$language]['app_name'])) {
                return $translations[$language]['app_name'];
            }
        }
    }
    return "Antantulla Appam"; // Default fallback
}

// Create SEO-friendly URL slug from title
function createSlug($title) {
    // Replace spaces with hyphens
    $slug = str_replace(' ', '-', $title);
    
    // Remove only punctuation and special symbols, keep all letters (including Tamil) and numbers
    // This preserves Tamil vowel signs and other diacritics
    $slug = preg_replace('/[^\p{L}\p{M}\p{N}\-]/u', '', $slug);
    
    // Replace multiple consecutive hyphens with single hyphen
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Remove leading/trailing hyphens
    $slug = trim($slug, '-');
    
    // Limit length to 100 characters
    if (mb_strlen($slug) > 100) {
        $slug = mb_substr($slug, 0, 100);
        $slug = preg_replace('/-[^-]*$/', '', $slug); // Remove partial word at end
    }
    
    return $slug;
}

$appName = getAppName($selectedLanguage);

// Initialize or get current meditation number
$mode = $_GET['mode'] ?? 'random';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Check and activate scheduled meditations for today
function checkAndActivateScheduledMeditations($language) {
    $file = __DIR__ . '/meditations/' . $language . '/all-meditations.json';
    if (!file_exists($file)) {
        return false;
    }
    
    $all_meditations = json_decode(file_get_contents($file), true);
    if (!$all_meditations) {
        return false;
    }
    
    $today = date('Y-m-d');
    $updated = false;
    
    // Check each meditation for scheduled items with today's date
    foreach ($all_meditations as $index => &$meditation) {
        if (isset($meditation['scheduled']) && $meditation['scheduled'] === true && $meditation['date'] === $today) {
            // Remove scheduled attribute from all-meditations.json
            unset($meditation['scheduled']);
            
            // Also update the individual meditation file
            $meditation_file = __DIR__ . '/meditations/' . $language . '/' . $meditation['filename'];
            if (file_exists($meditation_file)) {
                $meditation_data = json_decode(file_get_contents($meditation_file), true);
                if ($meditation_data && isset($meditation_data['scheduled'])) {
                    unset($meditation_data['scheduled']);
                    file_put_contents($meditation_file, json_encode($meditation_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }
            
            $updated = true;
        }
    }
    
    // Save updated all-meditations.json if any changes were made
    if ($updated) {
        file_put_contents($file, json_encode($all_meditations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    return $updated;
}

// Get all meditation files (excluding scheduled ones)
function getAllMeditations($language) {
    $file = __DIR__ . '/meditations/' . $language . '/all-meditations.json';
    if (file_exists($file)) {
        $all_meditations = json_decode(file_get_contents($file), true) ?: [];
        
        // Filter out scheduled meditations - only show published ones to users
        $published_meditations = array_filter($all_meditations, function($meditation) {
            return !isset($meditation['scheduled']) || $meditation['scheduled'] !== true;
        });
        
        // Re-index the array to maintain proper sequential indices
        return array_values($published_meditations);
    }
    return [];
}

// Get total number of meditations
function getTotalMeditations($language) {
    return count(getAllMeditations($language));
}

// Get current meditation based on mode
function getCurrentMeditationIndex($mode, $action, $index, $language) {
    $total = getTotalMeditations($language);
    
    // If no meditations exist, return null
    if ($total === 0) {
        return null;
    }
    
    if ($mode === 'random') {
        if (!isset($_SESSION['random_sequence']) || !isset($_SESSION['random_index'])) {
            $_SESSION['random_sequence'] = range(0, $total - 1);
            shuffle($_SESSION['random_sequence']);
            $_SESSION['random_index'] = 0;
        }
        
        // Validate that random sequence is valid for current language
        if (count($_SESSION['random_sequence']) !== $total) {
            $_SESSION['random_sequence'] = range(0, $total - 1);
            shuffle($_SESSION['random_sequence']);
            $_SESSION['random_index'] = 0;
        }
        
        // If a specific index is requested, find its position in the random sequence
        if ($index !== null) {
            $targetIndex = (int)$index;
            // Validate the target index exists in current language
            if ($targetIndex >= 0 && $targetIndex < $total) {
                $position = array_search($targetIndex, $_SESSION['random_sequence']);
                if ($position !== false) {
                    $_SESSION['random_index'] = $position;
                    return $targetIndex;
                }
            } else {
                // Index out of bounds, reset to first
                $_SESSION['random_index'] = 0;
            }
        }
        
        // Handle legacy action parameters (for backward compatibility)
        if ($action === 'next') {
            $_SESSION['random_index'] = ($_SESSION['random_index'] + 1) % $total;
        } elseif ($action === 'prev') {
            $_SESSION['random_index'] = ($_SESSION['random_index'] - 1 + $total) % $total;
        }
        
        return $_SESSION['random_sequence'][$_SESSION['random_index']];
    } else {
        // Latest mode
        if ($index !== null) {
            $targetIndex = (int)$index;
            // Validate the index is within range
            if ($targetIndex >= 0 && $targetIndex < $total) {
                $_SESSION['current_meditation_index'] = $targetIndex;
                return $targetIndex;
            } else {
                // Index out of bounds, reset to first
                $_SESSION['current_meditation_index'] = 0;
                return 0;
            }
        }
        
        if (!isset($_SESSION['current_meditation_index'])) {
            $_SESSION['current_meditation_index'] = 0; // Start with latest (first in array)
        } else {
            // Validate current index is within bounds for current language
            if ($_SESSION['current_meditation_index'] >= $total) {
                $_SESSION['current_meditation_index'] = 0;
            }
        }
        
        // Handle legacy action parameters (for backward compatibility)
        if ($action === 'next') {
            $_SESSION['current_meditation_index'] = min($_SESSION['current_meditation_index'] + 1, $total - 1);
        } elseif ($action === 'prev') {
            $_SESSION['current_meditation_index'] = max($_SESSION['current_meditation_index'] - 1, 0);
        }
        
        return $_SESSION['current_meditation_index'];
    }
}

// Load meditation data by filename
function loadMeditationByFilename($filename, $language) {
    $file = __DIR__ . "/meditations/{$language}/{$filename}";
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}

// Check and activate scheduled meditations for today
checkAndActivateScheduledMeditations($selectedLanguage);

// Initialize or get current meditation number
$mode = $_GET['mode'] ?? 'random';
$action = $_GET['action'] ?? '';
$index = $_GET['index'] ?? null;

// Get all meditations and current meditation
$allMeditations = getAllMeditations($selectedLanguage);
$total = count($allMeditations);
$currentIndex = getCurrentMeditationIndex($mode, $action, $index, $selectedLanguage);
$meditation = null;

if ($currentIndex !== null && isset($allMeditations[$currentIndex])) {
    $meditation = loadMeditationByFilename($allMeditations[$currentIndex]['filename'], $selectedLanguage);
}

// If viewing all meditations
$viewAll = ($_GET['view'] ?? '') === 'all';

// Redirect to add query params and title slug if missing (for proper sharing and SEO)
if (!$viewAll && $meditation && $currentIndex !== null) {
    $hasQueryParams = isset($_GET['mode']) && isset($_GET['index']) && isset($_GET['lang']);
    $titleSlug = createSlug($meditation['title']);
    $currentSlug = $_GET['title'] ?? '';
    
    // Redirect if missing params or slug doesn't match
    if (!$hasQueryParams || $currentSlug !== $titleSlug) {
        $newUrl = "?mode=" . urlencode($mode) . 
                  "&index=" . $currentIndex . 
                  "&lang=" . urlencode($selectedLanguage) . 
                  "&title=" . urlencode($titleSlug);
        header("Location: " . $newUrl, true, 302);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $meditation ? htmlspecialchars($meditation['title']) . ' - ' : ''; ?><?php echo htmlspecialchars($appName); ?> - WordOfGod.in</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $meditation ? htmlspecialchars($meditation['title']) . ' - ' : ''; ?><?php echo htmlspecialchars($appName); ?> - Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections. Daily devotions for spiritual growth.">
    <meta name="keywords" content="<?php echo $meditation ? htmlspecialchars($meditation['title']) . ', ' : ''; ?><?php echo htmlspecialchars($appName); ?>, meditation, christian, bible, devotion, prayer, spiritual, faith, daily, WordOfGod.in, WordOfGod">
    <meta name="author" content="Word of God Team">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo $meditation ? htmlspecialchars($meditation['title']) . ' - ' : ''; ?><?php echo htmlspecialchars($appName); ?> - Daily Christian Devotions - WordOfGod.in">
    <meta property="og:description" content="<?php echo $meditation ? htmlspecialchars($meditation['title']) . ' - ' : ''; ?><?php echo htmlspecialchars($appName); ?> - Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections - WordOfGod.in">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $meditation ? htmlspecialchars($meditation['title']) . ' - ' : ''; ?><?php echo htmlspecialchars($appName); ?> - Daily Christian Devotions - WordOfGod.in">
    <meta name="twitter:description" content="<?php echo $meditation ? htmlspecialchars($meditation['title']) . ' - ' : ''; ?><?php echo htmlspecialchars($appName); ?> - Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections - WordOfGod.in">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css?v=<?php echo $version; ?>" rel="stylesheet">
    <link href="css/style-teal.css?v=<?php echo $version; ?>" rel="stylesheet">

    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json?v=<?php echo $version; ?>">
    <meta name="theme-color" content="#0f766e">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?php echo htmlspecialchars($appName); ?>">

    <!-- Google Analytics -->
    <?php include '../google-analytics.php'; ?>

</head>

<body>
    <!-- Site Header - Common for all pages -->
    <div class="site-header">
        <div class="container">
            <h1><i class="fas fa-book-open"></i> <?php echo htmlspecialchars($appName); ?></h1>
            <p class="site-tagline">Daily Christian Devotions for Spiritual Growth</p>
            
            <!-- Mode Selector & Zoom Controls -->
            <div class="header-controls">
                
                <!-- Hamburger Menu -->
                <?php include '../menu-links.php'; ?>
                
                <!-- Language Selector -->
                <?php if (count($languages) > 1): ?>
                <div class="language-selector">
                    <button class="lang-btn dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Select Language">
                        <i class="fas fa-language"></i>
                        <span class="lang-label d-none d-sm-inline"><?php echo htmlspecialchars($selectedLanguage); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                        <?php foreach ($languages as $lang): ?>
                            <li>
                                <a class="dropdown-item <?php echo $lang === $selectedLanguage ? 'active' : ''; ?>" 
                                   href="?lang=<?php echo urlencode($lang); ?><?php echo isset($_GET['mode']) ? '&mode=' . htmlspecialchars($_GET['mode']) : ''; ?><?php echo isset($_GET['view']) ? '&view=' . htmlspecialchars($_GET['view']) : ''; ?><?php echo isset($_GET['index']) ? '&index=' . htmlspecialchars($_GET['index']) : ''; ?>">
                                    <?php if ($lang === $selectedLanguage): ?>
                                        <i class="fas fa-check me-2"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($lang); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Mode Selector -->
                <?php if (!$viewAll): ?>
                <div class="mode-selector">
                    <a href="?mode=latest&lang=<?php echo urlencode($selectedLanguage); ?>" class="mode-btn <?php echo $mode === 'latest' ? 'active' : ''; ?>" title="Latest Mode">
                        <i class="fas fa-clock"></i> 
                    </a>
                    <a href="?mode=random&lang=<?php echo urlencode($selectedLanguage); ?>" class="mode-btn <?php echo $mode === 'random' ? 'active' : ''; ?>" title="Random Mode">
                        <i class="fas fa-random"></i> 
                    </a>
                    <a href="?view=all&mode=<?php echo $mode; ?>&lang=<?php echo urlencode($selectedLanguage); ?>" class="mode-btn" title="View All Meditations">
                        <i class="fas fa-th-list"></i> 
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- Zoom Controls -->
                <div class="zoom-controls">
                    <button id="zoomOutBtn" class="zoom-btn" title="Zoom Out">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <button id="resetZoomBtn" class="zoom-btn" title="Reset Zoom">
                        <i class="fas fa-redo"></i>
                    </button>
                    <button id="zoomInBtn" class="zoom-btn" title="Zoom In">
                        <i class="fas fa-search-plus"></i>
                    </button>
                    <!-- PWA Install Button -->
                    <?php if ($installAsAppButton): ?>
                    <div>
                        <button id="installAppBtn" class="zoom-btn" title="Install as App">
                            <i class="bi bi-save"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                
            </div>

        </div>
    </div>

    <div class="container">
        <div class="devotion-container">
            <?php if ($viewAll): ?>
                <!-- View All Meditations -->
                <div class="devotion-header">
                    <h2><i class="fas fa-list"></i> All Meditations</h2>
                    <p class="devotion-date">Browse all available meditations</p>
                </div>
                <div class="devotion-content">
                    <?php if (empty($allMeditations)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>No meditations available yet.
                        </div>
                    <?php else: ?>
                        <?php foreach ($allMeditations as $idx => $med): ?>
                            <div class="meditation-item">
                                <div class="meditation-number">#<?php echo $idx + 1; ?></div>
                                <div class="meditation-info">
                                    <h5><?php echo htmlspecialchars($med['title']); ?></h5>
                                    <p class="text-muted mb-0"><?php echo htmlspecialchars($med['date']); ?></p>
                                </div>
                                <a href="?mode=<?php echo $mode; ?>&index=<?php echo $idx; ?>&lang=<?php echo urlencode($selectedLanguage); ?>&title=<?php echo urlencode(createSlug($med['title'])); ?>" class="btn btn-sm nav-btn">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="navigation">
                    <a href="?mode=<?php echo $mode; ?>&lang=<?php echo urlencode($selectedLanguage); ?>" class="nav-btn">
                        <i class="fas fa-arrow-left"></i> Back to Reading
                    </a>
                    <span class="devotion-counter">
                        Total: <?php echo count($allMeditations); ?> Meditations
                    </span>
                    <span></span>
                </div>
            <?php else: ?>
                <!-- Single Meditation View -->
                <?php if ($meditation): ?>
                    <div class="devotion-header">
                        <h2><?php echo htmlspecialchars($meditation['title']); ?></h2>
                    </div>
                    <div class="devotion-content fade-in">
                        <?php if (!empty($meditation['memory_verse']['text'])): ?>
                        <div class="section">
                            <h2><i class="fas fa-book"></i> <?php echo $meditation['memory_verse']['label'] ?? 'Memory Verse'; ?></h2>
                            <span class="verse-reference"><?php echo htmlspecialchars($meditation['memory_verse']['text']); ?></span>
                        </div>
                        <?php endif; ?>
                    
                        <div class="section">
                            <h2><i class="fas fa-heart"></i> <?php echo $meditation['devotion']['label'] ?? 'Insight & Reflection'; ?></h2>
                            <p><?php echo nl2br(htmlspecialchars($meditation['devotion']['text'])); ?></p>
                        </div>
                    
                        <?php if (!empty($meditation['quote']['text'])): ?>
                        <div class="section">
                            <h2><i class="fas fa-quote-right"></i> <?php echo $meditation['quote']['label'] ?? "Today's Quote"; ?></h2>
                            <p><?php echo htmlspecialchars($meditation['quote']['text']); ?></p>
                        </div>
                        <?php endif; ?>
                    
                        <?php if (!empty($meditation['recommended_book']) && !empty($meditation['recommended_book']['title'])): ?>
                        <div class="section">
                            <h2><i class="fas fa-book-reader"></i> <?php echo $meditation['recommended_book']['label'] ?? 'Recommended Book'; ?></h2>
                            <h6 class="fw-bold text-dark"><?php echo htmlspecialchars($meditation['recommended_book']['title']); ?></h6>
                            <p class="text-muted mb-3">by <?php echo htmlspecialchars($meditation['recommended_book']['author']); ?></p>
                            <blockquote class="border-start border-3 border-success ps-3 mb-3">
                                <p class="mb-1 fst-italic">"<?php echo htmlspecialchars($meditation['recommended_book']['quote']); ?>"</p>
                                <small class="text-muted">— <?php echo htmlspecialchars($meditation['recommended_book']['page']); ?></small>
                            </blockquote>
                            <?php if (!empty($meditation['recommended_book']['link'])): ?>
                                <a href="<?php echo htmlspecialchars($meditation['recommended_book']['link']); ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-external-link-alt me-1"></i>Read More
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    
                        <?php if (!empty($meditation['song']) && !empty($meditation['song']['text'])): ?>
                        <div class="section">
                            <h2><i class="fas fa-music"></i> <?php echo $meditation['song']['label'] ?? 'Song'; ?></h2>
                            <p><?php echo nl2br(htmlspecialchars($meditation['song']['text'])); ?></p>
                        </div>
                        <?php endif; ?>
                    
                        <?php if (!empty($meditation['prayer']) && !empty($meditation['prayer']['text'])): ?>
                        <div class="section">
                            <h2><i class="fas fa-praying-hands"></i> <?php echo $meditation['prayer']['label'] ?? 'Prayer'; ?></h2>
                            <p><?php echo nl2br(htmlspecialchars($meditation['prayer']['text'])); ?></p>
                        </div>
                        <?php endif; ?>
                    
                        <?php if (!empty($meditation['conclusion']['text'])): ?>
                        <div class="section">
                            <h2><i class="fas fa-star"></i> <?php echo $meditation['conclusion']['label'] ?? 'A Word to You'; ?></h2>
                            <?php foreach ($meditation['conclusion']['text'] as $word): ?>
                                <p><?php echo htmlspecialchars($word); ?></p>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    
                        <?php if (!empty($meditation['author']['author'])): ?>
                        <div class="section">
                            <h2><i class="fas fa-user"></i> <?php echo $meditation['author']['label'] ?? 'Author'; ?></h2>
                            <p class="mb-2"><strong><?php echo htmlspecialchars($meditation['author']['author']); ?></strong></p>
                            <?php if (!empty($meditation['author']['whatsapp'])): ?>
                                <p class="mb-1">
                                    <i class="fab fa-whatsapp me-2 text-success"></i>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $meditation['author']['whatsapp']); ?>?text=antantulla-appam" 
                                       target="_blank" class="text-decoration-none">
                                        <?php echo htmlspecialchars($meditation['author']['whatsapp']); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($meditation['author']['email'])): ?>
                                <p class="mb-0">
                                    <i class="fas fa-envelope me-2 text-primary"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($meditation['author']['email']); ?>" 
                                       class="text-decoration-none">
                                        <?php echo htmlspecialchars($meditation['author']['email']); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="navigation">
                        <?php 
                        if ($currentIndex !== null) {
                            $prevIndex = ($mode === 'random') ? 
                                ($_SESSION['random_index'] > 0 ? $_SESSION['random_sequence'][$_SESSION['random_index'] - 1] : $_SESSION['random_sequence'][$total - 1]) :
                                max($currentIndex - 1, 0);
                            $nextIndex = ($mode === 'random') ? 
                                ($_SESSION['random_index'] < $total - 1 ? $_SESSION['random_sequence'][$_SESSION['random_index'] + 1] : $_SESSION['random_sequence'][0]) :
                                min($currentIndex + 1, $total - 1);
                            
                            // Get title slugs for prev/next
                            $prevMed = isset($allMeditations[$prevIndex]) ? loadMeditationByFilename($allMeditations[$prevIndex]['filename'], $selectedLanguage) : null;
                            $nextMed = isset($allMeditations[$nextIndex]) ? loadMeditationByFilename($allMeditations[$nextIndex]['filename'], $selectedLanguage) : null;
                            $prevSlug = $prevMed ? createSlug($prevMed['title']) : '';
                            $nextSlug = $nextMed ? createSlug($nextMed['title']) : '';
                        ?>
                        <a href="?mode=<?php echo $mode; ?>&index=<?php echo $prevIndex; ?>&lang=<?php echo urlencode($selectedLanguage); ?>&title=<?php echo urlencode($prevSlug); ?>" 
                           class="nav-btn <?php echo ($mode === 'latest' && $currentIndex <= 0) ? 'disabled' : ''; ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                        
                        <div class="navigation-center">
                            <span class="devotion-counter">
                                <?php echo ($currentIndex + 1); ?> of <?php echo $total; ?>
                            </span>
                            <a href="?view=all&mode=<?php echo $mode; ?>&lang=<?php echo urlencode($selectedLanguage); ?>" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-th-list"></i> View All
                            </a>
                        </div>
                        
                        <a href="?mode=<?php echo $mode; ?>&index=<?php echo $nextIndex; ?>&lang=<?php echo urlencode($selectedLanguage); ?>&title=<?php echo urlencode($nextSlug); ?>" 
                           class="nav-btn <?php echo ($mode === 'latest' && $currentIndex >= $total - 1) ? 'disabled' : ''; ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php } ?>
                    </div>
                <?php else: ?>
                    <div class="devotion-header">
                        <h2><i class="fas fa-exclamation-triangle"></i> Not Found</h2>
                    </div>
                    <div class="devotion-content">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>Meditation not found.
                        </div>
                    </div>
                    <div class="navigation">
                        <a href="" class="nav-btn">
                            <i class="fas fa-home"></i> Go Home
                        </a>
                        <span></span>
                        <span></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="container">
            <!-- Contact Information -->
            <div class="footer-section">
                <h5><i class="fas fa-envelope"></i> Contact Us</h5>
                <p class="mb-1">
                    <i class="fas fa-envelope me-2"></i>
                    <a href="mailto:wordofgod@wordofgod.in" class="footer-link">wordofgod@wordofgod.in</a>
                </p>
                <p class="mb-0">
                    <i class="fas fa-phone me-2"></i>
                    <a href="https://wa.me/917676505599?text=antantulla-appam-App" class="footer-link">+91 7676505599</a>
                </p>
            </div>
            
            <!-- Quick Links -->
            <?php include '../footer-links.php'; ?>

            <!-- Copyright -->
            <?php include '../copyright.php'; ?>

        </div>
    </footer>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="../js/zoom.js?v=<?php echo $version; ?>" type="text/javascript"></script>
    <script src="../js/copy.js?v=<?php echo $version; ?>" type="text/javascript"></script>
    <script src="../pwa/pwa.js?v=<?php echo $version; ?>" type="text/javascript"></script>
</body>
</html>