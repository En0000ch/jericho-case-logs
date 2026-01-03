<?php
/**
 * Template Name: Hero Landing Page Simple
 * Description: Simple hero section - debugging version
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jericho Case Logs</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            overflow-x: hidden;
        }

        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at center, #1a1a1a 0%, #0a0a0a 100%);
            padding: 60px 20px;
            text-align: center;
        }

        .hero-container {
            max-width: 1200px;
            width: 100%;
        }

        .hero-heading {
            color: #00f3ff;
            font-size: 56px;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 20px 0;
            text-shadow: 0 0 20px rgba(0, 243, 255, 0.5);
        }

        .hero-subtitle {
            color: #b0b0b0;
            font-size: 20px;
            line-height: 1.6;
            margin: 0 auto 40px;
            max-width: 80%;
        }

        .model-container {
            width: 100%;
            max-width: 600px;
            height: 400px;
            margin: 0 auto 40px;
        }

        .model-container iframe {
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0, 243, 255, 0.3);
        }

        .app-buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin: 20px 0;
        }

        .app-buttons a {
            transition: transform 0.3s ease;
            display: inline-block;
        }

        .app-buttons img {
            height: 60px;
            width: auto;
        }

        .app-buttons a:hover {
            transform: scale(1.05) translateY(-3px);
        }

        .app-store-btn {
            filter: drop-shadow(0 0 15px rgba(0, 243, 255, 0.3));
        }

        .app-store-btn:hover {
            filter: drop-shadow(0 0 25px rgba(0, 243, 255, 0.6));
        }

        .play-store-btn {
            filter: drop-shadow(0 0 15px rgba(0, 255, 136, 0.3));
        }

        .play-store-btn:hover {
            filter: drop-shadow(0 0 25px rgba(0, 255, 136, 0.6));
        }

        .tagline {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 30px 0 0 0;
        }

        /* Mobile responsive styles */
        @media (max-width: 768px) {
            .hero-heading {
                font-size: 36px !important;
            }
            .hero-subtitle {
                font-size: 16px !important;
                max-width: 100% !important;
            }
            .model-container {
                height: 300px !important;
            }
        }

        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>

<section class="hero-section">
    <div class="hero-container">

        <h1 class="hero-heading">Transform Your Clinical Practice</h1>

        <p class="hero-subtitle">
            The complete case logging solution for CRNAs and clinical professionals. Track cases, generate reports, and advance your career.
        </p>

        <div class="model-container">
            <iframe
                src="/3d-viewer.html?model=/Meshy_Merged_Animations.glb"
                loading="lazy"
                allowfullscreen>
            </iframe>
        </div>

        <div class="app-buttons">
            <a href="https://apps.apple.com/app/jericho-case-logs/id6738419880" target="_blank" rel="noopener">
                <img
                    src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/apple-app-store.webp"
                    alt="Download on App Store"
                    class="app-store-btn">
            </a>
            <a href="https://play.google.com/store/apps/details?id=com.jericho.caselogs" target="_blank" rel="noopener">
                <img
                    src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/google-play-1-300x93-1.webp"
                    alt="Get it on Google Play"
                    class="play-store-btn">
            </a>
        </div>

        <p class="tagline">Available for iOS and Android</p>

    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/gsap@3.12/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12/dist/ScrollTrigger.min.js"></script>
<script>
// GSAP scroll animations
if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
    gsap.registerPlugin(ScrollTrigger);

    gsap.from('.hero-section', {
        opacity: 0,
        y: 50,
        duration: 1,
        ease: 'power2.out'
    });
}
</script>

</body>
</html>
