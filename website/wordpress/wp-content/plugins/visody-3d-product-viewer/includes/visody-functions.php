<?php

/**
 * The main functions of this plugin.
 *
 * @link       https://visody.com/
 * @since      1.0.0
 *
 * @package    Visody
 * @subpackage Visody/includes
 */

if (!function_exists('visody_get_viewer_template_options')) {
	/**
	 * 3D product viewer template post type options
	 *
	 * @return void
	 */
	function visody_get_viewer_template_options()
	{
		$template_results = get_posts(array(
			'post_type' => 'visody_template',
			'post_status' => 'publish',
			'posts_per_page' => 999,
			'no_found_rows' => true,
			'fields' => 'ids',
		));

		$template_options = array('' => __('- Choose viewer template -', 'visody'));
		if (count($template_results) > 0) {
			foreach ($template_results as $result_id) {
				$template_options[ esc_attr( $result_id ) ] = esc_attr( get_the_title($result_id) );
			}
		}

		return $template_options;
	}
}

if (!function_exists('visody_get_viewer_html')) {
	/**
	 * 3D product viewer html
	 *
	 * @return string
	 */
	function visody_get_viewer_html($product_id, $poster_id = '', $loop_item = false, $props = [])
	{
		$template_id = get_post_meta($product_id, 'visody_template', true);
		if ( isset( $props['template'] ) && '' !== $props['template'] ) {
			$template_id = $props['template']; // shortcode override
		}

		$model_src = '';
		if (get_post_meta($product_id, 'visody_viewer_model', true)) {
			$viewer_model_id = get_post_meta($product_id, 'visody_viewer_model', true);
			$model_src = wp_get_attachment_url($viewer_model_id);
		} else if (get_post_meta($product_id, 'visody_viewer_model_url', true)) {
			$model_src = sanitize_url(get_post_meta($product_id, 'visody_viewer_model_url', true));
		}

		if ( isset( $props['src'] ) && '' !== $props['src'] ) {
			$model_src = $props['src']; // shortcode override
		}

		$close_button = visody_get_button_html('close', array(
			'icon' => esc_url( VISODY_BASE_URL ) . 'public/img/close.svg',
			'alt'  => __( 'Close icon', 'visody' ),
			'reader' => __( 'Close 3D product viewer', 'visody' ),
		));

		$ar_button = visody_get_button_html('ar', array(
			'icon' => esc_url( VISODY_BASE_URL ) . 'public/img/ar-icon.svg',
			'slot' => 'ar-button',
			'alt'  => __( 'AR icon', 'visody' ),
			'reader' => __( 'View model in AR', 'visody' ),
			'loader' => true,
		));

		$full_button = visody_get_button_html('fs', array(
			'icon' =>  esc_url( VISODY_BASE_URL ) . 'public/img/fs-icon.svg',
			'alt'  => __( 'Fullscreen icon', 'visody' ),
			'reader' => __( 'View model in fullscreen', 'visody' ),
		));

		$cam_button = visody_get_button_html('cam', array(
			'icon' =>  esc_url( VISODY_BASE_URL ) . 'public/img/cam-icon.svg',
			'alt'  => __( 'Camera icon', 'visody' ),
			'reader' => __( 'Save image of model', 'visody' ),
			'title' => get_the_title($product_id),
		));

		$viewer_attributes = visody_get_viewer_attributes($template_id, $loop_item);
		if ('1' === get_post_meta($product_id, 'visody_viewer_show_poster', true) || 'yes' === get_post_meta($product_id, 'visody_viewer_show_poster', true)) {
			if (!$poster_id) {
				$poster_id = get_post_thumbnail_id();
			}

			$viewer_attributes .= sprintf(
				' poster="%s"',
				esc_url( wp_get_attachment_image_url($poster_id, 'full') )
			);
		}

		if (get_post_meta($product_id, 'visody_viewer_ios_model_url', true)) {
			$model_ios_src = sanitize_url(get_post_meta($product_id, 'visody_viewer_ios_model_url', true));
			if ( isset( $props['ios-src'] ) && '' !== $props['ios-src'] ) {
				$model_src = $props['ios-src']; // shortcode override
			}

			if ( '' !== $model_ios_src ) {
				$viewer_attributes .= sprintf(
					' ios-src="%s"',
					esc_url( $model_ios_src )
				);
			}
		}

		$threed_viewer = $close_button;

		if ((!$loop_item && get_option('visody_show_fullscreen_button'))
			|| ($loop_item && get_option('visody_show_item_fullscreen_button'))
		) {
			$threed_viewer .= $full_button;
		}

		if (!$loop_item && get_option('visody_show_camera_button')) {
			$threed_viewer .= $cam_button;
		}
		
		if ( 'underneath' === get_option('visody_float_button_position') ) {
			$viewer_attributes .= ' data-controls-position="underneath"';
		}

		// if (!$loop_item && get_option('visody_show_zoom_buttons')) {
		if (!$loop_item && get_option('visody_show_zoom_buttons')) {
			$threed_viewer .= visody_get_button_html('zoom-in', array(
				'icon' =>  esc_url( VISODY_BASE_URL ) . 'public/img/zoom-in-icon.svg',
				'alt'  => __( 'Zoom in icon', 'visody' ),
				'reader' => __( 'Zoom in on model', 'visody' ),
				'title' => get_the_title($product_id),
			));
			$threed_viewer .= visody_get_button_html('zoom-out', array(
				'icon' =>  esc_url( VISODY_BASE_URL ) . 'public/img/zoom-out-icon.svg',
				'alt'  => __( 'Zoom out icon', 'visody' ),
				'reader' => __( 'Zoom out on model', 'visody' ),
				'title' => get_the_title($product_id),
			));
		}
		if ($loop_item && get_option('visody_show_zoom_buttons')) {
			$viewer_attributes .= ' data-zoom="manual"';
		}

		$threed_viewer .= sprintf( 
			'<model-viewer src="%1$s" data-base_model_url="%1$s" interaction-prompt="none" %2$s>',
			esc_url( $model_src ),
			$viewer_attributes
		);

		if ('1' === get_post_meta($product_id, 'visody_viewer_show_poster', true) || 'yes' === get_post_meta($product_id, 'visody_viewer_show_poster', true)) {
			$threed_viewer .= '<div slot="progress-bar"></div>';
		} else {
			if ('1' === get_post_meta($product_id, 'visody_viewer_show_percentage', true) || 'yes' === get_post_meta($product_id, 'visody_viewer_show_percentage', true)) {
				$threed_viewer .= '<div slot="progress-bar" class="vsd-model-viewer-loader"><div class="vsd-model-viewer-loader-bar"><div class="vsd-model-viewer-loader-progress"></div><div class="vsd-model-viewer-loader-value"></div></div></div>';
			} else {
				$threed_viewer .= '<div slot="progress-bar" class="vsd-model-viewer-loader"><div class="vsd-model-viewer-loader-bar"><div class="vsd-model-viewer-loader-progress"></div></div></div>';
			}
		}

		if (get_post_meta($template_id, 'disable_ar', true)) {
			// No AR button
		} else {
			$threed_viewer .= $ar_button;
			$ar_not_supported_label = apply_filters( 'visody_ar_not_supported_label', __( 'AR is not supported on this device', 'visody' ) );
			$threed_viewer .= '<div class="vsd-ar-failed hide">' . esc_attr( $ar_not_supported_label ) . '</div>';
		}

		$annotation_list_html = '';
		$annotation_details_html = '';
		$viewer_note_id = get_post_meta($product_id, 'visody_viewer_notes', true);

		if (get_post_meta($viewer_note_id, 'annotations', true) && !$loop_item) {
			$view  = get_post_meta($template_id, 'annotation_display', true);
			$notes = '';

			if (function_exists('visody_viewer_get_annotations')) {
				$notes = visody_viewer_get_annotations($viewer_note_id, $template_id);
			}

			if (is_array($notes)) {
				if ('list' === $view) {
					$annotation_list_html .= $notes['list'];
				} else {
					$threed_viewer .= $notes['buttons'];
				}
				$annotation_details_html .= $notes['details'];
			}
		}

		if ( $template_id && get_post_meta( $template_id, 'animation_name', true ) ) {
			$play_icon = '<svg class="play" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M7 6v12l10-6z"></path></svg>';
			$pause_icon = '<svg class="pause" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M8 7h3v10H8zm5 0h3v10h-3z"></path></svg>';

			if ( ! get_post_meta( $template_id, 'animation_autoplay', true ) && get_option( 'visody_show_item_animations' ) ) {
				$threed_viewer .= '<button class="vsd-viewer-animation-control vsd-viewer-animation-control-base">' . $play_icon . $pause_icon . '</button>';
			}
		}

		if ('' !== $annotation_list_html) {
			$threed_viewer .= $annotation_list_html;
		}
		
		if ('' !== $annotation_details_html) {
			$threed_viewer .= $annotation_details_html;
		}

		$threed_viewer .= '</model-viewer>';

		return $threed_viewer;
	}
}

if (!function_exists('visody_get_button_html')) {
	/**
	 * Viewer button html
	 *
	 * @return void
	 */
	function visody_get_button_html($prefix, $defaults)
	{
		$button_icon = $defaults['icon'];

		$option_icon = sprintf( 'visody_%s_button_icon', esc_html( $prefix ) );
		if (get_option( $option_icon ) ) {
			$button_icon = wp_get_attachment_image_url( get_option( $option_icon ), 'visody_icon' );
		}

		$disable_icon_option = sprintf( 'visody_disable_%s_button_icon', esc_html( $prefix ) );
		if ( get_option( $disable_icon_option ) ) {
			$button_icon = ''; // Unset.
		}

		$button_html = sprintf( '<button class="vsd-model-viewer-%s-button', esc_html( $prefix ) );
		if (isset($defaults['id'])) {
			$button_html = '<button class="' . esc_html( $defaults['id'] );
		}

		if (isset($defaults['class'])) {
			$button_html .= ' ' . esc_html( $defaults['class'] );
		}
		if (get_option('visody_button_size')) {
			$button_html .= sprintf( ' vsd-button-%s', esc_html( get_option('visody_button_size') ) );
		}
		$button_html .= '"';

		if (isset($defaults['title'])) {
			$button_html .= sprintf( ' data-title="%s"', esc_html( $defaults['title'] ) );
		}

		if (isset($defaults['slot'])) {
			$button_html .= sprintf( ' slot="%s"', esc_html( $defaults['slot'] ) );
		}
		if (isset($defaults['disabled'])) {
			$button_html .= ' disabled="disabled"';
		}

		$button_html .= '>';

		if ($button_icon) {
			$button_icon_class = sprintf( 'vsd-model-viewer-%s-button-icon', esc_html( $prefix ) );
			$button_hover_icon = '';
	
			$option_hover_key = sprintf( 'visody_%s_button_hover_icon', esc_html( $prefix ) );
			if (get_option( $option_hover_key ) ) {
				$button_icon_class .= ' vsd-has-hover-icon';
				$button_hover_icon = wp_get_attachment_image_url( get_option( $option_hover_key ), 'visody_icon' );
			}

			$button_html .= sprintf( '<span class="%s">', esc_html( $button_icon_class ) );
			$button_html .= sprintf(
				'<img class="vsd-base-icon" src="%s" width="24" height="24" alt="%s">',
				esc_url($button_icon),
				esc_attr($defaults['alt'])
			);

			if ( $button_hover_icon ) {
				$button_html .= sprintf(
					'<img class="vsd-hover-icon" src="%s" width="24" height="24" alt="%s">',
					esc_url($button_hover_icon),
					esc_attr($defaults['alt'])
				);
			}

			$button_html .= '</span>';
			$button_html .= '<span class="screen-reader-text">' . esc_attr($defaults['reader']) . '</span>';
		}

		if (isset($defaults['loader'])) {
			$button_html .= file_get_contents( esc_url( VISODY_BASE . 'public/img/loader.svg' ) );
		}

		$option_text_key = sprintf( 'visody_%s_button_text', esc_html( $prefix ) );
		if (get_option( $option_text_key ) ) {
			$button_html .= '<span class="vsd-button-text">' . sprintf( esc_html__( '%s', 'visody' ), get_option( $option_text_key ) ) . '</span>';
		}

		$button_html .= '</button>';

		return $button_html;
	}
}

if (!function_exists('visody_get_viewer_attributes')) {
	/**
	 * Viewer button html
	 *
	 * @return void
	 */
	function visody_get_viewer_attributes($template_id, $loop_item = false)
	{
		$viewer_attributes = '';

		if ( ! $template_id || ! get_post_meta( $template_id, 'disable_camera_controls', true ) ) {
			$touch_action = 'pan-y';
			if ( $touch = get_post_meta($template_id, 'mobile_touch_action', true) ) {
				$touch_action = $touch;
			}
			
			$viewer_attributes .= sprintf(
				'camera-controls touch-action="%s"',
				esc_attr( $touch_action )
			);
		}

		if ( ! $template_id || ! get_post_meta($template_id, 'disable_ar', true)) {
			$viewer_attributes .= ' ar';

			if (get_post_meta($template_id, 'ar_model_placement', true)) {
				$ar_placement = get_post_meta($template_id, 'ar_model_placement', true);
				$viewer_attributes .= sprintf(
					' ar-placement="%s"',
					esc_attr( $ar_placement )
				);
			}
			
			if (get_post_meta($template_id, 'ar_scale_fixed', true)) {
				$viewer_attributes .= ' ar-scale="fixed"';
			}
		}

		if ( ! $template_id ) {
			return $viewer_attributes;
		}

		$env_image_url = '';
		if ($env_image_id = get_post_meta($template_id, 'environment_image', true)) {
			$env_image_url = wp_get_attachment_url($env_image_id, 'full');
		} else if ($env_url = get_post_meta($template_id, 'environment_image_url', true)) {
			$env_image_url = sanitize_url($env_url);
		}

		if ('' !== $env_image_url) {
			$viewer_attributes .= sprintf(
				' environment-image="%1$s" data-environment="%1$s"',
				esc_url($env_image_url)
			);

			if (get_post_meta($template_id, 'environment_is_skybox', true)) {
				$viewer_attributes .= sprintf(
					' skybox-image="%1$s" data-skybox="%1$s"',
					esc_url($env_image_url)
				);
			}

			if ($sky_height = get_post_meta($template_id, 'environment_skybox_height', true)) {
				$viewer_attributes .= sprintf(
					' skybox-height="%1$s" data-skybox-height="%1$s"',
					esc_attr($sky_height)
				);
			}

			$exposure = ( '' !== get_post_meta($template_id, 'environment_exposure', true) ) ? get_post_meta($template_id, 'environment_exposure', true) : 1;
			$viewer_attributes .= sprintf(
				' exposure="%1$s" data-exposure="%1$s"',
				esc_attr( $exposure )
			);
		}

		$intensity = get_post_meta($template_id, 'shadow_intensity', true);
		if ( '' !== $intensity) {
			$viewer_attributes .= ' shadow-intensity="' . esc_attr( $intensity ) . '" data-intensity="' . esc_attr( $intensity ) . '"';
		}

		$softness = get_post_meta($template_id, 'shadow_softness', true);
		if ( '' !== $softness) {
			$viewer_attributes .= ' shadow-softness="' . esc_attr( $softness ) . '" data-softness="' . esc_attr( $intensity ) . '"';
		}

		if (get_post_meta($template_id, 'set_camera_target', true)) {
			$pos_x = get_post_meta($template_id, 'camera_target_x', true) ?: '0';
			$pos_y = get_post_meta($template_id, 'camera_target_y', true) ?: '0';
			$zoom  = get_post_meta($template_id, 'camera_target_z', true) ?: '0';

			$viewer_attributes .= sprintf(
				' camera-target="%fm %fm %fm"',
				esc_html( $pos_x ),
				esc_html( $pos_y ),
				esc_html( $zoom )
			);
		}

		$initial_pos_x = get_post_meta($template_id, 'camera_initial_x', true);
		$initial_pos_y = get_post_meta($template_id, 'camera_initial_y', true);
		if ( '' !== $initial_pos_x && '' !== $initial_pos_y) {
			$viewer_attributes .= sprintf(
				' camera-orbit="%s %s auto"',
				esc_attr($initial_pos_x . 'deg'),
				esc_attr($initial_pos_y . 'deg')
			);
		}

		if (get_post_meta($template_id, 'set_camera_limits', true)) {
			$counter_clockwise = get_post_meta($template_id, 'camera_x_limit', true);
			$clockwise = get_post_meta($template_id, 'camera_x_limit_clockwise', true);
			$topdown = get_post_meta($template_id, 'camera_z_limit', true);
			$bottomup = get_post_meta($template_id, 'camera_z_limit_clockwise', true);

			if ( '' === $counter_clockwise) {
				$counter_clockwise = 'auto';
			} else {
				$counter_clockwise .= 'deg';
			}

			if ('' === $topdown) {
				$topdown = 'auto';
			} else {
				$topdown .= 'deg';
			}

			if ('' === $clockwise) {
				$clockwise = 'auto';
			} else {
				$clockwise .= 'deg';
			}

			if ('' === $bottomup) {
				$bottomup = 'auto';
			} else {
				$bottomup .= 'deg';
			}

			$viewer_attributes .= sprintf(
				' min-camera-orbit="%s %s auto"',
				esc_attr( $clockwise ),
				esc_attr( $topdown ),
			);

			$viewer_attributes .= sprintf(
				' max-camera-orbit="%s %s auto"',
				esc_attr( $counter_clockwise ),
				esc_attr( $bottomup )
			);
		}
		
		if (get_post_meta($template_id, 'set_camera_min_zoom', true)) {
			$minzoom = get_post_meta($template_id, 'camera_min_zoom', true);

			$viewer_attributes .= sprintf(
				' min-field-of-view="%s"',
				esc_attr( $minzoom )
			);
		}

		if (get_post_meta($template_id, 'camera_autorotate', true)) {
			$viewer_attributes .= ' auto-rotate';

			if ($speed = get_post_meta($template_id, 'camera_autorotate_speed', true)) {
				$viewer_attributes .= ' rotation-per-second="' . esc_attr( $speed ) . '"';
			}

			if ($delay = get_post_meta($template_id, 'camera_autorotate_delay', true)) {
				$viewer_attributes .= ' auto-rotate-delay="' . esc_attr( $delay ) . '"';
			} else {
				$viewer_attributes .= ' auto-rotate-delay="0"';
			}
		}

		if ( ! $loop_item || get_option('visody_show_item_animations') ) {	
			if (get_post_meta($template_id, 'animation_name', true)) {
				$animation_name = sanitize_text_field(get_post_meta($template_id, 'animation_name', true));
				$viewer_attributes .= sprintf( 
					' animation-name="%s"',
					$animation_name
				);
			}
	
			if (get_post_meta($template_id, 'animation_autoplay', true)) {
				$viewer_attributes .= ' autoplay';
			}
		}

		if ( get_post_meta( $template_id, 'camera_interpolation', true ) ) {
			$interpolation = sanitize_text_field( get_post_meta( $template_id, 'camera_interpolation', true ) );
			$viewer_attributes .= sprintf(
				' interpolation-decay="%s"',
				esc_attr( $interpolation )
			);
		}

		return $viewer_attributes;
	}
}
