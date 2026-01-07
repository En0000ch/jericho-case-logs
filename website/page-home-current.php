<?php
/**
 * Template Name: Home Page Minimal - Jericho Case Logs
 * Description: Absolutely minimal home page with ZERO transitions or transforms
 */

get_header();
?>

<style>
    /* ============================================
       CSS VARIABLES - UI Safe Zones & Spacing
       ============================================ */
    :root {
        --ui-safe-top-right: 90px;
        --ui-safe-bottom-right: 90px;
        --mascot-lane-width: 120px;
        --content-max-width: calc(100vw - var(--mascot-lane-width));
    }

    /* Minimal styles - NO transitions, NO transforms, NO animations */

    /* HERO SECTION - Maximum visual hierarchy */
    .jcl-hero {
        background: rgba(46, 50, 65, 0.75);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        color: #e0fbfc;
        padding: 60px 40px;
        text-align: center;
        border-bottom: 2px solid rgba(238, 108, 77, 0);
        margin-bottom: 60px;
        position: relative;
        max-width: var(--content-max-width);
        margin-left: auto;
        margin-right: auto;
    }
    /* Add subtle inner shadow for depth without hard overlay */
    .jcl-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.15);
        border-radius: inherit;
        pointer-events: none;
    }
    .jcl-hero > * {
        position: relative;
        z-index: 1;
    }
    .jcl-hero h1 {
        font-size: 42px;
        margin: 0 0 20px 0;
        font-weight: 700;
        color: #ffffff;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4), 0 0 20px rgba(224, 251, 252, 0.3);
        letter-spacing: -0.5px;
    }
    .jcl-hero p {
        font-size: 20px;
        margin: 0;
        opacity: 1;
        color: #e0fbfc;
        font-weight: 400;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
    }

    /* SCREENSHOT SHOWCASE - Improved contrast with readability vignette */
    .jcl-screenshot-showcase {
        background: rgba(46, 50, 65, 0);
        padding: 60px 40px;
        border-radius: 12px;
        margin: 60px 0;
        border: 1px solid rgba(238, 108, 77, 0);
        position: relative;
        max-width: var(--content-max-width);
        margin-left: auto;
        margin-right: auto;
    }
    /* Readability vignette - soft gradient to reduce background competition */
    .jcl-screenshot-showcase::before {
        content: '';
        position: absolute;
        inset: -100px;
        background: radial-gradient(ellipse at center, rgba(20, 25, 35, 0.7) 0%, transparent 70%);
        pointer-events: none;
        z-index: 0;
        filter: blur(40px);
    }
    .jcl-screenshot-showcase > * {
        position: relative;
        z-index: 1;
    }
    .jcl-screenshot-showcase h2 {
        text-align: center;
        color: #ffffff;
        font-size: 36px;
        margin-bottom: 50px;
        font-weight: 600;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4), 0 0 15px rgba(238, 108, 77, 0.2);
    }
    .jcl-screenshot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }
    /* Glass cards - IMPROVED CONTRAST */
    .jcl-screenshot-card {
        background: rgba(57, 62, 80, 0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        border: 1px solid rgba(238, 108, 77, 0.1);
        position: relative;
    }
    /* Darker overlay layer for better text contrast */
    .jcl-screenshot-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.12);
        border-radius: inherit;
        pointer-events: none;
    }
    .jcl-screenshot-card > * {
        position: relative;
        z-index: 1;
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
        border: 2px dashed rgba(238, 108, 77, 0);
    }
    .jcl-screenshot-card h4 {
        color: #ff8b6d;
        font-size: 19px;
        margin: 0 0 10px 0;
        font-weight: 600;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
    }
    .jcl-screenshot-card p {
        color: #d0e5e6;
        font-size: 15px;
        line-height: 1.6;
        font-weight: 400;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    /* Download buttons - NO transitions */
    .jcl-download-section {
        text-align: center;
        padding: 40px 20px;
        margin: 40px 0;
    }
    .jcl-download-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-top: 25px;
        flex-wrap: wrap;
    }
    .jcl-download-button {
        display: inline-block;
    }
    .jcl-download-button img {
        height: 60px;
        width: auto;
        display: block;
    }
    .jcl-download-button.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* FEATURES SECTION - Improved glass cards with better readability */
    .jcl-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin: 60px 0;
        position: relative;
        max-width: var(--content-max-width);
        margin-left: auto;
        margin-right: auto;
    }
    /* Readability vignette for feature sections */
    .jcl-features::before {
        content: '';
        position: absolute;
        inset: -120px;
        background: radial-gradient(ellipse at center, rgba(20, 25, 35, 0.65) 0%, transparent 65%);
        pointer-events: none;
        z-index: 0;
        filter: blur(50px);
    }
    .jcl-features > * {
        position: relative;
        z-index: 1;
    }
    /* Feature cards - ENHANCED CONTRAST */
    .jcl-feature-card {
        background: rgba(57, 62, 80, 0.68);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 35px;
        border-radius: 12px;
        border: 1px solid rgba(238, 108, 77, 0.1);
        position: relative;
    }
    /* Subtle inner overlay for better text contrast */
    .jcl-feature-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.15);
        border-radius: inherit;
        pointer-events: none;
    }
    .jcl-feature-card > * {
        position: relative;
        z-index: 1;
    }
    .jcl-feature-card h3 {
        color: #ff8b6d;
        font-size: 23px;
        margin: 0 0 14px 0;
        font-weight: 600;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
    }
    .jcl-feature-card p {
        color: #d0e5e6;
        line-height: 1.7;
        font-size: 15px;
        font-weight: 400;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }
    .jcl-feature-icon {
        font-size: 42px;
        margin-bottom: 15px;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    }

    /* SECTION TITLES - Consistent hierarchy */
    .jcl-section-title {
        text-align: center;
        font-size: 38px;
        color: #ffffff;
        margin: 80px 0 50px 0;
        font-weight: 600;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5), 0 0 20px rgba(238, 108, 77, 0.25);
        letter-spacing: -0.5px;
        position: relative;
        z-index: 10;
    }

    /* PROFESSIONAL TYPES SECTION - Enhanced glass cards */
    .jcl-professionals {
        background: rgba(46, 50, 65, 0);
        padding: 60px 40px;
        border-radius: 12px;
        margin: 60px 0;
        border: 1px solid rgba(238, 108, 77, 0);
        position: relative;
        max-width: var(--content-max-width);
        margin-left: auto;
        margin-right: auto;
    }
    /* Readability vignette */
    .jcl-professionals::before {
        content: '';
        position: absolute;
        inset: -100px;
        background: radial-gradient(ellipse at center, rgba(20, 25, 35, 0.7) 0%, transparent 70%);
        pointer-events: none;
        z-index: 0;
        filter: blur(40px);
    }
    .jcl-professionals > * {
        position: relative;
        z-index: 1;
    }
    .jcl-professionals h2 {
        text-align: center;
        color: #ffffff;
        font-size: 36px;
        margin-bottom: 50px;
        font-weight: 600;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4), 0 0 15px rgba(238, 108, 77, 0.2);
    }
    .jcl-professional-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }
    /* Professional cards - IMPROVED CONTRAST */
    .jcl-professional-card {
        background: rgba(57, 62, 80, 0.75);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        border: 1px solid rgba(238, 108, 77, 0.1);
        position: relative;
    }
    .jcl-professional-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.12);
        border-radius: inherit;
        pointer-events: none;
    }
    .jcl-professional-card > * {
        position: relative;
        z-index: 1;
    }
    .jcl-professional-card h4 {
        color: #ff8b6d;
        font-size: 21px;
        margin: 15px 0 10px 0;
        font-weight: 600;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
    }
    .jcl-professional-card p {
        color: #d0e5e6;
        font-size: 15px;
        font-weight: 400;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }
    .jcl-professional-card .jcl-status {
        font-size: 13px;
        font-weight: bold;
        padding: 7px 16px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 12px;
        text-shadow: none;
    }
    .jcl-status.available { background: rgba(76, 175, 80, 0.25); color: #66FF70; border: 1px solid #4CAF50; }
    .jcl-status.coming-soon { background: rgba(255, 193, 7, 0.25); color: #FFD54F; border: 1px solid #FFC107; }

    /* ============================================
       ENHANCED CTA SECTION - Maximum prominence
       ============================================ */
    .jcl-download-section {
        position: relative;
        margin-top: 100px !important;
        margin-bottom: 80px;
        padding: 80px 50px !important;
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
    }
    /* Spotlight effect behind CTA */
    .jcl-download-section::before {
        content: '';
        position: absolute;
        inset: -150px;
        background: radial-gradient(ellipse at center, rgba(238, 108, 77, 0.15) 0%, rgba(20, 25, 35, 0.8) 50%, transparent 75%);
        pointer-events: none;
        z-index: 0;
        filter: blur(60px);
    }
    /* Card-like container for CTA */
    .jcl-download-section::after {
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
    .jcl-download-section > * {
        position: relative;
        z-index: 1;
    }
    .jcl-download-section h2 {
        color: #ffffff !important;
        font-size: 36px !important;
        margin-bottom: 20px !important;
        font-weight: 700 !important;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5), 0 0 25px rgba(238, 108, 77, 0.3) !important;
    }
    .jcl-download-section p {
        color: #e0fbfc !important;
        margin-bottom: 35px !important;
        font-size: 18px !important;
        font-weight: 400 !important;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5) !important;
    }
    .jcl-download-buttons {
        gap: 30px !important;
        margin-top: 35px !important;
    }
    /* Make badges slightly larger on desktop */
    .jcl-download-button img {
        height: 70px !important;
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.4));
    }
    .jcl-download-button:hover img {
        filter: drop-shadow(0 6px 12px rgba(238, 108, 77, 0.4));
    }

    /* ============================================
       BACK-TO-TOP BUTTON - Safe zone positioning
       ============================================ */
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
    }
    #jcl-back-to-top.visible {
        display: flex;
        opacity: 1;
    }
    #jcl-back-to-top:hover {
        background: rgba(238, 108, 77, 1);
        box-shadow: 0 6px 16px rgba(238, 108, 77, 0.5);
    }

    /* ============================================
       MOBILE RESPONSIVE - Maintain improvements
       ============================================ */
    @media (max-width: 768px) {
        :root {
            --mascot-lane-width: 0px;
            --ui-safe-top-right: 20px;
            --ui-safe-bottom-right: 20px;
        }
        .jcl-hero {
            padding: 40px 30px;
        }
        .jcl-hero h1 {
            font-size: 28px;
        }
        .jcl-hero p {
            font-size: 17px;
        }
        .jcl-section-title {
            font-size: 28px;
            margin: 60px 0 35px 0;
        }
        .jcl-screenshot-showcase h2,
        .jcl-professionals h2 {
            font-size: 28px;
        }
        .jcl-features {
            grid-template-columns: 1fr;
        }
        .jcl-download-section {
            padding: 50px 30px !important;
        }
        .jcl-download-section h2 {
            font-size: 28px !important;
        }
        .jcl-download-buttons {
            flex-direction: column;
            align-items: center;
            gap: 20px !important;
        }
        .jcl-download-button img {
            height: 60px !important;
        }
        .jcl-screenshot-grid {
            grid-template-columns: 1fr;
        }
        #jcl-back-to-top {
            right: 20px;
            width: 45px;
            height: 45px;
            font-size: 20px;
        }
    }

    /* Ensure all vignettes work on mobile */
    @media (max-width: 480px) {
        .jcl-screenshot-showcase::before,
        .jcl-professionals::before,
        .jcl-features::before {
            inset: -50px;
            filter: blur(30px);
        }
        .jcl-download-section::before {
            inset: -80px;
            filter: blur(40px);
        }
    }
</style>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <!-- Subtle Hero Section -->
        <div class="jcl-hero">
            <h1>Professional Case Logging for Healthcare Providers</h1>
            <p>Track your clinical experience • Generate reports • Find opportunities</p>
        </div>

        <!-- Screenshot Showcase -->
        <div class="jcl-screenshot-showcase">
            <h2>See the App in Action</h2>
            <div class="jcl-screenshot-grid">
                <div class="jcl-screenshot-card">
                    <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/17_logs.png" alt="Case Logging" style="width: 100%; height: auto; border-radius: 8px; margin-bottom: 15px;" />
                    <h4>Quick Case Entry</h4>
                    <p>Smart forms designed for medical professionals</p>
                </div>
                <div class="jcl-screenshot-card">
                    <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/17_charts.png" alt="Visual Analytics" style="width: 100%; height: auto; border-radius: 8px; margin-bottom: 15px;" />
                    <h4>Visual Analytics</h4>
                    <p>Beautiful charts showing your experience</p>
                </div>
                <div class="jcl-screenshot-card">
                    <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/17_reports.png" alt="Professional Reports" style="width: 100%; height: auto; border-radius: 8px; margin-bottom: 15px;" />
                    <h4>Professional Reports</h4>
                    <p>Generate PDFs for credentialing</p>
                </div>
                <div class="jcl-screenshot-card">
                    <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/17_case.png" alt="Case Management" style="width: 100%; height: auto; border-radius: 8px; margin-bottom: 15px;" />
                    <h4>Case Management</h4>
                    <p>View and organize all your logged cases</p>
                </div>
                <div class="jcl-screenshot-card">
                    <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/17_calendar.png" alt="Calendar View" style="width: 100%; height: auto; border-radius: 8px; margin-bottom: 15px;" />
                    <h4>Calendar Integration</h4>
                    <p>Track your schedule and assignments</p>
                </div>
                <div class="jcl-screenshot-card">
                    <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/17_surgeries.png" alt="Surgery Tracking" style="width: 100%; height: auto; border-radius: 8px; margin-bottom: 15px;" />
                    <h4>Surgery Database</h4>
                    <p>Manage and track surgical procedures</p>
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
                    <div class="jcl-feature-icon"><img src="https://www.jerichocaselogs.com/wp-content/uploads/2026/01/stethoscope-48.png" alt="Nurses" style="width: 48px; height: 48px;"></div>
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
        <div class="jcl-download-section" style="margin-top: 80px;">
            <h2 style="color: #e0fbfc; font-size: 28px; margin-bottom: 15px;">Ready to Start Logging?</h2>
            <p style="color: #95b0b1; margin-bottom: 20px;">Download now and take control of your professional development</p>
            <div class="jcl-download-buttons">
                <a href="https://apps.apple.com/us/app/jericho-case-logs/id6466726836" target="_blank" class="jcl-download-button">
                    <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/apple-app-store.webp" alt="Get Started on iOS" />
                </a>
                <a href="#" class="jcl-download-button disabled" onclick="return false;">
                    <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/google-play-1-300x93-1.webp" alt="Get it on Google Play (Coming Soon)" />
                </a>
            </div>
        </div>

    </main>
</div>

<!-- Back to Top Button -->
<div id="jcl-back-to-top" aria-label="Back to top">↑</div>

<script>
// Back to Top Button - Vanilla JS
(function() {
    const backToTopBtn = document.getElementById('jcl-back-to-top');

    if (!backToTopBtn) return;

    // Show/hide button based on scroll position
    function handleScroll() {
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
    window.addEventListener('scroll', handleScroll, { passive: true });
    backToTopBtn.addEventListener('click', scrollToTop);

    // Initial check
    handleScroll();
})();
</script>

<?php
get_footer();
?>
