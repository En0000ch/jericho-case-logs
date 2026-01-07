<?php
/**
 * Plugin Name: JCL Complete Site Fix V36
 * Description: FIXED - All 4 animations in Phase 1, images converge to exact center (50%, 50%), login modal popup, 160px logo in header
 * Version: 36.0
 * Author: JCL Development
 */

if (!defined('ABSPATH')) exit;

// Enqueue styles and scripts
add_action('wp_enqueue_scripts', 'jcl_v34_enqueue', 99999);

function jcl_v34_enqueue() {
    add_action('wp_head', 'jcl_v34_head_styles', 99999);

    // Only add scroll animation on home page
    if (is_front_page()) {
        add_action('wp_footer', 'jcl_v34_home_scroll_animation', 99999);
    }
}

function jcl_v34_head_styles() {
    ?>
    <style>
        /* Starry Background */
        body {
            background-image: url('https://www.jerichocaselogs.com/wp-content/uploads/2025/12/starry_bkgd.jpg') !important;
            background-size: cover !important;
            background-position: center !important;
            background-attachment: fixed !important;
            background-repeat: no-repeat !important;
        }

        /* Global Font - Lightweight Century Gothic */
        body, body *, a, a * {
            font-family: 'Century Gothic', 'CenturyGothic', 'AppleGothic', sans-serif !important;
            font-weight: 300 !important;
        }

        /* TRANSPARENT HEADERS/FOOTERS - Maximum Priority */
        .site-header,
        .site-footer,
        header,
        footer,
        #masthead,
        #colophon,
        .site-branding,
        .site-info,
        .entry-header,
        .entry-footer,
        .site-header *,
        .site-footer *,
        header *,
        footer *,
        nav,
        nav *,
        .navigation,
        .main-navigation {
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
        }

        /* Hide header/footer separator lines */
        .site-header, .site-footer {
            border-top: none !important;
            border-bottom: none !important;
        }

        /* UI Safe Zones - For mascot lane and floating buttons */
        :root {
            --ui-safe-top-right: 90px;
            --ui-safe-bottom-right: 90px;
            --mascot-lane-width: 120px;
        }

        /* Employers Dropdown - Respects safe zone */
        .employers-corner-link {
            position: fixed !important;
            top: 20px !important;
            right: 25px !important;
            z-index: 99999 !important;
            background: rgba(0, 0, 0, 0.75) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            padding: 10px 18px !important;
            border-radius: 6px !important;
            border: 1px solid rgba(0, 243, 255, 0.2) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4) !important;
        }

        .employers-corner-link > a {
            color: #00f3ff !important;
            text-decoration: none !important;
            font-size: 16px !important;
            transition: all 0.3s ease !important;
            text-shadow: 0 0 10px rgba(0, 243, 255, 0.5) !important;
            cursor: pointer !important;
            display: block !important;
        }

        .employers-corner-link > a:hover {
            color: #a855f7 !important;
            text-shadow: 0 0 15px rgba(168, 85, 247, 0.7) !important;
        }

        .employers-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: rgba(0, 0, 0, 0.9) !important;
            padding: 10px !important;
            border-radius: 5px !important;
            min-width: 150px !important;
            margin-top: 5px;
        }

        .employers-corner-link:hover .employers-dropdown {
            display: block;
        }

        .employers-dropdown a {
            color: #00f3ff !important;
            display: block !important;
            padding: 8px 12px !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            transition: all 0.3s ease !important;
        }

        .employers-dropdown a:hover {
            color: #a855f7 !important;
            background: rgba(168, 85, 247, 0.1) !important;
        }

        /* Meteor Canvas */
        #meteor-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1;
            pointer-events: none;
        }

        /* ============================================
           UX IMPROVEMENTS - Glassmorphism Enhancement
           ============================================ */

        /* Breakdance Section Containers - Add readability vignettes */
        .bde-section,
        .breakdance-section,
        section[class*="section"],
        div[class*="section-wrapper"] {
            position: relative;
        }

        /* Soft vignette behind text-heavy sections */
        .bde-section::before,
        .breakdance-section::before {
            content: '';
            position: absolute;
            inset: -100px;
            background: radial-gradient(ellipse at center, rgba(20, 25, 35, 0.65) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
            filter: blur(50px);
        }

        /* Ensure content stays above vignette */
        .bde-section > *,
        .breakdance-section > * {
            position: relative;
            z-index: 1;
        }

        /* Enhanced Glass Cards - IMPROVED CONTRAST */
        .bde-card,
        .breakdance-card,
        div[class*="card"],
        div[class*="feature-box"],
        div[class*="service-box"],
        .ee-element-card,
        .bde-testimonial {
            background: rgba(57, 62, 80, 0.75) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(238, 108, 77, 0.1) !important;
            position: relative;
        }

        /* Darker overlay for better text contrast on cards */
        .bde-card::before,
        .breakdance-card::before,
        div[class*="card"]::before,
        div[class*="feature-box"]::before,
        div[class*="service-box"]::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.15);
            border-radius: inherit;
            pointer-events: none;
            z-index: 0;
        }

        /* Ensure card content stays above overlay */
        .bde-card > *,
        .breakdance-card > *,
        div[class*="card"] > *,
        div[class*="feature-box"] > *,
        div[class*="service-box"] > * {
            position: relative;
            z-index: 1;
        }

        /* HEADING HIERARCHY - Consistent styling */
        h1, .bde-heading-1, .breakdance h1 {
            color: #ffffff !important;
            font-weight: 700 !important;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5), 0 0 20px rgba(224, 251, 252, 0.3) !important;
            letter-spacing: -0.5px !important;
        }

        h2, .bde-heading-2, .breakdance h2 {
            color: #ffffff !important;
            font-weight: 600 !important;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4), 0 0 15px rgba(238, 108, 77, 0.2) !important;
        }

        h3, .bde-heading-3, .breakdance h3 {
            color: #ff8b6d !important;
            font-weight: 600 !important;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4) !important;
        }

        h4, .bde-heading-4, .breakdance h4 {
            color: #ff8b6d !important;
            font-weight: 600 !important;
        }

        /* Improved paragraph readability */
        p, .bde-text, .breakdance p {
            color: #d0e5e6 !important;
            font-weight: 400 !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
            line-height: 1.7 !important;
        }

        /* CTA Sections - Enhanced prominence */
        div[class*="cta"],
        div[class*="download"],
        div[class*="signup"],
        .bde-cta-section {
            position: relative;
            padding: 80px 50px !important;
        }

        /* Spotlight effect behind CTA */
        div[class*="cta"]::before,
        div[class*="download"]::before,
        div[class*="signup"]::before {
            content: '';
            position: absolute;
            inset: -150px;
            background: radial-gradient(ellipse at center, rgba(238, 108, 77, 0.15) 0%, rgba(20, 25, 35, 0.8) 50%, transparent 75%);
            pointer-events: none;
            z-index: 0;
            filter: blur(60px);
        }

        /* Card-like container for CTA */
        div[class*="cta"]::after,
        div[class*="download"]::after,
        div[class*="signup"]::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(46, 50, 65, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(238, 108, 77, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), 0 0 60px rgba(238, 108, 77, 0.1);
            pointer-events: none;
            z-index: 0;
        }

        /* App Store / Download badges - larger and more prominent */
        img[src*="app-store"],
        img[src*="google-play"],
        img[src*="download"],
        .bde-download-badge img {
            height: 70px !important;
            width: auto !important;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.4));
        }

        img[src*="app-store"]:hover,
        img[src*="google-play"]:hover {
            filter: drop-shadow(0 6px 12px rgba(238, 108, 77, 0.4));
        }

        /* Back to Top Button */
        #jcl-back-to-top {
            position: fixed;
            bottom: 30px;
            right: var(--ui-safe-bottom-right);
            background: rgba(238, 108, 77, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #ffffff;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
            z-index: 9999;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            opacity: 0;
            transition: opacity 0.3s ease, background 0.3s ease;
        }

        #jcl-back-to-top.visible {
            display: flex;
            opacity: 1;
        }

        #jcl-back-to-top:hover {
            background: rgba(238, 108, 77, 1);
            box-shadow: 0 6px 16px rgba(238, 108, 77, 0.5);
        }

        /* Mobile Responsive Adjustments */
        @media (max-width: 768px) {
            :root {
                --mascot-lane-width: 0px;
                --ui-safe-top-right: 20px;
                --ui-safe-bottom-right: 20px;
            }

            .bde-section::before,
            .breakdance-section::before {
                inset: -50px;
                filter: blur(30px);
            }

            div[class*="cta"]::before,
            div[class*="download"]::before,
            div[class*="signup"]::before {
                inset: -80px;
                filter: blur(40px);
            }

            div[class*="cta"],
            div[class*="download"],
            div[class*="signup"] {
                padding: 50px 30px !important;
            }

            img[src*="app-store"],
            img[src*="google-play"] {
                height: 60px !important;
            }

            #jcl-back-to-top {
                right: 20px;
                width: 45px;
                height: 45px;
                font-size: 20px;
            }
        }
    </style>
    <?php
}

function jcl_v34_home_scroll_animation() {
    ?>
    <!-- Employers Link with Dropdown -->
    <div class="employers-corner-link">
        <a href="#" onclick="return false;">Employers</a>
        <div class="employers-dropdown">
            <a href="#" onclick="openLoginModal(); return false;">Login</a>
            <a href="https://www.jerichocaselogs.com/employer-registration/">Create Account</a>
        </div>
    </div>

    <!-- Meteor Canvas -->
    <canvas id="meteor-canvas"></canvas>

    <style>
        /* Scroll Animation Container */
        #jcl-scroll-container {
            position: relative;
            width: 100%;
            height: 500vh;
            overflow: visible;
        }

        /* Robot Container */
        #jcl-robot-container {
            position: fixed;
            bottom: 15%;
            left: 50%;
            transform: translateX(-50%) scale(0.05);
            transform-origin: bottom center;
            width: 4000px;
            height: 4000px;
            z-index: 10;
            pointer-events: none;
            transition: opacity 0.5s ease;
            overflow: visible !important;
        }

        #jcl-robot-viewer {
            width: 100%;
            height: 100%;
            background: transparent !important;
            overflow: visible !important;
        }

        #jcl-robot-viewer canvas {
            background: transparent !important;
        }

        /* V35: Banner Container - Positioned at 25% from left */
        #jcl-banner-container {
            position: fixed;
            bottom: -150%;
            left: 25%;
            transform: translateX(-25%);
            width: 400px;
            max-width: 90vw;
            z-index: 20;
            opacity: 0;
            visibility: hidden;
            transition: bottom 0.5s ease, left 0.5s ease, top 0.5s ease, transform 0.5s ease, opacity 0.5s ease, visibility 0.5s ease;
        }

        #jcl-banner-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* V35: Placeholder Image - Drops from top, positioned 25% from right (= 75% from left) */
        #jcl-placeholder-container {
            position: fixed;
            top: -150%;
            left: 75%;
            transform: translateX(-75%);
            width: 400px;
            max-width: 90vw;
            z-index: 20;
            opacity: 0;
            visibility: hidden;
            transition: top 0.5s ease, left 0.5s ease, bottom 0.5s ease, transform 0.5s ease, opacity 0.5s ease, visibility 0.5s ease;
        }

        #jcl-placeholder-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Star Burst Effect - Matches Starfield Theme */
        #jcl-light-burst {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0;
            height: 0;
            background:
                conic-gradient(
                    from 0deg,
                    transparent 0deg, rgba(255,255,255,0.9) 5deg, transparent 10deg,
                    transparent 30deg, rgba(255,255,255,0.9) 35deg, transparent 40deg,
                    transparent 60deg, rgba(255,255,255,0.9) 65deg, transparent 70deg,
                    transparent 90deg, rgba(255,255,255,0.9) 95deg, transparent 100deg,
                    transparent 120deg, rgba(255,255,255,0.9) 125deg, transparent 130deg,
                    transparent 150deg, rgba(255,255,255,0.9) 155deg, transparent 160deg,
                    transparent 180deg, rgba(255,255,255,0.9) 185deg, transparent 190deg,
                    transparent 210deg, rgba(255,255,255,0.9) 215deg, transparent 220deg,
                    transparent 240deg, rgba(255,255,255,0.9) 245deg, transparent 250deg,
                    transparent 270deg, rgba(255,255,255,0.9) 275deg, transparent 280deg,
                    transparent 300deg, rgba(255,255,255,0.9) 305deg, transparent 310deg,
                    transparent 330deg, rgba(255,255,255,0.9) 335deg, transparent 340deg
                ),
                radial-gradient(circle, rgba(255,255,255,1) 0%, rgba(200,220,255,0.8) 15%, rgba(150,180,255,0.4) 40%, transparent 70%);
            border-radius: 50%;
            opacity: 0;
            visibility: hidden;
            z-index: 15;
            pointer-events: none;
            transition: all 0.5s ease;
            box-shadow:
                0 0 50px rgba(255,255,255,0.8),
                0 0 100px rgba(200,220,255,0.6),
                0 0 150px rgba(150,180,255,0.4);
        }

        /* Header Banner */
        #jcl-header-banner {
            position: fixed;
            top: calc(5vh + 20px);
            left: 50%;
            transform: translateX(-50%);
            opacity: 1;
            visibility: visible;
            z-index: 100;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        #jcl-header-banner.hide-on-scroll {
            opacity: 0 !important;
            visibility: hidden !important;
        }

        #jcl-header-banner img {
            height: 80px;
            width: auto;
            display: block;
        }

        /* TEMPORARILY HIDE ROBOT */
        #jcl-robot-container,
        #jcl-robot-viewer,
        #jcl-loading {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
        }

        /* Loading indicator */
        #jcl-loading {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: #00f3ff;
            font-size: 14px;
            text-shadow: 0 0 10px rgba(0, 243, 255, 0.5);
            z-index: 100;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px 20px;
            border-radius: 5px;
        }

        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            #meteor-canvas {
                display: none;
            }
            * {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>

    <!-- HTML Structure -->
    <div id="jcl-scroll-container">
        <div id="jcl-loading">Loading robot...</div>

        <!-- Robot Container -->
        <div id="jcl-robot-container" style="opacity: 0;">
            <div id="jcl-robot-viewer"></div>
        </div>

        <!-- Banner Container (left side, 25%) -->
        <div id="jcl-banner-container">
            <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/jclBanner_3d.png" alt="JCL Banner">
        </div>

        <!-- V35: Placeholder Container (right side, 25% from right = 75% from left) -->
        <div id="jcl-placeholder-container">
            <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/jclBanner_3d.png" alt="Placeholder Image">
        </div>

        <!-- Star Burst -->
        <div id="jcl-light-burst"></div>

        <!-- Header Banner -->
        <div id="jcl-header-banner">
            <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/jclBanner_3d.png" alt="JCL Banner">
        </div>
    </div>

    <script>
        console.log('JCL Complete Site Fix V34 - Starting initialization');
        console.log('V35: Banner at 25% left, placeholder at 25% right, images merge and disintegrate, charged_slam animation');

        // Meteor System Configuration
        const CONFIG = {
            meteorFrequencyRangeSeconds: [2, 6],
            maxMeteorsOnScreen: 3,
            meteorSpeedRange: [200, 400],
            trailLengthRange: [80, 150],
            brightnessRange: [0.6, 1.0],
            parallaxStrength: 3,
            starCount: 80,
            starTwinkleSpeed: 0.0005
        };

        // Meteor Canvas Setup
        const canvas = document.getElementById('meteor-canvas');
        const ctx = canvas.getContext('2d');
        let width = canvas.width = window.innerWidth;
        let height = canvas.height = window.innerHeight;

        let meteors = [];
        let stars = [];
        let lastMeteorTime = 0;
        let parallaxX = 0;
        let parallaxY = 0;
        let lastFrameTime = performance.now();

        // Initialize Stars
        function initStars() {
            stars = [];
            for (let i = 0; i < CONFIG.starCount; i++) {
                stars.push({
                    x: Math.random() * width,
                    y: Math.random() * height,
                    size: Math.random() * 1.5 + 0.5,
                    opacity: Math.random() * 0.5 + 0.3,
                    twinkleSpeed: Math.random() * CONFIG.starTwinkleSpeed + CONFIG.starTwinkleSpeed,
                    twinklePhase: Math.random() * Math.PI * 2
                });
            }
        }

        // Create Meteor
        function createMeteor() {
            const angle = Math.random() * Math.PI / 3 + Math.PI / 6;
            const speed = Math.random() * (CONFIG.meteorSpeedRange[1] - CONFIG.meteorSpeedRange[0]) + CONFIG.meteorSpeedRange[0];

            meteors.push({
                x: Math.random() * width * 0.5,
                y: Math.random() * height * 0.3,
                vx: Math.cos(angle) * speed,
                vy: Math.sin(angle) * speed,
                life: 1.0,
                decay: 0.008 + Math.random() * 0.004,
                trailLength: Math.random() * (CONFIG.trailLengthRange[1] - CONFIG.trailLengthRange[0]) + CONFIG.trailLengthRange[0],
                brightness: Math.random() * (CONFIG.brightnessRange[1] - CONFIG.brightnessRange[0]) + CONFIG.brightnessRange[0]
            });
        }

        // Draw Stars
        function drawStars(time) {
            ctx.save();
            ctx.translate(parallaxX * 0.5, parallaxY * 0.5);

            stars.forEach(star => {
                const twinkle = Math.sin(time * star.twinkleSpeed + star.twinklePhase) * 0.3 + 0.7;
                const opacity = star.opacity * twinkle;

                ctx.fillStyle = `rgba(255, 255, 255, ${opacity})`;
                ctx.beginPath();
                ctx.arc(star.x, star.y, star.size, 0, Math.PI * 2);
                ctx.fill();
            });

            ctx.restore();
        }

        // Draw Meteors
        function drawMeteors(deltaTime) {
            ctx.save();
            ctx.globalCompositeOperation = 'lighter';
            ctx.translate(parallaxX, parallaxY);

            meteors = meteors.filter(meteor => {
                meteor.x += meteor.vx * deltaTime / 1000;
                meteor.y += meteor.vy * deltaTime / 1000;
                meteor.life -= meteor.decay;

                if (meteor.life <= 0 || meteor.x > width + 100 || meteor.y > height + 100) {
                    return false;
                }

                const tailX = meteor.x - (meteor.vx / Math.abs(meteor.vx + meteor.vy)) * meteor.trailLength;
                const tailY = meteor.y - (meteor.vy / Math.abs(meteor.vx + meteor.vy)) * meteor.trailLength;

                const gradient = ctx.createLinearGradient(meteor.x, meteor.y, tailX, tailY);
                gradient.addColorStop(0, `rgba(255, 255, 255, ${meteor.life * meteor.brightness})`);
                gradient.addColorStop(0.5, `rgba(173, 216, 230, ${meteor.life * meteor.brightness * 0.5})`);
                gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

                ctx.strokeStyle = gradient;
                ctx.lineWidth = 2 + meteor.life;
                ctx.beginPath();
                ctx.moveTo(meteor.x, meteor.y);
                ctx.lineTo(tailX, tailY);
                ctx.stroke();

                ctx.fillStyle = `rgba(255, 255, 255, ${meteor.life})`;
                ctx.beginPath();
                ctx.arc(meteor.x, meteor.y, 2, 0, Math.PI * 2);
                ctx.fill();

                return true;
            });

            ctx.restore();
        }

        // Animation Loop
        function animate(currentTime) {
            const deltaTime = currentTime - lastFrameTime;
            lastFrameTime = currentTime;

            ctx.clearRect(0, 0, width, height);

            drawStars(currentTime);
            drawMeteors(deltaTime);

            // Create new meteors
            const minInterval = CONFIG.meteorFrequencyRangeSeconds[0] * 1000;
            const maxInterval = CONFIG.meteorFrequencyRangeSeconds[1] * 1000;
            const nextMeteorTime = lastMeteorTime + Math.random() * (maxInterval - minInterval) + minInterval;

            if (currentTime >= nextMeteorTime && meteors.length < CONFIG.maxMeteorsOnScreen) {
                createMeteor();
                lastMeteorTime = currentTime;
            }

            requestAnimationFrame(animate);
        }

        // Mouse/Touch Parallax
        function updateParallax(e) {
            const x = e.clientX !== undefined ? e.clientX : (e.touches ? e.touches[0].clientX : width / 2);
            const y = e.clientY !== undefined ? e.clientY : (e.touches ? e.touches[0].clientY : height / 2);

            parallaxX = ((x / width) - 0.5) * CONFIG.parallaxStrength;
            parallaxY = ((y / height) - 0.5) * CONFIG.parallaxStrength;
        }

        // Window Resize
        function handleResize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
            initStars();
        }

        // Event Listeners
        window.addEventListener('mousemove', updateParallax);
        window.addEventListener('touchmove', updateParallax);
        window.addEventListener('resize', handleResize);

        // Initialize
        initStars();
        requestAnimationFrame(animate);
    </script>

    <script type="importmap">
    {
        "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
            "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
        }
    }
    </script>

    <script type="module">
        import * as THREE from 'three';
        import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

        console.log('Starting robot initialization...');

        const modelPath = 'https://www.jerichocaselogs.com/wp-content/uploads/2025/12/Meshy_AI_Meshy_Merged_Animations.glb';

        // V35: Replaced JUMP_OBSTACLE with CHARGED_SLAM
        const ANIMATIONS = {
            WAKE_UP: 4,
            ARISE: 6,
            HANDSTAND_FLIP: 1,
            CHARGED_SLAM: 7
        };

        // Scene setup
        const scene = new THREE.Scene();
        scene.background = null;

        const robotContainer = document.getElementById('jcl-robot-viewer');
        const width = robotContainer.clientWidth;
        const height = robotContainer.clientHeight;

        console.log('Robot container dimensions:', width, 'x', height);

        const camera = new THREE.PerspectiveCamera(70, width / height, 0.1, 1000);
        camera.position.set(0, 1.5, 5);
        camera.lookAt(0, 0.5, 0);

        // Renderer setup
        const renderer = new THREE.WebGLRenderer({
            antialias: true,
            alpha: true
        });
        renderer.setClearColor(0x000000, 0);
        renderer.setSize(width, height);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.outputColorSpace = THREE.SRGBColorSpace;
        robotContainer.appendChild(renderer.domElement);

        console.log('Renderer created, canvas appended');

        // Enhanced Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 2);
        scene.add(ambientLight);

        const directionalLight1 = new THREE.DirectionalLight(0x00f3ff, 3);
        directionalLight1.position.set(5, 5, 5);
        scene.add(directionalLight1);

        const directionalLight2 = new THREE.DirectionalLight(0xa855f7, 2);
        directionalLight2.position.set(-5, 3, -5);
        scene.add(directionalLight2);

        // Animation variables
        let mixer = null;
        let clips = [];
        let actions = {};
        let currentAction = null;
        let currentPhase = 0;
        let robotModel = null;
        const clock = new THREE.Clock();

        // Load model
        const loader = new GLTFLoader();

        loader.load(
            modelPath,
            function (gltf) {
                console.log('Model loaded successfully!', gltf);
                robotModel = gltf.scene;

                const box = new THREE.Box3().setFromObject(robotModel);
                const center = box.getCenter(new THREE.Vector3());
                const size = box.getSize(new THREE.Vector3());

                console.log('Model size:', size);
                console.log('Model center:', center);

                const minDim = Math.min(size.x, size.y, size.z);
                const scale = 0.75 / minDim;
                robotModel.scale.setScalar(scale);

                console.log('V35: Robot scale calculated:', scale, '(0.75 / minDim:', minDim, ')');

                robotModel.position.x = -center.x * scale;
                robotModel.position.y = (-center.y * scale) - 0.3;
                robotModel.position.z = -center.z * scale;

                console.log('Robot positioned at:', robotModel.position);

                scene.add(robotModel);

                // Setup animations
                if (gltf.animations && gltf.animations.length > 0) {
                    mixer = new THREE.AnimationMixer(robotModel);
                    clips = gltf.animations;

                    console.log('Found', gltf.animations.length, 'animations');

                    clips.forEach((clip, index) => {
                        console.log(`Animation ${index}: ${clip.name}`);
                    });

                    clips.forEach((clip, index) => {
                        const action = mixer.clipAction(clip);
                        action.setLoop(THREE.LoopRepeat);
                        actions[index] = action;
                    });

                    // Start with wake_up
                    robotModel.rotation.y = THREE.MathUtils.degToRad(30);
                    playAnimation(ANIMATIONS.WAKE_UP);
                    document.getElementById('jcl-robot-container').style.opacity = '1';
                    console.log('Robot visible, wake_up animation started');
                }

                document.getElementById('jcl-loading').style.display = 'none';
            },
            function (progress) {
                const percent = (progress.loaded / progress.total * 100).toFixed(0);
                console.log('Loading progress: ' + percent + '%');
                document.getElementById('jcl-loading').textContent = `Loading... ${percent}%`;
            },
            function (error) {
                console.error('Error loading model:', error);
                document.getElementById('jcl-loading').textContent = 'Error loading robot';
            }
        );

        function playAnimation(index) {
            if (!actions[index]) return;

            console.log('Playing animation:', index);

            if (currentAction) {
                currentAction.fadeOut(0.5);
            }

            currentAction = actions[index];
            currentAction.reset().fadeIn(0.5).play();
        }

        // V35: New 5-phase animation with image merge and disintegration
        const scrollContainer = document.getElementById('jcl-scroll-container');
        const robotContainer_el = document.getElementById('jcl-robot-container');
        const bannerContainer = document.getElementById('jcl-banner-container');
        const placeholderContainer = document.getElementById('jcl-placeholder-container');
        const lightBurst = document.getElementById('jcl-light-burst');
        const headerBanner = document.getElementById('jcl-header-banner');

        function handleScroll() {
            const containerRect = scrollContainer.getBoundingClientRect();
            const scrollProgress = Math.max(0, Math.min(1, -containerRect.top / (containerRect.height - window.innerHeight)));
            const actualScrollPosition = window.pageYOffset || document.documentElement.scrollTop;

            // Show header banner at very top (above the fold, before animation starts)
            if (actualScrollPosition <= 100) {
                headerBanner.style.opacity = '1';
                headerBanner.style.visibility = 'visible';
                // Hide robot and other elements at the top
                bannerContainer.style.bottom = '-150%';
                bannerContainer.style.opacity = '0';
                bannerContainer.style.visibility = 'hidden';
                placeholderContainer.style.top = '-150%';
                placeholderContainer.style.opacity = '0';
                placeholderContainer.style.visibility = 'hidden';
                robotContainer_el.style.opacity = '0';
                lightBurst.style.opacity = '0';
                lightBurst.style.visibility = 'hidden';
                return; // Exit early, don't run animation phases
            }

            // Phase 1: 0-25% - wake_up → arise → handstand_flip → charged_slam (ALL 4 ANIMATIONS)
            if (scrollProgress < 0.25) {
                const phase1Progress = scrollProgress / 0.25;

                // V35 FIX: Removed currentPhase gating - now uses only phase1Progress thresholds
                if (phase1Progress < 0.25) {
                    if (robotModel) robotModel.rotation.y = THREE.MathUtils.degToRad(30);
                    playAnimation(ANIMATIONS.WAKE_UP);
                } else if (phase1Progress >= 0.25 && phase1Progress < 0.50) {
                    if (robotModel) robotModel.rotation.y = 0;
                    playAnimation(ANIMATIONS.ARISE);
                } else if (phase1Progress >= 0.50 && phase1Progress < 0.75) {
                    playAnimation(ANIMATIONS.HANDSTAND_FLIP);
                } else if (phase1Progress >= 0.75) {
                    // V35 FIX: charged_slam is now 4th animation in Phase 1 (not in Phase 2)
                    playAnimation(ANIMATIONS.CHARGED_SLAM);
                }

                // Hide all images
                bannerContainer.style.bottom = '-150%';
                bannerContainer.style.opacity = '0';
                bannerContainer.style.visibility = 'hidden';
                placeholderContainer.style.top = '-150%';
                placeholderContainer.style.opacity = '0';
                placeholderContainer.style.visibility = 'hidden';
                robotContainer_el.style.opacity = '1';
                headerBanner.style.opacity = '0';
                headerBanner.style.visibility = 'hidden';
                lightBurst.style.opacity = '0';
                lightBurst.style.visibility = 'hidden';
            }

            // Phase 2: 25-50% - Banner slides UP, Placeholder drops DOWN, both come to rest parallel
            // V35 FIX: No robot animation in Phase 2 (charged_slam moved to Phase 1)
            else if (scrollProgress >= 0.25 && scrollProgress < 0.50) {
                const phase2Progress = (scrollProgress - 0.25) / 0.25;

                // Banner slides up from bottom to 50% (halfway up)
                const bannerBottom = -150 + (phase2Progress * 200);
                bannerContainer.style.bottom = `${bannerBottom}%`;
                bannerContainer.style.left = '25%';
                bannerContainer.style.transform = 'translateX(-25%)';
                bannerContainer.style.opacity = '1';
                bannerContainer.style.visibility = 'visible';

                // Placeholder drops down from top to 50% (halfway down)
                const placeholderTop = -150 + (phase2Progress * 200);
                placeholderContainer.style.top = `${placeholderTop}%`;
                placeholderContainer.style.left = '75%';
                placeholderContainer.style.transform = 'translateX(-75%)';
                placeholderContainer.style.opacity = '1';
                placeholderContainer.style.visibility = 'visible';

                robotContainer_el.style.opacity = '1';
                headerBanner.style.opacity = '0';
                headerBanner.style.visibility = 'hidden';
            }

            // Phase 3: 50-70% - Both images merge toward EXACT center with star burst
            else if (scrollProgress >= 0.50 && scrollProgress < 0.70) {
                const phase3Progress = (scrollProgress - 0.50) / 0.20;

                // V35 FIX: Banner converges to EXACT center (both horizontally AND vertically)
                const bannerLeftProgress = 25 + (phase3Progress * 25);  // 25% → 50%
                const bannerTopProgress = 50 + (phase3Progress * 0);    // Stays at 50%
                bannerContainer.style.top = `${bannerTopProgress}%`;
                bannerContainer.style.left = `${bannerLeftProgress}%`;
                bannerContainer.style.bottom = 'auto';  // Override bottom positioning
                bannerContainer.style.transform = 'translate(-50%, -50%)';
                bannerContainer.style.opacity = '1';
                bannerContainer.style.visibility = 'visible';

                // V35 FIX: Placeholder converges to EXACT center (both horizontally AND vertically)
                const placeholderLeftProgress = 75 - (phase3Progress * 25);  // 75% → 50%
                const placeholderTopProgress = 50 - (phase3Progress * 0);    // Stays at 50%
                placeholderContainer.style.top = `${placeholderTopProgress}%`;
                placeholderContainer.style.left = `${placeholderLeftProgress}%`;
                placeholderContainer.style.transform = 'translate(-50%, -50%)';
                placeholderContainer.style.opacity = '1';
                placeholderContainer.style.visibility = 'visible';

                // Light burst intensifies as images approach center
                const burstSize = phase3Progress * 2000;
                const burstOpacity = phase3Progress * 0.8;
                lightBurst.style.width = `${burstSize}px`;
                lightBurst.style.height = `${burstSize}px`;
                lightBurst.style.opacity = Math.min(burstOpacity, 0.8);
                lightBurst.style.visibility = 'visible';

                robotContainer_el.style.opacity = '1';
                headerBanner.style.opacity = '0';
                headerBanner.style.visibility = 'hidden';

                currentPhase = 5;
            }

            // Phase 4: 70-85% - Images disintegrate at center with maximum star burst
            else if (scrollProgress >= 0.70 && scrollProgress < 0.85) {
                const phase4Progress = (scrollProgress - 0.70) / 0.15;

                // V35 FIX: Both images at EXACT center (50%, 50%), fading out (disintegrating)
                bannerContainer.style.top = '50%';
                bannerContainer.style.left = '50%';
                bannerContainer.style.bottom = 'auto';  // Override bottom positioning
                bannerContainer.style.transform = 'translate(-50%, -50%)';
                bannerContainer.style.opacity = `${1 - phase4Progress}`;
                bannerContainer.style.visibility = 'visible';

                placeholderContainer.style.top = '50%';
                placeholderContainer.style.left = '50%';
                placeholderContainer.style.transform = 'translate(-50%, -50%)';
                placeholderContainer.style.opacity = `${1 - phase4Progress}`;
                placeholderContainer.style.visibility = 'visible';

                // Light burst at maximum, then fades
                const burstSize = 2000;
                const burstOpacity = phase4Progress < 0.5 ? 1.0 : (1.0 - ((phase4Progress - 0.5) * 2));
                lightBurst.style.width = `${burstSize}px`;
                lightBurst.style.height = `${burstSize}px`;
                lightBurst.style.opacity = burstOpacity;
                lightBurst.style.visibility = 'visible';

                // Robot starts fading
                robotContainer_el.style.opacity = `${1 - phase4Progress}`;

                // Header banner starts fading in
                headerBanner.style.opacity = `${phase4Progress}`;
                headerBanner.style.visibility = 'visible';

                currentPhase = 6;
            }

            // Phase 5: 85-100% - Header banner fully visible, all else hidden
            else if (scrollProgress >= 0.85) {
                const phase5Progress = (scrollProgress - 0.85) / 0.15;

                // Hide disintegrated images
                bannerContainer.style.opacity = '0';
                bannerContainer.style.visibility = 'hidden';
                placeholderContainer.style.opacity = '0';
                placeholderContainer.style.visibility = 'hidden';

                // Light burst fades out
                lightBurst.style.opacity = '0';
                lightBurst.style.visibility = 'hidden';

                // Robot hidden
                robotContainer_el.style.opacity = '0';

                // Header banner fully visible
                headerBanner.style.opacity = '1';
                headerBanner.style.visibility = 'visible';

                currentPhase = 7;
            }
        }

        // Animation loop
        function animate() {
            requestAnimationFrame(animate);

            if (mixer) {
                mixer.update(clock.getDelta());
            }

            renderer.render(scene, camera);
        }

        console.log('Animation loop started');
        animate();

        // Scroll event
        window.addEventListener('scroll', handleScroll);
        handleScroll();
    </script>

    <!-- Back to Top Button -->
    <div id="jcl-back-to-top" aria-label="Back to top">↑</div>

    <script>
    // Back to Top Button - Vanilla JS
    (function() {
        const backToTopBtn = document.getElementById('jcl-back-to-top');

        if (!backToTopBtn) return;

        // Show/hide button based on scroll position
        function handleScrollButton() {
            const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollPosition > 400) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        }

        // Smooth scroll to top
        function scrollToTop(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Event listeners
        window.addEventListener('scroll', handleScrollButton, { passive: true });
        backToTopBtn.addEventListener('click', scrollToTop);

        // Initial check
        handleScrollButton();
    })();
    </script>
    <?php
}
