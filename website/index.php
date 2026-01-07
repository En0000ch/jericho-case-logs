<?php
/**
 * Mobile Device Redirect for Jericho Case Logs
 * Homepage: Redirects mobile phones to static HTML
 * Other pages: WordPress serves mobile-responsive content
 */

function is_mobile_phone() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // Check for mobile phones (not tablets)
    $mobile_patterns = array(
        '/iPhone/i',
        '/iPod/i',
        '/Android.*Mobile/i',  // Android phones (not tablets)
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

function is_homepage_request() {
    $request_uri = $_SERVER['REQUEST_URI'];

    // Check if this is the homepage
    // Homepage is: /, /index.php, or empty
    return ($request_uri == '/' || $request_uri == '/index.php' || $request_uri == '');
}

// Only redirect mobile phones on the HOMEPAGE to static HTML
// All other pages use WordPress with mobile-responsive CSS
if (is_mobile_phone() && is_homepage_request()) {
    // Serve the static HTML content for homepage only
    if (file_exists(__DIR__ . '/index.html.mobile')) {
        readfile(__DIR__ . '/index.html.mobile');
        exit;
    }
}

// Otherwise, load WordPress normally (all other pages + desktop)
// WordPress will serve mobile-responsive content via the JCL Mobile Responsive plugin
define('WP_USE_THEMES', true);
require(__DIR__ . '/wp-blog-header.php');
?>
