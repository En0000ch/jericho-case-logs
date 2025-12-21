<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://visody.com/
 * @since      1.0.0
 *
 * @package    Visody
 * @subpackage Visody/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Visody
 * @subpackage Visody/admin
 * @author     Visody <support@visody.com>
 */
class Visody_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('wp_ajax_save_form_option', array($this, 'save_form_option'));

		add_filter('wp_check_filetype_and_ext', array($this, 'modify_upload_mime_type_check'), 10, 5);
		add_filter('upload_mimes', array($this, 'modify_upload_mime_types'), 10, 2);

		add_action('in_admin_header', array($this, 'visody_admin_header'));
		add_filter('plugin_row_meta', array($this, 'plugin_listing_links'), 10, 4);
		add_action('after_setup_theme', array($this, 'register_visody_image_size'));

		add_filter( 'admin_footer_text', array( $this, 'edit_admin_footer_text' ) , 11 );
		add_filter( 'update_footer', array( $this, 'edit_admin_footer_version' ) , 11 );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		$min = defined( 'VISODY_LOCAL_DEV' ) ? '' : '.min';

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/visody-admin' . $min . '.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . '_font', plugin_dir_url(__FILE__) . 'css/visody-font.css', array(), $this->version, 'all');

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/visody-admin' . $min . '.js', array('jquery'), $this->version, true);
	}

	/**
	 * Add helpful links to plugin listing item row meta
	 * 
	 * @since   1.0.0
	 */
	public function plugin_listing_links($links_array, $plugin_file_name, $plugin_data, $status)
	{
		if (strpos($plugin_file_name, 'visody.php') !== false) {
			$links_array[] = wp_kses_post( '<a href="https://visody.com/docs/" target="_blank">Documentation</a>' );
		}
		return $links_array;
	}

	/**
	 * Allow 3D model uploads
	 *
	 * @since    1.0.0
	 */
	public function modify_upload_mime_types($mimes)
	{
		$mimes['gltf'] = 'model/gltf+json';
		$mimes['glb']  = 'model/gltf-binary';
		$mimes['hdr']  = 'image/hdr';
		return $mimes;
	}

	/**
	 * Modify mime type check for user.
	 *
	 * @since    1.0.0
	 */
	public function modify_upload_mime_type_check($data, $file, $filename, $mimes, $real_mime = null)
	{
		$ext = $type = $proper_filename = false;
		if (isset($data['ext'])) {
			$ext = $data['ext'];
		}
		if (isset($data['type'])) {
			$ext = $data['type'];
		}
		if (isset($data['proper_filename'])) {
			$ext = $data['proper_filename'];
		}

		if ($ext != false && $type != false) {
			return $data;
		}

		$f_sp = explode(".", $filename);
		$f_exp_count = count($f_sp);
		if ($f_exp_count <= 1) {
			return $data;
		}

		$f_name = $f_sp[0];
		$f_ext  = $f_sp[$f_exp_count - 1];

		$allowed_mimes = array(
			'gltf' => 'model/gltf+json',
			'glb'  => 'model/gltf-binary',
			'hdr'  => 'image/hdr',
		);

		$flag = false;
		foreach ($allowed_mimes as $mime_ext => $mime_type) {
			if (trim($mime_ext) === $f_ext) {
				$ext = $f_ext;
				$type = trim($mime_type);
				$flag = true;
				break;
			}
		}

		if (!$flag) {
			return $data;
		}

		return compact('ext', 'type', 'proper_filename');
	}

	/**
	 * Register plugin image sizes
	 *
	 * @since    1.0.0
	 */
	public function register_visody_image_size()
	{
		add_image_size('visody_icon', 32, 32);
	}

	/**
	 * Register the header for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function visody_admin_header()
	{
		global $typenow;
		if ('visody_viewer' !== $typenow && 'visody_template' !== $typenow && 'visody_viewer_note' !== $typenow) {
			return;
		}

		include esc_url( VISODY_BASE . 'admin/partials/admin-header.php' );
	}

	/**
	 * Customize the footer copy for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function edit_admin_footer_text( $content ) {
		global $typenow;
		if ('visody_viewer' !== $typenow && 'visody_template' !== $typenow && 'visody_viewer_note' !== $typenow) {
			return;
		}

		if ( $content ) {
			return sprintf(
				__( 'Thank you for creating with %s. Please rate %s to help spread the word!', 'visody'),
				'<a href="https://visody.com" target="_blank">Visody</a>',
				'<a href="https://wordpress.org/plugins/visody-3d-product-viewer/" target="_blank">★★★★★</a>'
			);
		}
	}

	/**
	 * Short description.
	 *
	 * @since    1.0.0
	 */
	public function edit_admin_footer_version( $content ) {
		global $typenow;
		if ('visody_viewer' !== $typenow && 'visody_template' !== $typenow && 'visody_viewer_note' !== $typenow) {
			return;
		}
		
		if ( $content ) {
			return __( 'Visody ', 'visody') . $this->version;
		}
	}
}
