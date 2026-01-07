<?php
/**
 * Plugin Name: JCL Mobile Sync
 * Plugin URI: https://jerichocaselogs.com
 * Description: Automatically syncs mobile version (index.html.mobile) with the primary WordPress homepage
 * Version: 1.0.0
 * Author: Jericho Case Logs
 * Author URI: https://jerichocaselogs.com
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class JCL_Mobile_Sync {

    private $mobile_file_path;

    public function __construct() {
        $this->mobile_file_path = ABSPATH . 'index.html.mobile';

        // Hook into various save events
        add_action('save_post', array($this, 'trigger_sync'), 10, 3);
        add_action('elementor/editor/after_save', array($this, 'trigger_sync_elementor'), 10, 2);
        add_action('wp_ajax_jcl_manual_sync', array($this, 'manual_sync'));

        // Add admin menu for manual sync
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_bar_menu', array($this, 'add_admin_bar_button'), 100);
    }

    /**
     * Trigger sync when a page is saved
     */
    public function trigger_sync($post_id, $post, $update) {
        // Only sync if it's the homepage or a page being updated
        if ($post->post_type !== 'page' || !$update) {
            return;
        }

        // Check if this is the homepage
        $homepage_id = get_option('page_on_front');
        if ($homepage_id && $post_id == $homepage_id) {
            $this->generate_mobile_version();
        }
    }

    /**
     * Trigger sync when Elementor saves
     */
    public function trigger_sync_elementor($post_id, $editor_data) {
        $homepage_id = get_option('page_on_front');
        if ($homepage_id && $post_id == $homepage_id) {
            $this->generate_mobile_version();
        }
    }

    /**
     * Generate the mobile HTML version
     */
    public function generate_mobile_version() {
        try {
            // Fetch the homepage HTML
            $home_url = home_url('/');
            $response = wp_remote_get($home_url, array(
                'timeout' => 30,
                'sslverify' => false,
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ));

            if (is_wp_error($response)) {
                error_log('JCL Mobile Sync Error: ' . $response->get_error_message());
                return false;
            }

            $html = wp_remote_retrieve_body($response);

            if (empty($html)) {
                error_log('JCL Mobile Sync Error: Empty HTML response');
                return false;
            }

            // Extract the content sections using regex
            $mobile_html = $this->extract_and_build_mobile_html($html);

            // Write to file
            $result = file_put_contents($this->mobile_file_path, $mobile_html);

            if ($result === false) {
                error_log('JCL Mobile Sync Error: Could not write to ' . $this->mobile_file_path);
                return false;
            }

            error_log('JCL Mobile Sync: Successfully updated mobile version (' . strlen($mobile_html) . ' bytes)');
            return true;

        } catch (Exception $e) {
            error_log('JCL Mobile Sync Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract content and build mobile HTML
     */
    private function extract_and_build_mobile_html($html) {
        // Extract CSS styles for JCL components
        preg_match('/\.jcl-hero\s*\{.*?\}.*?\.jcl-professionals.*?\}/s', $html, $style_matches);
        $jcl_styles = isset($style_matches[0]) ? $style_matches[0] : '';

        // Extract main content sections
        preg_match('/<div class="jcl-hero">.*?<\/div>/s', $html, $hero_match);
        preg_match('/<div class="jcl-screenshot-showcase">.*?<\/div>\s*<\/div>/s', $html, $screenshot_match);
        preg_match('/<div class="jcl-professionals">.*?<\/div>\s*<\/div>/s', $html, $professionals_match);
        preg_match('/<h2 class="jcl-section-title">Key Features<\/h2>.*?<\/div>/s', $html, $features_match);
        preg_match('/<h2 class="jcl-section-title">Why Healthcare Professionals Choose Us<\/h2>.*?<\/div>/s', $html, $benefits_match);
        preg_match('/<div class="jcl-download-section"[^>]*>.*?<\/div>\s*<\/div>/s', $html, $cta_match);

        $hero_html = isset($hero_match[0]) ? $hero_match[0] : '';
        $screenshot_html = isset($screenshot_match[0]) ? $screenshot_match[0] : '';
        $professionals_html = isset($professionals_match[0]) ? $professionals_match[0] : '';
        $features_html = isset($features_match[0]) ? $features_match[0] : '';
        $benefits_html = isset($benefits_match[0]) ? $benefits_match[0] : '';
        $cta_html = isset($cta_match[0]) ? $cta_match[0] : '';

        // Build the complete mobile HTML
        $mobile_html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Professional case logging app for CRNAs, nurses, and healthcare providers. Track clinical experience, generate reports, and find opportunities.">
    <title>Jericho Case Logs - Professional Case Logging for Healthcare Providers</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #2e3241;
            color: #e0fbfc;
            line-height: 1.6;
        }

        /* Mobile Header */
        .mobile-header {
            background: #2e3241;
            padding: 15px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 1px solid rgba(238, 108, 77, 0.2);
        }

        .site-title {
            font-size: 20px;
            font-weight: 600;
            color: #e0fbfc;
            text-decoration: none;
            display: flex;
            justify-content: center;
        }

        .site-title a {
            color: #e0fbfc;
            text-decoration: none;
            display: block;
        }

        {$jcl_styles}

        /* Fallback styles if extraction fails */
        .jcl-hero {
            color: #e0fbfc;
            padding: 40px 40px;
            text-align: center;
            margin-top: 0;
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

        .jcl-screenshot-showcase {
            background: rgba(46, 50, 65, 0);
            padding: 60px 40px;
            border-radius: 12px;
            margin: 60px 0;
            border: 1px solid rgba(238, 108, 77, 0);
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
            border: 1px solid rgba(238, 108, 77, 0);
        }
        .jcl-screenshot-card img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
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
            border: 1px solid rgba(238, 108, 77, 0);
        }
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

        .jcl-professionals {
            background: rgba(46, 50, 65, 0);
            padding: 60px 40px;
            border-radius: 12px;
            margin: 60px 0;
            border: 1px solid rgba(238, 108, 77, 0);
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
            border: 1px solid rgba(238, 108, 77, 0);
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
        .jcl-status {
            font-size: 13px;
            font-weight: bold;
            padding: 6px 14px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 10px;
        }
        .jcl-status.available {
            background: rgba(76, 175, 80, 0.2);
            color: #4CAF50;
            border: 1px solid #4CAF50;
        }
        .jcl-status.coming-soon {
            background: rgba(255, 193, 7, 0.2);
            color: #FFC107;
            border: 1px solid #FFC107;
        }

        footer {
            text-align: center;
            padding: 40px 20px 20px 20px;
            color: #95b0b1;
            border-top: 1px solid rgba(238, 108, 77, 0.2);
            margin-top: 60px;
        }

        footer a {
            color: #ee6c4d;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* Contact Support Section */
        #jcl-footer-support {
            text-align: center;
            padding: 20px;
            background: transparent;
        }

        #jcl-footer-support a {
            color: #EE6C4D;
            text-decoration: none;
            font-size: 14px;
        }

        #jcl-footer-support a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .jcl-hero h1 {
                font-size: 28px;
            }

            .jcl-hero p {
                font-size: 16px;
            }

            .jcl-section-title {
                font-size: 26px;
            }

            .jcl-download-buttons {
                flex-direction: column;
                align-items: center;
            }

            .jcl-screenshot-grid {
                grid-template-columns: 1fr;
            }

            .jcl-features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Header -->
    <header class="mobile-header">
        <div class="site-title">
            <a href="https://www.jerichocaselogs.com/admin-dashboard.html">
                <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/jclBanner_3d.png" alt="Jericho Case Logs" style="max-width: 200px; height: auto;">
            </a>
        </div>
    </header>

    {$hero_html}
    {$screenshot_html}
    {$professionals_html}
    {$features_html}
    {$benefits_html}
    {$cta_html}

    <footer>
        <p>Copyright © 2026 Jericho Case Logs. All rights reserved.</p>
        <p style="margin-top: 10px;">
            <a href="/privacy-policy">Privacy Policy</a> |
            <a href="/employer-login">Employer Login</a>
        </p>
    </footer>

    <!-- Contact Support Link -->
    <div id="jcl-footer-support">
        <a href="mailto:support@jerichocaselogs.com?subject=Support Request">Contact Support</a>
    </div>
</body>
</html>
HTML;

        return $mobile_html;
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_management_page(
            'JCL Mobile Sync',
            'Mobile Sync',
            'manage_options',
            'jcl-mobile-sync',
            array($this, 'admin_page')
        );
    }

    /**
     * Add button to admin bar
     */
    public function add_admin_bar_button($wp_admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $args = array(
            'id'    => 'jcl_mobile_sync',
            'title' => '📱 Sync Mobile',
            'href'  => '#',
            'meta'  => array(
                'class' => 'jcl-mobile-sync-button',
                'onclick' => 'jclMobileSync(); return false;'
            )
        );
        $wp_admin_bar->add_node($args);

        // Add inline script
        add_action('admin_footer', array($this, 'add_sync_script'));
        add_action('wp_footer', array($this, 'add_sync_script'));
    }

    /**
     * Add sync JavaScript
     */
    public function add_sync_script() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <script>
        function jclMobileSync() {
            if (confirm('Sync mobile version with current homepage?')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        alert(response.data.message || 'Mobile version synced successfully!');
                    } else {
                        alert('Sync failed. Check error logs.');
                    }
                };
                xhr.send('action=jcl_manual_sync');
            }
        }
        </script>
        <?php
    }

    /**
     * Handle manual sync AJAX request
     */
    public function manual_sync() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }

        $result = $this->generate_mobile_version();

        if ($result) {
            wp_send_json_success(array('message' => 'Mobile version synced successfully!'));
        } else {
            wp_send_json_error(array('message' => 'Sync failed. Check error logs.'));
        }
    }

    /**
     * Admin page content
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>JCL Mobile Sync</h1>
            <p>Automatically syncs the mobile version (index.html.mobile) with your WordPress homepage.</p>

            <h2>Status</h2>
            <table class="form-table">
                <tr>
                    <th>Mobile File:</th>
                    <td>
                        <?php if (file_exists($this->mobile_file_path)): ?>
                            ✅ <code><?php echo $this->mobile_file_path; ?></code>
                            <br>Last modified: <?php echo date('Y-m-d H:i:s', filemtime($this->mobile_file_path)); ?>
                            <br>Size: <?php echo number_format(filesize($this->mobile_file_path)); ?> bytes
                        <?php else: ?>
                            ❌ File not found
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Auto-sync:</th>
                    <td>✅ Enabled (triggers on page save)</td>
                </tr>
            </table>

            <h2>Manual Sync</h2>
            <p>Click the button below to manually sync the mobile version with the current homepage:</p>
            <p>
                <button type="button" class="button button-primary" onclick="jclMobileSync()">
                    📱 Sync Mobile Version Now
                </button>
            </p>

            <h2>How It Works</h2>
            <ul>
                <li>✅ Automatically syncs when you save/update the homepage</li>
                <li>✅ Works with Elementor, Gutenberg, and custom page templates</li>
                <li>✅ Extracts content sections and styles from the live site</li>
                <li>✅ Generates a standalone mobile HTML file</li>
                <li>✅ Mobile phones are automatically redirected via index.php</li>
            </ul>
        </div>
        <?php
    }
}

// Initialize the plugin
new JCL_Mobile_Sync();
