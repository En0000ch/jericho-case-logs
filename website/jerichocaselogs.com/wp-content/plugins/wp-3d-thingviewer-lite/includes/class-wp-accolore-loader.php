<?php
/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
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

if (! class_exists('WP_Accolore_Loader')) {
	class WP_Accolore_Loader {

		/**
		 * The singleton variable. Hold the only allowerd instance for this class.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @param    WP_Accolore_Loader    $singleton    The singleton variable. Hold the only allowerd instance for this class.
		 */
		private static $singleton = null;

		/**
		 * The array of actions registered with WordPress.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @param    array    $add_actions    The actions registered with WordPress to fire when the plugin loads.
		 */
		protected $add_actions;

		/**
		 * The array of actions unregistered with WordPress.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @param    array    $remove_actions    The actions unregistered with WordPress to fire when the plugin loads.
		 */
		protected $remove_actions;

		/**
		 * The array of filters registered with WordPress.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @param    array    $add_filters    The filters registered with WordPress to fire when the plugin loads.
		 */
		protected $add_filters;

		/**
		 * The array of filters unregistered with WordPress.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @param    array    $remove_filters    The filters unregistered with WordPress to fire when the plugin loads.
		 */
		protected $remove_filters;

		/**
		 * Initialize the collections used to maintain the actions and filters.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
			$this->add_actions    = array();
			$this->remove_actions = array();
			$this->add_filters    = array();
			$this->remove_filters = array();
		}

		/**
		 * Instantiate the class into the singleton variable and return this.
		 *
		 * @since    1.0.0
		 * @return   WP_Accolore_Loader	The singleton instance of the class.
		 */
		static function get_instance() {
			if (self::$singleton == null) {
				self::$singleton = new WP_Accolore_Loader();
			}
			return self::$singleton;
		}

		/**
		 * Add a new action to the collection to be registered with WordPress.
		 *
		 * @since    1.0.0
		 * @param    string               $hook             The name of the WordPress action that is being registered.
		 * @param    object               $component        A reference to the instance of the object on which the action is defined.
		 * @param    string               $callback         The name of the function definition on the $component.
		 * @param    int      Optional    $priority         The priority at which the function should be fired.
		 * @param    int      Optional    $accepted_args    The number of arguments that should be passed to the $callback.
		 */
		public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
			$this->add_actions = $this->add( $this->add_actions, $hook, $component, $callback, $priority, $accepted_args );
		}

		/**
		 * Add a new action to the collection to be unregistered with WordPress.
		 *
		 * @since    1.0.0
		 * @param    string               $hook             The name of the WordPress action that is being unregistered.
		 * @param    string               $callback         The name of the function definition on the $component.
		 * @param    int      Optional    $priority         The priority at which the function should be fired.
		 */
		public function remove_action( $hook, $component, $callback, $priority = 10 ) {
			$this->remove_actions = $this->add( $this->remove_actions, null, $hook, $callback, $priority, 1 );
		}

		/**
		 * Add a new filter to the collection to be registered with WordPress.
		 *
		 * @since    1.0.0
		 * @param    string               $hook             The name of the WordPress filter that is being registered.
		 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
		 * @param    string               $callback         The name of the function definition on the $component.
		 * @param    int      Optional    $priority         The priority at which the function should be fired.
		 * @param    int      Optional    $accepted_args    The number of arguments that should be passed to the $callback.
		 */
		public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
			$this->add_filters = $this->add( $this->add_filters, $hook, $component, $callback, $priority, $accepted_args );
		}

		/**
		 * Add a new filter to the collection to be unregistered with WordPress.
		 *
		 * @since    1.0.0
		 * @param    string               $hook             The name of the WordPress filter that is being unregistered.
		 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
		 * @param    string               $callback         The name of the function definition on the $component.
		 * @param    int      Optional    $priority         The priority at which the function should be fired.
		 */
		public function remove_filter( $hook, $component, $callback, $priority = 10 ) {
			$this->remove_filters = $this->add( $this->add_filters, $hook, $component, $callback, $priority, 1 );
		}

		/**
		 * A utility function that is used to register the actions and hooks into a single
		 * collection.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
		 * @param    string               $hook             The name of the WordPress filter that is being registered.
		 * @param    object               $component        A reference to the instance of the object on which the filter is defined.
		 * @param    string               $callback         The name of the function definition on the $component.
		 * @param    int      Optional    $priority         The priority at which the function should be fired.
		 * @param    int      Optional    $accepted_args    The number of arguments that should be passed to the $callback.
		 * @return   type                                   The collection of actions and filters registered with WordPress.
		 */
		private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

			$hooks[] = array(
				'hook'          => $hook,
				'component'     => $component,
				'callback'      => $callback,
				'priority'      => $priority,
				'accepted_args' => $accepted_args
			);

			return $hooks;

		}

		/**
		 * Register the filters and actions with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {

			foreach ( $this->add_filters as $hook ) {
				add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
			}

			foreach ( $this->remove_filters as $hook ) {
				remove_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'] );
			}

			foreach ( $this->add_actions as $hook ) {
				add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
			}

			foreach ( $this->remove_actions as $hook ) {
				remove_action( $hook['hook'], $hook['callback'], $hook['priority'] );
			}
		}
	}
}