<?php
/*
	Copyright 2014-2023 Misha Rudrastyh ( https://rudrastyh.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
	the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( __DIR__ . '/includes/class-visody-meta-fields.php' );

/**
 * Enqueue all the scripts and styles of the plugin
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	// do nothing when not needed
	if (
		! in_array( // default pages
			$hook,
			array(
				'post.php',
				'post-new.php',
				'edit-tags.php',
				'term.php',
				'options-general.php',
				'options-writing.php',
				'options-reading.php',
				'options-discussion.php',
				'options-media.php',
				'options-permalink.php',
				'profile.php',
				'comment.php'
			)
		)
		&& substr( $hook, 0, 14 ) != 'settings_page_' // custom settings pages
		&& substr( $hook, 0, 14 ) != 'toplevel_page_'  // custom settings parentpages
		&& substr( get_plugin_page_hookname( get_admin_page_parent(), $hook), 0, 14 ) != 'toplevel_page_'
	) {
		return;
	}

	if ( ! did_action( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script( 'jquery-ui-sortable' ); // for gallery

	wp_enqueue_style(
		'visody_fields_css',
		plugin_dir_url( __FILE__ ) . 'assets/main.css',
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'assets/main.css' )
	);

	wp_register_script(
		'visody_fields_js',
		plugin_dir_url( __FILE__ ) . 'assets/main.js',
		array( 'jquery' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'assets/main.js' )
	);

	wp_localize_script( 'visody_fields_js', 'visodyObject', array(
		// uploader localizations
		'insertImage' => __( 'Insert image', 'visody' ),
		'useThisImage' => __( 'Use this image', 'visody' ),
		'uploadImage' => __( 'Upload Image', 'visody' ),
		'insertFile' => __( 'Insert file', 'visody' ),
		'useThisFile' => __( 'Use this file', 'visody' ),
		'uploadFile' => __( 'Upload File', 'visody' ),
		// gallery
		'insertImages' => __( 'Insert images', 'visody' ),
		'useThisImages' => __( 'Add images', 'visody' ),
		'theSameImage' => __('The same images are not allowed.', 'visody' ),
	) );

	wp_enqueue_script( 'visody_fields_js' );
});

/**
 * Empty the fields after a term has been added
 */
add_action( 'admin_footer-edit-tags.php', function() {
	?><script>
		jQuery(function($){
			var numberOfTags = 0;

			if( ! $('#the-list').children('tr').first().hasClass('no-items') ) {
				numberOfTags = $('#the-list').children('tr').length;
			}

			$(document).ajaxComplete(function( event, xhr, settings ){
				newNumberOfTags = $('#the-list').children('tr').length;
				if( parseInt(newNumberOfTags) > parseInt(numberOfTags) ) {
					numberOfTags = newNumberOfTags;

					$('.visody-remove-img-button').each(function(){
						$(this).hide().prev().val('').prev().prev().addClass('button').html('<?php esc_html_e( 'Upload image', 'visody' ) ?>');
					});

					$('.visody-remove-file-button').each(function(){
						$(this).hide().prev().val('').prev().addClass('button').removeClass( 'visody-upload-file-button--selected' ).html('<?php esc_html_e( 'Upload file', 'visody' ) ?>');
					});

					$('.visody-gallery-field').empty();

					$('.visody-repeater-container').children().not(':first').remove();
				}
			});
		});
	</script><?php
});

add_action( 'after_setup_theme', function(){
	$metaboxes = apply_filters( 'visody_register_metaboxes', array() );

	if( $metaboxes && is_array( $metaboxes ) ) {
		require_once( __DIR__ . '/includes/class-visody-meta-boxes.php' );

		foreach( $metaboxes as $metabox ){
			new Visody_Meta_Boxes( $metabox );
		}
	}

	$option_pages = apply_filters( 'visody_register_option_pages', array() );
	if( $option_pages && is_array( $option_pages ) ) {
		include_once( __DIR__ . '/includes/class-visody-option-pages.php' );

		foreach( $option_pages as $option_page ){
			new Visody_Option_Page( $option_page );
		}
	}
});

if ( ! function_exists( 'visody_simple_esc_array_or_string' ) ) {
	/**
	 * Escape array or string
	 */
	function visody_simple_esc_array_or_string( $array_or_string ) {
		if ( is_string( $array_or_string ) ) {
			$array_or_string = esc_attr( $array_or_string );
		} elseif ( is_array($array_or_string) ) {
			foreach ( $array_or_string as $key => &$value ) {
				if ( is_array( $value ) ) {
					$value = visody_simple_esc_array_or_string($value);
				} else {
					$value = esc_attr( $value );
				}
			}
		}

		return $array_or_string;
	}
}