<?php

include 'counter.php';
include 'detect-app.php';

$version = "2025.10.1";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bible Devotions App - Word of God Team</title>
    
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
    </style>
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

                <!-- Apps Section -->
                <div class="row g-4 mb-5">
                    
                    <!-- 3-Minute Meditation App -->
                    <div class="col-md-6">
                        <div class="card app-card h-100">
                            <div class="card-body text-center p-4">
                                <div class="app-icon mb-3">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <h4 class="card-title mb-3">3-Minute Meditation</h4>
                                <p class="card-text text-muted mb-4">
                                    Quick daily spiritual meditations designed to fit into your busy schedule. 
                                    Perfect for morning devotions or moments of reflection.
                                </p>
                                <p>Pr. Maria Joseph</p>
                                <a href="3-minute-meditation/" class="btn btn-app text-white">
                                    <i class="bi bi-play-circle me-2"></i>Start Meditating
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Tamil Daily Manna App -->
                    <div class="col-md-6">
                        <div class="card app-card h-100">
                            <div class="card-body text-center p-4">
                                <div class="app-icon mb-3">
                                    <i class="bi bi-sunrise"></i>
                                </div>
                                <h4 class="card-title mb-3">அனுதின மன்னா</h4>
                                <p class="card-text text-muted mb-4">
                                    Daily spiritual nourishment in Tamil. Traditional devotions and 
                                    scriptural insights for Tamil-speaking believers.
                                </p>
                                <p>Gladys Sugandhi Hazlitt</p>
                                <a href="அனுதின-மன்னா/" class="btn btn-app text-white">
                                    <i class="bi bi-book me-2"></i>Read Devotions
                                </a>
                            </div>
                        </div>
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
    
    <!-- Optional: Add some interactive effects -->
    <script>
        // Add a subtle parallax effect on scroll
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelector('.hero-section');
            const speed = scrolled * 0.5;
            parallax.style.transform = `translateY(${speed}px)`;
        });

        // Add click animation to app cards
        document.querySelectorAll('.app-card:not(.coming-soon)').forEach(card => {
            card.addEventListener('click', function(e) {
                if (!e.target.closest('a')) {
                    const link = this.querySelector('a');
                    if (link) {
                        window.location.href = link.href;
                    }
                }
            });
        });
    </script>
</body>
</html>