<?php
/**
 * Plugin Name: JCL Mobile Responsive
 * Plugin URI: https://jerichocaselogs.com
 * Description: Ensures all pages are mobile-responsive and optimized for mobile devices
 * Version: 1.0.0
 * Author: Jericho Case Logs
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JCL_Mobile_Responsive {

    public function __construct() {
        // Add mobile-specific CSS to all pages
        add_action('wp_enqueue_scripts', array($this, 'enqueue_mobile_styles'));

        // Add mobile viewport meta tag if not present
        add_action('wp_head', array($this, 'add_viewport_meta'), 1);

        // Add mobile-optimized body class
        add_filter('body_class', array($this, 'add_mobile_body_class'));
    }

    /**
     * Detect if user is on mobile device
     */
    public function is_mobile_device() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $mobile_patterns = array(
            '/iPhone/i',
            '/iPod/i',
            '/Android.*Mobile/i',
            '/BlackBerry/i',
            '/Windows Phone/i',
            '/Opera Mini/i',
            '/IEMobile/i'
        );

        foreach ($mobile_patterns as $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add viewport meta tag
     */
    public function add_viewport_meta() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">' . "\n";
    }

    /**
     * Add mobile body class
     */
    public function add_mobile_body_class($classes) {
        if ($this->is_mobile_device()) {
            $classes[] = 'jcl-mobile-device';
        }
        return $classes;
    }

    /**
     * Enqueue mobile-specific styles
     */
    public function enqueue_mobile_styles() {
        // Add inline mobile CSS
        $mobile_css = "
        /* JCL Mobile Responsive Styles - Applied to ALL pages */

        @media (max-width: 768px) {
            /* Ensure all pages are responsive */
            body {
                font-size: 16px !important;
                -webkit-text-size-adjust: 100%;
            }

            /* Make images responsive */
            img {
                max-width: 100% !important;
                height: auto !important;
            }

            /* Make tables responsive */
            table {
                display: block !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }

            /* Ensure containers are mobile-friendly */
            .ast-container,
            .site-content,
            .entry-content {
                padding-left: 20px !important;
                padding-right: 20px !important;
                max-width: 100% !important;
            }

            /* Fix header on mobile */
            .site-header {
                padding: 15px 20px !important;
            }

            /* Make buttons touch-friendly */
            button,
            .ast-button,
            .button,
            a.button,
            input[type='submit'],
            input[type='button'] {
                min-height: 44px !important;
                padding: 12px 20px !important;
                font-size: 16px !important;
            }

            /* Make form inputs mobile-friendly */
            input[type='text'],
            input[type='email'],
            input[type='password'],
            input[type='tel'],
            input[type='url'],
            input[type='number'],
            textarea,
            select {
                font-size: 16px !important;
                padding: 12px !important;
                min-height: 44px !important;
                width: 100% !important;
            }

            /* Improve readability */
            h1 { font-size: 28px !important; line-height: 1.3 !important; }
            h2 { font-size: 24px !important; line-height: 1.3 !important; }
            h3 { font-size: 20px !important; line-height: 1.4 !important; }
            h4 { font-size: 18px !important; line-height: 1.4 !important; }
            p { font-size: 16px !important; line-height: 1.6 !important; }

            /* Stack columns on mobile */
            .elementor-column {
                width: 100% !important;
                flex: 0 0 100% !important;
            }

            /* Fix navigation menu on mobile */
            .main-header-menu {
                display: flex !important;
                flex-direction: column !important;
            }

            .main-header-menu li {
                width: 100% !important;
                padding: 10px 0 !important;
            }

            /* Make modals mobile-friendly */
            .jcl-modal-content {
                width: 95% !important;
                max-width: 95% !important;
                padding: 20px !important;
                max-height: 90vh !important;
                overflow-y: auto !important;
            }

            /* Employer login/registration forms */
            .jcl-form-row {
                display: block !important;
            }

            .jcl-form-row > * {
                width: 100% !important;
                margin-bottom: 15px !important;
            }

            /* Job posting forms */
            #jcl-post-job-form input,
            #jcl-post-job-form textarea,
            #jcl-post-job-form select {
                width: 100% !important;
            }

            /* Dashboard tables */
            .jcl-dashboard-table {
                display: block !important;
                overflow-x: auto !important;
            }

            /* Store/ecommerce */
            .woocommerce-products-header,
            .woocommerce-product {
                padding: 10px !important;
            }

            .products {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 20px !important;
            }

            /* Fix any fixed-width elements */
            * {
                max-width: 100vw !important;
            }

            /* Remove horizontal scrolling */
            body {
                overflow-x: hidden !important;
            }

            /* Touch-friendly spacing */
            a, button {
                min-height: 44px !important;
                min-width: 44px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
        }

        /* Mobile-specific enhancements for JCL pages */
        @media (max-width: 768px) {
            /* Employer pages */
            .jcl-employer-login-form,
            .jcl-employer-registration-form {
                width: 100% !important;
                padding: 20px !important;
            }

            /* Job posting form */
            .jcl-job-form {
                padding: 15px !important;
            }

            /* Dashboard */
            .jcl-dashboard {
                padding: 15px !important;
            }

            .jcl-dashboard-card {
                margin-bottom: 20px !important;
            }

            /* Privacy policy page */
            .privacy-policy-content {
                font-size: 14px !important;
                line-height: 1.6 !important;
            }
        }
        ";

        wp_add_inline_style('astra-theme-css', $mobile_css);
    }
}

// Initialize the plugin
new JCL_Mobile_Responsive();
