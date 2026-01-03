<?php
/**
 * Template Name: Jericho Homepage
 * Description: Custom homepage template for Jericho Case Logs
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo('name'); ?> - Professional Case Logging for Medical Professionals</title>
    <?php wp_head(); ?>
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Century Gothic', 'Futura', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #2B3241;
            color: #E0FBFC;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Hero Section */
        .hero {
            background-color: #2B3241;
            padding: 80px 0;
            min-height: 600px;
            display: flex;
            align-items: center;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-content {
            max-width: 600px;
        }

        .logo {
            width: 120px;
            height: 120px;
            margin-bottom: 30px;
        }

        .hero h1 {
            font-size: 42px;
            font-weight: 500;
            color: #E0FBFC;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .tagline {
            font-size: 21px;
            font-weight: 300;
            color: #EE6C4D;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .app-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .app-button img {
            height: 60px;
            transition: transform 0.3s ease;
        }

        .app-button:hover img {
            transform: scale(1.05);
        }

        .hero-image {
            text-align: center;
        }

        .hero-image img {
            max-width: 100%;
            max-height: 600px;
            height: auto;
        }

        /* Features Section */
        .features {
            background-color: #2B3241;
            padding: 80px 0;
        }

        .section-heading {
            font-size: 32px;
            font-weight: 500;
            text-align: center;
            color: #E0FBFC;
            margin-bottom: 60px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .feature-card {
            background: #2B3241;
            border: 2px solid #EE6C4D;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(238, 108, 77, 0.3);
        }

        .feature-icon {
            font-size: 40px;
            margin-bottom: 20px;
        }

        .feature-title {
            font-size: 21px;
            font-weight: 400;
            color: #E0FBFC;
            margin-bottom: 15px;
        }

        .feature-description {
            font-size: 18px;
            font-weight: 300;
            color: #E0FBFC;
            line-height: 1.6;
        }

        .feature-badge {
            display: inline-block;
            font-size: 12px;
            color: #EE6C4D;
            margin-left: 8px;
            font-weight: 400;
        }

        /* Stats Section */
        .stats {
            background-color: #2B3241;
            padding: 80px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin-top: 60px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 42px;
            font-weight: 500;
            color: #EE6C4D;
            margin-bottom: 10px;
        }

        .stat-stars {
            font-size: 32px;
            color: #FFB800;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 18px;
            color: #E0FBFC;
        }

        /* CTA Section */
        .cta {
            background-color: #2B3241;
            padding: 80px 0;
            text-align: center;
        }

        .cta h2 {
            font-size: 32px;
            font-weight: 500;
            color: #E0FBFC;
            margin-bottom: 20px;
        }

        .cta p {
            font-size: 18px;
            font-weight: 300;
            color: #E0FBFC;
            margin-bottom: 40px;
        }

        /* Footer */
        .footer {
            background-color: #1a1a1a;
            padding: 40px 0;
            text-align: center;
        }

        .footer p {
            color: #EE6C4D;
            font-size: 16px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero {
                padding: 40px 0;
            }

            .hero h1 {
                font-size: 34px;
            }

            .tagline {
                font-size: 18px;
            }

            .logo {
                width: 100px;
                height: 100px;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .feature-card {
                padding: 30px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .section-heading {
                font-size: 26px;
            }

            .cta h2 {
                font-size: 26px;
            }
        }
    </style>
</head>
<body <?php body_class(); ?>>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/1024Logo.png' ); ?>" alt="Jericho Case Logs" class="logo">
                <h1>Professional Case Logging for Medical Professionals</h1>
                <p class="tagline">Track your clinical cases effortlessly. Build your portfolio. Advance your career.</p>
                <div class="app-buttons">
                    <a href="https://apps.apple.com/us/app/jericho-case-logs/id6466726836" class="app-button" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/apple-app-store.webp' ); ?>" alt="Download on App Store">
                    </a>
                    <a href="https://play.google.com/store/apps" class="app-button" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/google-play-1-300x93.webp' ); ?>" alt="Get it on Google Play">
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/817443CB-2D80-4976-A0C2-F5D0F57A7AAB.png" alt="Jericho Case Logs App">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <h2 class="section-heading">Everything You Need to Track Your Cases</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3 class="feature-title">Quick Case Entry</h3>
                <p class="feature-description">Log clinical cases in seconds. Track patient demographics, ASA classifications, procedures, and clinical plans with our intuitive interface.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title">Visual Analytics & Reports</h3>
                <p class="feature-description">Beautiful charts and graphs showing your cases by ASA classification, anesthetic plan, surgery type, and timeline. Understand your practice patterns at a glance with professional reports.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📅</div>
                <h3 class="feature-title">Interactive Calendar</h3>
                <p class="feature-description">See all your cases organized by date in an interactive calendar with month, week, and two-week views. Perfect for reviewing your monthly activity and planning documentation.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">☁️</div>
                <h3 class="feature-title">Automatic Cloud Sync</h3>
                <p class="feature-description">Your data automatically syncs to the cloud and works offline. SQLite local database ensures you never lose access to your cases, even without an internet connection.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3 class="feature-title">Powerful Search & Filters</h3>
                <p class="feature-description">Find any case instantly with advanced search and filtering by procedure, date range, ASA classification, surgery class, facility, and more. Quickly access the exact cases you need.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📄</div>
                <h3 class="feature-title">PDF Export Reports <span class="feature-badge">(Coming Soon)</span></h3>
                <p class="feature-description">Generate professional PDF reports of your cases for certifications, job applications, and continuing education requirements. Share your portfolio with ease.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats">
    <div class="container">
        <h2 class="section-heading">Trusted by Medical Professionals Nationwide</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">10,000+</div>
                <div class="stat-label">Cases Logged</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-stars">★★★★★</div>
                <div class="stat-label">4.8 App Store Rating</div>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA Section -->
<section class="cta">
    <div class="container">
        <h2>Ready to Transform Your Case Logging?</h2>
        <p>Join hundreds of medical professionals who trust Jericho Case Logs to manage their clinical portfolios.</p>
        <div class="app-buttons" style="justify-content: center;">
            <a href="https://apps.apple.com/us/app/jericho-case-logs/id6466726836" class="app-button" target="_blank" rel="noopener">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/apple-app-store.webp' ); ?>" alt="Download on App Store">
            </a>
            <a href="https://play.google.com/store/apps" class="app-button" target="_blank" rel="noopener">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/google-play-1-300x93.webp' ); ?>" alt="Get it on Google Play">
            </a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Jericho Case Logs. All rights reserved.</p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
