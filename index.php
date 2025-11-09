<?php

include 'counter.php';
include 'detect-app.php';

$version = "2025.11.9";

// Load devotions data
$devotionsData = [];
$devotionsFile = __DIR__ . '/data/devotions.json';
if (file_exists($devotionsFile)) {
    $devotionsJson = file_get_contents($devotionsFile);
    $devotionsData = json_decode($devotionsJson, true);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bible Devotions - a multi language daily devotions and meditation to help your spiritual growth - WordOfGod.in</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Bible Devotions - a multi language daily devotions and meditation to help your spiritual growth - WordOfGod.in - Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections. Daily devotions for spiritual growth.">
    <meta name="keywords" content="<?php echo $meditation ? htmlspecialchars($meditation['title']) . ', ' : ''; ?><?php echo htmlspecialchars($appName); ?>, meditation, christian, bible, devotion, prayer, spiritual, faith, daily, WordOfGod.in, WordOfGod">
    <meta name="author" content="Word of God Team">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Bible Devotions - a multi language daily devotions and meditation to help your spiritual growth - WordOfGod.in - Daily Christian Devotions - WordOfGod.in">
    <meta property="og:description" content="Bible Devotions - a multi language daily devotions and meditation to help your spiritual growth - WordOfGod.in - Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections - WordOfGod.in">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Bible Devotions - a multi language daily devotions and meditation to help your spiritual growth - WordOfGod.in - Daily Christian Devotions - WordOfGod.in">
    <meta name="twitter:description" content="Bible Devotions - a multi language daily devotions and meditation to help your spiritual growth - WordOfGod.in - Daily Christian meditation app with inspirational content, Bible verses, and spiritual reflections - WordOfGod.in">

    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json?v=<?php echo $version; ?>">
    <meta name="theme-color" content="#667eea">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Bible Devotions - a multi language daily devotions and meditation to help your spiritual growth - WordOfGod.in">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .app-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: none;
        }
        
        .app-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .app-icon {
            font-size: 3rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .verse-text {
            color: rgba(255, 255, 255, 0.9);
            font-style: italic;
            font-size: 1.1rem;
        }
        
        .btn-app {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-app:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .footer-text {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .coming-soon {
            opacity: 0.6;
            position: relative;
        }
        
        .coming-soon::after {
            content: "Coming Soon";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 193, 7, 0.9);
            color: #000;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .language-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: none;
            cursor: pointer;
            padding: 2rem;
        }
        
        .language-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 1);
        }
        
        .language-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .language-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .brand-count {
            color: #666;
            font-size: 0.9rem;
        }
        
        .contact-links a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.85rem;
            margin: 0 0.3rem;
        }
        
        .contact-links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 25px;
            padding: 10px 25px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }
        
        .section-hidden {
            display: none;
        }
    </style>

    <!-- Google Analytics -->
    <?php include 'google-analytics.php'; ?>

</head>
<body>
    <div class="container-fluid min-vh-100 d-flex flex-column justify-content-center py-5">
        <div class="row justify-content-center">
            <div class="col-11 col-md-10 col-lg-8">
                
                <!-- Hero Section -->
                <div class="hero-section p-5 mb-5 text-center text-white">
                    <div class="mb-4">
                        <i class="bi bi-book text-white" style="font-size: 4rem;"></i>
                    </div>
                    <h1 class="display-4 fw-bold mb-3">Bible Devotions</h1>
                    <p class="lead mb-4">Bible Devotions from various authors in various languages at one place with mobile friendly UX</p>
                </div>

                <!-- Language Selection Section -->
                <div id="languageSection" class="mb-5">
                    <div class="text-center text-white mb-4">
                        <h2 class="fw-bold">Choose Your Language</h2>
                        <p>Select a language to view available devotions</p>
                    </div>
                    <div class="row g-4">
                        <?php if (!empty($devotionsData['devotions'])): ?>
                            <?php foreach ($devotionsData['devotions'] as $language => $langData): ?>
                                <?php $brandCount = count($langData['brands']); ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card language-card h-100" onclick="selectLanguage('<?= htmlspecialchars($language, ENT_QUOTES) ?>')">
                                        <div class="card-body text-center">
                                            <div class="language-icon">🌐</div>
                                            <div class="language-name"><?= htmlspecialchars($language) ?></div>
                                            <div class="brand-count"><?= $brandCount ?> devotion<?= $brandCount > 1 ? 's' : '' ?> available</div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Apps Section -->
                <div id="appsSection" class="section-hidden mb-5">
                    <div class="text-center mb-4">
                        <button class="btn back-btn" onclick="showLanguageSelection()">
                            <i class="bi bi-arrow-left me-2"></i>Back to Languages
                        </button>
                    </div>
                    <div class="row g-4" id="appsContainer">
                        <?php if (!empty($devotionsData['devotions'])): ?>
                            <?php 
                            foreach ($devotionsData['devotions'] as $language => $langData): 
                                foreach ($langData['brands'] as $brand):
                                    // Get icon from JSON, fallback to bi-book if not specified
                                    $icon = !empty($brand['icon']) ? $brand['icon'] : 'bi-book';
                                    // Remove 'bi ' prefix if present, as we add it in the template
                                    $icon = str_replace('bi ', '', $icon);
                                    $hasContact = !empty($brand['author']) || !empty($brand['email']) || !empty($brand['phone']) || !empty($brand['whatsapp']) || !empty($brand['website']);
                            ?>
                                <div class="col-md-6 col-lg-4 app-item" data-language="<?= htmlspecialchars($language, ENT_QUOTES) ?>">
                                    <div class="card app-card h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="app-icon mb-3">
                                                <i class="bi <?= $icon ?>"></i>
                                            </div>
                                            <h4 class="card-title mb-3"><?= htmlspecialchars($brand['name']) ?></h4>
                                            <p class="card-text text-muted mb-3">
                                                <?= htmlspecialchars($brand['description']) ?>
                                            </p>
                                            <?php if (!empty($brand['author'])): ?>
                                                <p class="mb-2"><strong><?= htmlspecialchars($brand['author']) ?></strong></p>
                                            <?php endif; ?>
                                            <?php if ($hasContact): ?>
                                                <div class="contact-links mb-3">
                                                    <?php if (!empty($brand['email'])): ?>
                                                        <a href="mailto:<?= htmlspecialchars($brand['email']) ?>" title="Email">
                                                            <i class="bi bi-envelope-fill"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (!empty($brand['phone'])): ?>
                                                        <a href="tel:<?= htmlspecialchars($brand['phone']) ?>" title="Phone">
                                                            <i class="bi bi-telephone-fill"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (!empty($brand['whatsapp'])): ?>
                                                        <a href="https://wa.me/<?= htmlspecialchars($brand['whatsapp']) ?>" target="_blank" title="WhatsApp">
                                                            <i class="bi bi-whatsapp"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (!empty($brand['website'])): ?>
                                                        <a href="<?= htmlspecialchars($brand['website']) ?>" target="_blank" title="Website">
                                                            <i class="bi bi-globe"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <a href="<?= htmlspecialchars($brand['appFolder']) ?>/" class="btn btn-app text-white" data-app-link>
                                                <i class="bi bi-play-circle me-2"></i><?= htmlspecialchars($brand['labels']['app_name'] ?? 'Open App') ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                endforeach;
                            endforeach; 
                            ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Features Section -->
                <div class="row text-center text-white mb-5">
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-phone" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        <h5>Mobile Friendly</h5>
                        <p class="small">Optimized for all devices</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-heart" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        <h5>Completely Free</h5>
                        <p class="small">No cost, no ads, no subscriptions</p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <i class="bi bi-globe" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        <h5>Multiple Languages</h5>
                        <p class="small">Devotions in various languages</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center">
                    <div class="footer-text">
                        <p class="mb-2">
                            <i class="bi bi-globe2 me-2"></i>
                            Visit us at <a href="https://wordofgod.in" class="text-warning text-decoration-none">WordOfGod.in</a>
                        </p>
                        <p class="mb-0">
                            <small>"Freely you have received; freely give." - Matthew 10:8. Word of God Team. Made with <i class="bi bi-heart-fill text-danger"></i> for the Kingdom of God.</small>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-emoji-heart-eyes me-1"></i>Visitors: <?= $visitors2 ?>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let selectedLanguage = localStorage.getItem('selectedLanguage');
        
        // Show language selection or apps based on stored preference
        window.addEventListener('DOMContentLoaded', () => {
            if (selectedLanguage) {
                selectLanguage(selectedLanguage);
            }
        });
        
        function selectLanguage(language) {
            selectedLanguage = language;
            localStorage.setItem('selectedLanguage', language);
            
            // Hide language section
            document.getElementById('languageSection').classList.add('section-hidden');
            
            // Show apps section
            const appsSection = document.getElementById('appsSection');
            appsSection.classList.remove('section-hidden');
            
            // Filter and show only apps for selected language
            const appItems = document.querySelectorAll('.app-item');
            appItems.forEach(item => {
                if (item.dataset.language === language) {
                    item.style.display = 'block';
                    
                    // Update app link to include language parameter
                    const appLink = item.querySelector('a[data-app-link]');
                    if (appLink) {
                        const baseUrl = appLink.href.split('?')[0]; // Remove existing params
                        appLink.href = baseUrl + '?lang=' + encodeURIComponent(language);
                    }
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Scroll to apps section
            appsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        function showLanguageSelection() {
            localStorage.removeItem('selectedLanguage');
            selectedLanguage = null;
            
            // Show language section
            document.getElementById('languageSection').classList.remove('section-hidden');
            
            // Hide apps section
            document.getElementById('appsSection').classList.add('section-hidden');
            
            // Scroll to language section
            document.getElementById('languageSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        // Add a subtle parallax effect on scroll
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelector('.hero-section');
            if (parallax) {
                const speed = scrolled * 0.5;
                parallax.style.transform = `translateY(${speed}px)`;
            }
        });

        // Add click animation to app cards
        document.querySelectorAll('.app-card:not(.coming-soon)').forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.closest('a') && !e.target.closest('.contact-links')) {
                    const link = this.querySelector('a.btn-app');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
            });
        });
    </script>
</body>
</html>