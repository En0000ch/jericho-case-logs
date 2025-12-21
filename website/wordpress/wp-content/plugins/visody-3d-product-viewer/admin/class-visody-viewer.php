<?php

/**
 * The file that defines the core plugin viewer class
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
 * This is used to define core viewer post type and maintain its data.
 *
 * @since      1.0.0
 * @package    Visody
 * @subpackage Visody/admin
 * @author     Visody <support@visody.com>
 */

class Visody_Viewer
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
		add_action('init', array($this, 'register_viewer'));
		add_filter('visody_register_metaboxes', array($this, 'visody_register_viewer_fields'));
		add_filter('manage_visody_viewer_posts_columns', array($this, 'add_shortcode_column'));
		add_filter('manage_visody_viewer_posts_custom_column', array($this, 'populate_shortcode_column'), 10, 2);
	}

	/**
	 * Registers the viewer post type for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function register_viewer()
	{
		$labels = array(
			'name'               => __('3D Viewers', 'visody'),
			'singular_name'      => __('3D Viewer', 'visody'),
			'menu_name'          => __('Visody', 'visody'),
			'name_admin_bar'     => __('3D Viewers', 'visody'),
			'add_new'            => __('New 3D viewer', 'visody'),
			'add_new_item'       => __('Add new 3D viewer', 'visody'),
			'new_item'           => __('New 3D viewer', 'visody'),
			'edit_item'          => __('Edit 3D viewer', 'visody'),
			'view_item'          => __('View 3D viewer', 'visody'),
			'all_items'          => __('3D Viewers', 'visody'),
			'search_items'       => __('Search 3D viewers', 'visody'),
			'parent_item_colon'  => __('Parent 3D viewers:', 'visody'),
			'not_found'          => __('No 3D viewers found.', 'visody'),
			'not_found_in_trash' => __('No 3D viewers found in bin.', 'visody'),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'menu_position'      => 90,
			'query_var'          => false,
			'rewrite'            => false,
			'with_front'         => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-visody',
			'supports'           => array('title'),
		);

		register_post_type('visody_viewer', $args);
	}

	/**
	 * Registers the viewer post type for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function add_shortcode_column($columns)
	{
		$new_columns = array();

		foreach ($columns as $column_slug => $column_name) {
			$new_columns[$column_slug] = $column_name;

			if ('title' == $column_slug) {
				$new_columns['shortcode'] = 'Shortcode';
			}
		}

		return $new_columns;
	}

	/**
	 * Populate the viewer post type for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function populate_shortcode_column($column_name, $post_id)
	{
		switch ($column_name) {
			case 'shortcode':
				echo wp_kses_post( '<pre class="visody_shortcode">[visody_viewer id="' . $post_id . '"]</pre>' );
			default:
		}
	}

	/**
	 * Registers the viewer fields for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function visody_register_viewer_fields($metaboxes)
	{
		$viewer_templates = visody_get_viewer_template_options();

		$viewer_fields = array(
			'id'	=> 'visody_viewer_metabox',
			'name'	=> 'Viewer Options',
			'post_type' => array('visody_viewer'),
			'fields' => array(
				array(
					'id' => 'visody_viewer_model',
					'label' => __( '3D model', 'visody' ),
					'description' => __( 'Choose or upload your 3D GLB model file', 'visody' ),
					'type' => 'file',
				),
				array(
					'id' => 'visody_viewer_model_url',
					'label' => __( '3D model url', 'visody' ),
					'description' => __( 'Enter full URL to the 3D GLB model file location. Make sure location is publicly accessible.', 'visody' ),
					'type' => 'text',
					'class' => 'large-text',
				),
				array(
					'id' => 'visody_viewer_ios_model_url',
					'label' => __( '3D iOS model url', 'visody' ),
					'description' => __( 'Enter full URL to the iOS 3D model file location (USDZ format). Make sure location is publicly accessible.', 'visody' ),
					'type' => 'text',
					'class' => 'large-text',
				),
				array(
					'id' => 'visody_viewer_show_poster',
					'label' => __( '3D model poster', 'visody' ),
					'type' => 'checkbox',
					'short_description' => __( 'Use image as loader image', 'visody' ),
				),
				array(
					'id' => 'visody_viewer_poster',
					'label' => __( '3D loader image', 'visody' ),
					'description' => __( 'Choose image to show while loading in the 3D model', 'visody' ),
					'type' => 'image',
					'show_if' => array(
						'id' => 'visody_viewer_show_poster',
						'value' => 'yes'
					)
				),
				array(
					'id' => 'visody_viewer_show_percentage',
					'label' => __( '3D loader percentage', 'visody' ),
					'type' => 'checkbox',
					'short_description' => __( 'Show loader percentage', 'visody' ),
					'show_if' => array(
						'id' => 'visody_viewer_show_poster',
						'value' => 'no'
					)
				),
				array(
					'id' => 'visody_viewer_frame_ratio',
					'label' => __( 'Viewer frame ratio', 'visody' ),
					'description' => __( 'Customize viewer frame ratio', 'visody' ),
					'type' => 'select',
					'options' => array(
						'landscape' => '16:9 (default landscape)',
						'wide' => '16:10 (landscape)',
						'fourthree' => '4:3 (landscape)',
						'threetwo' => '3:2 (landscape)',
						'portrait' => '9:16 (default portrait)',
						'high' => '10:16 (portrait)',
						'threefour' => '3:4 (portrait)',
						'twothree' => '2:3 (portrait)',
						'squared' => '1:1 (square)',
					)
				),
				array(
					'id' => 'visody_viewer_frame_ratio_mobile',
					'label' => __( 'Viewer frame ratio mobile', 'visody' ),
					'description' => __( 'Customize viewer frame ratio for mobile', 'visody' ),
					'type' => 'select',
					'options' => array(
						'portrait' => '9:16 (default portrait)',
						'high' => '10:16 (portrait)',
						'threefour' => '3:4 (portrait)',
						'twothree' => '2:3 (portrait)',
						'landscape' => '16:9 (default landscape)',
						'wide' => '16:10 (landscape)',
						'fourthree' => '4:3 (landscape)',
						'threetwo' => '3:2 (landscape)',
						'squared' => '1:1 (square)',
					)
				),
				array(
					'id' => 'visody_template',
					'label' => __( 'Viewer template', 'visody' ),
					'description' => __( 'Select viewer template for the 3D model', 'visody' ),
					'type' => 'select',
					'options' => $viewer_templates,
				),
			)
		);

		$viewer_fields = apply_filters('visody_admin_viewer_fields', $viewer_fields);

		$metaboxes[] = $viewer_fields;

		return $metaboxes;
	}
}
