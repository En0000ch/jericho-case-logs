<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://visody.com/
 * @since      1.0.0
 *
 * @package    Visody
 * @subpackage Visody/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Visody
 * @subpackage Visody/includes
 * @author     Visody <support@visody.com>
 */
class Visody
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Visody_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('VISODY_VERSION')) {
			$this->version = VISODY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'visody';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_core_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Visody_Loader. Orchestrates the hooks of the plugin.
	 * - Visody_i18n. Defines internationalization functionality.
	 * - Visody_Admin. Defines all hooks for the admin area.
	 * - Visody_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-visody-loader.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/visody-functions.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-visody-i18n.php';

		/**
		 * The class responsible for defining the admin fields functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/simple-fields/visody-simple-fields.php';

		/**
		 * The classes responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-visody-admin.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-visody-welcome.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-visody-viewer.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-visody-viewer-template.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-visody-options.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-visody-woocommerce.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-visody-public.php';

		/**
		 * The class responsible for defining all paid functions
		 */

		if (visody_fs()->is_plan_or_trial('pro')) {
			if (file_exists(plugin_dir_path(dirname(__FILE__)) . 'pro/visody-pro-functions.php')) {

				require_once plugin_dir_path(dirname(__FILE__)) . 'pro/visody-pro-functions.php';

				require_once plugin_dir_path(dirname(__FILE__)) . 'pro/class-visody-pro.php';

				require_once plugin_dir_path(dirname(__FILE__)) . 'pro/class-visody-pro-admin.php';

				require_once plugin_dir_path(dirname(__FILE__)) . 'pro/class-visody-pro-viewer.php';

				require_once plugin_dir_path(dirname(__FILE__)) . 'pro/class-visody-pro-annotation.php';
			}
		}

		$this->loader = new Visody_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Visody_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{
		$plugin_i18n = new Visody_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to both the admin area and public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_core_hooks()
	{
		/**
		 * Core hooks.
		 */
		$plugin_viewer = new Visody_Viewer();
		$plugin_viewer_template = new visody_template();

		if (visody_fs()->is_plan_or_trial('pro')) {
			if (class_exists('Visody_Pro_Viewer')) {
				$plugin_viewer_pro = new Visody_Pro_Viewer();
				$plugin_viewer_annotation = new Visody_Pro_Annotation();
			}
		}
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		/**
		 * Admin hooks.
		 */
		$plugin_admin = new Visody_Admin($this->get_plugin_name(), $this->get_version());
		$plugin_welcome = new Visody_Welcome();
		$plugin_options = new Visody_Options();
		$plugin_wc = new Visody_WooCommerce();

		if (visody_fs()->is_plan_or_trial('pro')) {
			if (class_exists('Visody_Pro_Admin')) {
				$plugin_admin_pro = new Visody_Pro_Admin($this->get_plugin_name(), $this->get_version());
			}
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{
		/**
		 * Plugin Public hooks.
		 */
		$plugin_public = new Visody_Public($this->get_plugin_name(), $this->get_version());

		if (visody_fs()->is_plan_or_trial('pro')) {
			if (class_exists('Visody_Pro')) {
				$plugin_pro = new Visody_Pro($this->get_plugin_name(), $this->get_version());
			}
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Visody_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
