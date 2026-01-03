<?php
/**
 * Plugin Name: JCL Complete Site Fix V35
 * Description: FIXED - All 4 animations in Phase 1, images converge to exact center (50%, 50%), login modal popup
 * Version: 35.2
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

        /* Employers Dropdown */
        .employers-corner-link {
            position: fixed !important;
            top: 20px !important;
            right: 30px !important;
            z-index: 99999 !important;
            background: rgba(0, 0, 0, 0.7) !important;
            padding: 8px 16px !important;
            border-radius: 5px !important;
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

        /* REMOVED: Banner CSS per user request */

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

        /* REMOVED: Header Banner CSS per user request */

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

        <!-- REMOVED: Banner elements per user request -->
        <!-- Star Burst -->
        <div id="jcl-light-burst"></div>
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
        // REMOVED: Banner elements per user request
        const bannerContainer = null; // document.getElementById('jcl-banner-container');
        const placeholderContainer = null; // document.getElementById('jcl-placeholder-container');
        const lightBurst = document.getElementById('jcl-light-burst');
        const headerBanner = null; // document.getElementById('jcl-header-banner');

        function handleScroll() {
            const containerRect = scrollContainer.getBoundingClientRect();
            const scrollProgress = Math.max(0, Math.min(1, -containerRect.top / (containerRect.height - window.innerHeight)));

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
                // REMOVED: bannerContainer.style.bottom = '-150%';
                // REMOVED: bannerContainer.style.opacity = '0';
                // REMOVED: bannerContainer.style.visibility = 'hidden';
                // REMOVED: placeholderContainer.style.top = '-150%';
                // REMOVED: placeholderContainer.style.opacity = '0';
                // REMOVED: placeholderContainer.style.visibility = 'hidden';
                robotContainer_el.style.opacity = '1';
                // REMOVED: headerBanner.style.opacity = '0';
                // REMOVED: headerBanner.style.visibility = 'hidden';
                lightBurst.style.opacity = '0';
                lightBurst.style.visibility = 'hidden';
            }

            // Phase 2: 25-50% - Banner slides UP, Placeholder drops DOWN, both come to rest parallel
            // V35 FIX: No robot animation in Phase 2 (charged_slam moved to Phase 1)
            else if (scrollProgress >= 0.25 && scrollProgress < 0.50) {
                const phase2Progress = (scrollProgress - 0.25) / 0.25;

                // Banner slides up from bottom to 50% (halfway up)
                const bannerBottom = -150 + (phase2Progress * 200);
                // REMOVED: bannerContainer.style.bottom = `${bannerBottom}%`;
                // REMOVED: bannerContainer.style.left = '25%';
                // REMOVED: bannerContainer.style.transform = 'translateX(-25%)';
                // REMOVED: bannerContainer.style.opacity = '1';
                // REMOVED: bannerContainer.style.visibility = 'visible';

                // Placeholder drops down from top to 50% (halfway down)
                const placeholderTop = -150 + (phase2Progress * 200);
                // REMOVED: placeholderContainer.style.top = `${placeholderTop}%`;
                // REMOVED: placeholderContainer.style.left = '75%';
                // REMOVED: placeholderContainer.style.transform = 'translateX(-75%)';
                // REMOVED: placeholderContainer.style.opacity = '1';
                // REMOVED: placeholderContainer.style.visibility = 'visible';

                robotContainer_el.style.opacity = '1';
                // REMOVED: headerBanner.style.opacity = '0';
                // REMOVED: headerBanner.style.visibility = 'hidden';
            }

            // Phase 3: 50-70% - Both images merge toward EXACT center with star burst
            else if (scrollProgress >= 0.50 && scrollProgress < 0.70) {
                const phase3Progress = (scrollProgress - 0.50) / 0.20;

                // V35 FIX: Banner converges to EXACT center (both horizontally AND vertically)
                const bannerLeftProgress = 25 + (phase3Progress * 25);  // 25% → 50%
                const bannerTopProgress = 50 + (phase3Progress * 0);    // Stays at 50%
                // REMOVED: bannerContainer.style.top = `${bannerTopProgress}%`;
                // REMOVED: bannerContainer.style.left = `${bannerLeftProgress}%`;
                // REMOVED: bannerContainer.style.bottom = 'auto';  // Override bottom positioning
                // REMOVED: bannerContainer.style.transform = 'translate(-50%, -50%)';
                // REMOVED: bannerContainer.style.opacity = '1';
                // REMOVED: bannerContainer.style.visibility = 'visible';

                // V35 FIX: Placeholder converges to EXACT center (both horizontally AND vertically)
                const placeholderLeftProgress = 75 - (phase3Progress * 25);  // 75% → 50%
                const placeholderTopProgress = 50 - (phase3Progress * 0);    // Stays at 50%
                // REMOVED: placeholderContainer.style.top = `${placeholderTopProgress}%`;
                // REMOVED: placeholderContainer.style.left = `${placeholderLeftProgress}%`;
                // REMOVED: placeholderContainer.style.transform = 'translate(-50%, -50%)';
                // REMOVED: placeholderContainer.style.opacity = '1';
                // REMOVED: placeholderContainer.style.visibility = 'visible';

                // Light burst intensifies as images approach center
                const burstSize = phase3Progress * 2000;
                const burstOpacity = phase3Progress * 0.8;
                lightBurst.style.width = `${burstSize}px`;
                lightBurst.style.height = `${burstSize}px`;
                lightBurst.style.opacity = Math.min(burstOpacity, 0.8);
                lightBurst.style.visibility = 'visible';

                robotContainer_el.style.opacity = '1';
                // REMOVED: headerBanner.style.opacity = '0';
                // REMOVED: headerBanner.style.visibility = 'hidden';

                currentPhase = 5;
            }

            // Phase 4: 70-85% - Images disintegrate at center with maximum star burst
            else if (scrollProgress >= 0.70 && scrollProgress < 0.85) {
                const phase4Progress = (scrollProgress - 0.70) / 0.15;

                // V35 FIX: Both images at EXACT center (50%, 50%), fading out (disintegrating)
                // REMOVED: bannerContainer.style.top = '50%';
                // REMOVED: bannerContainer.style.left = '50%';
                // REMOVED: bannerContainer.style.bottom = 'auto';  // Override bottom positioning
                // REMOVED: bannerContainer.style.transform = 'translate(-50%, -50%)';
                // REMOVED: bannerContainer.style.opacity = `${1 - phase4Progress}`;
                // REMOVED: bannerContainer.style.visibility = 'visible';

                // REMOVED: placeholderContainer.style.top = '50%';
                // REMOVED: placeholderContainer.style.left = '50%';
                // REMOVED: placeholderContainer.style.transform = 'translate(-50%, -50%)';
                // REMOVED: placeholderContainer.style.opacity = `${1 - phase4Progress}`;
                // REMOVED: placeholderContainer.style.visibility = 'visible';

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
                // REMOVED: headerBanner.style.opacity = `${phase4Progress}`;
                // REMOVED: headerBanner.style.visibility = 'visible';

                currentPhase = 6;
            }

            // Phase 5: 85-100% - Header banner fully visible, all else hidden
            else if (scrollProgress >= 0.85) {
                const phase5Progress = (scrollProgress - 0.85) / 0.15;

                // Hide disintegrated images
                // REMOVED: bannerContainer.style.opacity = '0';
                // REMOVED: bannerContainer.style.visibility = 'hidden';
                // REMOVED: placeholderContainer.style.opacity = '0';
                // REMOVED: placeholderContainer.style.visibility = 'hidden';

                // Light burst fades out
                lightBurst.style.opacity = '0';
                lightBurst.style.visibility = 'hidden';

                // Robot hidden
                robotContainer_el.style.opacity = '0';

                // Header banner fully visible
                // REMOVED: headerBanner.style.opacity = '1';
                // REMOVED: headerBanner.style.visibility = 'visible';

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
    <?php
}
