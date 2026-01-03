<?php
/**
 * Mobile Device Redirect for Jericho Case Logs
 * Redirects mobile phones to static HTML, keeps desktops/tablets on WordPress
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

// If it's a mobile phone, serve the static HTML
if (is_mobile_phone()) {
    // Serve the static HTML content
    readfile('index.html.mobile');
    exit;
}

// Otherwise, load WordPress normally
define('WP_USE_THEMES', true);
require(__DIR__ . '/wp-blog-header.php');
?>
