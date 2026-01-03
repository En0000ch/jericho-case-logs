<?php
/**
 * Template Name: Hero Landing Page
 * Description: Custom template for hero section with 3D model
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<section class="hero-section section-hero scroll-fade-in" style="
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at center, #1a1a1a 0%, #0a0a0a 100%);
    padding: 60px 20px;
    text-align: center;
">
    <div style="max-width: 1200px; width: 100%;">

        <!-- Heading -->
        <h1 style="
            color: #00f3ff;
            font-size: 56px;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 20px 0;
            text-shadow: 0 0 20px rgba(0, 243, 255, 0.5);
        ">
            Transform Your Clinical Practice
        </h1>

        <!-- Subtitle -->
        <p style="
            color: #b0b0b0;
            font-size: 20px;
            line-height: 1.6;
            margin: 0 auto 40px;
            max-width: 80%;
        ">
            The complete case logging solution for CRNAs and clinical professionals. Track cases, generate reports, and advance your career.
        </p>

        <!-- 3D Model -->
        <div style="width: 100%; max-width: 600px; height: 400px; margin: 0 auto 40px;">
            <iframe src="/3d-viewer.html?model=/Meshy_Merged_Animations.glb"
                    width="100%"
                    height="100%"
                    frameborder="0"
                    loading="lazy"
                    style="border-radius: 20px; box-shadow: 0 0 30px rgba(0, 243, 255, 0.3);">
            </iframe>
        </div>

        <!-- App Store Buttons -->
        <div style="display: flex; justify-content: center; align-items: center; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
            <a href="https://apps.apple.com/app/jericho-case-logs/id6738419880" target="_blank" rel="noopener" style="transition: transform 0.3s ease;">
                <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/apple-app-store.webp"
                     alt="Download on App Store"
                     style="height: 60px; width: auto; filter: drop-shadow(0 0 15px rgba(0, 243, 255, 0.3));"
                     onmouseover="this.style.transform='scale(1.05) translateY(-3px)'; this.style.filter='drop-shadow(0 0 25px rgba(0, 243, 255, 0.6))'"
                     onmouseout="this.style.transform='scale(1)'; this.style.filter='drop-shadow(0 0 15px rgba(0, 243, 255, 0.3))'">
            </a>
            <a href="https://play.google.com/store/apps/details?id=com.jericho.caselogs" target="_blank" rel="noopener" style="transition: transform 0.3s ease;">
                <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/google-play-1-300x93-1.webp"
                     alt="Get it on Google Play"
                     style="height: 60px; width: auto; filter: drop-shadow(0 0 15px rgba(0, 255, 136, 0.3));"
                     onmouseover="this.style.transform='scale(1.05) translateY(-3px)'; this.style.filter='drop-shadow(0 0 25px rgba(0, 255, 136, 0.6))'"
                     onmouseout="this.style.transform='scale(1)'; this.style.filter='drop-shadow(0 0 15px rgba(0, 255, 136, 0.3))'">
            </a>
        </div>

        <!-- Tagline -->
        <p style="
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 30px 0 0 0;
        ">
            Available for iOS and Android
        </p>

    </div>
</section>

<style>
/* Mobile responsive styles */
@media (max-width: 768px) {
    .hero-section h1 {
        font-size: 36px !important;
    }
    .hero-section p:first-of-type {
        font-size: 16px !important;
        max-width: 100% !important;
    }
    .hero-section > div > div:nth-child(3) {
        height: 300px !important;
    }
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}
</style>

<?php wp_footer(); ?>
</body>
</html>
