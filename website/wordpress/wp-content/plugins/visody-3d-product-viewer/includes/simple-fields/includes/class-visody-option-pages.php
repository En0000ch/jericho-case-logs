<?php
/*
 * Option pages
 */
if( !class_exists( 'Visody_Option_Page' ) ) {

	#[\AllowDynamicProperties]
	class Visody_Option_Page extends Visody_Meta_Fields{

		function __construct( $options ) {
			$this->options = $options;
			$this->options[ 'capability' ] = ! empty( $this->options[ 'capability' ] ) ? $this->options[ 'capability' ] : 'manage_options';
			$this->options[ 'position' ] = ! empty( $this->options[ 'position' ] ) ? $this->options['position'] : null;
			$this->options[ 'icon' ] = ! empty( $this->options[ 'icon' ] ) ? $this->options[ 'icon' ] : '';
			$this->options[ 'post_type' ] = ! empty( $this->options[ 'post_type' ] ) ? $this->options[ 'post_type' ] : '';
			$this->prefix = $this->options['id'] . '_';

			if( ! in_array( $this->options[ 'id' ], array( 'general', 'writing', 'reading', 'discussion', 'media', 'permalink' ) ) ) {
				add_action( 'admin_menu', array( $this, 'add_page' ) );
			}

			add_action( 'admin_init', array( $this, 'settings_fields') );
			add_action( 'admin_enqueue_scripts', array( $this, 'set_color_scripts' ), 1, 2 );
		}

		function set_color_scripts($hook) {
			wp_enqueue_media();
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}

		function add_page() {
			if( empty( $this->options[ 'parent_slug' ] ) ) {
				add_menu_page( $this->options[ 'title' ], $this->options[ 'menu_name' ], $this->options[ 'capability' ], $this->options[ 'id' ], array( $this,'body'), $this->options[ 'icon' ], $this->options[ 'position' ] );
			} else {
				add_submenu_page( $this->options[ 'parent_slug' ], $this->options[ 'title' ], $this->options[ 'menu_name' ], $this->options[ 'capability' ], $this->options[ 'id' ], array( $this, 'body' ), $this->options[ 'position' ] );
			}
		}

		function body() {
			$option_group = $this->options[ 'id' ];
			?><div class="wrap">
			<h1><?php echo esc_attr( $this->options['title'] ); ?></h1>
			<form method="POST" action="options.php">
				<?php
					if( isset( $this->options[ 'tabs' ] ) && is_array( $this->options[ 'tabs' ] ) ) {
						// print tabs
						$tabs = wp_list_pluck( $this->options[ 'tabs' ], 'name' );
						$current = isset( $_GET[ 'tab' ] ) && in_array( $_GET[ 'tab' ], array_map( 'sanitize_key', $tabs ) ) ? sanitize_key( $_GET[ 'tab' ] ) : sanitize_key( $tabs[0] );
						$option_group = $option_group . $current;
						?>
						<nav class="nav-tab-wrapper">
							<?php
							foreach( $tabs as $name ){
								$slug = sanitize_key( $name );
								?>
								<a class="nav-tab<?php echo $slug === $current ? ' nav-tab-active' : '' ?>" href="<?php echo add_query_arg( array( 'page' => $this->options[ 'id' ], 'post_type' => $this->options[ 'post_type' ], 'tab' => $slug ), '' ) ?>"><?php echo $name ?></a>
								<?php
							}
							?>
						</nav>
						<?php
					}
					settings_fields( $option_group );
					do_settings_sections( $option_group );
					submit_button();
				?>
			</form>
			<script>
				jQuery(document).ready(function($) {
					$('.color-picker').each(function(){
						$(this).wpColorPicker();
					});
				});
			</script>
			</div>
			<?php
		}

		function settings_fields(){
			if( isset( $this->options[ 'tabs' ] ) && is_array( $this->options[ 'tabs' ] ) ) {
				foreach( $this->options[ 'tabs' ] as $tab ) {
					$this->register_fields( $tab, $this->options[ 'id' ] . sanitize_key( $tab[ 'name' ] ) );
				}
			} else {
				$this->register_fields( $this->options, $this->options[ 'id' ] );
			}
		}

		function register_fields( $fields, $option_group ) {
			if( empty( $fields[ 'sections' ] ) || ! is_array( $fields[ 'sections' ] ) ) {
				$fields[ 'sections' ] = array(
					array(
						'id'		=> 'default',
						'name'		=> '',
						'fields'	=> $fields[ 'fields' ],
					)
				);
			}

			foreach ( $fields[ 'sections' ] as $section ) :
				// Either NOT default section OR default section BUT not default page
				if( 'default' !== $section[ 'id' ] || ! in_array( $this->options[ 'id' ], array( 'general', 'writing', 'reading', 'discussion', 'media', 'permalink' ) ) ) {
					add_settings_section(
						$section[ 'id' ],
						( ! empty( $section[ 'name' ] ) ? $section[ 'name' ] : '' ),
						null,
						$option_group
					);
				}

				if( empty( $section[ 'fields' ] ) || ! is_array( $section[ 'fields' ] ) ) {
					return;
				}

				foreach( $section[ 'fields' ] as $field ) :
					$field[ 'value' ] = get_option( $this->field_name( $field ) );

					if( ! in_array( $field[ 'type' ], array( 'checkbox', 'radio', 'checklist' ) ) ) {
						$field[ 'label_for' ] = $this->prefix . $field[ 'id' ];
					}

					$field[ 'class' ] = ( isset( $field[ 'class' ] ) && ! is_array( $field[ 'class' ] ) ) ? $field[ 'class' ] : ( isset( $field[ 'class' ] ) ? join( ' ', $field[ 'class' ] ) : '' );
					if( $show_if_classes = $this->show_if_classes( $field ) ) {
						$field[ 'class' ] .= ' ' . $show_if_classes;
					}

					add_settings_field(
						$field[ 'id' ],
						( ! empty( $field[ 'label' ] ) ? $field[ 'label' ] : '' ),
						array( $this, 'the_field'),
						$option_group,
						$section[ 'id' ],
						$field
					);

					register_setting(
						$option_group,
						$field[ 'id' ],
						array( 'sanitize_callback'=> $this->sanitize_callback( $field[ 'type' ] ) )
					);
				endforeach;
			endforeach;
		}

		// displaying the field
		function the_field( $params = array() ) {
			$params = visody_simple_esc_array_or_string( $params );

			echo $this->field(
				$params,
				esc_attr( $params[ 'value' ] ),
				esc_attr( $this->prefix )
			);
		}

		// decide about proper sanitization function
		public function sanitize_callback( $type ) {
			switch( $type ) {
				case 'textarea' : {
					$callback = 'sanitize_textarea_field';
					break;
				}
				case 'checkbox' : {
					$callback = array( $this, 'sanitize_checkbox' );
					break;
				}
				case 'checklist' : {
					$callback = array( $this, 'sanitize_checklist' );
					break;
				}
				case 'editor' : {
					$callback = array( $this, 'sanitize_editor' );
					break;
				}
				case 'gallery' : {
					$callback = array( $this, 'sanitize_gallery' );
					break;
				}
				case 'repeater' : {
					$callback = array( $this, 'sanitize_repeater_field' );
					break;
				}
				default : {
					$callback = 'sanitize_text_field';
					break;
				}
			}
			return $callback;
		}
	}
}
