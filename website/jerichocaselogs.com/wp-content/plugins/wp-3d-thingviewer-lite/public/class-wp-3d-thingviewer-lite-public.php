<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    WP_3D_Thingviewer_Lite
 * @subpackage WP_3D_Thingviewer_Lite/public
 * @author     Accolore <support@accolore.com>
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (! class_exists('WP_3D_Thingviewer_Lite_Public')) {
	class WP_3D_Thingviewer_Lite_Public {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @param    string    $plugin_name    The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param    string                    $plugin_name       The name of the plugin.
		 */
		public function __construct( $plugin_name ) {
			$this->plugin_name   = $plugin_name;
		}

		/**
		 * Register the stylesheets for the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles() {
			global $accolore_config;

			$version = $accolore_config[$this->plugin_name]['version'];

			wp_enqueue_style('dashicons');
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/public.css', array(), $version, 'all' );
		}

		/**
		 * Register the stylesheets for the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts() {
			global $accolore_config;
			
			$version    = $accolore_config[$this->plugin_name]['version'];
			$textdomain = $accolore_config[$this->plugin_name]['text_domain'];
			$prefix     = $accolore_config[$this->plugin_name]['prefix'];

			wp_register_script( 'three-js', plugin_dir_url( __FILE__ ) . 'libraries/three-js-r145/three.min.js', false, $version, true );
			wp_register_script( 'three-js-webgl', plugin_dir_url( __FILE__ ) . 'libraries/three-js-r145/WebGL.js', array('three-js'), $version, true );
			wp_register_script( 'three-js-orbit-controls', plugin_dir_url( __FILE__ ) . 'libraries/three-js-r145/OrbitControls.js', array('three-js'), $version, true );
			
			wp_register_script( 'three-js-bgeo-utils', plugin_dir_url( __FILE__ ) . 'libraries/three-js-r145/BufferGeometryUtils.js', array('three-js'), $version, true );
			wp_register_script( 'three-js-fflate-lib', plugin_dir_url( __FILE__ ) . 'libraries/three-js-r145/fflate.min.js', array('three-js'), $version, true );

			wp_register_script( 'three-js-stl-loader', plugin_dir_url( __FILE__ ) . 'libraries/three-js-r145/STLLoader.js', array('three-js'), $version, true );
			wp_register_script( 'three-js-fbx-loader', plugin_dir_url( __FILE__ ) . 'libraries/three-js-r145/FBXLoader.js', array('three-js'), $version, true );
			wp_register_script( 'three-js-obj-loader', plugin_dir_url( __FILE__ ) . 'libraries/three-js-r145/OBJLoader.js', array('three-js'), $version, true );
			wp_register_script( 'three-js-gltf-loader', plugin_dir_url( __FILE__ ) . 'libraries/three-js-r145/GLTFLoader.js', array('three-js'), $version, true );
			wp_register_script( 'three-js-draco-loader', plugin_dir_url( __FILE__ ) . 'libraries/three-js-r145/DRACOLoader.js', array('three-js'), $version, true );

			wp_register_script( $this->plugin_name . '-thingviewer', plugin_dir_url( __FILE__ ) . 'js/Thingviewer.js', array('three-js', 'three-js-stl-loader', 'three-js-obj-loader', 'three-js-fflate-lib', 'three-js-bgeo-utils', 'three-js-orbit-controls', 'three-js-webgl'), $version, true );

			wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/public.js', array( 'three-js', 'three-js-stl-loader', 'three-js-fbx-loader', 'three-js-obj-loader', 'three-js-gltf-loader', 'three-js-orbit-controls', $this->plugin_name . '-thingviewer' ), $version, true );
			// Using localization to customize thingviewer parameters
			$tv_options = array(
				'help_button_display'            => 1,
				'fullscreen_button_display'      => 1,
				'info_button_display'            => 1,
				'bounding_box_button_display'    => 1,
				'model_download_button_display'  => 1,
				'camera_rotation_button_display' => 1,
				'camera_rotation_value'          => 10,
				'model_color'                    => '#2776c8',
				'fog_display'                    => 1,
				'fog_color'                      => '#cccccc',
				'plane_color'                    => '#cccccc',
				'plane_wire_display'             => 1,
				'zoom_factor'                    => 1,
				'ambient_light_intensity'        => 0,
				'wire_color'                     => '#ffffff',
				'light_color'                    => '#ffffff',
				'bounding_box_color'             => '#ff0000',
				'file_name_display'              => 1,
				'file_size_display'              => 1,
				'triangles_display'              => 1,
				'surface_area_display'           => 1,
				'volume_display'                 => 1,
				'bounding_box_display'           => 1,
				'help_template'                  => esc_html__("MOUSE LEFT BUTTON = rotate<br/>MOUSE RIGHT BUTTON = pan<br/>MOUSE WHEEL = zoom in / zoom out<br/>X = Exit from fullscreen mode<br/>", $textdomain ),
				'info_template'                  => esc_html__("File name: {0}<br/>File size: {1}<br/>Triangles: {2}<br/>Surface area: {3} cm2<br/>Model volume: {4} cm3<br/>Bounding box<br/>X: {5} cm<br/>Y: {6} cm<br/>Z: {7} cm<br/>", $textdomain ),
				'container_prefix'               => esc_html__($prefix),
				'base_url'                       => get_site_url(),
				'axis_up'                        => 'z_up',
			);

			wp_localize_script( $this->plugin_name . '-thingviewer', 'tv_options', $tv_options );
			wp_localize_script( $this->plugin_name, 'data', array(
				'vc'       => get_option('wp3dtv_envato_purchase_code_validation'),
				'base_url' => get_site_url(),
			));

			wp_enqueue_script( 'three-js' );
			wp_enqueue_script( 'three-js-webgl' );
			wp_enqueue_script( 'three-js-orbit-controls' );
			wp_enqueue_script( 'three-js-bgeo-utils' );
			wp_enqueue_script( 'three-js-fflate-lib' );
			wp_enqueue_script( 'three-js-stl-loader' );
			wp_enqueue_script( 'three-js-fbx-loader' );
			wp_enqueue_script( 'three-js-obj-loader' );
			wp_enqueue_script( 'three-js-gltf-loader' );
			wp_enqueue_script( 'three-js-draco-loader' );
			wp_enqueue_script( $this->plugin_name . '-thingviewer' );
			wp_enqueue_script( $this->plugin_name );
		}

		/**
		 * Thingviewer shortcode
		 *
		 * @since    1.0.0
		 * @param    array    $atts 	The shortcode attributes array
		 * @param    string   $content	The shortcode content
		 * @return   mixed    $result 	The shortcode output
		 */
		public function thingviewer_shortcode( $atts,  $content = null ) {
			global $wpdb;
			global $accolore_config;

			extract(shortcode_atts( array(
				'uid'              => uniqid(),
				'model_file'       => '',
				'model_id'         => '',
				'model_preview'    => '',
				'rotation_x'       => '',
				'rotation_y'       => '',
				'rotation_z'       => '',
			), $atts ));

			$api_url = get_rest_url(null, '/wp3dtvl/v1/model/');
			$nonce   = WP_3D_Thingviewer_Lite_Rest::create_nonce();

			// backward compatibility with older version shortcode (only url)
			if ($model_file != '') {
				$model_cloaked_url = $model_file;
				$model_url         = $model_file;
			} else {
				$url               = wp_get_attachment_url($model_id);
				$model_type        = pathinfo($url, PATHINFO_EXTENSION);
				$model_cloaked_url = $api_url . $nonce . '/' . $model_id . '.' . $model_type;
				$model_url = '';
			}

			ob_start();
			include 'partials/thingviewer-shortcode.php';
			$result = ob_get_clean();
		
			return $result;
		}
	}
}