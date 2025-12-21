<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://visody.com/
 * @since      1.0.0
 *
 * @package    Visody
 * @subpackage Visody/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Visody
 * @subpackage Visody/admin
 * @author     Visody <support@visody.com>
 */
class Visody_Welcome {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    	1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_welcome_page' ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function register_welcome_page() {
		add_submenu_page(
			'edit.php?post_type=visody_viewer',
			__( 'Welcome', 'visody' ),
			__( 'Welcome', 'visody' ),
			'edit_posts',
			'welcome',
			array( $this, 'welcome_page_contents' )
		);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function welcome_page_contents() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Welcome to Visody!', 'visody' ); ?></h1>
			<div class="visody-welcome-page">
				<div class="visody-welcome-header">
					<div class="visody-welcome-icon">
						<img src="<?php echo esc_url( VISODY_BASE_URL ); ?>/admin/img/visody-icon.png" width="80" height="80" alt="Visody" />
					</div>
					<div class="visody-welcome-title">
						<p class="h1">Visody.</p>
						<p><?php esc_html_e( 'Display interactive 3D viewers in WooCommerce product galleries and on WordPress pages', 'visody' ); ?></p>
						<p class="links">
							<a href="https://visody.com/docs/" target="_blank"><?php esc_html_e( 'Documentation', 'visody' ); ?></a>
							<span class="separator">|</span> 
							<a href="https://visody.com/faq/" target="_blank"><?php esc_html_e( 'FAQ', 'visody' ); ?></a>
							<?php if (visody_fs()->is_plan_or_trial('pro')) : ?>
								<span class="separator">|</span> 
								<a href="https://visody.com/get-help/" target="_blank"><?php esc_html_e( 'Get help', 'visody' ); ?></a>
							<?php endif; ?>
						</p>
					</div>
				</div>
				<h2><?php esc_html_e( 'Add 3D product viewers to your products in 3 steps', 'visody' ); ?></h2>
				<div class="visody-welcome-step-columns">
					<h3><?php esc_html_e( '1. Add your 3D models', 'visody' ); ?></h3>
					<div class="visody-welcome-step visody-welcome-step-half-col">
						<div class="visody-welcome-step-text">
							<h4><?php esc_html_e( 'Add to website pages', 'visody' ); ?></h4>
							<p><?php esc_html_e( 'Simply go to Visody -> 3D viewers, click "New 3D viewer" and set your 3D model. You can upload the 3D model to WordPress or add a link to the 3D model source file.', 'visody' ); ?></p>
							<p><a href="<?php echo esc_url( admin_url( '/edit.php?post_type=visody_viewer' ) ); ?>"><?php esc_html_e( 'Go to 3D viewers', 'visody' ); ?></a></p>
						</div>
						<div class="visody-welcome-step-image">
							<img src="<?php echo esc_url( VISODY_BASE_URL ); ?>/admin/img/visody-3d-viewer-settings.png" width="1000" height="406" alt="Visody 3D viewer settings" />
						</div>
					</div>
					<div class="visody-welcome-step visody-welcome-step-half-col">
						<div class="visody-welcome-step-text">
							<h4><?php esc_html_e( 'Add to WooCommerce products', 'visody' ); ?></h4>
							<p><?php esc_html_e( 'Simply go to Products -> Your product and locate the "Visody Viewer" tab. Check the box "Activate Visody 3D product viewer" and set your 3D model. You can upload the 3D model to WordPress or add a link to the 3D model source file.', 'visody' ); ?></p>
							<p><a href="<?php echo esc_url( admin_url( '/edit.php?post_type=product' ) ); ?>"><?php esc_html_e( 'Go to products', 'visody' ); ?></a></p>
						</div>
						<div class="visody-welcome-step-image">
							<img src="<?php echo esc_url( VISODY_BASE_URL ); ?>/admin/img/visody-product-settings.png" width="1000" height="406" alt="Visody product settings" />
						</div>
					</div>
				</div>
				<div class="visody-welcome-step">
					<div class="visody-welcome-step-text">
						<h3><?php esc_html_e( '2. Customize the 3D viewer camera settings', 'visody' ); ?></h3>
						<p><?php esc_html_e( 'The plugin comes with templates to modify the product viewer options, like camera position, model position and environment images.', 'visody' ); ?></p>
						<p><?php esc_html_e( 'Simply go to Visody -> Templates and add a new viewer template. When you are done, go back to your product, re-open the Visody Viewer tab and select the newly created viewer in Viewer template field.', 'visody' ); ?></p>
						<p><a href="<?php echo esc_url( admin_url( '/post-new.php?post_type=visody_template' ) ); ?>"><?php esc_html_e( 'Add a new viewer template', 'visody' ); ?></a></p>
					</div>
					<div class="visody-welcome-step-image">
						<img src="<?php echo esc_url( VISODY_BASE_URL ); ?>/admin/img/visody-viewer-options.png" width="1017" height="674" alt="Visody viewer template options" />
					</div>
				</div>
				<div class="visody-welcome-step">
					<div class="visody-welcome-step-text">
						<h3><?php esc_html_e( '3. Personalize the 3D viewer appearance', 'visody' ); ?></h3>
						<p><?php esc_html_e( 'With the 3D model in place, you can customize the viewer appearance.', 'visody' ); ?></p>
						<p><?php esc_html_e( 'Change the default colors and buttons to make it fit in with your webshop.', 'visody' ); ?></p>
						<p><a href="<?php echo esc_url( admin_url( '/edit.php?post_type=visody_viewer&page=visody_options' ) ); ?>"><?php esc_html_e( 'Customize settings', 'visody' ); ?></a></p>
					</div>
					<div class="visody-welcome-step-image">
						<img src="<?php echo esc_url( VISODY_BASE_URL ); ?>/admin/img/visody-settings.png" width="1017" height="674" alt="Visody settings" />
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
