<?php

if (!class_exists('BP3DAdmin')) {
	class BP3DAdmin
	{
		function __construct()
		{
			add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
			add_action('admin_menu', [$this, 'adminMenu']);
		}

		function adminEnqueueScripts($hook)
		{
			if (str_contains($hook, '3d-viewer')) {
				wp_enqueue_style('bp3d-admin-style', BP3D_DIR . 'build/dashboard.css', [], BP3D_VERSION);

				wp_enqueue_script('bp3d-admin-script', BP3D_DIR . 'build/dashboard.js', ['react', 'react-dom',  'wp-components', 'wp-i18n', 'wp-api', 'wp-util', 'lodash', 'wp-media-utils', 'wp-data', 'wp-core-data', 'wp-api-request'], BP3D_VERSION, true);
				wp_localize_script('bp3d-admin-script', 'bp3dDashboard', [
					'dir' => BP3D_DIR,
				]);
			}
		}

		function adminMenu()
		{

			add_menu_page(
				__('3D Viewer', 'model-viewer'),
				__('3D Viewer', 'model-viewer'),
				'manage_options',
				'3d-viewer',
				[$this, 'dashboardPage'],
				'dashicons-format-image',
				15
			);

			add_submenu_page(
				'3d-viewer',
				__('Dashboard', 'model-viewer'),
				__('Dashboard', 'model-viewer'),
				'manage_options',
				'3d-viewer',
				[$this, 'dashboardPage'],
				0
			);

			add_submenu_page(
				'3d-viewer',
				__('Add New', 'model-viewer'),
				__(' &#8627; Add New', 'model-viewer'),
				'manage_options',
				'3d-viewer-add-new',
				[$this, 'redirectToAddNew'],
				2
			);

			add_submenu_page(
				'3d-viewer',
				__('Visual Editor', 'model-viewer'),
				__('Visual Editor', 'model-viewer'),
				'manage_options',
				'3d-viewer-visual-editor',
				[$this, 'visualEditorPage'],
				4
			);
		}


		function dashboardPage()
		{ ?>
			<div
				id='bp3dAdminDashboard'
				data-info='<?php echo esc_attr(wp_json_encode([
								'version' => BP3D_VERSION,
								'isPremium' => bp3dv_fs()->can_use_premium_code(),
								'hasPro' => false
							])); ?>'></div>
		<?php }

		function upgradePage()
		{ ?>
			<div id='bp3dAdminUpgrade'>Coming soon...</div>
			<?php }

		/**	
		 * Redirect to add new Model Viewer
		 * */
		function redirectToAddNew()
		{
			if (function_exists('headers_sent') && headers_sent()) {
			?>
				<script>
					window.location.href = "<?php echo esc_url(admin_url('post-new.php?post_type=bp3d-model-viewer')); ?>";
				</script>
			<?php
			} else {
				wp_redirect(admin_url('post-new.php?post_type=bp3d-model-viewer'));
			}
		}


		function visualEditorPage()
		{
			wp_enqueue_script('bp3d-visual-editor');

			wp_enqueue_media();

			wp_enqueue_style('bp3d-visual-editor');
			?>
			<div class="wrap" id='bp3dAdminVisualEditor'></div>
<?php
		}
	}

	new BP3DAdmin;
}
