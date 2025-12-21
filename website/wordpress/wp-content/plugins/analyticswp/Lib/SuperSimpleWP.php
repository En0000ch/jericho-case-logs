<?php

namespace AnalyticsWP\Lib;

/**
 * @psalm-type SettingInput = 'radio'|'checkbox'|'color'|'date'|'datetime-local'|'email'|'hidden'|'month'|'number'|'password'|'range'|'search'|'select'|'text'|'textarea'|'time'|'url'|'week'|'wp-user-roles'|'wp-user-roles-without-subscriber'
 * 
 * // Updated SettingsArgs so each setting may include a tab name.
 * @psalm-type SettingConfig = array{
 *   type: SettingInput,
 *   label: string,
 *   description: string,
 *   default?: mixed,
 *   options?: array<string, string>,
 *   tab?: string,
 *   attributes?: array<string, string>
 * }
 * 
 * @psalm-type SettingsArgs = array<string, SettingConfig>
 * 
 * @psalm-type MenuPagesArgs = array<string, array{
 *     title: string,
 *     menu_title: string,
 *     capability: string,
 *     roles_function?: callable():bool,
 *     hidden?: bool,
 *     function: callable,
 *   }>
 *
 * @psalm-type PluginRegistrationArgs = array{
 *   slug: string,
 *   name: string,
 *   menu_pages: MenuPagesArgs,
 *   settings: SettingsArgs,
 *   on_plugins_loaded: callable
 * }
 */
class SuperSimpleWP
{
	/**
	 * @var PluginRegistrationArgs
	 */
	private static $plugin_registration_args = [
		'slug' => '',
		'name' => '',
		'menu_pages' => [],
		'settings' => [],
		'on_plugins_loaded' => '__return_null',
	];

	/**
	 * Registers a plugin.
	 *
	 * @param PluginRegistrationArgs $args
	 *
	 * @return void
	 */
	public static function register_plugin($args)
	{
		self::$plugin_registration_args = $args;

		add_action(
			'admin_menu',
			function () use ($args) {
				self::register_admin_menu($args);
				self::register_settings($args['slug'], $args['settings']);
			}
		);

		add_action('plugins_loaded', function () use ($args) {
			$args['on_plugins_loaded']();
		});
	}

	/**
	 * Registers the admin menu.
	 *
	 * @param PluginRegistrationArgs $args
	 *
	 * @return void
	 */
	public static function register_admin_menu($args)
	{
		add_menu_page(
			$args['name'],
			$args['name'],
			'read',
			$args['slug'],
			function () use ($args) {
				if (isset($args['menu_pages']['main']['roles_function'])) {
					$roles_function = $args['menu_pages']['main']['roles_function'];
					if (!$roles_function()) {
						echo '<h1>Your user role does not have access to this page.</h1>';
						echo '<p>Please contact your site administrator for access.</p>';
						echo '<p>If you are the site administrator, please check the AnalyticsWP Settings menu</p>';
						return;
					}
				}
			},
			'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iNyIgdmlld0JveD0iMCAwIDIwIDciIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0wLjg3NzE5MiA2LjcxODg1TDcuMjM0NDVlLTA3IDUuNzYyODdMNS44NzEzMSAwLjAzMzc5OTRMNi43NzQ1NiAwLjk4ODUxNUwwLjg3NzE5MiA2LjcxODg1Wk01Ljc4ODcgNi43MTg4NUwyLjY2MzUgMy4yNDE0N0wzLjY4ODggMi4yNTQwMUw3LjAwOTE5IDUuNDg0ODVMNS43ODg3IDYuNzE4ODVaTTUuNzg4NyA2LjcxODg1TDYuMjcxNzcgMC4wMzkzOTU0TDcuNzY3NzggMC4wMzM3OTk0TDcuNjI1NTQgNi43MTg4NUg1Ljc4ODdaTTUuNzg4NyAxLjM4MTU4TDUuODcxMzEgMC4wMzM3OTk0TDYuMzgwMjIgMC4wMzkzOTU0VjEuMzgxNThINS43ODg3WiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTcuNjI1NTQgNi43MTg4NUw3LjYzMjc5IDQuODg2ODdMMTIuNjA1NyAwLjAzOTM5NTRIMTMuOTg1OVY0Ljg2MTY5TDE4Ljk3ODIgMEwyMCAwLjkzNDYyMkwxNC4wMjYxIDYuNzUwMzRIMTIuNjA1N1YxLjg4Mjg2TDcuNjI1NTQgNi43MTg4NVoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo='
		);

		// add the other menu pages
		foreach ($args['menu_pages'] as $menu_slug => $menu_page) {
			if (isset($menu_page['roles_function'])) {
				$roles_function = $menu_page['roles_function'];
				if (!$roles_function()) {
					continue;
				}
			}

			if ($menu_slug === 'main') {
				add_submenu_page(
					$args['slug'],
					$menu_page['title'],
					$menu_page['menu_title'],
					$menu_page['capability'],
					$args['slug'],
					$menu_page['function']
				);
				continue;
			}
			add_submenu_page(
				$args['slug'],
				$menu_page['title'],
				$menu_page['menu_title'],
				$menu_page['capability'],
				$menu_slug,
				$menu_page['function']
			);

			// Remove the submenu page from view if it should be hidden
			if (isset($menu_page['hidden']) && $menu_page['hidden']) {
				$url_to_match_on = 'admin.php?page=' . $menu_slug;
				// $css to hide the li with child a[href="$url_to_match_on"]
				$style = '<style>
                    li a[href="' . $url_to_match_on . '"] {
                        display: none !important;
                    }
                    </style>
                    ';

				add_action('admin_head', function () use ($style) {
					echo $style;
				});
			}
		}
	}

	/**
	 * Registers settings.
	 *
	 * @param string $slug
	 * @param SettingsArgs $settings_args
	 *
	 * @return void
	 */
	public static function register_settings($slug, $settings_args)
	{
		if (empty($settings_args)) {
			return;
		}

		// if the current user is not an admin, do not show the settings page
		if (!current_user_can('manage_options')) {
			return;
		}

		// Use the WordPress settings API – we still use a single section.
		add_settings_section(
			$slug . '-settings',
			'General Settings',
			function () use ($slug) {
				echo '<p>These are the settings for ' . esc_attr($slug) . '</p>';
			},
			$slug
		);

		// Register each setting. We wrap each field in a div with a data-tab attribute.
		foreach ($settings_args as $setting_slug => $setting) {
			$option_name = $slug . '_' . $setting_slug;
			register_setting($slug, $option_name, ['default' => $setting['default'] ?? false]);

			add_settings_field(
				$option_name,
				$setting['label'],
				function () use ($slug, $setting_slug, $setting) {
					/** @var mixed $option_value */
					$option_value = self::get_setting($slug, $setting_slug);
					// Determine the tab for this setting (default: General)
					$tab = isset($setting['tab']) ? $setting['tab'] : 'General';
					$tab_slug = sanitize_title($tab);

					// Begin tab container for this field.
					echo '<div class="setting-field" data-tab="' . esc_attr($tab_slug) . '">';

					if ($setting['type'] === 'wp-user-roles' || $setting['type'] === 'wp-user-roles-without-subscriber') {
						$roles = get_editable_roles();
						if ($setting['type'] === 'wp-user-roles-without-subscriber') {
							unset($roles['subscriber']);
						}

						foreach ($roles as $role_value => $role_name) {
							$checked = isset($option_value[$role_value]) ? 'checked="checked"' : '';
							$checkbox_id = esc_attr($slug . '_' . $setting_slug . '_' . $role_value);

							echo '<input type="checkbox" id="' . $checkbox_id . '" name="' . esc_attr($slug . '_' . $setting_slug . '[' . $role_value . ']') . '" value="1" ' . $checked . '>';
							echo '<label for="' . $checkbox_id . '">' . esc_html((string)$role_name['name']) . '</label><br>';
						}
					} elseif ($setting['type'] === 'radio') {
						$options = $setting['options'] ?? [];
						foreach ($options as $option_key => $option_label) {
							$checked = ($option_value === $option_key) ? 'checked="checked"' : '';
							$radio_id = esc_attr($slug . '_' . $setting_slug . '_' . $option_key);

							echo '<input type="radio" id="' . $radio_id . '" name="' . esc_attr($slug . '_' . $setting_slug) . '" value="' . esc_attr($option_key) . '" ' . $checked . '>';
							echo '<label for="' . $radio_id . '">' . esc_html($option_label) . '</label><br>';
						}
					} elseif ($setting['type'] === 'select') {
						$options = $setting['options'] ?? [];
						echo '<select name="' . esc_attr($slug . '_' . $setting_slug) . '" id="' . esc_attr($slug . '_' . $setting_slug) . '">';
						foreach ($options as $option_key => $option_label) {
							$selected = ($option_value === $option_key) ? 'selected="selected"' : '';
							echo '<option value="' . esc_attr($option_key) . '" ' . $selected . '>' .
								esc_html($option_label) .
								'</option>';
						}
						echo '</select>';
					} elseif ($setting['type'] === 'checkbox') {
						$checked = ($option_value) ? 'checked="checked"' : '';
						$checkbox_id = esc_attr($slug . '_' . $setting_slug);
						echo '<input type="checkbox" id="' . $checkbox_id . '" name="' . esc_attr($slug . '_' . $setting_slug) . '" value="1" ' . $checked . '>';
						echo '<label for="' . $checkbox_id . '">' . esc_html($setting['label']) . '</label>';
					} elseif ($setting['type'] === 'textarea') {
						echo '<textarea name="' . esc_attr($slug . '_' . $setting_slug) . '" id="' . esc_attr($slug . '_' . $setting_slug) . '">'
							. esc_html((string)$option_value)
							. '</textarea>';
					} else {
						$attributes = '';
						if (isset($setting['attributes']) && is_array($setting['attributes'])) {
							foreach ($setting['attributes'] as $attr => $value) {
								$attributes .= ' ' . esc_attr($attr) . '="' . esc_attr($value) . '"';
							}
						}
						echo '<input type="' . esc_attr($setting['type']) . '" name="' . esc_attr($slug . '_' . $setting_slug) . '" value="' . esc_attr((string)$option_value) . '"' . $attributes . ' />';
					}

					echo '<p class="description">' . esc_attr($setting['description']) . '</p>';
					// End tab container.
					echo '</div>';
				},
				$slug,
				$slug . '-settings'
			);
		}

		//
		// Add a submenu page to render the Settings page with tabs.
		//
		add_submenu_page(
			$slug,
			'Settings',
			'Settings',
			'read',
			$slug . '-settings',
			function () use ($slug, $settings_args) {
				// Gather unique tabs from settings.
				$tabs = [];
				foreach ($settings_args as $setting) {
					$tab = isset($setting['tab']) ? $setting['tab'] : 'General';
					if (!in_array($tab, $tabs, true)) {
						$tabs[] = $tab;
					}
				}

				echo '<h1>Settings</h1>';

				// Output nav tabs.
				echo '<h2 class="nav-tab-wrapper">';
				foreach ($tabs as $tab) {
					echo '<a href="#" class="nav-tab" data-tab="' . esc_attr(sanitize_title($tab)) . '">' . esc_html($tab) . '</a>';
				}
				echo '</h2>';

				echo '<form method="post" action="options.php">';
				settings_fields($slug);
				do_settings_sections($slug);
				submit_button();
				echo '</form>';

				// Inline JavaScript to toggle tab content.

?>
			<script>
				document.addEventListener("DOMContentLoaded", function() {
					var tabs = document.querySelectorAll(".nav-tab");
					if (tabs.length > 0) {
						var activeTab = tabs[0].getAttribute("data-tab");
						tabs[0].classList.add("nav-tab-active");

						// Hide rows not in the active tab.
						var rows = document.querySelectorAll("form table tr");
						rows.forEach(function(row) {
							var fieldDiv = row.querySelector(".setting-field");
							if (fieldDiv) {
								if (fieldDiv.getAttribute("data-tab") !== activeTab) {
									row.style.display = "none";
								} else {
									row.style.display = "";
								}
							}
						});
					}

					// Handle tab clicks.
					tabs.forEach(function(tab) {
						tab.addEventListener("click", function(e) {
							e.preventDefault();
							tabs.forEach(function(t) {
								t.classList.remove("nav-tab-active");
							});
							this.classList.add("nav-tab-active");

							var selectedTab = this.getAttribute("data-tab");
							var rows = document.querySelectorAll("form table tr");
							rows.forEach(function(row) {
								var fieldDiv = row.querySelector(".setting-field");
								if (fieldDiv) {
									if (fieldDiv.getAttribute("data-tab") === selectedTab) {
										row.style.display = "";
									} else {
										row.style.display = "none";
									}
								}
							});
						});
					});
				});
			</script>

<?php
			}
		);
	}

	/**
	 * Gets a setting value.
	 *
	 * @param string $plugin_slug
	 * @param string $setting_slug
	 *
	 * @return mixed
	 */
	public static function get_setting($plugin_slug, $setting_slug)
	{
		/** @var mixed */
		$default = self::$plugin_registration_args['settings'][$setting_slug]['default'] ?? false;

		return get_option($plugin_slug . '_' . $setting_slug, $default);
	}

	/**
	 * Enqueue a JavaScript file in the WordPress admin area.
	 *
	 * @param string $plugin_directory
	 *
	 * @return void
	 */
	public static function enqueue_scripts($plugin_directory) {}

	/**
	 * Checks if the current user has access to the admin pages.
	 * 
	 * Example usage:
	 *   $allowed_roles = ['administrator' => '1', 'editor' => '1'];
	 *   SuperSimpleWP::does_current_user_have_admin_access($allowed_roles);
	 * 
	 * @param array<string, string> $allowed_roles
	 *
	 * @return bool
	 */
	public static function does_current_user_have_admin_access($allowed_roles = [])
	{
		static $has_admin_access = null;

		if ($has_admin_access !== null) {
			return (bool)$has_admin_access;
		}

		if (is_super_admin()) {
			$has_admin_access = true;
			return true;
		}

		$current_user = wp_get_current_user();

		foreach ($current_user->roles as $role) {
			if (isset($allowed_roles[$role])) {
				$has_admin_access = true;
				return true;
			}
		}

		$has_admin_access = false;
		return false;
	}
}
