<?php

namespace Breakdance\Themeless\ThemeDisabler;

use WP_Customize_Manager;

/** @psalm-suppress UndefinedConstant */
define("BREAKDANCE_THEME_ROOT", __BREAKDANCE_DIR__ . "/plugin/themeless/themes");
define("BREAKDANCE_THEME_SLUG", "breakdance-zero");
define("BREAKDANCE_THEME_NAME", "Breakdance Zero Theme");
define("BREAKDANCE_THEME_DISABLED_MESSAGE", "You disabled the WordPress theme system entirely at <a href='admin.php?page=breakdance_settings&tab=theme_disabler'>Breakdance &raquo; Settings &raquo; Theme</a>. The active theme will never be loaded and has no impact on your site's performance or appearance.");

define("OXYGEN_THEME_SLUG", "oxygen-zero");
define("OXYGEN_THEME_NAME", "Oxygen Zero Theme");
define("OXYGEN_THEME_DISABLED_MESSAGE", "You're using Oxygen, so the WordPress theme system is entirely disabled. <strong>The active theme will never be loaded and has no impact on your site's performance or appearance.</strong>");

/**
 * @return string
 */
function getThemeDisabledMessage()
{
    if (BREAKDANCE_MODE === 'oxygen') {
        return OXYGEN_THEME_DISABLED_MESSAGE;
    } else {
        return BREAKDANCE_THEME_DISABLED_MESSAGE;
    }
}

function disableTheme()
{
    register_theme_directory(BREAKDANCE_THEME_ROOT);

    // See class-wp-customize-manager.php - line 676

    // Disable the current active theme and replace it with Breakdance Zero.
    add_filter("template", 'Breakdance\Themeless\ThemeDisabler\getThemeName', 15);
    add_filter("stylesheet", 'Breakdance\Themeless\ThemeDisabler\getThemeName', 15);

    add_filter("pre_option_stylesheet", 'Breakdance\Themeless\ThemeDisabler\getThemeName');
    add_filter("pre_option_template", 'Breakdance\Themeless\ThemeDisabler\getThemeName');

    add_filter("pre_option_stylesheet_root", 'Breakdance\Themeless\ThemeDisabler\getThemeRoot');
    add_filter("pre_option_template_root", 'Breakdance\Themeless\ThemeDisabler\getThemeRoot');

    // Show message on the themes page.
    add_action("admin_notices", 'Breakdance\Themeless\ThemeDisabler\themeScreenNotice');
    add_action("customize_register", 'Breakdance\Themeless\ThemeDisabler\customizerRemoveThemePanel', 15);
    // add_action("customize_controls_print_footer_scripts", 'Breakdance\Themeless\ThemeDisabler\customizerNotice');
}

// Disable the theme as soon as possible. WordPress calls get_stylesheet() or get_template()
// before the theme is setup, by that point it's too late to change the theme.
if (is_theme_disabled()) {
    disableTheme();
}

/**
 * @return boolean
 */
function is_theme_disabled()
{
    if (BREAKDANCE_MODE === 'oxygen') return true;
    $is_theme_disabled = (string) \Breakdance\Data\get_global_option('is_theme_disabled');
    return $is_theme_disabled === 'yes' || boolval($_GET['builder_preview'] ?? false);
}

/**
 * @return boolean
 */
function is_zero_theme_enabled()
{
    if (is_theme_disabled()) return false;
    $parentThemeOrThemeName = getEnabledParentThemeName();
    return $parentThemeOrThemeName === BREAKDANCE_THEME_NAME || $parentThemeOrThemeName === OXYGEN_THEME_NAME;
}

/**
 * @return string
 */
function getEnabledParentThemeName()
{
    /** @var string  */
    $themeName = wp_get_theme(get_template())->get('Name') ?: '';

    return $themeName;
}

/**
 * @param string $themeRoot
 * @return string
 */
function getThemeRoot($themeRoot)
{
    return BREAKDANCE_THEME_ROOT;
}

/**
 * @param string $stylesheetOrTemplate
 * @return string
 */
function getThemeName($stylesheetOrTemplate)
{
    return BREAKDANCE_MODE === 'oxygen' ? OXYGEN_THEME_SLUG : BREAKDANCE_THEME_SLUG;
}

function customizerRemoveThemePanel(WP_Customize_Manager $wp_customize)
{
    $wp_customize->remove_panel("themes");
}

function customizerNotice()
{
    $msg = getThemeDisabledMessage();

    echo <<<JS
        <script>
            jQuery(() => {
                wp.customize.bind('ready', () => {
                  const notification = new wp.customize.Notification(
                    "breakdance-notification", {
                      dismissible: true,
                      message: "{$msg}",
                      type: "error",
                      priority: 100
                    }
                  );

                  wp.customize.notifications.add(
                    "breakdance-notification",
                    notification
                  );
               });
            });
       </script>
    JS;
}

function themeScreenNotice()
{
    $current_screen = get_current_screen();
    if ($current_screen && $current_screen->id === "themes") {
?>
        <div class="notice notice-error">
            <p>
                <?php echo getThemeDisabledMessage(); ?>
            </p>
        </div>

        <style>
            .theme-browser {
                transition: 1s ease-in-out all;
            }

            .theme-browser:not(:hover) {
                opacity: .1;
                filter: grayscale(100%);
            }

            .theme-browser .button.activate {
                display: none;
            }

            /* Hide zero theme from the UI */
            .theme-browser .theme.active {
                display: none;
            }
        </style>
<?php
    }
}

/**
 * @param array<string, mixed> $themes
 * @return array<string, mixed>
 */
function hideFromThemeScreen($themes)
{
  // Hide Breakdance Zero from Oxygen, vice-versa.
  if (BREAKDANCE_MODE == 'oxygen') {
    unset($themes['breakdance-zero']);
  } else {
    unset($themes['oxygen-zero']);
  }

  return $themes;
}
add_filter('wp_prepare_themes_for_js','\Breakdance\Themeless\ThemeDisabler\hideFromThemeScreen');
