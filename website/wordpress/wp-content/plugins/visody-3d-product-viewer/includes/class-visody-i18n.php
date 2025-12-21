<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://visody.com/
 * @since      1.0.0
 *
 * @package    Visody
 * @subpackage Visody/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Visody
 * @subpackage Visody/includes
 * @author     Visody <support@visody.com>
 */
class Visody_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$pro_dir = dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/pro/';

		if (visody_fs()->is_plan_or_trial('pro') && file_exists($pro_dir) ) {
			load_plugin_textdomain(
				'visody',
				false,
				dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/pro/languages/'
			);
		} else {
			load_plugin_textdomain(
				'visody',
				false,
				dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
			);
		}
	}
}
