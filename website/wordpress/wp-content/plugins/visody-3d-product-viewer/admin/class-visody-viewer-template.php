<?php

/**
 * The file that defines the core plugin viewer template class
 *
 * @link       https://visody.app
 * @since      1.0.0
 *
 * @package    Visody
 * @subpackage Visody/admin
 */

/**
 * The core plugin viewer class.
 *
 * This is used to define core viewer template post type and maintain its data.
 *
 * @since      1.0.0
 * @package    Visody
 * @subpackage Visody/admin
 * @author     Visody <support@visody.com>
 */

class visody_template
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
		add_action('init', array($this, 'register_viewer_template'));
		add_filter('visody_register_metaboxes', array( $this, 'visody_register_viewer_template_fields' ));
	}

	/**
	 * Registers the viewer template post type for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function register_viewer_template()
	{
		$labels = array(
			'name'               => __('Templates', 'visody'),
			'singular_name'      => __('Template', 'visody'),
			'menu_name'          => __('Templates', 'visody'),
			'name_admin_bar'     => __('Template', 'visody'),
			'add_new'            => __('New viewer template', 'visody'),
			'add_new_item'       => __('Add new viewer template', 'visody'),
			'new_item'           => __('New viewer template', 'visody'),
			'edit_item'          => __('Edit viewer template', 'visody'),
			'view_item'          => __('View viewer template', 'visody'),
			'all_items'          => __('Templates', 'visody'),
			'search_items'       => __('Search viewer templates', 'visody'),
			'parent_item_colon'  => __('Parent viewer templates:', 'visody'),
			'not_found'          => __('No viewer templates found.', 'visody'),
			'not_found_in_trash' => __('No viewer templates found in bin.', 'visody'),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=visody_viewer',
			'menu_position'      => 90,
			'query_var'          => false,
			'rewrite'            => false,
			'with_front'         => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array('title'),
		);

		register_post_type('visody_template', $args);
	}

	/**
	 * Registers the viewer fields for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function visody_register_viewer_template_fields($metaboxes)
	{
		$template_fields = array(
			'id'	=> 'visody_template_metabox',
			'name'	=> 'Viewer Template Options',
			'post_type' => array('visody_template'),
			'tabs' => array(
			 	array(
				   'name' => __( 'Model scene', 'visody' ),
				   'fields' => array(
						array(
							'id' => 'environment_image',
							'label' => __( 'Model environment image', 'visody' ),
							'description' => __( 'Upload HDR environment image in WordPress', 'visody' ),
							'type' => 'file',
						),
						array(
							'id' => 'environment_image_url',
							'label' => __( 'Model environment url', 'visody' ),
							'description' => __( 'Enter link to external HDR environment image', 'visody' ),
							'type' => 'text',
							'class' => 'large-text',
						),
						array(
							'id' => 'environment_is_skybox',
							'label' => __( 'Skybox', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Use environment image as skybox', 'visody' ),
						),
						array(
							'id' => 'environment_skybox_height',
							'label' => __( 'Skybox height', 'visody' ),
							'description' => __( 'Optionally control height of skybox view (e.g. 15m)', 'visody' ),
							'type' => 'text',
							'class' => 'large-text',
						),
						array(
							'id' => 'environment_exposure',
							'label' => __( 'Exposure', 'visody' ),
							'type' => 'number',
							'min' => 0,
							'max' => 2,
							'step' => 0.01,
							'description' => __( 'Set number between 0.00 and 2.00', 'visody' ),
							'class' => 'regular-text',
						),
						array(
							'id' => 'shadow_intensity',
							'label' => __( 'Shadow intensity', 'visody' ),
							'type' => 'number',
							'min' => 0,
							'max' => 2,
							'step' => 0.01,
							'description' => __( 'Set number between 0.00 and 2.00', 'visody' ),
							'class' => 'regular-text',
						),
						array(
							'id' => 'shadow_softness',
							'label' => __( 'Shadow softness', 'visody' ),
							'type' => 'number',
							'min' => 0,
							'max' => 1,
							'step' => 0.01,
							'description' => __( 'Set number between 0.00 and 1.00', 'visody' ),
							'class' => 'regular-text',
						),
						array(
							'id' => 'disable_ar',
							'label' => __( 'AR mode', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Disable AR mode', 'visody' ),
						),
						array(
							'id' => 'ar_model_placement',
							'label' => __( 'AR placement', 'visody' ),
							'type' => 'radio',
							'options' => array(
								'floor' => __( 'Place on floor', 'visody' ),
								'wall' => __( 'Place on wall', 'visody' ),
							),
							'default' => 'floor',
							'show_if' => array(
								'id' => 'disable_ar',
								'value' => 'no',
							),
						),
						array(
							'id' => 'ar_scale_fixed',
							'label' => __( 'AR zoom', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Disable AR zoom', 'visody' ),
							'show_if' => array(
								'id' => 'disable_ar',
								'value' => 'no',
							),
						),
					)
				),
				array(
					'name' => __( 'Camera options', 'visody' ),
					'fields' => array(
						array(
							'id' => 'camera_autorotate',
							'label' => __( 'Auto-rotate', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Auto rotate model', 'visody' ),
						),
						array(
							'id' => 'camera_autorotate_delay',
							'label' => __( 'Rotation delay:', 'visody' ),
							'type' => 'number',
							'placeholder' => '0',
							'description' => __( 'Start autorotating after X milliseconds.', 'visody' ),
							'step' => 1,
							'class' => 'regular-text',
							'show_if' => array(
								'id' => 'camera_autorotate',
								'value' => 'yes',
							),
						),
						array(
							'id' => 'camera_autorotate_speed',
							'label' => __( 'Rotation per second:', 'visody' ),
							'type' => 'text',
							'description' => __( 'Enter a number with unit (e.g., "30deg", "0.5rad" or "-100%").', 'visody' ),
							'class' => 'regular-text',
							'show_if' => array(
								'id' => 'camera_autorotate',
								'value' => 'yes',
							),
						),
						array(
							'id' => 'camera_initial_x',
							'label' => __( 'Initial camera position X:', 'visody' ),
							'type' => 'number',
							'min' => -180,
							'max' => 180,
							'step' => 0.01,
							'class' => 'regular-text',
							'description' => __( 'Enter number between -180.00 and 180.00. Defaults to exported model position.', 'visody' ),
						),
						array(
							'id' => 'camera_initial_y',
							'label' => __( 'Initial camera position Y:', 'visody' ),
							'type' => 'number',
							'min' => -180,
							'max' => 180,
							'step' => 0.01,
							'class' => 'regular-text',
							'description' => __( 'Set number between 0.00 (top) and 180.00 (bottom). Defaults to exported model position.', 'visody' ),
						),
						array(
							'id' => 'set_camera_target',
							'label' => __( 'Target point', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Modify model position', 'visody' ),
						),
						array(
							'id' => 'camera_target_x',
							'label' => __( 'Target point X:', 'visody' ),
							'type' => 'number',
							'description' => __( 'Enter a number with 2 decimals (e.g. 0.00).', 'visody' ),
							'step' => 0.01,
							'class' => 'regular-text',
							'show_if' => array(
								'id' => 'set_camera_target',
								'value' => 'yes',
							),
						),
						array(
							'id' => 'camera_target_y',
							'label' => __( 'Target point Y:', 'visody' ),
							'type' => 'number',
							'required' => true,
							'description' => __( 'Enter a number with 2 decimals (e.g. 0.00).', 'visody' ),
							'step' => 0.01,
							'class' => 'regular-text',
							'show_if' => array(
								'id' => 'set_camera_target',
								'value' => 'yes',
							),
						),
						array(
							'id' => 'camera_target_z',
							'label' => __( 'Target point Z:', 'visody' ),
							'type' => 'number',
							'description' => __( 'Enter a number with 2 decimals (e.g. 0.00).', 'visody' ),
							'step' => 0.01,
							'class' => 'regular-text',
							'show_if' => array(
								'id' => 'set_camera_target',
								'value' => 'yes',
							),
						),
						array(
							'id' => 'set_camera_limits',
							'label' => __( 'Camera view limit', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Apply camera viewer limits', 'visody' ),
						),
						array(
							'id' => 'camera_x_limit_clockwise',
							'label' => __( 'Horizontal limit to right:', 'visody' ),
							'description' => __( 'Counter-clockwise limit', 'visody' ),
							'type' => 'number',
							'description' => __( 'Enter a number with 2 decimals (e.g. 0.00).', 'visody' ),
							'step' => 0.01,
							'class' => 'regular-text',
							'show_if' => array(
								'id' => 'set_camera_limits',
								'value' => 'yes',
							),
						),
						array(
							'id' => 'camera_x_limit',
							'label' => __( 'Horizontal limit to left:', 'visody' ),
							'description' => __( 'Clockwise limit', 'visody' ),
							'type' => 'number',
							'description' => __( 'Enter a number with 2 decimals (e.g. 0.00).', 'visody' ),
							'step' => 0.01,
							'class' => 'regular-text',
							'show_if' => array(
								'id' => 'set_camera_limits',
								'value' => 'yes',
							),
						),
						array(
							'id' => 'camera_z_limit',
							'label' => __( 'Vertical limit from top:', 'visody' ),
							'description' => __( 'Top-down limit', 'visody' ),
							'type' => 'number',
							'description' => __( 'Enter a number with 2 decimals (e.g. 0.00).', 'visody' ),
							'step' => 0.01,
							'class' => 'regular-text',
							'show_if' => array(
								'id' => 'set_camera_limits',
								'value' => 'yes',
							),
						),
						array(
							'id' => 'camera_z_limit_clockwise',
							'label' => __( 'Vertical limit from bottom:', 'visody' ),
							'description' => __( 'Bottom-up limit', 'visody' ),
							'type' => 'number',
							'description' => __( 'Enter a number with 2 decimals (e.g. 0.00).', 'visody' ),
							'step' => 0.01,
							'class' => 'regular-text',
							'show_if' => array(
								'id' => 'set_camera_limits',
								'value' => 'yes',
							),
						),
						array(
							'id' => 'set_camera_min_zoom',
							'label' => __( 'Camera zoom', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Apply minimum model zoom', 'visody' ),
						),
						array(
							'id' => 'camera_min_zoom',
							'label' => __( 'Camera minimum zoom:', 'visody' ),
							'description' => __( 'Set full minimum camera zoom level value (e.g. 25deg).', 'visody' ),
							'type' => 'text',
							'class' => 'regular-text',
							'show_if' => array(
								'id' => 'set_camera_min_zoom',
								'value' => 'yes',
							),
						),
						array(
							'id' => 'camera_interpolation',
							'label' => __( 'Camera interpolation', 'visody' ),
							'type' => 'number',
							'min' => 50,
							'placeholder' => '50',
							'class' => 'regular-text',
							'description' => __( 'Smoothness of the 3D model viewer interactions. Higher is smoother.', 'visody' ),
						),
						array(
							'id' => 'disable_camera_controls',
							'label' => __( 'Disable camera controls', 'visody' ),
							'type' => 'checkbox',
							'short_description' => __( 'Block interaction with the 3D viewer', 'visody' ),
						),
						array(
							'id' => 'mobile_touch_action',
							'label' => __( 'Touch action', 'visody' ),
							'description' => __( 'Optionally change the touch action behaviour', 'visody' ),
							'placeholder' => __( 'pan-y', 'visody' ),
							'type' => 'text',
							'class' => 'large-text',
						),
					)
				)
			)
		);

		$template_fields = apply_filters( 'visody_admin_viewer_template_fields', $template_fields );

		$metaboxes[] = $template_fields;

		return $metaboxes;
	}
}
