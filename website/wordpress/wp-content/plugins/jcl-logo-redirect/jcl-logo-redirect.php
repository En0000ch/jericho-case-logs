<?php
/**
 * Plugin Name: JCL Logo Redirect
 * Plugin URI: https://jerichocaselogs.com
 * Description: Makes the site logo a hidden link to the admin dashboard
 * Version: 1.0.0
 * Author: Jericho Case Logs
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JCL_Logo_Redirect {

    public function __construct() {
        // Add JavaScript to make logo clickable
        add_action('wp_footer', array($this, 'add_logo_redirect_script'));
    }

    /**
     * Add JavaScript to make logo link to admin dashboard
     */
    public function add_logo_redirect_script() {
        ?>
        <script type="text/javascript">
        (function() {
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initLogoRedirect);
            } else {
                initLogoRedirect();
            }

            function initLogoRedirect() {
                // Find the logo elements - try multiple selectors
                var logoSelectors = [
                    '.custom-logo-link',
                    '.site-logo a',
                    '.site-branding a',
                    '.ast-site-identity a',
                    '#jcl-video-logo',
                    '.custom-logo',
                    'a[rel="home"] img',
                    '.site-title a'
                ];

                var logoFound = false;

                logoSelectors.forEach(function(selector) {
                    var elements = document.querySelectorAll(selector);
                    elements.forEach(function(element) {
                        if (!logoFound || element.id === 'jcl-video-logo' || element.classList.contains('custom-logo')) {
                            // If it's a video, wrap it in a clickable container
                            if (element.tagName === 'VIDEO') {
                                element.style.cursor = 'pointer';
                                element.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    window.location.href = 'https://www.jerichocaselogs.com/admin-dashboard.html';
                                });
                                logoFound = true;
                            }
                            // If it's a link, change its href
                            else if (element.tagName === 'A') {
                                element.href = 'https://www.jerichocaselogs.com/admin-dashboard.html';
                                logoFound = true;
                            }
                            // If it's an image, make parent link point to dashboard
                            else if (element.tagName === 'IMG' && element.parentElement && element.parentElement.tagName === 'A') {
                                element.parentElement.href = 'https://www.jerichocaselogs.com/admin-dashboard.html';
                                logoFound = true;
                            }
                        }
                    });
                });

                // Also check for the video logo specifically
                var videoLogo = document.getElementById('jcl-video-logo');
                if (videoLogo) {
                    videoLogo.style.cursor = 'pointer';
                    videoLogo.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        window.location.href = 'https://www.jerichocaselogs.com/admin-dashboard.html';
                    });
                }
            }
        })();
        </script>
        <?php
    }
}

// Initialize the plugin
new JCL_Logo_Redirect();
