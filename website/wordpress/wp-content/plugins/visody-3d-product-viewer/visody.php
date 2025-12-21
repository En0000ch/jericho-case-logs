<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://visody.com/
 * @since             1.0.0
 * @package           Visody
 *
 * @wordpress-plugin
 * Plugin Name: 	  3D viewer by Visody
 * Description:       Easily add beautiful, fully-customizable 3D viewers to your WooCommerce product galleries and WordPress pages! AR capabilies included.
 * Version:           2.4.2
 * Author:            Visody
 * Author URI:        https://visody.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       visody
 * Domain Path:       /languages
 * 
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Freemius integration
 */
if ( function_exists( 'visody_fs' ) ) {
    visody_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'visody_fs' ) ) {
        // Create a helper function for easy SDK access.
        function visody_fs() {
            global $visody_fs;
            if ( !isset( $visody_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $visody_fs = fs_dynamic_init( array(
                    'id'             => '14273',
                    'slug'           => 'visody',
                    'premium_slug'   => 'visody-pro',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_02132103c0a35819d7a26ec3ee438',
                    'is_premium'     => false,
                    'premium_suffix' => 'PRO',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                        'slug'       => 'edit.php?post_type=visody_viewer',
                        'first-path' => 'edit.php?post_type=visody_viewer&page=welcome',
                        'contact'    => false,
                        'support'    => false,
                    ),
                    'is_live'        => true,
                ) );
            }
            return $visody_fs;
        }

        // Init Freemius.
        visody_fs();
        // Signal that SDK was initiated.
        do_action( 'visody_fs_loaded' );
    }
    /**
     * Currently plugin version.
     * Start at version 1.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define( 'VISODY_VERSION', '2.4.2' );
    define( 'VISODY_BASE', plugin_dir_path( __FILE__ ) );
    define( 'VISODY_BASE_URL', plugin_dir_url( __FILE__ ) );
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-visody-activator.php
     */
    function visody_activate() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-visody-activator.php';
        Visody_Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-visody-deactivator.php
     */
    function visody_deactivate() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-visody-deactivator.php';
        Visody_Deactivator::deactivate();
    }

    register_activation_hook( __FILE__, 'visody_activate' );
    register_deactivation_hook( __FILE__, 'visody_deactivate' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-visody.php';
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function visody_execute() {
        $plugin = new Visody();
        $plugin->run();
    }

    visody_execute();
}