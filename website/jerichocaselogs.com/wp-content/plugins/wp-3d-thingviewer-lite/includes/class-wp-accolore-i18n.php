<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WP_Accolore
 * @subpackage WP_Accolore/includes
 * @author     Accolore <support@accolore.com>
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (! class_exists('WP_Accolore_i18n')) {
	class WP_Accolore_i18n {

		/**
		 * The text domain specified for this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @param    string    $text_domain    The text domain identifier for this plugin.
		 */
		private $text_domain;

		/**
		 * Set the domain equal to that of the specified domain.
		 *
		 * @since    1.0.0
		 * @param    string    $text_domain    The text domain that represents the locale of this plugin.
		 */
		public function __construct( $text_domain ) {
			$this->text_domain = $text_domain;
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain(
				$this->text_domain,
				false,
				$this->text_domain .'/languages/'
			);
		}

		/**
		 * Set the translation for the Gutenberg block editor integration
		 *
		 * @since    2.8.1
		 */
		public function set_script_translations() {
        	wp_set_script_translations( 'wp3dtvl-gutemberg-block', $this->text_domain, plugin_dir_path( dirname(__FILE__) ) . 'languages' );
	   	}
	}
}