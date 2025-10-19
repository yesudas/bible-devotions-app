<?php

include 'counter.php';

$version = "2025.10.1";

session_start();

// Initialize or get current meditation number
$mode = $_GET['mode'] ?? 'latest';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Get all meditation files
function getMeditationFiles() {
    $files = glob('meditations/*.json');
    usort($files, function($a, $b) {
        return (int)basename($a, '.json') - (int)basename($b, '.json');
    });
    return $files;
}

// Get total number of meditations
function getTotalMeditations() {
    return count(getMeditationFiles());
}

// Get current meditation based on mode
function getCurrentMeditationId($mode, $action, $id) {
    $total = getTotalMeditations();
    
    if ($mode === 'random') {
        if (!isset($_SESSION['random_sequence'])) {
            $_SESSION['random_sequence'] = range(1, $total);
            shuffle($_SESSION['random_sequence']);
            $_SESSION['random_index'] = 0;
        }
        
        if ($action === 'next') {
            $_SESSION['random_index'] = ($_SESSION['random_index'] + 1) % $total;
        } elseif ($action === 'prev') {
            $_SESSION['random_index'] = ($_SESSION['random_index'] - 1 + $total) % $total;
        }
        
        return $_SESSION['random_sequence'][$_SESSION['random_index']];
    } else {
        // Latest mode
        if ($id !== null) {
            return (int)$id;
        }
        
        if (!isset($_SESSION['current_meditation'])) {
            $_SESSION['current_meditation'] = $total; // Start with latest
        }
        
        if ($action === 'next') {
            $_SESSION['current_meditation'] = min($_SESSION['current_meditation'] + 1, $total);
        } elseif ($action === 'prev') {
            $_SESSION['current_meditation'] = max($_SESSION['current_meditation'] - 1, 1);
        }
        
        return $_SESSION['current_meditation'];
    }
}

// Load meditation data
function loadMeditation($id) {
    $file = "meditations/{$id}.json";
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}

// Load all meditations for listing
function loadAllMeditations() {
    $file = 'all-meditations.json';
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    }
    return [];
}

// Get current meditation
$currentId = getCurrentMeditationId($mode, $action, $id);
$meditation = loadMeditation($currentId);
$total = getTotalMeditations();

// If viewing all meditations
$viewAll = ($_GET['view'] ?? '') === 'all';
$allMeditations = $viewAll ? loadAllMeditations() : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo $meditation ? $meditation['title'] . ' - ' : ''; ?>3-Minute Meditation - WordOfGod.in</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections. 3-minute daily devotions for spiritual growth.">
    <meta name="keywords" content="meditation, christian, bible, devotion, prayer, spiritual, faith, daily, WordOfGod.in, WordOfGod">
    <meta name="author" content="Word of God Team">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="3-Minute Meditation - Daily Christian Devotions - WordOfGod.in">
    <meta property="og:description" content="Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections - WordOfGod.in">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="3-Minute Meditation - Daily Christian Devotions - WordOfGod.in">
    <meta name="twitter:description" content="Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections - WordOfGod.in">

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
            <div class="d-flex align-items-center justify-content-center gap-3 mt-3">
                <!-- Mode Selector -->
                <?php if (!$viewAll): ?>
                <div class="mode-selector">
                    <a href="index.php?mode=latest" class="mode-btn <?php echo $mode === 'latest' ? 'active' : ''; ?>" title="Latest Mode">
                        <i class="fas fa-clock"></i> 
                    </a>
                    <a href="index.php?mode=random" class="mode-btn <?php echo $mode === 'random' ? 'active' : ''; ?>" title="Random Mode">
                        <i class="fas fa-random"></i> 
                    </a>
                    <a href="index.php?view=all&mode=<?php echo $mode; ?>" class="mode-btn" title="View All Meditations">
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
                    
                    <!-- Hamburger Menu -->
                    <?php include '../menu-links.php'; ?>

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
                        <?php foreach ($allMeditations as $med): ?>
                            <div class="meditation-item">
                                <div class="meditation-number">#<?php echo $med['sequence']; ?></div>
                                <div class="meditation-info">
                                    <h5><?php echo htmlspecialchars($med['title']); ?></h5>
                                </div>
                                <a href="index.php?mode=<?php echo $mode; ?>&id=<?php echo $med['sequence']; ?>" class="btn btn-sm nav-btn">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="navigation">
                    <a href="index.php?mode=<?php echo $mode; ?>" class="nav-btn">
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
                        <a href="index.php?mode=<?php echo $mode; ?>&action=prev" 
                           class="nav-btn <?php echo ($mode === 'latest' && $currentId <= 1) ? 'disabled' : ''; ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                        
                        <div class="navigation-center">
                            <span class="devotion-counter">
                                <?php echo $currentId; ?> of <?php echo $total; ?>
                            </span>
                            <a href="index.php?view=all&mode=<?php echo $mode; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-th-list"></i> View All
                            </a>
                        </div>
                        
                        <a href="index.php?mode=<?php echo $mode; ?>&action=next" 
                           class="nav-btn <?php echo ($mode === 'latest' && $currentId >= $total) ? 'disabled' : ''; ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
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
                        <a href="index.php" class="nav-btn">
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
</body>
</html>