<?php

if (!defined('ABSPATH')) {
	exit;
}

// Constant
if (!defined('BP3D_VERSION')) {
	define('BP3D_VERSION', isset($_SERVER['HTTP_HOST']) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.0.8');
}
define('TDVB_DIR', plugin_dir_url(__FILE__));

// 3D Viewer Block
class TDVB3DViewerBlock
{
	function __construct()
	{
		add_action('enqueue_block_assets', [$this, 'enqueueBlockAssets']);
		add_filter('script_loader_tag', [$this, 'scriptLoaderTag'], 10, 3);
		add_action('wp_ajax_bp3d_pipe_checker', [$this, 'bp3d_pipe_checker']);

		add_filter('upload_mimes', [$this, 'uploadMimes']);
		if (version_compare($GLOBALS['wp_version'], '5.1') >= 0) {
			add_filter('wp_check_filetype_and_ext', [$this, 'wpCheckFiletypeAndExt'], 10, 5);
		} else {
			add_filter('wp_check_filetype_and_ext', [$this, 'wpCheckFiletypeAndExt'], 10, 4);
		}
	}

	function enqueueBlockAssets()
	{
		wp_register_script('bp3d-model-viewer', BP3D_DIR . 'public/js/model-viewer.latest.min.js', [], BP3D_VERSION, true);
	}

	function scriptLoaderTag($tag, $handle, $src)
	{
		if ('bp3d-model-viewer' !== $handle) {
			return $tag;
		}
		$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
		return $tag;
	}

	function bp3d_pipe_checker()
	{
		$nonce = $_GET['_wpnonce'];

		if (!wp_verify_nonce($nonce, 'wp_ajax')) {
			echo wp_send_json([
				'success' => false
			]);
			wp_die();
		}

		echo wp_send_json([
			'data' => [
				'isPipe' => false
			]
		]);
		wp_die();
	}

	//Allow some additional file types for upload
	function uploadMimes($mimes)
	{
		// New allowed mime types.
		$mimes['glb'] = 'model/gltf-binary';
		$mimes['gltf'] = 'model/gltf-binary';
		return $mimes;
	}
	function wpCheckFiletypeAndExt($data, $file, $filename, $mimes, $real_mime = null)
	{
		// If file extension is 2 or more 
		$f_sp = explode('.', $filename);
		$f_exp_count = count($f_sp);

		if ($f_exp_count <= 1) {
			return $data;
		} else {
			$f_name = $f_sp[0];
			$ext = $f_sp[$f_exp_count - 1];
		}

		if ($ext == 'glb' || $ext == 'gltf') {
			$type = 'model/gltf-binary';
			$proper_filename = '';
			return compact('ext', 'type', 'proper_filename');
		} else {
			return $data;
		}
	}
}
new TDVB3DViewerBlock;

// Require files
require_once plugin_dir_path(__FILE__) . 'inc/block.php';
