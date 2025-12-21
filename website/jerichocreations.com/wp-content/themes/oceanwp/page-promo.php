<?php
/**
 * Template Name: Jericho Promo Page
 * Description: Professional promo page for Jericho Case Logs
 */

get_header(); ?>

<style>
/* Hero Section */
.jcl-hero {
    background: linear-gradient(135deg, #2B3241 0%, #1a1f2a 100%);
    color: #E0FBFC;
    padding: 100px 20px;
    text-align: center;
}

.jcl-logo {
    width: 180px;
    height: 180px;
    margin: 0 auto 30px;
    display: block;
}

.jcl-hero h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 20px;
    line-height: 1.2;
    color: #E0FBFC;
}

.jcl-hero p {
    font-size: 1.5rem;
    margin-bottom: 40px;
    opacity: 0.95;
    color: #E0FBFC;
}

.jcl-cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.jcl-btn {
    padding: 15px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.jcl-btn-primary {
    background: #EE6C4D;
    color: #E0FBFC;
    box-shadow: 0 0 0 0 rgba(238, 108, 77, 0.7);
    position: relative;
    overflow: hidden;
}

.jcl-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(238, 108, 77, 0.4);
    background: #d95a3d;
}

.jcl-btn-primary:active {
    animation: pulse-glow 0.6s ease-out;
    transform: translateY(0);
}

@keyframes pulse-glow {
    0% {
        box-shadow: 0 0 0 0 rgba(238, 108, 77, 0.7);
    }
    50% {
        box-shadow: 0 0 20px 10px rgba(238, 108, 77, 0.5),
                    0 0 40px 20px rgba(238, 108, 77, 0.3),
                    0 0 60px 30px rgba(238, 108, 77, 0.1);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(238, 108, 77, 0);
    }
}

.jcl-btn-secondary {
    background: transparent;
    color: #E0FBFC;
    border: 2px solid #EE6C4D;
    box-shadow: 0 0 0 0 rgba(238, 108, 77, 0.7);
    position: relative;
    overflow: hidden;
}

.jcl-btn-secondary:hover {
    background: #EE6C4D;
    color: #E0FBFC;
}

.jcl-btn-secondary:active {
    animation: pulse-glow 0.6s ease-out;
    transform: translateY(0);
}

/* Features Section */
.jcl-features {
    padding: 80px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.jcl-features h2 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 60px;
    color: #2d3748;
}

.jcl-feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
}

.jcl-feature-card {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s ease;
}

.jcl-feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 40px rgba(0,0,0,0.12);
}

.jcl-feature-icon {
    font-size: 3rem;
    margin-bottom: 20px;
}

.jcl-feature-card h3 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: #2d3748;
}

.jcl-feature-card p {
    color: #718096;
    line-height: 1.6;
}

/* Stats Section */
.jcl-stats {
    background: #f7fafc;
    padding: 80px 20px;
}

.jcl-stats-container {
    max-width: 1200px;
    margin: 0 auto;
}

.jcl-stats h2 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 60px;
    color: #2d3748;
}

.jcl-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
    text-align: center;
}

.jcl-stat-item h3 {
    font-size: 3rem;
    color: #EE6C4D;
    margin-bottom: 10px;
}

.jcl-stat-item p {
    font-size: 1.2rem;
    color: #4a5568;
}

/* Pricing Section */
.jcl-pricing {
    padding: 80px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.jcl-pricing h2 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 60px;
    color: #2d3748;
}

.jcl-pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 40px;
    max-width: 900px;
    margin: 0 auto;
}

.jcl-pricing-card {
    background: white;
    padding: 50px 40px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    text-align: center;
    border: 3px solid transparent;
    transition: all 0.3s ease;
}

.jcl-pricing-card.featured {
    border-color: #EE6C4D;
    transform: scale(1.05);
}

.jcl-pricing-card h3 {
    font-size: 1.8rem;
    margin-bottom: 20px;
    color: #2d3748;
}

.jcl-pricing-price {
    font-size: 3rem;
    font-weight: 700;
    color: #EE6C4D;
    margin-bottom: 10px;
}

.jcl-pricing-period {
    color: #718096;
    margin-bottom: 30px;
}

.jcl-pricing-features {
    list-style: none;
    padding: 0;
    margin: 30px 0;
    text-align: left;
}

.jcl-pricing-features li {
    padding: 12px 0;
    border-bottom: 1px solid #e2e8f0;
    color: #4a5568;
}

.jcl-pricing-features li:before {
    content: "✓ ";
    color: #48bb78;
    font-weight: bold;
    margin-right: 10px;
}

/* CTA Section */
.jcl-cta {
    background: linear-gradient(135deg, #2B3241 0%, #1a1f2a 100%);
    color: #E0FBFC;
    padding: 80px 20px;
    text-align: center;
}

.jcl-cta h2 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: #E0FBFC;
}

.jcl-cta p {
    font-size: 1.3rem;
    margin-bottom: 40px;
    opacity: 0.95;
    color: #E0FBFC;
}

/* Job Poster Footer */
.jcl-job-footer {
    background: #1a1f2a;
    border-top: 1px solid #2B3241;
    padding: 40px 20px;
    text-align: center;
}

.jcl-job-footer-content {
    max-width: 600px;
    margin: 0 auto;
}

.jcl-job-footer h3 {
    color: #E0FBFC;
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 15px;
    opacity: 0.8;
}

.jcl-job-links {
    display: flex;
    gap: 30px;
    justify-content: center;
    align-items: center;
}

.jcl-job-link {
    color: #EE6C4D;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
    padding: 8px 16px;
    border-radius: 6px;
}

.jcl-job-link:hover {
    background: rgba(238, 108, 77, 0.1);
    color: #d95a3d;
}

.jcl-job-divider {
    color: #2B3241;
    font-weight: 300;
}

/* Responsive */
@media (max-width: 768px) {
    .jcl-hero h1 {
        font-size: 2.5rem;
    }

    .jcl-hero p {
        font-size: 1.2rem;
    }

    .jcl-logo {
        width: 140px;
        height: 140px;
    }

    .jcl-pricing-card.featured {
        transform: scale(1);
    }

    .jcl-job-links {
        flex-direction: column;
        gap: 15px;
    }

    .jcl-job-divider {
        display: none;
    }

    .jcl-job-footer h3 {
        font-size: 1rem;
    }
}
</style>

<div class="jcl-promo-page">

    <!-- Hero Section -->
    <section class="jcl-hero">
        <div class="jcl-hero-content">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/1024Logo.png" alt="Jericho Case Logs Logo" class="jcl-logo">
            <h1>Jericho Case Logs</h1>
            <p>The Professional Anesthesia Case Logging Platform for CRNAs</p>
            <div class="jcl-cta-buttons">
                <a href="https://apps.apple.com/us/app/jericho-case-logs/id6466726836" class="jcl-btn jcl-btn-primary">Download on App Store</a>
                <a href="#features" class="jcl-btn jcl-btn-secondary">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="jcl-features">
        <h2>Powerful Features for Busy Professionals</h2>
        <div class="jcl-feature-grid">

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">📝</div>
                <h3>Quick Case Entry</h3>
                <p>Log anesthesia cases in seconds with our intuitive interface. Track patient demographics, ASA classifications, procedures, and anesthetic plans effortlessly.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">📊</div>
                <h3>Visual Analytics</h3>
                <p>Beautiful charts and graphs showing your cases by ASA classification, anesthetic plan, surgery type, and timeline. Understand your practice patterns at a glance.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">📅</div>
                <h3>Calendar View</h3>
                <p>See all your cases organized by date in an interactive calendar. Perfect for reviewing your monthly activity and planning your documentation.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">☁️</div>
                <h3>Cloud Sync</h3>
                <p>Your data is automatically synced to the cloud and available across all your devices. Never lose your case logs again.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">📱</div>
                <h3>Offline Support</h3>
                <p>Log cases even without internet connection. Your data syncs automatically when you're back online.</p>
            </div>

            <div class="jcl-feature-card">
                <div class="jcl-feature-icon">🔍</div>
                <h3>Advanced Search</h3>
                <p>Find any case instantly with powerful search and filtering by procedure, date range, ASA classification, and more.</p>
            </div>

        </div>
    </section>

    <!-- Stats Section -->
    <section class="jcl-stats">
        <div class="jcl-stats-container">
            <h2>Trusted by CRNAs Nationwide</h2>
            <div class="jcl-stats-grid">
                <div class="jcl-stat-item">
                    <h3>10,000+</h3>
                    <p>Cases Logged</p>
                </div>
                <div class="jcl-stat-item">
                    <h3>500+</h3>
                    <p>Active Users</p>
                </div>
                <div class="jcl-stat-item">
                    <h3>4.8★</h3>
                    <p>App Store Rating</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="jcl-pricing">
        <h2>Simple, Transparent Pricing</h2>
        <div class="jcl-pricing-grid">

            <div class="jcl-pricing-card">
                <h3>Free</h3>
                <div class="jcl-pricing-price">$0</div>
                <div class="jcl-pricing-period">Forever</div>
                <ul class="jcl-pricing-features">
                    <li>Up to 5 cases</li>
                    <li>Basic case logging</li>
                    <li>Calendar view</li>
                    <li>Cloud sync</li>
                </ul>
                <a href="https://apps.apple.com/us/app/jericho-case-logs/id6466726836" class="jcl-btn jcl-btn-secondary">Get Started</a>
            </div>

            <div class="jcl-pricing-card featured">
                <h3>Premium</h3>
                <div class="jcl-pricing-price">$9.99</div>
                <div class="jcl-pricing-period">One-time purchase</div>
                <ul class="jcl-pricing-features">
                    <li>Unlimited cases</li>
                    <li>Advanced analytics</li>
                    <li>Export to PDF</li>
                    <li>Priority support</li>
                    <li>All future updates</li>
                </ul>
                <a href="https://apps.apple.com/us/app/jericho-case-logs/id6466726836" class="jcl-btn jcl-btn-primary">Upgrade Now</a>
            </div>

        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="jcl-cta">
        <h2>Ready to Transform Your Case Logging?</h2>
        <p>Join hundreds of CRNAs who trust Jericho Case Logs</p>
        <a href="https://apps.apple.com/us/app/jericho-case-logs/id6466726836" class="jcl-btn jcl-btn-primary">Download Now</a>
    </section>

    <!-- Job Poster Footer -->
    <footer class="jcl-job-footer">
        <div class="jcl-job-footer-content">
            <h3>For Healthcare Facilities & Recruiters</h3>
            <div class="jcl-job-links">
                <a href="<?php echo home_url('/post-job'); ?>" class="jcl-job-link">Post a Job</a>
                <span class="jcl-job-divider">|</span>
                <a href="<?php echo home_url('/employer-login'); ?>" class="jcl-job-link">Employer Login</a>
                <span class="jcl-job-divider">|</span>
                <a href="<?php echo home_url('/employer-signup'); ?>" class="jcl-job-link">Create Employer Account</a>
            </div>
        </div>
    </footer>

</div>

<?php get_footer(); ?>
