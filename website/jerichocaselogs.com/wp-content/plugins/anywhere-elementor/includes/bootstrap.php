<?php
namespace WPV_AE;


class Plugin{

    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct(){

        add_action('plugins_loaded', [ $this, 'plugins_loaded' ] );

        add_action('wp_head', [ $this, 'wp_head'] );

		add_filter('template_redirect', [ $this, 'block_template_frontend' ]);

		add_action( 'admin_enqueue_scripts', [ $this, 'load_admin_style' ] );

    }

    private function includes(){

        require_once( WTS_AE_PATH . 'includes/post-type.php' );
        require_once( WTS_AE_PATH . 'includes/meta-box.php' );
        require_once( WTS_AE_PATH . 'includes/shortcode.php' );
		require_once( WTS_AE_PATH . 'includes/ui.php' );
    }

    public function plugins_loaded(){

        if( class_exists('Aepro\Aepro')){
            return;
        } 
        load_plugin_textdomain( 'wts_ae' );

        $this->includes();

    }

    public function wp_head(){

        $custom_css = "<style type='text/css'> .ae_data .elementor-editor-element-setting {
            display:none !important;
            }
            </style>";
        echo $custom_css;
    }

	public function block_template_frontend()
    {
        if (is_singular('ae_global_templates') && ! current_user_can('edit_posts') ) {
            wp_safe_redirect(site_url(), 301);
            die;
        }
    }

	public function load_admin_style( $hook ) {
		wp_enqueue_style( 'wpvae-admin', WTS_AE_URL . 'includes/admin/css/admin.css', [], AE_VERSION );
	}
}

Plugin::instance();
