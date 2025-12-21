<?php

if( ! class_exists( 'Visody_Meta_Fields' ) ) {
	class Visody_Meta_Fields {

		public function field( $param, $value, $prefix = '' ) {
			// commons
			$classes = ( isset( $param[ 'class' ] ) && ! is_array( $param[ 'class' ] ) ) ? $param[ 'class' ] : ( isset( $param[ 'class' ] ) ? join( ' ', $param[ 'class' ] ) : '' );
			$param[ 'type' ] = isset( $param[ 'type' ] ) ? $param[ 'type' ] : 'text';
			$name = $this->field_name( $param );

			switch( $param[ 'type' ] ) {

				case 'text':
				default:{

					$field = array();

					$field[] = '<input type="text"';
					if( ! empty( $param[ 'id' ] ) ) {
						$field[] = 'id="' . $prefix . $param[ 'id' ] . '"';
					}
					$field[] = 'name="' . $name . '"';
					$field[] = 'value="' . ( $value ? esc_textarea( $value ) : ( isset( $param[ 'default' ] ) ? $param[ 'default' ] : '' ) ) . '"';
					$field[] = 'class="' . $this->add_size_class( $classes, 'regular-text' ) . '"';

					if( isset( $param[ 'placeholder' ] ) ) $field[] = 'placeholder="' . $param[ 'placeholder' ] . '"';
					if( !empty( $param[ 'maxlength' ] ) ) $field[] = 'maxlength="' . absint( $param[ 'maxlength' ] ) . '"';

					$field[] = '/>';

					$return = join( ' ', $field );

					break;

				}

				case 'color' : {

					$field = array();

					$field[] = '<input type="text"';
					if( ! empty( $param[ 'id' ] ) ) {
						$field[] = 'id="' . $prefix . $param[ 'id' ] . '"';
					}
					$field[] = 'name="' . $name . '"';
					$field[] = 'value="' . ( $value ? esc_textarea( $value ) : ( isset( $param[ 'default' ] ) ? $param[ 'default' ] : '' ) ) . '"';
					$field[] = 'class="' . $this->add_size_class( $classes, 'regular-text color-picker' ) . '"';

					if( isset( $param[ 'placeholder' ] ) ) $field[] = 'placeholder="' . $param[ 'placeholder' ] . '"';
					if( !empty( $param[ 'maxlength' ] ) ) $field[] = 'maxlength="' . absint( $param[ 'maxlength' ] ) . '"';

					$field[] = '/>';

					$return = join( ' ', $field );

					break;

				}

				case 'number' : {

					$field = array();

					$field[] = '<input type="number"';
					if( ! empty( $param[ 'id' ] ) ) {
						$field[] = 'id="' . $prefix . $param[ 'id' ] . '"';
					}
					$field[] = 'name="' . $name . '"';
					$field[] = 'value="' . ( '' != $value ? floatval( $value ) : ( isset( $param[ 'default' ] ) ? floatval( $param[ 'default' ] ) : '' ) ) . '"';
					$field[] = 'class="' . $this->add_size_class( $classes, 'small-text' ) . '"';

					if( isset( $param[ 'placeholder' ] ) ) {
						$field[] = 'placeholder="' . $param[ 'placeholder' ] . '"';
					}
					if( ! empty( $param[ 'min' ] ) ) {
						$field[] = 'min="' . floatval( $param[ 'min' ] ) . '"';
					}
					if( ! empty( $param[ 'max' ] ) ) {
						$field[] = 'max="' . floatval( $param[ 'max' ] ) . '"';
					}
					if( ! empty( $param[ 'step' ] ) ) {
						$field[] = 'step="' . floatval( $param[ 'step' ] ) . '"';
					}

					$field[] = '/>';

					if( ! empty( $param[ 'short_description' ] ) ) {
						$field[] = ' ' . $param[ 'short_description' ];
					}
					$return = join( ' ', $field );

					break;
				}

				case 'date' : {

					$field = array();

					$field[] = '<input type="text"';
					if( ! empty( $param[ 'id' ] ) ) {
						$field[] = 'id="' . $prefix . $param[ 'id' ] . '"';
					}
					$field[] = 'name="' . $name . '"';
					$field[] = 'value="' . ( $value ? esc_attr( $value ) : ( isset( $param[ 'default' ] ) ? $param[ 'default' ] : '' ) ) . '"';
					$field[] = 'class="visody-datepicker ' . $classes . '"';

					if( !empty( $param[ 'min_date' ] ) ) $field[] = 'data-mindate="' . esc_attr( $param[ 'min_date' ] ) . '"';
					if( !empty( $param[ 'max_date' ] ) ) $field[] = 'data-maxdate="' . esc_attr( $param[ 'max_date' ] ) . '"';
					$field[] = 'data-dateformat="' . ( ! empty( $param[ 'date_format' ] ) ? esc_attr( $param[ 'date_format' ] ) : 'yy-mm-dd' ) . '"';

					$field[] = '/>';
					$return = join( ' ', $field );

					break;

				}

				case 'textarea' : {

					$attributes = array();
					$value = $value ? esc_textarea( $value ) : ( isset( $param[ 'default' ] ) ? $param[ 'default' ] : '' );

					if( ! empty( $param[ 'id' ] ) ) {
						$attributes[] = 'id="' . $prefix . $param[ 'id' ] . '"';
					}
					$attributes[] = 'name="' . $name . '"';
					$attributes[] = 'class="' . $this->add_size_class( $classes, 'large-text' ) . '"';
					$attributes[] = 'rows="' . ( ! empty( $param[ 'rows' ] ) ? intval( $param[ 'rows' ] ) : 5 ) . '"';

					if( isset( $param[ 'placeholder' ] ) ) {
						$attributes[] = 'placeholder="' . $param[ 'placeholder' ] . '"';
					}
					if( ! empty( $param[ 'maxlength' ] ) ) {
						$attributes[] = 'maxlength="' . absint( $param[ 'maxlength' ] ) . '"';
					}
					if( ! empty( $param[ 'cols' ] ) ) {
						$attributes[] = 'cols="' . absint( $param[ 'cols' ] ) . '" style="width:auto"';
					}

					$return = '<textarea ' . join( ' ', $attributes ) . '>' . $value . '</textarea>';

					break;

				}

				case 'checkbox' : {
					$field = array();
					$value = ( $value == 1 || ( ! $value && isset( $param[ 'default' ] ) && $param[ 'default' ] == 1 ) ) ? 1 : 0;
					$label = ! empty( $param[ 'short_description' ] )  ? $param[ 'short_description' ] : '';

					$field[] = '<input type="checkbox"';
					if( ! empty( $param[ 'id' ] ) ) {
						$field[] = 'id="' . $prefix . $param[ 'id' ] . '"';
					}
					$field[] = 'name="' . $name . '"';
					$field[] = checked( 1, $value, false );
					$field[] = 'value="1" />';

					$return = join( ' ', $field );

					if( ! empty( $param[ 'label' ] ) )	{
						$return = '<label> ' . $return . ' ' . $label . '</label>';
					}


					break;
				}

				case 'select' : {
					$value = $value ? $value : ( isset( $param[ 'default' ] ) ? $param[ 'default' ] : '' );

					$attributes = array();

					if( ! empty( $param[ 'id' ] ) ) {
						$attributes[] = 'id="' . $prefix . $param[ 'id' ] . '"';
					}
					$attributes[] = 'name="' . $name . '"';
					$attributes[] = 'class="' . $classes . '"';

					$field = '<select ' . join( ' ', $attributes ) . '>';

					if( isset( $param[ 'placeholder' ] ) ) {
						$field .= '<option value="">' . $param[ 'placeholder' ] . '</option>';
					}

					if( ! empty( $param[ 'options' ] ) && is_array( $param[ 'options' ] ) ) {
						foreach( $param[ 'options' ] as $v => $l ) {
							$field .= '<option value="' . $v . '"' . selected( $value, $v, false ) . ' >' . $l . '</option>';
						}
					}

					$field .= '</select>';
					$return = $field;
					break;

				}

				case 'radio' : {
					$field = '';
					$tag = ( isset( $param[ 'inline' ] ) && true === $param[ 'inline' ] ) ? 'span' : 'p';
					$style = ( isset( $param[ 'inline' ] ) && true === $param[ 'inline' ] ) ? ' class="visody-radio-inline"' : '';
					$value = $value ? $value : ( isset( $param[ 'default' ] ) ? $param[ 'default' ] : '' );


					if( !empty( $param[ 'options' ] ) && is_array( $param[ 'options' ] ) ) {
						foreach( $param[ 'options' ] as $option_value => $option_name ) {

							$field .= '<'.$tag.$style.'><label><input type="radio" name="'.$name.'"' . checked( $value, $option_value, false ) . ' value="' . $option_value . '"> '. $option_name .'</label></'.$tag.'>';

						}
					}
					$return = $field;
					break;
				}

				case 'checklist' : {

					$field = '';

					$value = $value ? $value : ( isset( $param[ 'default' ] ) ? $param[ 'default' ] : '' );

					if( ! empty( $param[ 'options' ] ) && is_array( $param[ 'options' ] ) ) {
						foreach( $param[ 'options' ] as $option_value => $option_name ) {

							$checked = ( is_array( $value ) && in_array( $option_value, $value ) || $value == $option_value ) ? ' checked="checked"' : '';
							$field .= '<p><label><input type="checkbox" name="'.$name.'[]"' . $checked . ' value="' . $option_value . '"> '. $option_name .'</label></p>';

						}
					}
					$return = $field;
					break;
				}

				case 'image' : {

					$value = $value ? absint( $value ) : ( ! empty( $param[ 'default' ] ) ? absint( $param[ 'default' ] ) : '' );

					/* if no image */
					$image = ' button">' . __( 'Upload image', 'visody' );
					$display = 'none';

					/* if image is set */
					if( $value && $image_attributes = wp_get_attachment_image_src( $value, 'full' ) ) {
						$image = '"><img src="' . $image_attributes[0] . '" style="max-width:300px;height:auto;display:block;" />';
						$display = 'inline-block';
					}

					$return = '
					<div>
						<a href="javascript:void(0)" class="visody-upload-img-button' . $image . '</a><br />
						<input type="hidden" name="' . $name . '" id="' . ( ! empty( $param[ 'id' ] ) ? $prefix.$param[ 'id' ] : '' ) . '" value="' . $value . '" />
						<a href="javascript:void(0)" class="visody-remove-img-button" style="display:inline-block;display:' . $display . '">' . __( 'Remove image', 'visody' ) . '</a>
					</div>
					';
					break;

				}

				case 'file' : {

					$return = '<div>';
					// value == attachment id
					$value = $value ? absint( $value ) : ( !empty( $param[ 'default' ] ) ? absint( $param[ 'default' ] ) : '' );

					/* if file exists */
					if( $value && $url = wp_get_attachment_url( $value ) ) {
						$parsed = parse_url( $url );
						$filename = rawurlencode( basename( $parsed[ 'path' ] ) );

						$return .= '<a href="javascript:void(0)" class="visody-upload-file-button visody-upload-file-button--selected"><span class="visody-upload-file-button__icon">' . wp_get_attachment_image( $value, '', true ) . '</span><span class="visody-upload-file-button__fname">' . esc_html( $filename ) . '</span></a>';
						$return .= '<input type="hidden" name="' . $name . '" id="' . ( ! empty( $param[ 'id' ] ) ? $prefix.$param[ 'id' ] : '' ) . '" value="' . absint( $value ) . '" />';
						$return .= '<a href="javascript:void(0)" class="visody-remove-file-button" style="display:inline-block;">' . __( 'Remove file', 'visody' ) . '</a>';

					} else {

						$return .= '<a href="javascript:void(0)" class="visody-upload-file-button button">' . __( 'Upload file', 'visody' ) . '</a>';
						$return .= '<input type="hidden" name="' . $name . '" id="' . ( ! empty( $param[ 'id' ] ) ? $prefix.$param[ 'id' ] : '' ) . '" value="" />';
						$return .= '<a href="javascript:void(0)" class="visody-remove-file-button" style="display:inline-block;display:none">' . __( 'Remove file', 'visody' ) . '</a>';

					}

					$return .= '</div>';
					break;
				}

				case 'editor' : {

					$editor_args = array(
				 		'textarea_rows'=> ( !empty( $param[ 'rows' ] ) ? absint( $param[ 'rows' ] ) : 5 ),
				 		'teeny'=> true,
						'tinymce' => false,
						'media_buttons' => false,
						'drag_drop_upload' => false,
						'quicktags' => array(
							'buttons' => 'strong,em'
						)
				 	);

					$value = $value ? $value : ( isset( $param[ 'default' ] ) ? $param[ 'default' ] : '' );

				 	ob_start();

				 	// Echo the editor to the buffer
				 	wp_editor( $value, $name, $editor_args );

				 	// Store the contents of the buffer in a variable
				 	$return = ob_get_clean();

					break;
				}

				case 'gallery' : {
					// get
					$images = ! empty( $value ) ? $value : ( ! empty( $param[ 'default' ] ) ? $param[ 'default' ] : array() );
					// sanitize (it is kind of a support if there were just an integer in post meta or in a default field)
					$images = is_array( $value ) ? array_map( 'absint', $value ) : array_map( 'absint', array( $value ) );

					$return = '<div><ul class="visody-gallery-field">';

					foreach( $images as $image_id ) {

						if( $image = wp_get_attachment_image_src( $image_id, array( 100, 100 ) ) ) {

							$return .= '<li data-id="' . $image_id .  '">';
							$return .= '<span style="background-image:url(' . $image[0] . ')"></span><a href="#" class="visody-gallery-remove">&times;</a>';
							$return .= '<input type="hidden" name="' . $name . '[]" value="' . $image_id . '" />';
							$return .= '</li>';

						}

					}

					$return .= '</ul><div style="clear:both"></div></div><a href="#" data-name="' . $name . '" class="button visody-upload-images-button">' . __( 'Add images', 'visody' ) . '</a>';
					break;
				}

				case 'repeater' : {


					// redefine just for convenience
					$repeater_values = is_array( $value ) ? array_merge( array( array( 'hidden' ) ), $value ) : array( array() );

					$return = '<div class="visody-repeater-container" data-name="' . $name . '">';
					$index = 0;
					// no matter what we are going to display an empty repeater to copy

					// we might have repeater values otherwise it is just an empty field
					if( $repeater_values ) {
						foreach ( $repeater_values as $repeater_value ) {

							// wrapper
							$return .=  '<div class="visody-repeater-item"' . ( 0 === $index ? ' style="display:none"' : '' ) . '>';
							// title of an repeater item
							$return .=  "<p class=\"visody-repeater-item__handle\"><strong>" . __( 'Group', 'visody' ) . " <span class=\"visody-index\">{$index}</span></strong></p>";
							// our next goal is to walk through subfields
							foreach( $param[ 'subfields' ] as $subfield ) {

								if( in_array( $subfield[ 'type' ], array( 'repeater' ) ) ) {
									continue;
								}

								$return .= '<div class="' . $this->show_if_classes( $subfield, 'visody-repeater-item__field' ) . '" data-type="' . $subfield[ 'type' ] . '" data-name="' . $this->field_name( $subfield ) . '">';
								$return .= $this->subfield( $param, $subfield, $repeater_value, $index );
								$return .= '</div>';
							}
							$return .= "<p><a class=\"visody-repeater-remove\" href=\"#\">" . __( 'Remove line', 'visody' ) . "<span class=\"visody-index\">{$index}</span></a></p></div>";

							$index++;

						}
					}
					$return .= '</div><p><a href="#" class="button visody-repeater-add">' . __( 'Add item', 'visody' ) . '</a></p>';


					break;

				}


			} // endswitch

			if( isset( $param[ 'description' ] ) ) {
				$return .= '<p class="description">' . $param[ 'description' ] . '</p>';
			}

			return $return;

		}


		public function subfield( $field, $subfield, $value, $index ) {

			// first let's get repeater field name
			$repeater_name = $this->field_name( $field );
			// second, let's get a subfield name
			$name = $this->field_name( $subfield );
			// we do not need ID in subfield
			unset( $subfield[ 'id' ] );
			// it is a subfield name
			$subfield[ 'name' ] = "{$repeater_name}[{$index}][{$name}]";

			// kind of label
			//if( in_array( $subfield[ 'type' ], array( 'checkbox', 'radio', 'image' ) ) ) {
			$return =  "<p style=\"margin-bottom:4px\">{$subfield[ 'label' ]}</p>";
			//} else {
				// stuff against notices
			//	$return =  "<label for=\"{$subfield[ 'id' ]}\">{$subfield[ 'label' ]}</label>";
			//}

			$return .= $this->field(
				$subfield,
				( isset( $value[ $name ] ) ? $value[ $name ] : '' )
			);

			return $return;
		}

		public function field_name( $field ) {
			// name attribute support is quite useful
			return ( isset( $field[ 'name' ] ) && $field[ 'name' ] ) ? $field[ 'name' ] : $field[ 'id' ];
		}

		private function add_size_class( $classes, $default ) {
			// empty string
			if( '' == $classes ) {
				return $default;
			}
			// list of size classes
			$size_classes = array( 'regular-text', 'small-text', 'large-text' );
			// our classes
			$classes = explode( ' ', $classes );
			// if user didn't prodivde size class
			$has_size_class = false;
			foreach( $size_classes as $size_class ) {
				if( in_array( $size_class, $classes ) ) {
					$has_size_class = true;
				}
			}
			if( ! $has_size_class ) {
				$classes[] = $default;
			}
			return join( ' ', $classes );
		}

		public function sanitize( $value, $type ) {
			switch( $type ) {
				case 'textarea':{
					$value = sanitize_textarea_field( $value );
					break;
				}
				case 'checkbox':{
					$value = $this->sanitize_checkbox( $value );
					break;
				}
				case 'checklist':{
					$value = $this->sanitize_checklist( $value );
					break;
				}
				case 'editor':{
					$value = $this->sanitize_editor( $value );
					break;
				}
				case 'gallery':{
					$value = $this->sanitize_gallery( $value );
					break;
				}
				case 'repeater':{
					$value = $this->sanitize_repeater_field( $value );
					break;
				}
				default: {
					$value = sanitize_text_field( $value );
					break;
				}
			}
			return $value;
		}

		public function sanitize_checkbox( $value ) {
			return ( $value == 1 ) ? 1 : 0;
		}

		public function sanitize_checklist( $value ) {
			return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : array();
		}

		public function sanitize_editor( $value ) {
			$allowed_html =	array(
				'h1' => array(),
				'h2' => array(),
				'h3' => array(),
				'h4' => array(),
				'h5' => array(),
				'h6' => array(),
				'p' => array(),
				'br' => array(),
				'hr' => array(),
				'strong' => array(),
				'em' => array(),
				'i' => array(),
				's' => array(),
				'del' => array(),
				'ul' => array(),
				'ol' => array(),
				'li' => array(),
				'code' => array(),
				'iframe' => array(
					'align' => true,
					'allow' => true,
					'allowfullscreen' => true,
					'class' => true,
					'frameborder' => true,
					'height' => true,
					'id' => true,
					'loading' => true,
					'marginheight' => true,
					'marginwidth' => true,
					'mozallowfullscreen' => true,
					'name' => true,
					'referrerpolicy' => true,
					'scrolling' => true,
					'src' => true,
					'srcdoc' => true,
					'style' => true,
					'title' => true,
					'webkitAllowFullScreen' => true,
					'width' => true,
				),
				'a' => array(
					'href' => true,
					'target' => true,
					'rel' => true,
				)
			);

			return wp_kses( $value, apply_filters( 'visody_sanitize_editor_allowed_html', $allowed_html ) );

		}

		public function sanitize_gallery( $value ) {
			return is_array( $value ) ? array_map( 'absint', $value ) : array();
		}

		public function sanitize_repeater_field( $value ) {
			// TODO
			$newvalue = array();
			if( $value ) {
				foreach( $value as $item ) {
					$item_values = array_values( $item );
					if( $item_values ) {
						foreach( $item_values as $item_value ) {
							if( isset( $item_value ) && $item_value ) {
								$newvalue[] = $item;
								break;
							}
						}
					}
				}
			}
			return $newvalue ;
		}

		public function show_if_classes( $param, $classes = '' ) {
			if(
				isset( $param[ 'show_if' ][ 'id' ] ) && $param[ 'show_if' ][ 'id' ]
				&& isset( $param[ 'show_if' ][ 'value' ] ) && $param[ 'show_if' ][ 'value' ]
			) {
				$values = is_array( $param[ 'show_if' ][ 'value' ] ) ? join( '||', $param[ 'show_if' ][ 'value' ] ) : $param[ 'show_if' ][ 'value' ];
				$classes = "smf-show-if show-if--{$param[ 'show_if' ][ 'id' ]}--{$values}";
			}

			return $classes;
		}
	}
}
