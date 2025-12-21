<?php

/**
 * The file that defines the core plugin main options class
 *
 * @link       https://visody.com
 * @since      1.0.0
 *
 * @package    Visody
 * @subpackage Visody/admin
 */

/**
 * The core plugin options class.
 *
 * This is used to define core options.
 *
 * @since      1.0.0
 * @package    Visody
 * @subpackage Visody/admin
 * @author     Visody <support@visody.com>
 */

class Visody_Options
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
		add_filter('visody_register_option_pages', array($this, 'visody_register_option_page'));
	}

	/**
	 * Registers the options fields for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function visody_register_option_page()
	{
		$visody_options = array(
			'id'	      => 'visody_options',
			'title'       => 'Appearance options',
			'post_type'   => 'visody_viewer',
			'menu_name'   => 'Appearance',
			'parent_slug' => 'edit.php?post_type=visody_viewer',
			'capability'  => 'edit_posts',
			'tabs' => array(
				'appearance' => array(
					'name' => __( 'Viewer appearance', 'visody' ),
					'fields' => array(
						array(
							'id' => 'visody_viewer_bg_color',
							'label' => __( 'Viewer background color', 'visody' ),
							'type' => 'color',
							'default' => '#FFFFFF',
						),
						array(
							'id' => 'visody_viewer_loader_color',
							'label' => __( 'Loader bar color', 'visody' ),
							'type' => 'color',
							'default' => '#F1F1F1',
						),
						array(
							'id' => 'visody_viewer_loader_bar_color',
							'label' => __( 'Loader progress color', 'visody' ),
							'type' => 'color',
							'default' => '#000000',
						),
						array(
							'id' => 'visody_button_border_radius',
							'label' => __( 'Button border radius', 'visody' ),
							'type' => 'text',
							'class' => 'regular-text',
							'description' => __( 'Use CSS units (e.g. 1px)', 'visody' ),
						),
						array(
							'id' => 'visody_button_border_width',
							'label' => __( 'Button border width', 'visody' ),
							'type' => 'text',
							'class' => 'regular-text',
							'description' => __( 'Use CSS units (e.g. 1px)', 'visody' ),
						),
						array(
							'id' => 'visody_button_bg_color',
							'label' => __( 'Button background', 'visody' ),
							'type' => 'color',
							'default' => '#FFFFFF',
						),
						array(
							'id' => 'visody_button_border_color',
							'label' => __( 'Button border color', 'visody' ),
							'type' => 'color',
							'default' => '#000000',
						),
						array(
							'id' => 'visody_button_color',
							'label' => __( 'Button text color', 'visody' ),
							'type' => 'color',
							'default' => '#000000',
						),
						array(
							'id' => 'visody_button_loader_color',
							'label' => __( 'Button loader icon color', 'visody' ),
							'type' => 'color',
							'default' => '#000000',
							'description' => __( 'AR button loader icon color', 'visody' ),
						),
						array(
							'id' => 'visody_button_hover_bg_color',
							'label' => __( 'Button hover background', 'visody' ),
							'type' => 'color',
							'default' => '#F1F1F1',
						),
						array(
							'id' => 'visody_button_hover_text_color',
							'label' => __( 'Button hover text color', 'visody' ),
							'type' => 'color',
							'default' => '#000000',
						),
						array(
							'id' => 'visody_close_button_bg_color',
							'label' => __( 'Close button background', 'visody' ),
							'type' => 'color',
							'default' => '#000000',
						),
						array(
							'id' => 'visody_close_button_border_color',
							'label' => __( 'Close button border color', 'visody' ),
							'type' => 'color',
							'default' => '#000000',
						),
						array(
							'id' => 'visody_close_button_color',
							'label' => __( 'Close button text color', 'visody' ),
							'type' => 'color',
							'default' => '#FFFFFF',
						),
						array(
							'id' => 'visody_close_button_hover_bg_color',
							'label' => __( 'Close button hover background', 'visody' ),
							'type' => 'color',
							'default' => '#000000',
						),
						array(
							'id' => 'visody_close_button_hover_text_color',
							'label' => __( 'Close button hover text color', 'visody' ),
							'type' => 'color',
							'default' => '#FFFFFF',
						),
						array(
							'id' => 'visody_button_remove_shadow',
							'label' => __( 'Button shadow', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Remove button shadows', 'visody' ),
						),
					)
				),
				'controls' => array(
					'name' => __( 'Viewer controls', 'visody' ),
					'fields' => array(
						array(
							'id' => 'visody_show_fullscreen_button',
							'label' => __( 'Fullscreen button display', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Show fullscreen button in viewer', 'visody' ),
						),
						array(
							'id' => 'visody_show_camera_button',
							'label' => __( 'Camera button display', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Show camera button in viewer', 'visody' ),
						),
						array(
							'id' => 'visody_show_zoom_buttons',
							'label' => __( 'Zoom buttons display', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Show zoom in and out buttons in viewer', 'visody' ),
							'description' => __( 'Note: this automatically disables the zoom to scroll ability in the viewer', 'visody' ),
						),
						array(
							'id' => 'visody_zoom_button_factor',
							'label' => __( 'Zoom button factor', 'visody' ),
							'type' => 'number',
							'placeholder' => 2,
							'min' => 1,
							'max' => 20,
							'description' => __( 'Enter zoom factor. Higher numbers will change zoom level faster.', 'visody' ),
							'show_if' => array(
								'id' => 'visody_show_zoom_buttons',
								'value' => 'yes'
							),
						),
						array(
							'id' => 'visody_float_button_position',
							'label' => __( 'Floating button position', 'visody' ),
							'type' => 'select',
							'options' => array(
								'inside' => __( 'Inside the product image', 'visody' ),
								'underneath' => __( 'Underneath the product images', 'visody' ),
							),
							'default' => 'inside',
							'short_description' => __( 'Position of floating 3D and AR buttons', 'visody' ),
						),
						array(
							'id' => 'visody_inside_float_button_position',
							'label' => __( 'Floating gallery button position', 'visody' ),
							'type' => 'select',
							'options' => array(
								'bottom-right' => __( 'Bottom right', 'visody' ),
								'bottom-left' => __( 'Bottom left', 'visody' ),
								'top-right' => __( 'Top right', 'visody' ),
								'top-left' => __( 'Top left', 'visody' ),
							),
							'default' => 'bottom-right',
							'short_description' => __( 'Position of floating buttons within the gallery', 'visody' ),
						),
						array(
							'id' => 'visody_control_button_position',
							'label' => __( 'Control button positions', 'visody' ),
							'type' => 'select',
							'options' => array(
								'right' => __( 'Right', 'visody' ),
								'left' => __( 'Left', 'visody' ),
							),
							'default' => 'right',
							'short_description' => __( 'Position of floating 3D and AR buttons', 'visody' ),
						),
						array(
							'id' => 'visody_disable_3d_button_icon',
							'label' => __( '3D button icon display', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Hide 3D button icon', 'visody' ),
						),
						array(
							'id' => 'visody_3d_button_text',
							'label' => __( '3D button text', 'visody' ),
							'type' => 'text',
						),
						array(
							'id' => 'visody_disable_ar_button_icon',
							'label' => __( 'AR button icon display', 'visody' ),
							'type' => 'checkbox',
							'short_description' => 'Hide AR button icon',
						),
						array(
							'id' => 'visody_ar_button_text',
							'label' => __( 'AR button text', 'visody' ),
							'type' => 'text',
						),
						array(
							'id' => 'visody_close_button_text',
							'label' => __( 'Close button label', 'visody' ),
							'type' => 'text',
						),
						array(
							'id' => 'visody_fs_button_text',
							'label' => __( 'Fullscreen button label', 'visody' ),
							'type' => 'text',
						),
						array(
							'id' => 'visody_cam_button_text',
							'label' => __( 'Camera button label', 'visody' ),
							'type' => 'text',
						),
					)
				),
				'icons' => array(
					'name' => __( 'Viewer icons', 'visody' ),
					'fields' => array(
						array(
							'id' => 'visody_ar_button_icon',
							'label' => __( 'AR button icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_ar_button_hover_icon',
							'label' => __( 'AR button hover icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_3d_button_icon',
							'label' => __( '3D button icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_3d_button_hover_icon',
							'label' => __( '3D button hover icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_close_button_icon',
							'label' => __( 'Close button icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_close_button_hover_icon',
							'label' => __( 'Close button hover icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_fs_button_icon',
							'label' => __( 'Fullscreen button icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_fs_button_hover_icon',
							'label' => __( 'Fullscreen button hover icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_cam_button_icon',
							'label' => __( 'Camera button icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_cam_button_hover_icon',
							'label' => __( 'Camer button hover icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_zoom-in_button_icon',
							'label' => __( 'Zoom in button icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_zoom-in_button_hover_icon',
							'label' => __( 'Zoom in button hover icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_zoom-out_button_icon',
							'label' => __( 'Zoom out button icon', 'visody' ),
							'type' => 'image',
						),
						array(
							'id' => 'visody_zoom-out_button_hover_icon',
							'label' => __( 'Zoom out button hover icon', 'visody' ),
							'type' => 'image',
						),
					)
				),
				'advanced' => array(
					'name' => __( 'Advanced', 'visody' ),
					'fields' => array(
						array(
							'id' => 'visody_class_gallery',
							'label' => __( 'Gallery selector', 'visody' ),
							'type' => 'text',
							'class' => 'regular-text',
							'placeholder' => '.woocommerce-product-gallery',
						),
						array(
							'id' => 'visody_class_gallery_wrapper',
							'label' => __( 'Gallery wrapper selector', 'visody' ),
							'type' => 'text',
							'class' => 'regular-text',
							'placeholder' => '.woocommerce-product-gallery__wrapper',
						),
						array(
							'id' => 'visody_class_gallery_item',
							'label' => __( 'Gallery item selector', 'visody' ),
							'type' => 'text',
							'class' => 'regular-text',
							'placeholder' => '.woocommerce-product-gallery__image',
						),
						array(
							'id' => 'visody_class_gallery_item_active',
							'label' => __( 'Gallery item active selector', 'visody' ),
							'type' => 'text',
							'class' => 'regular-text',
							'placeholder' => '.woocommerce-product-gallery__image.flex-active-slide',
						),
						array(
							'id' => 'visody_class_gallery_thumbnail_item',
							'label' => __( 'Gallery thumbnail item selector', 'visody' ),
							'type' => 'text',
							'class' => 'regular-text',
							'placeholder' => '.flex-control-thumbs li',
						),
						array(
							'id' => 'visody_class_gallery_trigger',
							'label' => __( 'Gallery trigger selector', 'visody' ),
							'type' => 'text',
							'class' => 'regular-text',
							'placeholder' => '.woocommerce-product-gallery__trigger',
						),
						array(
							'id' => 'visody_check_gallery_interval',
							'label' => __( 'Gallery checker interval', 'visody' ),
							'type' => 'number',
							'default' => '200',
							'min' => 100,
							'description' => __( 'Interval to check if gallery has been loaded (in milliseconds)', 'visody' ),
							'class' => 'regular-text',
						),
						array(
							'id' => 'visody_custom_css',
							'label' => __( 'Custom CSS', 'visody' ),
							'type' => 'textarea',
							'description' => '',
							'rows' => '15',
							'class' => 'regular-text',
							'default' => '',
						),
					)
				)
			)
		);

		$visody_options = apply_filters( 'visody_admin_settings_fields', $visody_options );

		$metaboxes[] = $visody_options;

		return $metaboxes;
	}
}
