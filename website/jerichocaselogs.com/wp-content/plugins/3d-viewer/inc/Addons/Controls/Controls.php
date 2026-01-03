<?php
namespace BP3D\Addons\Controls;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

final class Controls {

	const VERSION = '1.0.0';
	const MINIMUM_ELEMENTOR_VERSION = '2.0.0';
	const MINIMUM_PHP_VERSION = '7.0';

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'elementor/controls/register', [ $this, 'init_controls' ] );
	}

	public function init_controls($controls_manager) {

		// Include Widget files
		require_once( __DIR__ . '/b-select-file.php' );

		// Register controls
		$controls_manager->register( new SelectFile() );
	}

}
