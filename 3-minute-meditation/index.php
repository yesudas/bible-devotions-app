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

$version = "2025.10.5";

// Initialize or get current meditation number
$mode = $_GET['mode'] ?? 'latest';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Get all meditation files
function getAllMeditations() {
    $file = __DIR__ . '/meditations/all-meditations.json';
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true) ?: [];
    }
    return [];
}

// Get total number of meditations
function getTotalMeditations() {
    return count(getAllMeditations());
}

// Get current meditation based on mode
function getCurrentMeditationIndex($mode, $action, $index) {
    $total = getTotalMeditations();
    
    // If no meditations exist, return null
    if ($total === 0) {
        return null;
    }
    
    if ($mode === 'random') {
        if (!isset($_SESSION['random_sequence'])) {
            $_SESSION['random_sequence'] = range(0, $total - 1);
            shuffle($_SESSION['random_sequence']);
            $_SESSION['random_index'] = 0;
        }
        
        // If a specific index is requested, find its position in the random sequence
        if ($index !== null) {
            $targetIndex = (int)$index;
            $position = array_search($targetIndex, $_SESSION['random_sequence']);
            if ($position !== false) {
                $_SESSION['random_index'] = $position;
                return $targetIndex;
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
            }
        }
        
        if (!isset($_SESSION['current_meditation_index'])) {
            $_SESSION['current_meditation_index'] = 0; // Start with latest (first in array)
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
function loadMeditationByFilename($filename) {
    $file = __DIR__ . "/meditations/{$filename}";
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}

// Initialize or get current meditation number
$mode = $_GET['mode'] ?? 'latest';
$action = $_GET['action'] ?? '';
$index = $_GET['index'] ?? null;

// Get all meditations and current meditation
$allMeditations = getAllMeditations();
$total = count($allMeditations);
$currentIndex = getCurrentMeditationIndex($mode, $action, $index);
$meditation = null;

if ($currentIndex !== null && isset($allMeditations[$currentIndex])) {
    $meditation = loadMeditationByFilename($allMeditations[$currentIndex]['filename']);
}

// If viewing all meditations
$viewAll = ($_GET['view'] ?? '') === 'all';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $meditation ? $meditation['title'] . ' - ' : ''; ?> 3-Minute Meditation - WordOfGod.in</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $meditation ? $meditation['title'] . ' - ' : ''; ?> Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections. 3-minute daily devotions for spiritual growth.">
    <meta name="keywords" content="<?php echo $meditation ? $meditation['title'] . ' - ' : ''; ?> , meditation, christian, bible, devotion, prayer, spiritual, faith, daily, WordOfGod.in, WordOfGod">
    <meta name="author" content="Word of God Team">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo $meditation ? $meditation['title'] . ' - ' : ''; ?> 3-Minute Meditation - Daily Christian Devotions - WordOfGod.in">
    <meta property="og:description" content="<?php echo $meditation ? $meditation['title'] . ' - ' : ''; ?> Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections - WordOfGod.in">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $meditation ? $meditation['title'] . ' - ' : ''; ?> 3-Minute Meditation - Daily Christian Devotions - WordOfGod.in">
    <meta name="twitter:description" content="<?php echo $meditation ? $meditation['title'] . ' - ' : ''; ?> Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections - WordOfGod.in">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/style.css?v=<?php echo $version; ?>" rel="stylesheet">
<body>
    <!-- Site Header - Common for all pages -->
    <div class="site-header">
        <div class="container">
            <h1><i class="fas fa-book-open"></i> 3-Minute Meditation</h1>
            <p class="site-tagline">Daily Christian Devotions for Spiritual Growth</p>
            
            <!-- Mode Selector & Zoom Controls -->
            <div class="header-controls">
                
                <!-- Hamburger Menu -->
                <?php include '../menu-links.php'; ?>

                <!-- Mode Selector -->
                <?php if (!$viewAll): ?>
                <div class="mode-selector">
                    <a href="?mode=latest" class="mode-btn <?php echo $mode === 'latest' ? 'active' : ''; ?>" title="Latest Mode">
                        <i class="fas fa-clock"></i> 
                    </a>
                    <a href="?mode=random" class="mode-btn <?php echo $mode === 'random' ? 'active' : ''; ?>" title="Random Mode">
                        <i class="fas fa-random"></i> 
                    </a>
                    <a href="?view=all&mode=<?php echo $mode; ?>" class="mode-btn" title="View All Meditations">
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
                </div>
                
            </div>

            <!-- PWA Install Button -->
            <?php if ($installAsAppButton): ?>
            <div>
                    <button id="installAppBtn" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fas fa-download me-1"></i>Install as App
                    </button>
            </div>
            <?php endif; ?>

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
                                <a href="?mode=<?php echo $mode; ?>&index=<?php echo $idx; ?>" class="btn btn-sm nav-btn">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="navigation">
                    <a href="?mode=<?php echo $mode; ?>" class="nav-btn">
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
                        <div class="section">
                            <h2><i class="fas fa-book"></i> <?php echo $meditation['memory_verse']['label'] ?? 'Memory Verse'; ?></h2>
                            <span class="verse-reference"><?php echo htmlspecialchars($meditation['memory_verse']['text']); ?></span>
                        </div>
                    
                        <div class="section">
                            <h2><i class="fas fa-heart"></i> <?php echo $meditation['devotion']['label'] ?? 'Insight & Reflection'; ?></h2>
                            <p><?php echo nl2br(htmlspecialchars($meditation['devotion']['text'])); ?></p>
                        </div>
                    
                        <div class="section">
                            <h2><i class="fas fa-quote-right"></i> <?php echo $meditation['quote']['label'] ?? "Today's Quote"; ?></h2>
                            <p><?php echo htmlspecialchars($meditation['quote']['text']); ?></p>
                        </div>
                    
                        <?php if (!empty($meditation['recommended_book']) && !empty($meditation['recommended_book']['title'])): ?>
                        <div class="section">
                            <h2><i class="fas fa-book-reader"></i> Recommended Book</h2>
                            <h6 class="fw-bold text-dark"><?php echo htmlspecialchars($meditation['recommended_book']['title']); ?></h6>
                            <p class="text-muted mb-3">by <?php echo htmlspecialchars($meditation['recommended_book']['author']); ?></p>
                            <blockquote class="border-start border-3 border-success ps-3 mb-3">
                                <p class="mb-1 fst-italic">"<?php echo htmlspecialchars($meditation['recommended_book']['quote']); ?>"</p>
                                <small class="text-muted">— Page <?php echo $meditation['recommended_book']['page']; ?></small>
                            </blockquote>
                            <?php if (!empty($meditation['recommended_book']['link'])): ?>
                                <a href="<?php echo htmlspecialchars($meditation['recommended_book']['link']); ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-external-link-alt me-1"></i>Read More
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    
                        <?php if (!empty($meditation['song'])): ?>
                        <div class="section">
                            <h2><i class="fas fa-music"></i> <?php echo $meditation['song']['label'] ?? 'Song'; ?></h2>
                            <p><?php echo nl2br(htmlspecialchars($meditation['song']['text'])); ?></p>
                        </div>
                        <?php endif; ?>
                    
                        <div class="section">
                            <h2><i class="fas fa-praying-hands"></i> <?php echo $meditation['prayer']['label'] ?? 'Prayer'; ?></h2>
                            <p><?php echo nl2br(htmlspecialchars($meditation['prayer']['text'])); ?></p>
                        </div>
                    
                        <div class="section">
                            <h2><i class="fas fa-star"></i> <?php echo $meditation['conclusion']['label'] ?? 'A Word to You'; ?></h2>
                            <?php foreach ($meditation['conclusion']['text'] as $word): ?>
                                <p><?php echo htmlspecialchars($word); ?></p>
                            <?php endforeach; ?>
                        </div>
                    
                        <div class="section">
                            <h2><i class="fas fa-user"></i> <?php echo $meditation['author']['label'] ?? 'Author'; ?></h2>
                            <p class="mb-2"><strong><?php echo htmlspecialchars($meditation['author']['author']); ?></strong></p>
                            <?php if (!empty($meditation['author']['whatsapp'])): ?>
                                <p class="mb-1">
                                    <i class="fab fa-whatsapp me-2 text-success"></i>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $meditation['author']['whatsapp']); ?>?text=3-Minute-Meditation" 
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
                        ?>
                        <a href="?mode=<?php echo $mode; ?>&index=<?php echo $prevIndex; ?>" 
                           class="nav-btn <?php echo ($mode === 'latest' && $currentIndex <= 0) ? 'disabled' : ''; ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                        
                        <div class="navigation-center">
                            <span class="devotion-counter">
                                <?php echo ($currentIndex + 1); ?> of <?php echo $total; ?>
                            </span>
                            <a href="?view=all&mode=<?php echo $mode; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-th-list"></i> View All
                            </a>
                        </div>
                        
                        <a href="?mode=<?php echo $mode; ?>&index=<?php echo $nextIndex; ?>" 
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
                    <a href="mailto:mjosephnj@gmail.com" class="footer-link">mjosephnj@gmail.com</a>
                </p>
                <p class="mb-0">
                    <i class="fas fa-phone me-2"></i>
                    <a href="https://wa.me/919243183231?text=3-Minute-Meditation-App" class="footer-link">+91 9243183231</a>
                </p>
            </div>
            
            <!-- Quick Links -->
            <?php include '../footer-links.php'; ?>

            <!-- Copyright -->
            <?php include '../copyright.php'; ?>

        </div>
    </footer>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="../js/zoom.js?v=<?php echo $version; ?>"></script>
    <script src="../js/copy.js?v=<?php echo $version; ?>"></script>
</body>
</html>