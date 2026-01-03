<?php
/**
 * Template Name: Home Page Minimal - Jericho Case Logs
 * Description: Absolutely minimal home page with ZERO transitions or transforms
 */

get_header();
?>

<style>
    /* Minimal styles - NO transitions, NO transforms, NO animations */

    .jcl-hero {
        background: rgba(46, 50, 65, 0.6);
        color: #e0fbfc;
        padding: 40px 40px;
        text-align: center;
        border-bottom: 2px solid rgba(238, 108, 77, 0.3);
        margin-bottom: 60px;
    }
    .jcl-hero h1 {
        font-size: 32px;
        margin: 0 0 15px 0;
        font-weight: 600;
        color: #e0fbfc;
    }
    .jcl-hero p {
        font-size: 18px;
        margin: 0;
        opacity: 0.85;
        color: #95b0b1;
    }

    /* Screenshot showcase section */
    .jcl-screenshot-showcase {
        background: rgba(46, 50, 65, 0.4);
        padding: 60px 40px;
        border-radius: 12px;
        margin: 60px 0;
    }
    .jcl-screenshot-showcase h2 {
        text-align: center;
        color: #e0fbfc;
        font-size: 30px;
        margin-bottom: 40px;
    }
    .jcl-screenshot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }
    .jcl-screenshot-card {
        background: rgba(57, 62, 80, 0.6);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        animation: gentleFloat 6s ease-in-out infinite;
    }
    .jcl-screenshot-card:nth-child(1) { animation-delay: 0s; }
    .jcl-screenshot-card:nth-child(2) { animation-delay: 1s; }
    .jcl-screenshot-card:nth-child(3) { animation-delay: 2s; }
    .jcl-screenshot-card:nth-child(4) { animation-delay: 3s; }
    .jcl-screenshot-card:nth-child(5) { animation-delay: 4s; }
    .jcl-screenshot-card:nth-child(6) { animation-delay: 5s; }
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
        font-size: 18px;
        margin: 0 0 8px 0;
    }
    .jcl-screenshot-card p {
        color: #95b0b1;
        font-size: 14px;
        line-height: 1.5;
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

    /* Features section - NO transitions */
    .jcl-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin: 60px 0;
    }
    .jcl-feature-card {
        background: rgba(57, 62, 80, 0.4);
        padding: 35px;
        border-radius: 12px;
        border: 1px solid rgba(238, 108, 77, 0.2);
        animation: gentleFloat 6s ease-in-out infinite;
    }
    .jcl-feature-card:nth-child(1) { animation-delay: 0s; }
    .jcl-feature-card:nth-child(2) { animation-delay: 0.5s; }
    .jcl-feature-card:nth-child(3) { animation-delay: 1s; }
    .jcl-feature-card:nth-child(4) { animation-delay: 1.5s; }
    .jcl-feature-card:nth-child(5) { animation-delay: 2s; }
    .jcl-feature-card:nth-child(6) { animation-delay: 2.5s; }
    .jcl-feature-card:nth-child(7) { animation-delay: 3s; }
    .jcl-feature-card:nth-child(8) { animation-delay: 3.5s; }
    .jcl-feature-card h3 {
        color: #ee6c4d;
        font-size: 22px;
        margin: 0 0 12px 0;
    }
    .jcl-feature-card p {
        color: #95b0b1;
        line-height: 1.6;
        font-size: 15px;
    }
    .jcl-feature-icon {
        font-size: 42px;
        margin-bottom: 15px;
    }

    .jcl-section-title {
        text-align: center;
        font-size: 32px;
        color: #e0fbfc;
        margin: 60px 0 40px 0;
    }

    /* Professional types section */
    .jcl-professionals {
        background: rgba(46, 50, 65, 0.4);
        padding: 60px 40px;
        border-radius: 12px;
        margin: 60px 0;
    }
    .jcl-professionals h2 {
        text-align: center;
        color: #e0fbfc;
        font-size: 30px;
        margin-bottom: 40px;
    }
    .jcl-professional-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }
    .jcl-professional-card {
        background: rgba(57, 62, 80, 0.6);
        padding: 30px;
        border-radius: 8px;
        text-align: center;
    }
    .jcl-professional-card h4 {
        color: #ee6c4d;
        font-size: 20px;
        margin: 15px 0 10px 0;
    }
    .jcl-professional-card p {
        color: #95b0b1;
        font-size: 14px;
    }
    .jcl-professional-card .jcl-status {
        font-size: 13px;
        font-weight: bold;
        padding: 6px 14px;
        border-radius: 20px;
        display: inline-block;
        margin-top: 10px;
    }
    .jcl-status.available { background: rgba(76, 175, 80, 0.2); color: #4CAF50; border: 1px solid #4CAF50; }
    .jcl-status.coming-soon { background: rgba(255, 193, 7, 0.2); color: #FFC107; border: 1px solid #FFC107; }

    /* Floating animation */
    @keyframes gentleFloat {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .jcl-hero h1 { font-size: 26px; }
        .jcl-hero p { font-size: 16px; }
        .jcl-section-title { font-size: 26px; }
        .jcl-features { grid-template-columns: 1fr; }
        .jcl-download-buttons { flex-direction: column; align-items: center; }
        .jcl-screenshot-grid { grid-template-columns: 1fr; }
        /* Disable floating animation on mobile */
        .jcl-screenshot-card, .jcl-feature-card { animation: none; }
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

<?php
get_footer();
?>
