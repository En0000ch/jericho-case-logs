<?php
/**
 * The class for REST API handling
 * *
 * @package    WP_3D_Thingviewer_Lite
 * @subpackage WP_3D_Thingviewer_Lite/public
 * @author     Accolore <support@accolore.com>
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (! class_exists('WP_3D_Thingviewer_Lite_Rest')) {
	class WP_3D_Thingviewer_Lite_Rest {

        /**
		 * The ID of this plugin.
		 *
		 * @since    1.1.0
		 * @access   private
		 * @param    string    $plugin_name    The ID of this plugin.
		 */
        private $plugin_name;

        /**
		 * The nonce life expressed in seconds
		 *
		 * @since    1.1.0
		 * @access   private
		 * @param    integer    $nonce_life     The non ce life in seconds
		 */
        private $nonce_life;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.1.0
		 * @param    string    $plugin_name       The name of the plugin.
		 * @param    int       $nonce_life        The number of second of life of nonce
		 */
		public function __construct( $plugin_name, $nonce_life ) {
            $this->plugin_name = $plugin_name;
            $this->nonce_life  = $nonce_life;
        }

        /**
		 * Initialize the REST API 
		 *
		 * @since    1.1.0
		 */
		public function rest_api_init() {
			if( !session_id() ) {
				session_start();
			}

			register_rest_route( 'wp3dtvl/v1', '/model/(?P<nonce>\w+)/(?P<model>\d+[.][a-z]+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'model_load'),
				'args' => array(
					'nonce' => array(
						'required' => true
					),
					'model' => array(
						'required' => true
					),
				),
				'permission_callback' => '__return_true',
			));
		}
     
        /**
         * Verify the nonce against the referrer url and the action
         *
         * @since    1.1.0
		 * @param    string    $nonce	The nonce
		 * @return   bool               true if is valid, false otherwise
         */
		public function verify_nonce( $nonce ) {
			$timestamp  = intval(microtime(true));

			$http_referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
			$site_url = get_site_url();

			// in referrer not set or different from current wp url
			if ( $http_referer == false || strpos($http_referer , $site_url) === false ) return false;

			// if noncelife is set to 0 it means infite life, so return true
			if ($this->nonce_life == 0) return true;

			if ( ($timestamp - $nonce) < $this->nonce_life ) {
				return true;
			} 
			return false;
        }

        /**
         * Create a nonce
         *
         * @since    1.1.0
         * @return   string    $nonce   The generated nonce
         */
        public static function create_nonce() {
			return intval(microtime(true));
        }
		
        /**
		 * Load the model via REST api
		 *
		 * @since    1.0.0
		 * @param    WP_REST_Request    $request 	The REST request
		 * @param    string   	                    The raw data for the model
		 */
		public function model_load( WP_REST_Request $request ) {
			global $accolore_config;

			$textdomain = $accolore_config[$this->plugin_name]['text_domain'];
			$params     = explode( ".", $request->get_param('model') );
			$model_id   = $params[0];
			$nonce      = $request->get_param('nonce');

			if ( $this->verify_nonce($nonce) !== true ) {
				return new WP_Error('restricted_access', __('You cannot access to this page', $textdomain), array('status' => 404));
			}

			$model_url = wp_get_attachment_url($model_id); 
			$model_raw = file_get_contents($model_url);

			$quoted = sprintf('"%s"', addcslashes(basename($model_url), '"\\'));
			$size   = filesize(get_attached_file($model_id));

			header("Pragma: public");
			header("Expires: 0");
			header('Connection: Keep-Alive');
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header('Content-Disposition: attachment; filename=' . $quoted); 
			header('Content-Length: ' . $size);
			header("Content-Transfer-Encoding: binary ");

			echo $model_raw;
			exit();
		}

    }
}