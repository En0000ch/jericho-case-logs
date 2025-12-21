<?php

namespace Breakdance\Themeless;

use function Breakdance\Themeless\ThemeDisabler\is_theme_disabled;
use function Breakdance\Themeless\ThemeDisabler\is_zero_theme_enabled;

function outputHeadHtml()
{

    $isThemelessOrZeroTheme = is_theme_disabled() || is_zero_theme_enabled();

?>
    <!doctype html>
    <html <?php language_attributes(); ?>>

    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php if (is_theme_disabled() || is_zero_theme_enabled()) : ?>
            <?php echo getNormalizeDotCssLinkTag(); ?>
        <?php endif ?>
        <?php wp_head(); ?>
    </head>
    <?php

    $breakdance_or_oxygen_body_class = (string) BREAKDANCE_MODE;

    ?>

    <body <?php body_class($isThemelessOrZeroTheme ? [$breakdance_or_oxygen_body_class] : []); ?>>
        <?php wp_body_open(); ?>
    <?php
}

function outputFootHtml()
{
    ?>
        <?php wp_footer(); ?>
    </body>

    </html>
<?php
}

/**
 * @return string
 */
function getNormalizeCssUrl()
{
    return (string) bdox_run_filters('breakdance_normalize_dot_css_url', BREAKDANCE_PLUGIN_URL . "plugin/themeless/normalize.min.css");
}

/**
 * @return string
 */
function getNormalizeDotCssLinkTag()
{
    return (string) bdox_run_filters("breakdance_normalize_dot_css_link_tag", "<link rel='stylesheet' href='" . getNormalizeCssUrl() . "'>");
}

?>
