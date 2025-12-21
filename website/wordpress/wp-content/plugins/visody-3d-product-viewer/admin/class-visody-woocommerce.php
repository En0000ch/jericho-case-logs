<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://visody.com/
 * @since      1.0.0
 *
 * @package    Visody
 * @subpackage Visody/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Visody
 * @subpackage Visody/includes
 * @author     Visody <support@visody.com>
 */
class Visody_WooCommerce
{
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name       The name of this plugin.
	 * @param    string $version   		   The version of this plugin.
	 */
	public function __construct()
	{
		add_action('admin_head', array($this, 'action_admin_head'));
		add_filter('woocommerce_product_data_tabs', array($this, 'visody_add_viewer_tab'));
		add_action('woocommerce_product_data_panels', array($this, 'visody_add_viewer_tab_fields'));
		add_action('woocommerce_process_product_meta', array($this, 'visody_process_viewer_tab_meta_save'));
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function visody_add_viewer_tab($tabs)
	{
		$tabs['visody_viewer'] = array(
			'label'  => __('Visody Viewer', 'visody'),
			'target' => 'visody_viewer_tab',
		);

		return $tabs;
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function visody_add_viewer_tab_fields($settings)
	{
		global $woocommerce, $post;
		?>
		<div id="visody_viewer_tab" class="panel woocommerce_options_panel">

			<?php
			echo wp_nonce_field( 'visody', 'visody_wpnonce' );

			woocommerce_wp_checkbox(array(
				'id'            => 'visody_enable_viewer',
				'wrapper_class' => '',
				'label'         => __('Visody viewer', 'visody'),
				'description'   => __('Activate Visody 3D product viewer', 'visody'),
				'default'       => '0',
			));

			/**
			 * Viewer model upload
			 */
			$visody_viewer_model = get_post_meta($post->ID, 'visody_viewer_model', true) ?? null;
			?>
			<p class="form-field vsd-file-upload visody_viewer_model<?php if ($visody_viewer_model) echo ' remove'; ?>">
				<label for="visody_viewer_model_button">3D model</label>

				<input type="hidden" name="upload_visody_viewer_model" class="vsd-file-upload-field upload_visody_viewer_model" value="<?php echo esc_attr($visody_viewer_model); ?>" />

				<?php if ($visody_viewer_model) :
					$image_path = get_attached_file($visody_viewer_model);
					$filesize = '';
					$filename = '';
					if (is_file($image_path)) {
						$filesize = size_format(filesize($image_path));
						$filename = basename($image_path);
					}
					$fileicon = wp_get_attachment_image_src($visody_viewer_model, '', true);
					?>

					<span class="thumbnail">
						<img src="<?php echo is_array($fileicon) ? esc_url($fileicon[0]) : esc_url(wc_placeholder_img_src()); ?>" />
						<span class="details">
							<span class="details-title"><?php echo esc_attr($filename); ?></span>
							<span class="details-size"><?php echo esc_attr($filesize); ?></span>
						</span>
					</span>

				<?php else : ?>

					<span class="thumbnail">
						<img src="" />
						<span class="details">
							<span class="details-title"></span>
							<span class="details-size"></span>
						</span>
					</span>

				<?php endif; ?>

				<a href="#" id="visody_viewer_model_button" class="visody_viewer_model_button vsd-file-upload-button <?php echo $visody_viewer_model ? 'remove' : ''; ?>" rel="<?php echo esc_attr($post->ID); ?>">
					<span class="action-name"><?php echo ($visody_viewer_model) ? 'Remove' : 'Set'; ?> 3D model</span>
				</a>
			</p>
			<?php
			woocommerce_wp_text_input(array(
				'id'            => 'visody_viewer_model_url',
				'wrapper_class' => '',
				'label'         => __('3D model url', 'visody'),
				'description'   => __('Enter URL to location of your 3D model. Use only when no 3D model set.', 'visody'),
				'default'       => '0',
				'desc_tip'      => true,
			));

			woocommerce_wp_text_input(array(
				'id'            => 'visody_viewer_ios_model_url',
				'wrapper_class' => '',
				'label'         => __('3D iOS model url', 'visody'),
				'description'   => __('Enter full URL to the iOS 3D model file location (USDZ format). Make sure location is publicly accessible.', 'visody'),
				'default'       => '0',
				'desc_tip'      => true,
			));

			woocommerce_wp_checkbox(array(
				'id'            => 'visody_inline_viewer',
				'wrapper_class' => '',
				'label'         => __('Inline viewer', 'visody'),
				'description'   => __('Show product viewer inline in WooCommerce gallery', 'visody'),
				'default'       => '0',
			));

			woocommerce_wp_select(array(
				'id'            => 'visody_inline_viewer_position',
				'wrapper_class' => '',
				'label'         => __('Inline position', 'visody'),
				'description'   => __('Select viewer inline position for the 3D model', 'visody'),
				'default'       => '0',
				'desc_tip'      => true,
				'options'       => array(
					'replace' => __( 'On top of the gallery image', 'visody' ),
					'above' => __( 'Above the gallery image', 'visody' ),
					'below' => __( 'Below the gallery image', 'visody' ),
				),
			));

			woocommerce_wp_checkbox(array(
				'id'            => 'visody_inline_viewer_last_slide',
				'wrapper_class' => '',
				'label'         => __('Gallery slide display', 'visody'),
				'description'   => __('Show product viewer inline on last slide in WooCommerce Gallery', 'visody'),
				'default'       => '0',
			));

			woocommerce_wp_checkbox(array(
				'id'            => 'visody_viewer_show_percentage',
				'wrapper_class' => '',
				'label'         => __('3D loader percentage', 'visody'),
				'description'   => __('Show loader percentage above loader bar (not visible when using loader image)', 'visody'),
				'default'       => '0',
			));

			woocommerce_wp_checkbox(array(
				'id'            => 'visody_viewer_show_poster',
				'wrapper_class' => '',
				'label'         => __('3D model poster', 'visody'),
				'description'   => __('Use product thumbnail as loader image', 'visody'),
				'default'       => '0',
			));

			$viewer_templates = visody_get_viewer_template_options();

			woocommerce_wp_select(array(
				'id'            => 'visody_template',
				'wrapper_class' => '',
				'label'         => __('Viewer template', 'visody'),
				'description'   => __('Select viewer template for the 3D model', 'visody'),
				'default'       => '0',
				'desc_tip'      => true,
				'options'       => $viewer_templates,
			));

			if ( function_exists( 'visody_get_viewer_note_options' ) ) {
				$viewer_notes = visody_get_viewer_note_options();

				woocommerce_wp_select(array(
					'id'            => 'visody_viewer_notes',
					'wrapper_class' => '',
					'label'         => __('Viewer notes', 'visody'),
					'description'   => __('Select annotations for the 3D model', 'visody'),
					'default'       => '0',
					'desc_tip'      => true,
					'options'       => $viewer_notes,
				));
			}

			woocommerce_wp_checkbox(array(
				'id'            => 'visody_inline_shortcode_viewer',
				'wrapper_class' => '',
				'label'         => __('Shortcode display', 'visody'),
				'description'   => __('Show product viewer inline at shortcode location instead of WooCommerce Gallery', 'visody'),
				'default'       => '0',
			));

			woocommerce_wp_select(array(
				'id'            => 'visody_viewer_frame_ratio',
				'wrapper_class' => '',
				'label'         => __('Viewer frame ratio', 'visody'),
				'description'   => __('Customize viewer frame ratio. Only applicable to shortcode display.', 'visody'),
				'default'       => '',
				'desc_tip'      => true,
				'options'       => array(
					'' => '- Choose frame ratio -',
					'landscape' => '16:9 (default landscape)',
					'wide' => '16:10 (landscape)',
					'fourthree' => '4:3 (landscape)',
					'threetwo' => '3:2 (landscape)',
					'portrait' => '9:16 (default portrait)',
					'high' => '10:16 (portrait)',
					'threefour' => '3:4 (portrait)',
					'twothree' => '2:3 (portrait)',
					'squared' => '1:1 (square)',
				),
			));

			woocommerce_wp_select(array(
				'id'            => 'visody_viewer_frame_ratio_mobile',
				'wrapper_class' => '',
				'label'         => __('Viewer frame ratio mobile', 'visody'),
				'description'   => __('Customize viewer frame ratio for mobile. Only applicable to shortcode display.', 'visody'),
				'default'       => '',
				'desc_tip'      => true,
				'options'       => array(
					'' => '- Choose frame ratio mobile -',
					'portrait' => '9:16 (default portrait)',
					'high' => '10:16 (portrait)',
					'threefour' => '3:4 (portrait)',
					'twothree' => '2:3 (portrait)',
					'landscape' => '16:9 (default landscape)',
					'wide' => '16:10 (landscape)',
					'fourthree' => '4:3 (landscape)',
					'threetwo' => '3:2 (landscape)',
					'squared' => '1:1 (square)',
				),
			));
			?>
		</div>
		<?php
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function visody_process_viewer_tab_meta_save($post_id)
	{
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (defined('DOING_AJAX') && DOING_AJAX) {
			return;
		}

		if ( ! isset( $_POST['visody_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['visody_wpnonce'] ) ), 'visody' ) ) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$enabled = isset($_POST['visody_enable_viewer']) ? 'yes' : 'no';
		update_post_meta($post_id, 'visody_enable_viewer', esc_attr($enabled));

		$inline = isset($_POST['visody_inline_viewer']) ? 'yes' : 'no';
		update_post_meta($post_id, 'visody_inline_viewer', esc_attr($inline));

		$last_slide = isset($_POST['visody_inline_viewer_last_slide']) ? 'yes' : 'no';
		update_post_meta($post_id, 'visody_inline_viewer_last_slide', esc_attr($last_slide));

		$shortcode = isset($_POST['visody_inline_shortcode_viewer']) ? 'yes' : 'no';
		update_post_meta($post_id, 'visody_inline_shortcode_viewer', esc_attr($shortcode));

		$show_percentage = isset($_POST['visody_viewer_show_percentage']) ? 'yes' : 'no';
		update_post_meta($post_id, 'visody_viewer_show_percentage', esc_attr($show_percentage));

		$show_poster = isset($_POST['visody_viewer_show_poster']) ? 'yes' : 'no';
		update_post_meta($post_id, 'visody_viewer_show_poster', esc_attr($show_poster));

		if (isset($_POST['visody_inline_viewer_position'])) {
			$value = wc_clean(wp_unslash($_POST['visody_inline_viewer_position']));
			update_post_meta($post_id, 'visody_inline_viewer_position', esc_attr($value));
		}

		if (isset($_POST['upload_visody_viewer_model'])) {
			$value = wc_clean(wp_unslash($_POST['upload_visody_viewer_model']));
			update_post_meta($post_id, 'visody_viewer_model', esc_attr($value));
		}

		if (isset($_POST['visody_viewer_model_url'])) {
			$value = wc_clean(wp_unslash($_POST['visody_viewer_model_url']));
			update_post_meta($post_id, 'visody_viewer_model_url', esc_attr($value));
		}

		if (isset($_POST['visody_viewer_ios_model_url'])) {
			$value = wc_clean(wp_unslash($_POST['visody_viewer_ios_model_url']));
			update_post_meta($post_id, 'visody_viewer_ios_model_url', esc_attr($value));
		}

		if (isset($_POST['visody_template'])) {
			$value = wc_clean(wp_unslash($_POST['visody_template']));
			update_post_meta($post_id, 'visody_template', esc_attr($value));
		}

		if (isset($_POST['visody_viewer_frame_ratio'])) {
			$value = wc_clean(wp_unslash($_POST['visody_viewer_frame_ratio']));
			update_post_meta($post_id, 'visody_viewer_frame_ratio', esc_attr($value));
		}

		if (isset($_POST['visody_viewer_frame_ratio_mobile'])) {
			$value = wc_clean(wp_unslash($_POST['visody_viewer_frame_ratio_mobile']));
			update_post_meta($post_id, 'visody_viewer_frame_ratio_mobile', esc_attr($value));
		}

		if (isset($_POST['visody_viewer_notes'])) {
			$value = wc_clean(wp_unslash($_POST['visody_viewer_notes']));
			update_post_meta($post_id, 'visody_viewer_notes', esc_attr($value));
		}
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function action_admin_head()
	{
		echo '<style>
			#woocommerce-product-data ul.wc-tabs li.visody_viewer_options a::before {
				content: "\f129";
			}
		</style>';
	}
}
