<?php

/**
 * Fired during plugin activation and deactivation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_Accolore
 * @subpackage WP_Accolore/includes
 * @author     Accolore <support@accolore.com>
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( ABSPATH . 'wp-admin/includes/file.php' );
require_once( ABSPATH . 'wp-admin/includes/image.php' );

if (! class_exists('WP_Accolore_Registration')) {
	class WP_Accolore_Registration {

		/**
		 * Handle the plugin activation
		 *
		 * @since    1.0.0
		 * @param    string    $plugin_name    The slug of this plugin.
		 */
		public static function activate( $plugin_name ) {
			add_option( 'Activated_Plugin', $plugin_name );

			global $accolore_config;
			
			$demo_page = $accolore_config[$plugin_name]['demo_page'];
			
			if ( !self::post_exists_by_slug($demo_page['name']) ) {
				$demo_content = $demo_page['content'];
	
				if ($demo_page != false) {
					if ($demo_page['media'] != false) {
						$local_url = array();
						foreach($demo_page['media'] as $key => $media_url) {
							$local_url = self::upload_media($media_url);
							$demo_content = str_replace("{{" . $key . "}}", $local_url, $demo_content);
						}
					}
					// if page not exists
					$demo_post = array(
						'post_title'   => $demo_page['title'],
						'post_name'    => $demo_page['name'],
						'post_content' => $demo_content,
						'post_status'  => 'draft',
						'post_author'  => 1,
						'post_type'    => 'page',
					);
					wp_insert_post( $demo_post );
				}
			}
		}

		/**
		 * Handle the media uploading in the activation function
		 *
		 * @since    2.9.0
		 * @param    string    $url    The URL of the media to be uploaded
		 * @return   string            The local URL of the uploaded media
		 */
		private static function upload_media($url) {
			$temp_file = download_url($url);

			if( is_wp_error($temp_file) ) {
				return false;
			}

			$file = array(
				'name'     => basename($url),
				'type'     => mime_content_type($temp_file),
				'tmp_name' => $temp_file,
				'size'     => filesize($temp_file),
			);
			$override = array(
				'test_form' => false,
			);

			$sideload = wp_handle_sideload( $file, $override );

			if( is_wp_error($sideload) ) {
				@unlink($temp_file);
				return false;
			}

			$attachment_id = wp_insert_attachment(
				array(
					'guid'           => $sideload['url'],
					'post_mime_type' => $sideload['type'],
					'post_title'     => basename($sideload['file']),
					'post_content'   => '',
					'post_status'    => 'inherit',
				),
				$sideload['file']
			);

			if( is_wp_error($attachment_id) || !$attachment_id ) {
				return false;
			}

			wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $sideload['file']));

			return $sideload['url'];
		}

		/**
		 * Check if post exists by post name (slug)
		 *
		 * @since    2.9.0
		 * @param    string    $post_name    The slug of the post.
		 * @return   boolean                 True if the post exists, false otherwise
		 */
		public static function post_exists_by_slug( $post_name) {
			$args_posts = array(
				'post_type'      => 'any',
				'name'           => $post_name,
				'posts_per_page' => 1,
			);
			$loop_posts = new WP_Query( $args_posts );
			if ( ! $loop_posts->have_posts() ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Handle the plugin deactivation
		 *
		 * @since    1.0.0
		 * @param    string    $plugin_name    The slug of this plugin.
		 */
		public static function deactivate( $plugin_name ) {

		}
	}
}