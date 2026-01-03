<?php
/**
 * Template Name: Home Page with Stars - Jericho Case Logs
 * Description: Full animated homepage with shooting stars background for desktop/tablet, static for mobile
 */

get_header();
?>

<script>
// Add shooting stars to body after page loads
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth > 768) {  // Only on desktop/tablet
        const starsContainer = document.createElement('div');
        starsContainer.className = 'jcl-stars-background';
        starsContainer.setAttribute('aria-hidden', 'true');

        // Create 6 shooting stars
        for (let i = 0; i < 6; i++) {
            const star = document.createElement('div');
            star.className = 'shooting-star';
            starsContainer.appendChild(star);
        }

        // Add to body as first child
        document.body.insertBefore(starsContainer, document.body.firstChild);
    }
});
</script>

<style>
    /* Force dark background on body */
    body {
        background-color: #2e3241 !important;
        background-image: none !important;
        position: relative;
        overflow-x: hidden;
    }

    /* SHOOTING STARS BACKGROUND - Only on desktop/tablet */
    .jcl-stars-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 999999;
        overflow: hidden;
        background: transparent;
    }

    .shooting-star {
        position: absolute;
        width: 2px;
        height: 2px;
        background: #e0fbfc;
        border-radius: 50%;
        box-shadow: 0 0 10px 2px rgba(224, 251, 252, 0.8);
        animation: shootingStar 3s linear infinite;
    }

    .shooting-star::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 2px;
        background: linear-gradient(to left, rgba(224, 251, 252, 0.8), transparent);
    }

    /* Create multiple shooting stars with different delays and positions */
    .shooting-star:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
    .shooting-star:nth-child(2) { top: 30%; left: 50%; animation-delay: 1.5s; }
    .shooting-star:nth-child(3) { top: 50%; left: 80%; animation-delay: 3s; }
    .shooting-star:nth-child(4) { top: 70%; left: 20%; animation-delay: 4.5s; }
    .shooting-star:nth-child(5) { top: 20%; left: 70%; animation-delay: 2s; }
    .shooting-star:nth-child(6) { top: 60%; left: 40%; animation-delay: 5s; }

    @keyframes shootingStar {
        0% {
            transform: translate(0, 0) rotate(-45deg);
            opacity: 1;
        }
        70% {
            opacity: 1;
        }
        100% {
            transform: translate(300px, 300px) rotate(-45deg);
            opacity: 0;
        }
    }

    /* Twinkling stars background */
    .twinkle-star {
        position: absolute;
        width: 2px;
        height: 2px;
        background: rgba(224, 251, 252, 0.6);
        border-radius: 50%;
        animation: twinkle 2s ease-in-out infinite;
    }

    @keyframes twinkle {
        0%, 100% { opacity: 0.3; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.5); }
    }

    /* Main content with higher z-index than stars */
    #primary,
    .site-header,
    .site-footer,
    header,
    footer,
    nav {
        position: relative;
        z-index: 1000000;
    }

    /* Hero Section with animated glow */
    .jcl-hero {
        background: rgba(46, 50, 65, 0.8);
        color: #e0fbfc;
        padding: 60px 40px;
        text-align: center;
        border-bottom: 2px solid rgba(238, 108, 77, 0.3);
        margin-bottom: 60px;
        position: relative;
        overflow: hidden;
    }

    .jcl-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(238, 108, 77, 0.1) 0%, transparent 70%);
        animation: heroGlow 8s ease-in-out infinite;
    }

    @keyframes heroGlow {
        0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.3; }
        50% { transform: translate(10%, 10%) scale(1.1); opacity: 0.6; }
    }

    .jcl-hero h1, .jcl-hero p {
        position: relative;
        z-index: 1;
    }

    .jcl-hero h1 {
        font-size: 36px;
        margin: 0 0 20px 0;
        font-weight: 600;
        color: #e0fbfc;
        animation: fadeInUp 1s ease-out;
    }

    .jcl-hero p {
        font-size: 20px;
        margin: 0;
        color: #95b0b1;
        animation: fadeInUp 1s ease-out 0.2s both;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Screenshot showcase with floating particles */
    .jcl-screenshot-showcase {
        background: rgba(46, 50, 65, 0.6);
        padding: 80px 40px;
        border-radius: 12px;
        margin: 60px 20px;
        position: relative;
        overflow: hidden;
    }

    .jcl-screenshot-showcase::before {
        content: '';
        position: absolute;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(238, 108, 77, 0.15) 0%, transparent 70%);
        border-radius: 50%;
        animation: particleFloat 15s ease-in-out infinite;
        top: 10%;
        left: 10%;
    }

    .jcl-screenshot-showcase::after {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(224, 251, 252, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        animation: particleFloat 20s ease-in-out infinite reverse;
        bottom: 10%;
        right: 10%;
    }

    @keyframes particleFloat {
        0%, 100% { transform: translate(0, 0); }
        33% { transform: translate(50px, -50px); }
        66% { transform: translate(-30px, 30px); }
    }

    .jcl-screenshot-showcase h2 {
        text-align: center;
        color: #e0fbfc;
        font-size: 32px;
        margin-bottom: 50px;
        position: relative;
        z-index: 1;
        animation: fadeInUp 0.8s ease-out;
    }

    .jcl-screenshot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .jcl-screenshot-card {
        background: rgba(57, 62, 80, 0.8);
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        border: 1px solid rgba(238, 108, 77, 0.3);
        position: relative;
        animation: floatIn 1s ease-out both, gentleFloat 6s ease-in-out infinite;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .jcl-screenshot-card:nth-child(1) {
        animation: floatIn 1s ease-out 0.1s both, gentleFloat 6s ease-in-out 0s infinite;
    }
    .jcl-screenshot-card:nth-child(2) {
        animation: floatIn 1s ease-out 0.3s both, gentleFloat 6s ease-in-out 2s infinite;
    }
    .jcl-screenshot-card:nth-child(3) {
        animation: floatIn 1s ease-out 0.5s both, gentleFloat 6s ease-in-out 4s infinite;
    }

    @keyframes floatIn {
        from { opacity: 0; transform: translateY(50px) scale(0.9); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @keyframes gentleFloat {
        0%, 100% { transform: translateY(0px) scale(1); }
        50% { transform: translateY(-15px) scale(1.02); }
    }

    .jcl-screenshot-card:hover {
        transform: translateY(-10px) scale(1.03);
        box-shadow: 0 15px 40px rgba(238, 108, 77, 0.3);
    }

    .jcl-screenshot-placeholder {
        background: rgba(149, 176, 177, 0.1);
        height: 400px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #95b0b1;
        font-size: 14px;
        margin-bottom: 15px;
        border: 2px dashed rgba(238, 108, 77, 0.3);
    }

    .jcl-screenshot-card h4 {
        color: #ee6c4d;
        font-size: 20px;
        margin: 0 0 10px 0;
    }

    .jcl-screenshot-card p {
        color: #95b0b1;
        font-size: 15px;
        line-height: 1.6;
    }

    /* Professional types section */
    .jcl-professionals {
        background: rgba(46, 50, 65, 0.6);
        padding: 60px 40px;
        border-radius: 12px;
        margin: 60px 20px;
        border: 1px solid rgba(238, 108, 77, 0.2);
    }

    .jcl-professionals h2 {
        text-align: center;
        color: #e0fbfc;
        font-size: 32px;
        margin-bottom: 50px;
        animation: fadeInUp 0.8s ease-out;
    }

    .jcl-professional-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .jcl-professional-card {
        background: rgba(57, 62, 80, 0.8);
        padding: 35px;
        border-radius: 12px;
        text-align: center;
        border: 1px solid rgba(238, 108, 77, 0.2);
        animation: gentleFloat 7s ease-in-out infinite;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .jcl-professional-card:nth-child(2) { animation-delay: 1.5s; }
    .jcl-professional-card:nth-child(3) { animation-delay: 3s; }
    .jcl-professional-card:nth-child(4) { animation-delay: 4.5s; }

    .jcl-professional-card:hover {
        transform: translateY(-10px) scale(1.05);
        box-shadow: 0 15px 40px rgba(238, 108, 77, 0.3);
    }

    .jcl-professional-card h4 {
        color: #ee6c4d;
        font-size: 22px;
        margin: 15px 0 10px 0;
    }

    .jcl-professional-card p {
        color: #95b0b1;
        font-size: 15px;
    }

    .jcl-feature-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .jcl-status {
        font-size: 13px;
        font-weight: bold;
        padding: 6px 14px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 12px;
    }
    .jcl-status.available { background: rgba(76, 175, 80, 0.2); color: #4CAF50; border: 1px solid #4CAF50; }
    .jcl-status.coming-soon { background: rgba(255, 193, 7, 0.2); color: #FFC107; border: 1px solid #FFC107; }

    /* Features section */
    .jcl-section-title {
        text-align: center;
        font-size: 32px;
        color: #e0fbfc;
        margin: 80px 0 50px 0;
        animation: fadeInUp 0.8s ease-out;
    }

    .jcl-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 35px;
        margin: 60px 20px;
    }

    .jcl-feature-card {
        background: rgba(57, 62, 80, 0.6);
        padding: 40px;
        border-radius: 12px;
        border: 1px solid rgba(238, 108, 77, 0.2);
        animation: gentleFloat 8s ease-in-out infinite;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .jcl-feature-card:nth-child(2n) { animation-delay: 1s; }
    .jcl-feature-card:nth-child(3n) { animation-delay: 2s; }
    .jcl-feature-card:nth-child(5n) { animation-delay: 3s; }

    .jcl-feature-card:hover {
        transform: translateY(-8px) scale(1.03);
        box-shadow: 0 12px 35px rgba(238, 108, 77, 0.25);
    }

    .jcl-feature-card h3 {
        color: #ee6c4d;
        font-size: 22px;
        margin: 0 0 15px 0;
    }

    .jcl-feature-card p {
        color: #95b0b1;
        line-height: 1.7;
        font-size: 15px;
    }

    /* Download section */
    .jcl-download-section {
        text-align: center;
        padding: 60px 40px;
        margin: 80px 20px;
        background: rgba(46, 50, 65, 0.6);
        border-radius: 12px;
        border: 1px solid rgba(238, 108, 77, 0.2);
    }

    .jcl-download-section h2 {
        color: #e0fbfc;
        font-size: 32px;
        margin-bottom: 20px;
    }

    .jcl-download-section p {
        color: #95b0b1;
        font-size: 18px;
        margin-bottom: 30px;
    }

    .jcl-download-buttons {
        display: flex;
        gap: 25px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .jcl-download-button {
        display: inline-block;
        transition: transform 0.3s ease;
    }

    .jcl-download-button:hover {
        transform: scale(1.1);
    }

    .jcl-download-button img {
        height: 65px;
        width: auto;
        display: block;
    }

    .jcl-download-button.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .jcl-download-button.disabled:hover {
        transform: scale(1);
    }

    /* MOBILE: Disable ALL animations and effects */
    @media (max-width: 768px) {
        /* Hide stars completely on mobile */
        .jcl-stars-container,
        .shooting-star,
        .twinkle-star {
            display: none !important;
        }

        /* Remove all animations */
        *,
        *::before,
        *::after {
            animation: none !important;
            transition: none !important;
        }

        /* Simplify layouts */
        .jcl-hero {
            padding: 40px 20px;
        }

        .jcl-hero h1 { font-size: 28px; }
        .jcl-hero p { font-size: 18px; }

        .jcl-screenshot-showcase,
        .jcl-professionals,
        .jcl-download-section {
            padding: 40px 20px;
            margin: 40px 10px;
        }

        .jcl-section-title { font-size: 26px; margin: 50px 0 30px 0; }

        .jcl-features {
            grid-template-columns: 1fr;
            margin: 40px 10px;
        }

        .jcl-screenshot-grid,
        .jcl-professional-grid {
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .jcl-download-buttons {
            flex-direction: column;
            align-items: center;
        }

        /* Remove hover effects on mobile */
        .jcl-screenshot-card:hover,
        .jcl-professional-card:hover,
        .jcl-feature-card:hover,
        .jcl-download-button:hover {
            transform: none;
            box-shadow: none;
        }
    }
</style>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <!-- Hero Section -->
        <div class="jcl-hero">
            <h1>Professional Case Logging for Healthcare Providers</h1>
            <p>Track your clinical experience • Generate reports • Find opportunities</p>
        </div>

        <!-- Screenshot Showcase -->
        <div class="jcl-screenshot-showcase">
            <h2>See the App in Action</h2>
            <div class="jcl-screenshot-grid">
                <div class="jcl-screenshot-card">
                    <div class="jcl-screenshot-placeholder">
                        [Screenshot: Case Logging]
                    </div>
                    <h4>Quick Case Entry</h4>
                    <p>Smart forms designed for medical professionals</p>
                </div>
                <div class="jcl-screenshot-card">
                    <div class="jcl-screenshot-placeholder">
                        [Screenshot: Analytics]
                    </div>
                    <h4>Visual Analytics</h4>
                    <p>Beautiful charts showing your experience</p>
                </div>
                <div class="jcl-screenshot-card">
                    <div class="jcl-screenshot-placeholder">
                        [Screenshot: Reports]
                    </div>
                    <h4>Professional Reports</h4>
                    <p>Generate PDFs for credentialing</p>
                </div>
            </div>
        </div>

        <!-- Who It's For Section -->
        <div class="jcl-professionals">
            <h2>Built for Healthcare Professionals</h2>
            <div class="jcl-professional-grid">
                <div class="jcl-professional-card">
                    <div class="jcl-feature-icon">💉</div>
                    <h4>CRNAs & Anesthesia</h4>
                    <p>Comprehensive anesthetic case tracking</p>
                    <span class="jcl-status available">✓ Available</span>
                </div>
                <div class="jcl-professional-card">
                    <div class="jcl-feature-icon">🩺</div>
                    <h4>Nurses</h4>
                    <p>Build your professional portfolio</p>
                    <span class="jcl-status available">✓ Available</span>
                </div>
                <div class="jcl-professional-card">
                    <div class="jcl-feature-icon">⚕️</div>
                    <h4>Scrub Techs</h4>
                    <p>Document surgical experience</p>
                    <span class="jcl-status coming-soon">Coming Soon</span>
                </div>
                <div class="jcl-professional-card">
                    <div class="jcl-feature-icon">👨‍⚕️</div>
                    <h4>Physicians</h4>
                    <p>Manage clinical documentation</p>
                    <span class="jcl-status coming-soon">Coming Soon</span>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <h2 class="jcl-section-title">Key Features</h2>

        <div class="jcl-features">
            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">📊</div>
                <h3>Smart Case Logging</h3>
                <p>Quick documentation with intelligent forms designed for medical professionals. Track procedures, demographics, and outcomes efficiently.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">📈</div>
                <h3>Visual Analytics</h3>
                <p>See your experience at a glance with charts and graphs. Generate professional reports for credentialing and performance reviews.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">🔍</div>
                <h3>Job Search</h3>
                <p>Discover travel nursing, CRNA, and locum opportunities. Filter by location, pay, and facility type.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">📅</div>
                <h3>Calendar Management</h3>
                <p>Track your schedule and assignments. Sync with your device calendar and never miss a shift.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">🏥</div>
                <h3>Facility Database</h3>
                <p>Build your own database of facilities and surgeons you've worked with. Quickly add them to new cases.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">🔒</div>
                <h3>HIPAA Compliant</h3>
                <p>Your data is encrypted and secure. We never store identifiable patient information.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">📄</div>
                <h3>Export Reports</h3>
                <p>Generate professional PDF reports for credentialing, CME submissions, or performance reviews.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">🎯</div>
                <h3>Skill Tracking</h3>
                <p>Monitor your progress across procedures and techniques. Set goals and watch your expertise grow.</p>
            </div>
        </div>

        <!-- Benefits Section -->
        <h2 class="jcl-section-title">Why Healthcare Professionals Choose Us</h2>

        <div class="jcl-features">
            <div class="jcl-feature-card">
                <h3>🎓 Perfect for Credentialing</h3>
                <p>Generate reports that meet hospital and agency requirements. Show your experience with professional case logs.</p>
            </div>

            <div class="jcl-feature-card">
                <h3>⏱️ Save Time</h3>
                <p>Stop using spreadsheets. Our smart forms and auto-fill features cut documentation time in half.</p>
            </div>

            <div class="jcl-feature-card">
                <h3>💼 Advance Your Career</h3>
                <p>Track growth, discover opportunities, and showcase skills with comprehensive analytics.</p>
            </div>

            <div class="jcl-feature-card">
                <h3>🌟 Built by Professionals</h3>
                <p>Designed with input from CRNAs and nurses who understand what you need to succeed.</p>
            </div>
        </div>

        <!-- Final CTA -->
        <div class="jcl-download-section">
            <h2>Ready to Start Logging?</h2>
            <p>Download now and take control of your professional development</p>
            <div class="jcl-download-buttons">
                <a href="https://apps.apple.com/us/app/jericho-case-logs/id6466726836" target="_blank" class="jcl-download-button">
                    <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/apple-app-store.webp" alt="Download on the App Store" />
                </a>
                <a href="#" class="jcl-download-button disabled" onclick="return false;">
                    <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/google-play-1-300x93-1.webp" alt="Get it on Google Play (Coming Soon)" />
                </a>
            </div>
        </div>

    </main>
</div>

<?php
get_footer();
?>
