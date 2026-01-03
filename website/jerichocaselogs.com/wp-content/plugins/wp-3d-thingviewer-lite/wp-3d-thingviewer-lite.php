<?php
/**
 * @since             1.0.0
 * @package           WP_3D_Thingviewer_Lite
 *
 * @wordpress-plugin
 * Plugin Name:       WP 3D Thingviewer Lite
 * Plugin URI:        https://codecanyon.net/user/accolore/portfolio
 * Description:       You will have something like Thingiverse model viewer on your Wordpress website. This plugin let you load a 3D model from file (STL, OBJ, FBX, GLB, DRACO) and display into a page or post using a shortcode.
 * Version:           3.2
 * Author:            Accolore <support@accolore.com>
 * Author URI:        https://codecanyon.net/user/accolore/portfolio
 * Text Domain:       wp-3d-thingviewer-lite
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// call global configuration variable
global $accolore_config;

// if not exist instantiate it
if( ! isset($accolore_config)) {
	$accolore_config = array();
}

// Configuration array
$accolore_config['wp-3d-thingviewer-lite'] = array(
    'title'          => 'WP 3D Thingviewer Lite',
    'version'        => '3.2',
    'text_domain'    => 'wp-3d-thingviewer-lite',
    'prefix'         => 'wp3dtvl',
    'demo_page' => array(
        'title'   => 'WP 3D Thingviewer Lite Demo Page',
        'name'    => 'wp3dtvl-demo-page',
        'content' => '[wp-3dtvl model_file="{{1}}" model_preview="{{3}}"][/wp-3dtvl] <br/> [wp-3dtvl model_file="{{2}}"][/wp-3dtvl]',
        'media'   => array(
            1 => 'https://wpdemo.accolore.com/wp-3d-thingviewer-lite/wp-content/uploads/sites/3/2019/04/two-objects.stl',
            2 => 'https://wpdemo.accolore.com/wp-3d-thingviewer-lite/wp-content/uploads/sites/3/2022/02/Nefertiti.glb',
            3 => 'https://wpdemo.accolore.com/wp-3d-thingviewer-lite/wp-content/uploads/sites/3/2022/07/model_preview.jpg',
        ),
    ),
);


// include files
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-accolore-registration.php';

// The code that runs during plugin activation.
function wp3dtvl_activate () { WP_Accolore_Registration::activate( 'wp-3d-thingviewer-lite' ); }

// The code that runs during plugin deactivation.
function wp3dtvl_deactivate () { WP_Accolore_Registration::deactivate( 'wp-3d-thingviewer-lite' ); }

register_activation_hook( __FILE__, 'wp3dtvl_activate' );
register_deactivation_hook( __FILE__, 'wp3dtvl_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wp-3d-thingviewer-lite-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function wp3dtvl_run () {
	$plugin = new WP_3D_Thingviewer_Lite_Plugin ( 'wp-3d-thingviewer-lite' );
	$plugin->run();
}
wp3dtvl_run();