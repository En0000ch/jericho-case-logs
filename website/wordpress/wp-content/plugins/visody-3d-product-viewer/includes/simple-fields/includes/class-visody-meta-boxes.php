<?php

if (!class_exists('Visody_Meta_Boxes')) {

	#[\AllowDynamicProperties]
	class Visody_Meta_Boxes extends Visody_Meta_Fields
	{
		/**
		 * Constructor
		 */
		function __construct($metabox)
		{
			$this->metabox = $metabox;
			$this->prefix = $this->metabox['id'] . '_';

			add_action('add_meta_boxes', array($this, 'create'));
			add_action('save_post', array($this, 'save'), 1, 2);
			add_action('admin_enqueue_scripts', array($this, 'set_color_scripts'), 1, 2);
		}

		function set_color_scripts($hook)
		{
			wp_enqueue_media();
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_script('wp-color-picker');
		}

		// Create a metabox for each post type and with given capabilities
		function create()
		{
			// do nothing if user cap for this metabox is not enough
			if (!empty($this->metabox['capability']) && !current_user_can($this->metabox['capability'])) {
				return;
			}

			if (!empty($this->metabox['post_template']) && !empty($_GET['post'])) {
				$post_template = is_array($this->metabox['post_template']) ? $this->metabox['post_template'] : array($this->metabox['post_template']);
				$selected_post_template = get_post_meta(sanitize_key($_GET['post']), '_wp_page_template', true);
				if (!in_array($selected_post_template, $post_template)) {
					return;
				}
			}

			add_meta_box(
				$this->metabox['id'],
				$this->metabox['name'],
				array($this, 'render'),
				(isset($this->metabox['post_type']) ? $this->metabox['post_type'] : 'post'),
				'normal',
				(isset($this->metabox['priority']) ? $this->metabox['priority'] : 'default')
			);
		}

		// Meta box content
		function render($post)
		{
			wp_nonce_field($this->metabox['id'], $this->metabox['id'] . '_wpnonce');

			if (isset($this->metabox['tabs']) && is_array($this->metabox['tabs'])) {
				?>
				<div id="taxonomy-<?php echo esc_attr($this->metabox['id']) ?>" class="categorydiv">
					<ul id="<?php echo esc_attr($this->metabox['id']) ?>-tabs" class="category-tabs">
						<?php
						$first = true;
						foreach ($this->metabox['tabs'] as $tab) {
							?><li<?php echo ($first ? ' class="tabs"' : '') ?>>
								<a href="#<?php echo esc_attr(sanitize_key($tab['name'])) ?>">
									<?php echo esc_attr($tab['name']) ?>
								</a>
							</li>
							<?php
						$first = false;
					}
					?>
					</ul>
					<?php
					$first = true;
					foreach ($this->metabox['tabs'] as $tab) {
						?>
						<div id="<?php echo esc_attr(sanitize_key($tab['name'])) ?>" class="tabs-panel" style="max-height: none;<?php echo !$first ? 'display:none;' : '' ?>">
							<?php
							$this->display_fields($tab['fields']);
							?>
						</div>
						<?php
						$first = false;
					}
					?>
				</div>
				<?php
			} else {
				$this->display_fields($this->metabox['fields']);
			}
		}

		function display_fields($fields)
		{
			global $post;
			if (!is_array($fields)) {
				return;
			}
			?>
			<script>
				jQuery(document).ready(function($) {
					$('.color-picker').each(function() {
						$(this).wpColorPicker();
					});
				});
			</script>
			<table class="form-table">
				<tbody><?php
					foreach ($fields as $field) {
						$value = get_post_meta($post->ID, $this->field_name($field), true);
						echo $this->field_html($field, $value, $post->ID);
					}
					?>
				</tbody>
			</table>
			<?php
		}

		function field_html($field, $value, $object_id)
		{
			$html = '';
			$field['type'] = isset($field['type']) ? $field['type'] : 'text';

			// begin field wrap
			if (in_array($field['type'], array('checkbox', 'radio'))) {
				$html .= '<tr class="' . $this->show_if_classes($field) . '"><th style="font-weight:normal">' . (!empty($field['label']) ? $field['label'] : '') . '</th><td>';
			} else {
				$html .= '<tr class="' . $this->show_if_classes($field) . '"><th style="font-weight:normal"><label for="' . $this->prefix . $field['id'] . '">' . (!empty($field['label']) ? $field['label'] : '') . '</label></th><td>';
			}

			$html .= $this->field($field, $value, $this->prefix);
			$html .= '</td></tr>';

			return $html;
		}

		// Save metabox content
		function save($post_id, $post)
		{
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
			}
			
			if (defined('DOING_AJAX') && DOING_AJAX) {
				return;
			}

			if ( ! isset( $_POST[$this->metabox['id'] . '_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $this->metabox['id'] . '_wpnonce' ] ) ) , $this->metabox['id'] ) ) {
				return;
			}

			if (!current_user_can('edit_post', $post_id)) {
				return;
			}

			if (is_array($this->metabox['post_type']) && !in_array($post->post_type, $this->metabox['post_type']) || !is_array($this->metabox['post_type']) && $this->metabox['post_type'] !== $post->post_type) {
				return; // this post type does not have a metabox
			}

			if (isset($this->metabox['tabs']) && is_array($this->metabox['tabs'])) {
				foreach ($this->metabox['tabs'] as $tab) {
					if (isset($tab['fields'])) {
						$this->save_fields($tab['fields'], $post_id);
					}
				}
			} else {
				// if fields are specified
				if (isset($this->metabox['fields'])) {
					$this->save_fields($this->metabox['fields'], $post_id);
				}
			}
		}

		function save_fields($fields, $post_id)
		{
			foreach ($fields as $field) {
				$name = $this->field_name($field);
				$value = '';

				if (isset($_POST[$name])) {
					// call specific sanitize function based on field type.
					$value = $this->sanitize( $_POST[$name], $field['type'] );
				}

				$value = visody_simple_esc_array_or_string( $value );

				update_post_meta(
					$post_id,
					$name,
					$value
				);
			}
		}
	}
}
