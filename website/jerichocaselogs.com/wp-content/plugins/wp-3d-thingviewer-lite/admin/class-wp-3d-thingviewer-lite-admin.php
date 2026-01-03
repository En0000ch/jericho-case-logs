<?php
/**
 * The dashboard-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    WP_3D_Thingviewer_Lite
 * @subpackage WP_3D_Thingviewer_Lite/admin
 * @author     Accolore <support@accolore.com>
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//include_once ('class-tgm-plugin-activation.php');

if (! class_exists('WP_3D_Thingviewer_Lite_Admin')) {
	class WP_3D_Thingviewer_Lite_Admin {

		/**
		 * The text domain of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @param    string    $plugin_name    The text domain of the plugin (used as unique ID)
		 */
		private $plugin_name;

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @param    WP_Accolore_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;

		/**
		 * The notices handler class responsible to handle all administration notices for the plugin
		 *
		 * @since    2.0.0
		 * @access   protected
		 * @param    WP_Accolore_Notices    $notices    Maintains and registers all notices for the plugin.
		 */
		protected $notices;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 * @param    string    $plugin_name     The name of this plugin.
		 */
		public function __construct( $plugin_name ) {
			$this->plugin_name = $plugin_name;
			$this->loader = WP_Accolore_Loader::get_instance();
			$this->notices = WP_Accolore_Notices::get_instance();
		}

		/**
		 * Display a notice after plugin activation once
		 *
		 * @since    1.0.0
		 */
		public function display_activation_notices () {
			if ( is_admin() && get_option( 'Activated_Plugin' ) == $this->plugin_name ) {
				delete_option( 'Activated_Plugin' );
				
				/*
				$this->notices->enqueue_notice(
					'WP 3D Thingviewer',
					__( "With the 2.9 version the plugin does not need anymore the plugin Redux Framework to work. You can safely disable if you don't need.", $this->plugin_name ),
					'notice-warning is-dismissible'
				);
				echo $this->notices->generate_output();
				*/
			}
		}

		/**
		 * Register custom Gutenberg block
		 *
		 * @since    1.0.0
		 */
		public function register_gutemberg_block() {
			global $accolore_config;
			
			$version = $accolore_config[$this->plugin_name]['version'];

			if ( ! function_exists( 'register_block_type' ) ) {
				// Gutenberg is not active.
				return;
			}

			wp_register_script( 'wp3dtvl-gutemberg-block', plugin_dir_url( __FILE__ ) . 'js/gutemberg/build/index.js', array('wp-blocks','wp-editor', 'wp-components', 'wp-element'), $version, false );
			
			register_block_type( 'wp3d-thingviewer-lite/gutemberg-block', array(
				'editor_script' => 'wp3dtvl-gutemberg-block',
			));
		}

		/**
		 * Enqueue custom TinyMCE shortcode generator buttons
		 *
		 * @since    1.0.0
		 */
		public function enqueue_mce_buttons() {
			$tiny_mce = new WP_3D_Thingviewer_Lite_Tinymce( $this->plugin_name );

			// Check if user have permission
			if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
				return;
			}
			// Check if WYSIWYG is enabled
			if ( 'true' == get_user_option( 'rich_editing' ) ) {
				//$this->loader->add_filter('mce_buttons', $tiny_mce, 'register_button');
				//$this->loader->add_filter('mce_external_plugins', $tiny_mce, 'register_plugin');

				add_filter( 'mce_buttons', array( $tiny_mce, 'register_button' ));
				add_filter( 'mce_external_plugins', array( $tiny_mce, 'register_plugin' ));		
			}
		}

		/**
		 * Add STL MIME types for media uploading
		 *
		 * @since    1.0.0
		 * @params $mimes <array> Array of allowed MIME types
		 */
		public function add_upload_mime_types ( $mimes ) {
			$mimes['stl']   = 'application/sla';
			$mimes['fbx']   = 'application/octet-stream';
			$mimes['glb']   = 'application/octet-stream';
			$mimes['drc']   = 'application/octet-stream';
			$mimes['obj']   = 'text/plain';
			
			return $mimes;
		}

		/**
		 * Add STL MIME types filter into media manager
		 *
		 * @since    1.0.0
		 * @params $post_mime_types <array> Array of MIME data for media gallery
		 */
		public function add_media_mime_types ( $post_mime_types ) {
			global $accolore_config;

			$textdomain = $accolore_config[$this->plugin_name]['text_domain'];

			$post_mime_types['application/sla'] = array(
				__( 'STL Files', $textdomain ),
				__( 'Manage STL', $textdomain ),
				__( 'STL Files <span class="count">(%s)</span>', $textdomain )
			);

			return $post_mime_types;
		}

	}
}