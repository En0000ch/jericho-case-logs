<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://visody.com/
 * @since      1.0.0
 *
 * @package    Visody
 * @subpackage Visody/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Visody
 * @subpackage Visody/public
 * @author     Visody <support@visody.com>
 */
class Visody_Public
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
	 * The check of the viewer has already been loaded.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    Checks state of the viewer.
	 */
	private $viewer_loaded;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name   = $plugin_name;
		$this->version       = $version;
		$this->viewer_loaded = false;

		add_action('init', array($this, 'register_shortcodes'), 99);
		add_action('wp_enqueue_scripts', array($this, 'register_public_scripts'), 99);
		add_action('visody_output_scripts', array($this, 'output_public_scripts'), 99);
		add_filter("script_loader_tag", array($this, "add_module_to_scripts"), 10, 3);

		add_filter( 'woocommerce_single_product_image_thumbnail_html', array($this, 'visody_load_inline_3d_model_viewer'), 99, 2 );
		add_action( 'woocommerce_product_thumbnails', array($this, 'visody_load_panel_3d_model_viewer'), 30 );
		add_action( 'woo_variation_product_gallery_end', array($this, 'visody_load_panel_3d_model_viewer'), 30 );

		add_action('init', array($this, 'set_plugin_gallery_control_hooks'));

		add_action('wp', array( $this, 'output_inline_model_viewer' ), 20);
	}

	public function output_inline_model_viewer() {
		$position = get_post_meta( get_the_ID(), 'visody_inline_viewer_position', true );
		if ( ! $position || 'replace' === $position ) {
			return;
		}

		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 10 );
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

		add_action( 'woocommerce_before_single_product_summary', array( $this, 'vsd_display_product_images' ) );
	}

	public function vsd_display_product_images() {

		if (!get_post_meta(get_the_ID(), 'visody_enable_viewer', true) || 'no' === get_post_meta(get_the_ID(), 'visody_enable_viewer', true)) {
			return;
		}
		if ('no' === get_post_meta(get_the_ID(), 'visody_inline_viewer', true)) {
			return;
		}
		if ('yes' === get_post_meta(get_the_ID(), 'visody_inline_shortcode_viewer', true)) {
			return;
		}

		if ($this->viewer_loaded) {
			return;
		}
		
		$position = get_post_meta( get_the_ID(), 'visody_inline_viewer_position', true );

		echo '<div class="vsd-model-gallery-wrapper">';

		if ( 'above' === $position ){
			echo do_shortcode( '[visody_viewer id="' . esc_attr( get_the_ID() ) . '"]');
		}

		woocommerce_show_product_images();

		if ( 'below' === $position ){
			echo do_shortcode( '[visody_viewer id="' . esc_attr( get_the_ID() ) . '"]');
		}

		echo '</div>';
	}

	public function set_plugin_gallery_control_hooks()
	{
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			// Add filter to 'woocommerce/product-image-gallery' block
			add_filter('render_block', array($this, 'render_3d_controls_in_product_gallery_block'), 10, 2);
		} else {
			// Dynamically place 3D buttons
			add_action('woocommerce_product_thumbnails', array($this, 'visody_load_3d_model_viewer_controls'), 30);
			add_action( 'woo_variation_product_gallery_end', array($this, 'visody_load_3d_model_viewer_controls'), 30);
		}
	}

	/**
	 * Register the configurator stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function register_public_scripts()
	{
		$min = defined( 'VISODY_LOCAL_DEV' ) ? '' : '.min';

		wp_register_style($this->plugin_name . '_public', plugin_dir_url(__FILE__) . 'css/visody-public' . $min . '.css', array(), $this->version, 'all');
		wp_add_inline_style($this->plugin_name . '_public', $this->get_inline_styles());

		wp_register_script($this->plugin_name . '_viewer', plugin_dir_url(__FILE__) . 'js/model-viewer.min.js', array(), '3.3.0', true);
		wp_register_script($this->plugin_name . '_public', plugin_dir_url(__FILE__) . 'js/visody-public' . $min . '.js', array('jquery'), $this->version, true);
		wp_add_inline_script($this->plugin_name . '_public', $this->get_inline_script(), 'before');
	}

	/**
	 * Register the configurator stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function output_public_scripts()
	{
		wp_enqueue_style($this->plugin_name . '_public');
		wp_enqueue_script($this->plugin_name . '_viewer');
		wp_enqueue_script($this->plugin_name . '_public');
	}

	/**
	 * Actually output registered scripts to frontend.
	 *
	 * @since    1.0.0
	 */
	public function add_module_to_scripts($tag, $handle, $src)
	{
		if ($handle === $this->plugin_name . '_viewer') {
			$tag = str_replace( '<script', '<script type="module"', $tag );
		}
		return $tag;
	}

	/**
	 * Set custom styles for buttons
	 *
	 * @since    1.0.0
	 */
	public function get_inline_styles()
	{
		$style = '';

		if ($bg_color = get_option('visody_viewer_bg_color')) {
			$style .= ' .vsd-model-viewer model-viewer, .vsd-model-viewer-loader { background-color: ' . esc_attr($bg_color) . '; }';
		}

		if ($loader_color = get_option('visody_viewer_loader_color')) {
			$style .= ' .vsd-model-viewer-loader-bar { background-color: ' . esc_attr($loader_color) . '; }';
		}
		if ($loader_bar_color = get_option('visody_viewer_loader_bar_color')) {
			$style .= ' .vsd-model-viewer-loader-progress { background-color: ' . esc_attr($loader_bar_color) . '; }';
		}

		if ($btn_loader_color = get_option('visody_button_loader_color')) {
			$style .= ' .vsd-model-viewer-control-buttons .vsd-loader-icon,';
			$style .= ' .vsd-model-viewer .vsd-loader-icon { stroke: ' . esc_attr($btn_loader_color) . ' !important; }';
		}

		if ($btn_color = get_option('visody_button_color')) {
			$style .= ' .vsd-model-viewer-control-buttons button,';
			$style .= ' .vsd-model-viewer button { color: ' . esc_attr($btn_color) . '; }';
		}
		if ($btn_bg_color = get_option('visody_button_bg_color')) {
			$style .= ' .vsd-model-viewer-control-buttons button,';
			$style .= ' .vsd-model-viewer button { background-color: ' . esc_attr($btn_bg_color) . '; }';
		}
		if (get_option('visody_button_border_width') && '' !== get_option('visody_button_border_width')) {
			$style .= ' .vsd-model-viewer-control-buttons button,';
			$style .= ' .vsd-model-viewer button { border: ' . esc_attr(get_option('visody_button_border_width')) . ' solid; }';
		}
		if ($btn_border_color = get_option('visody_button_border_color')) {
			$style .= ' .vsd-model-viewer-control-buttons button,';
			$style .= ' .vsd-model-viewer button { border-color: ' . esc_attr($btn_border_color) . '; }';
		}
		if ('' !== get_option('visody_button_border_radius')) {
			$style .= ' .vsd-model-viewer-control-buttons button,';
			$style .= ' .vsd-model-viewer button { border-radius: ' . esc_attr(get_option('visody_button_border_radius')) . '; }';
		}

		if ($btn_close_color = get_option('visody_close_button_color')) {
			$style .= ' .vsd-model-viewer .vsd-model-viewer-close-button { color: ' . esc_attr($btn_close_color) . '; }';
		}
		if ($btn_close_bg_color = get_option('visody_close_button_bg_color')) {
			$style .= ' .vsd-model-viewer .vsd-model-viewer-close-button { background-color: ' . esc_attr($btn_close_bg_color) . '; }';
		}
		if ($btn_close_border_color = get_option('visody_button_close_border_color')) {
			$style .= ' .vsd-model-viewer .vsd-model-viewer-close-button { border-color: ' . esc_attr($btn_close_border_color) . '; }';
		}

		if ($btn_hover_bg = get_option('visody_button_hover_bg_color')) {
			$style .= ' .vsd-model-viewer-control-buttons button:hover, .vsd-model-viewer-control-buttons button:focus,';
			$style .= ' .vsd-model-viewer button:not(.disabled):hover, .vsd-model-viewer button:not(.disabled):focus { background-color: ' . esc_attr($btn_hover_bg) . '; }';
		}
		if ($btn_hover_text = get_option('visody_button_hover_text_color')) {
			$style .= ' .vsd-model-viewer-control-buttons button:hover, .vsd-model-viewer-control-buttons button:focus,';
			$style .= ' .vsd-model-viewer button:not(.disabled):hover, .vsd-model-viewer button:not(.disabled):focus { color: ' . esc_attr($btn_hover_text) . '; }';
		}
		if ($btn_close_hover_bg = get_option('visody_close_button_hover_bg_color')) {
			$style .= ' .vsd-model-viewer .vsd-model-viewer-close-button:hover, .vsd-model-viewer .vsd-model-viewer-close-button:focus { background-color: ' . esc_attr($btn_close_hover_bg) . '; }';
		}
		if ($btn_close_hover_text = get_option('visody_close_button_hover_text_color')) {
			$style .= ' .vsd-model-viewer .vsd-model-viewer-close-button:hover, .vsd-model-viewer .vsd-model-viewer-close-button:focus { color: ' . esc_attr($btn_close_hover_text) . '; }';
		}

		if (get_option('visody_button_remove_shadow')) {
			$style .= ' .vsd-model-viewer-control-buttons button,';
			$style .= ' .vsd-model-viewer button { box-shadow: unset; }';
		}

		if (get_option('visody_custom_css')) {
			$style .= get_option('visody_custom_css');
		}

		$style = apply_filters('visody_inline_styles', $style);

		return $style;
	}

	/**
	 * Set variables for public script
	 *
	 * @since    1.0.0
	 */
	public function get_inline_script()
	{
		// Open up for change of default gallery selectors.
		$visody_gallery_selector = get_option('visody_class_gallery') ?: '.woocommerce-product-gallery';
		$visody_gallery_wrapper_selector = get_option('visody_class_gallery_wrapper') ?: '.woocommerce-product-gallery__wrapper';
		$visody_gallery_item_selector = get_option('visody_class_gallery_item') ?: '.woocommerce-product-gallery__image';
		$visody_gallery_item_active_selector = get_option('visody_class_gallery_item_active') ?: '.woocommerce-product-gallery__image.flex-active-slide';
		$visody_gallery_thumb_selector = get_option('visody_class_gallery_thumbnail_item') ?: '.flex-control-thumbs li';
		$visody_gallery_trigger_selector = get_option('visody_class_gallery_trigger') ?: '.woocommerce-product-gallery__trigger';

		$visody_check_interval = get_option('visody_check_gallery_interval') ?: 200;
		$visody_zoom_button_factor = get_option('visody_zoom_button_factor') ?: 2;

		return '
		var visody_gallery = "' . esc_attr($visody_gallery_selector) . '";
		var visody_gallery_wrapper = "' . esc_attr($visody_gallery_wrapper_selector) . '";
		var visody_gallery_slide = "' . esc_attr($visody_gallery_item_selector) . '";
		var visody_gallery_active_slide = "' . esc_attr($visody_gallery_item_active_selector) . '";
		var visody_gallery_control_thumbs_item = "' . esc_attr($visody_gallery_thumb_selector) . '";
		var visody_gallery_trigger = "' . esc_attr($visody_gallery_trigger_selector) . '";
		var visody_check_interval = ' . esc_attr(intval($visody_check_interval)) . ';
		var visody_zoom_factor = ' . esc_attr(intval($visody_zoom_button_factor)) . ';
		';
	}

	/**
	 * Load inline 3D product viewer.
	 *
	 * @since    1.0.0
	 */
	public function visody_load_inline_3d_model_viewer($html, $post_thumbnail_id)
	{
		if (!get_post_meta(get_the_ID(), 'visody_enable_viewer', true) || 'no' === get_post_meta(get_the_ID(), 'visody_enable_viewer', true)) {
			return $html;
		}
		if ('no' === get_post_meta(get_the_ID(), 'visody_inline_viewer', true)) {
			return $html;
		}
		if ('yes' === get_post_meta(get_the_ID(), 'visody_inline_shortcode_viewer', true)) {
			return $html;
		}

		$position = get_post_meta( get_the_ID(), 'visody_inline_viewer_position', true );
		if ( 'below' == $position || 'above' == $position ) {
			return $html;
		}

		if ($this->viewer_loaded) {
			return $html;
		}

		do_action( 'visody_output_scripts' );

		$model_html = visody_get_viewer_html(get_the_ID());

		$panel_html = '';
		$template_id = get_post_meta(get_the_ID(), 'visody_template', true);

		if (function_exists('visody_get_qrcode_panel_html')) {
			$panel_html = visody_get_qrcode_panel_html();

			if ($template_id && get_post_meta($template_id, 'disable_ar', true)) {
				$panel_html = '';
			}
		}

		$viewer_classes = '';
		if (get_option('visody_inside_float_button_position')) {
			$viewer_classes .= sprintf( ' vsd-button-float-%s', esc_attr( get_option('visody_inside_float_button_position') ) );
		} else {
			$viewer_classes .= ' vsd-button-float-bottom-right'; // default.
		}

		if (get_option('visody_control_button_position')) {
			$viewer_classes .= sprintf( ' vsd-button-align-%s', esc_attr( get_option('visody_control_button_position') ) );
		} else {
			$viewer_classes .= ' vsd-button-align-right'; // default.
		}

		if ('list' == get_post_meta($template_id, 'annotation_display', true) && get_post_meta(get_the_ID(), 'visody_viewer_notes', true)) {
			$viewer_classes .= ' vsd-model-viewer-control-bar';
		}

		if ('yes' === get_post_meta(get_the_ID(), 'visody_inline_viewer_last_slide', true)) {
			$viewer_classes .= ' vsd-model-viewer-last-slide';
		}

		$html = str_replace('woocommerce-product-gallery__image', 'woocommerce-product-gallery__image vsd-model-viewer__image', $html);

		$viewer_html = sprintf(
			'<div id="vsd-model-viewer-%d" class="vsd-model-viewer vsd-model-viewer-inline %s">',
			esc_attr( get_the_ID() ),
			esc_attr( $viewer_classes )
		);

		$viewer_html .= $model_html;
		if ('underneath' !== get_option('visody_float_button_position')) {
			$viewer_html .= $panel_html;
		}
		$viewer_html .= '</div>';

		$html = str_replace('</div>', $viewer_html . '</div>', $html);

		$html = apply_filters('visody_inline_viewer_html', $html, get_the_ID());

		$this->viewer_loaded = true;

		return $html;
	}

	/**
	 * Load panel 3D product viewer.
	 *
	 * @since    1.0.0
	 */
	public function visody_load_panel_3d_model_viewer()
	{
		if (!get_post_meta(get_the_ID(), 'visody_enable_viewer', true) || 'no' === get_post_meta(get_the_ID(), 'visody_enable_viewer', true)) {
			return;
		}
		if ('yes' === get_post_meta(get_the_ID(), 'visody_inline_shortcode_viewer', true)) {
			return;
		}
		if ('yes' === get_post_meta(get_the_ID(), 'visody_inline_viewer', true)) {
			return;
		}

		do_action( 'visody_output_scripts' );

		$button_html = visody_get_button_html('3d', array(
			'icon' =>  esc_url( VISODY_BASE_URL ) . 'public/img/3d-icon.svg',
			'alt'  => '3D model icon',
			'reader' => 'View 3D product viewer',
		));
		$model_html = visody_get_viewer_html(get_the_ID());

		$template_id = get_post_meta(get_the_ID(), 'visody_template', true);
		$panel_html = '';
		if (function_exists('visody_get_qrcode_panel_html')) {
			$panel_html = visody_get_qrcode_panel_html();

			if ($template_id && get_post_meta($template_id, 'disable_ar', true)) {
				$panel_html = '';
			}
		}

		$viewer_classes = '';
		if (get_option('visody_inside_float_button_position')) {
			$viewer_classes .= sprintf( ' vsd-button-float-%s', esc_attr( get_option('visody_inside_float_button_position') ) );
		} else {
			$viewer_classes .= ' vsd-button-float-bottom-right'; // default.
		}
		if (get_option('visody_control_button_position')) {
			$viewer_classes .= sprintf( ' vsd-button-align-%s', esc_attr( get_option('visody_control_button_position') ) );
		} else {
			$viewer_classes .= ' vsd-button-align-right'; // default.
		}

		if (get_option('visody_show_ar_desktop_button') && get_option('visody_show_ar_button_inline')) {
			$viewer_classes .= ' vsd-model-viewer-ar-inline';
		}

		if ('list' == get_post_meta($template_id, 'annotation_display', true) && get_post_meta(get_the_ID(), 'visody_viewer_notes', true)) {
			$viewer_classes .= ' vsd-model-viewer-control-bar';
		}

		$html = sprintf(
			'<div id="vsd-model-viewer-%d" class="vsd-model-viewer vsd-model-viewer-overlay%s">',
			get_the_ID(),
			$viewer_classes
		);

		if ('underneath' !== get_option('visody_float_button_position')) {
			if ( get_option( 'visody_show_ar_desktop_button' ) && get_option( 'visody_show_ar_button_inline' ) ) {
				$html .= sprintf(
					'<div class="vsd-model-viewer-inline-actions">%s</div>',
					$button_html . $panel_html
				);
			} else {
				$html .= $button_html . $panel_html;
			}
		}
		$html .= $model_html . '</div>';

		$html = apply_filters('visody_overlay_viewer_html', $html, get_the_ID());
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				waitForEl(visody_gallery_wrapper,  function () {
					$(visody_gallery_wrapper).append(
						`<?php echo $html; ?>`
					);
					visodyBindViewerEventHandlers();
				});
			});
		</script>
		<?php
	}

	/**
	 * Dynamically add 3D viewer controls underneath the WooCommerce gallery
	 *
	 * @since    1.0.0
	 */
	public function visody_load_3d_model_viewer_controls()
	{
		if ('underneath' !== get_option('visody_float_button_position')) {
			return;
		}
		$this->visody_display_3d_viewer_controls(false, true);
	}

	/**
	 * Remove options
	 *
	 * @since    1.0.0
	 */
	public function render_3d_controls_in_product_gallery_block($block_content, $block)
	{
		if ('underneath' !== get_option('visody_float_button_position')) {
			return $block_content;
		}

		if ($block['blockName'] === 'woocommerce/product-image-gallery') {
			// Returns altered $block_content to be rendered
			$new_block_content = $block_content;
			$new_block_content .= $this->visody_display_3d_viewer_controls(true);

			return $new_block_content;
		}

		return $block_content;
	}

	/**
	 * Register the buttons for public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function visody_display_3d_viewer_controls($return = false, $hidden = false)
	{
		if (!get_post_meta(get_the_ID(), 'visody_enable_viewer', true) || 'no' === get_post_meta(get_the_ID(), 'visody_enable_viewer', true)) {
			return;
		}
		if ('yes' === get_post_meta( get_the_ID(), 'visody_inline_shortcode_viewer', true)) {
			return;
		}

		$threed_button = visody_get_button_html('3d', array(
			'icon' =>  esc_url( VISODY_BASE_URL ) . 'public/img/3d-icon.svg',
			'alt'  => '3D model icon',
			'reader' => 'View 3D product viewer',
		));

		$ar_button = visody_get_button_html('ar', array(
			'icon' =>  esc_url( VISODY_BASE_URL ) . 'public/img/ar-icon.svg',
			'slot' => '',
			'alt'  => 'AR icon',
			'reader' => 'View model in AR',
			'loader' => true,
		));

		$classes = 'vsd-model-viewer-control-buttons control-buttons-gallery';
		if ( $hidden ) {
			$classes .= ' hidden';
		}

		$html = sprintf(
			'<div class="%s" data-viewer-id="vsd-model-viewer-%d">',
			esc_attr( $classes ),
			esc_attr( get_the_ID() )
		);
		if ('no' === get_post_meta(get_the_ID(), 'visody_inline_viewer', true)) {
			$html .= $threed_button;
		}
		$html .= $ar_button;

		if (function_exists('visody_get_qrcode_panel_html')) {
			$html .= visody_get_qrcode_panel_html();
		}

		$html .= '</div>';

		if ($return) {
			return $html;
		}

		echo $html;
	}

	/**
	 * Register shortcode for outputting the Visody 3D viewer on any page.
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes()
	{
		add_shortcode('visody_viewer', array($this, 'visody_load_shortcode_3d_model_viewer'));
	}

	/**
	 * Display Visody 3D viewer shortcode contents
	 *
	 * @since    1.0.0
	 */
	public function visody_load_shortcode_3d_model_viewer($attributes)
	{
		$viewer_id = false;
		if (isset($attributes['id'])) {
			$viewer_id = esc_attr(sanitize_key($attributes['id']));
		} else if ( 'product' === get_post_type() ) {
			$viewer_id = get_the_ID();
		}

		if ( ! $viewer_id ) {
			if (is_user_logged_in()) {
				return __('Viewer ID is not set or invalid', 'visody');
			}
			return;
		}

		do_action( 'visody_output_scripts' );

		$poster_id = '';
		if ('1' === get_post_meta($viewer_id, 'visody_viewer_show_poster', true) || 'yes' === get_post_meta($viewer_id, 'visody_viewer_show_poster', true)) {
			$poster_id = get_post_meta($viewer_id, 'visody_viewer_poster', true);
		}

		$model_html = visody_get_viewer_html($viewer_id, $poster_id, false, $attributes);
		$panel_html = '';
		if (function_exists('visody_get_qrcode_panel_html')) {
			$panel_html = visody_get_qrcode_panel_html($viewer_id);
		}

		$template_id = get_post_meta($viewer_id, 'visody_template', true);
		if ($template_id && get_post_meta($template_id, 'disable_ar', true)) {
			$panel_html = '';
		}

		$viewer_classes = '';
		if (get_option('visody_inside_float_button_position')) {
			$viewer_classes .= sprintf( ' vsd-button-float-%s', esc_attr( get_option('visody_inside_float_button_position') ) );
		} else {
			$viewer_classes .= ' vsd-button-float-bottom-right'; // default.
		}

		if (get_option('visody_control_button_position')) {
			$viewer_classes .= sprintf( ' vsd-button-align-%s', esc_attr( get_option('visody_control_button_position') ) );
		} else {
			$viewer_classes .= ' vsd-button-align-right'; // default.
		}

		if ('list' == get_post_meta($template_id, 'annotation_display', true) && get_post_meta($viewer_id, 'visody_viewer_notes', true)) {
			$viewer_classes .= ' vsd-model-viewer-control-bar';
		}

		$frame_class = '';
		if (get_post_meta($viewer_id, 'visody_viewer_frame_ratio', true)) {
			$frame_class = sprintf( ' ratio-%s', esc_attr( get_post_meta($viewer_id, 'visody_viewer_frame_ratio', true) ) );
		}
		if (get_post_meta($viewer_id, 'visody_viewer_frame_ratio_mobile', true)) {
			$frame_class .= sprintf( ' ratio-mobile-%s', esc_attr( get_post_meta($viewer_id, 'visody_viewer_frame_ratio_mobile', true) ) );
		}

		$panel_inside_html = '';
		$panel_underneath_html = '';
		if ('underneath' !== get_option('visody_float_button_position')) {
			$panel_inside_html = $panel_html;
		} else {
			$panel_underneath_html = sprintf(
				'<div id="vsd-model-viewer-control-buttons-%s" class="vsd-model-viewer-control-buttons" data-viewer-id="vsd-model-viewer-%s"></div>',
				esc_attr( $viewer_id ),
				esc_attr( $viewer_id ),
				$panel_html
			);
		}

		$shortcode_html = sprintf(
			'<div class="vsd-model-viewer-wrapper">
				<div class="vsd-model-viewer-frame%s">
					<div id="vsd-model-viewer-%s" class="vsd-model-viewer vsd-model-viewer-inline %s">
					%s
					%s
					</div>
				</div>
				%s
			</div>',
			esc_attr( $frame_class ),
			esc_attr( $viewer_id ),
			esc_attr( $viewer_classes ),
			$model_html,
			$panel_inside_html,
			$panel_underneath_html
		);

		$shortcode_html = apply_filters('visody_shortcode_viewer_html', $shortcode_html, $viewer_id);

		return $shortcode_html;
	}
}
