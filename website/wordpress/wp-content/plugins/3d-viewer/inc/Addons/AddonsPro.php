<?php

namespace BP3D\Addons;

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

final class AddonsPro
{

	const VERSION = '1.0.0';
	const MINIMUM_ELEMENTOR_VERSION = '2.0.0';
	const MINIMUM_PHP_VERSION = '7.0';
	private static $_instance = null;

	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {}

	public function register()
	{
		//Register Frontend Script
		add_action("elementor/frontend/after_register_scripts", [$this, 'frontend_assets_scripts']);

		// // Add Plugin actions
		add_action('elementor/widgets/register', [$this, 'init_widgets']);
		add_action('elementor/editor/before_enqueue_scripts', [$this, 'editor_assets_scripts']);
	}


	/**
	 * Frontend script
	 */
	public function frontend_assets_scripts()
	{
		if (!wp_script_is('bp3d-model-viewer', 'registered')) {
			// wp_register_style('bp3d-public', BP3D_DIR . 'build/public.css', [], BP3D_VERSION);

			wp_register_script('bp3d-model-viewer', BP3D_DIR . 'public/js/model-viewer.latest.min.js', [], BP3D_VERSION, true);
			wp_register_script('bp3d-public', BP3D_DIR . 'build/frontend.js', ['react', 'react-dom', 'jquery'], BP3D_VERSION, true);
		}
	}

	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_widgets()
	{
		// Include Widget files
		require_once(__DIR__ . '/ModelViewer.php');

		// Register widget
		\Elementor\Plugin::instance()->widgets_manager->register(new ModelViewer());
		\Elementor\Plugin::instance()->widgets_manager->register(new BP3DProductModel());
	}

	public function editor_assets_scripts()
	{
		wp_enqueue_script('bp3d-o3dviewer', BP3D_DIR . 'public/js/o3dv.min.js', [], BP3D_VERSION, true);
		wp_enqueue_script('bp3d-model-viewer', BP3D_DIR . 'public/js/model-viewer.latest.min.js', [], BP3D_VERSION, true);
	}
}
