<?php

namespace Breakdance\GlobalSettings;

use function Breakdance\GlobalSettings\WooCommerce\WOO_SECTION;
use function Breakdance\GlobalSettings\WooCommerce\WOO_TEMPLATE;
use function Breakdance\WooCommerce\Settings\isWooIntegrationEnabled;

require_once __DIR__ . "/atoms/base.php";
require_once __DIR__ . "/colors/base.php";
require_once __DIR__ . "/buttons/base.php";
require_once __DIR__ . "/typography/base.php";
require_once __DIR__ . "/forms/base.php";
require_once __DIR__ . "/containers/base.php";
require_once __DIR__ . "/other/base.php";
require_once __DIR__ . "/advanced/base.php";
require_once __DIR__ . "/woocommerce/base.php";

/**
 * @return Control[]
 */
function globalSettingsControlSections()
{

    /*
    this filter is for internal use and is subject to a
    backwards-incompatible change in a future version.
    */
    if (!bdox_run_filters('breakdance_global_settings_enable_default_control_sections', true)) {
        $sections = [];
    } else {
        $sections = [
            COLORS_SECTION(),
            BUTTONS_SECTION(),
            TYPOGRAPHY_SECTION(),
            FORMS_SECTION(),
            CONTAINERS_SECTION(),
            ADVANCED_SECTION(),
            OTHER_SECTION(),
        ];

        if (isWooIntegrationEnabled()) {
            $sections[] = WOO_SECTION();
        }
    }

    /**
     * @var Control[]
     */
    $additionalSections = bdox_run_filters('breakdance_global_settings_control_sections_append', []);

    return array_merge($sections, $additionalSections);
}

/**
 * @return string
 */
function globalSettingsCssTemplate()
{

    /*
    this filter is for internal use and is subject to a
    backwards-incompatible change in a future version.
    */
    if (bdox_run_filters('breakdance_global_settings_css_template_enable_default_template', true)) {
        $template =
            GLOBAL_CSS_VARS() .
            "\n" . ATOMS_TEMPLATE() .
            "\n" . OTHER_DEFAULT_CSS() .
            "\n" . COLORS_TEMPLATE() .
            "\n" . BUTTONS_TEMPLATE() .
            "\n" . TYPOGRAPHY_TEMPLATE() .
            "\n" . FORMS_TEMPLATE() .
            "\n" . CONTAINERS_TEMPLATE() .
            "\n" . ADVANCED_TEMPLATE();
    } else {
        $template = '';
    }

    if (isWooIntegrationEnabled()) {
        $template .= "\n" . WOO_TEMPLATE();
    }

    $append = "\n" . (string) bdox_run_filters('breakdance_global_settings_css_twig_template_append', '');

    $template .= $append;

    return $template;
}

/**
 * @return string[]
 */
function globalPropertyPathsToWhitelistInFlatProps()
{
    $typographyProps = typographyPropertyPathsToWhitelistInFlatProps();

    /** @var string[] */
    $additionalProps = bdox_run_filters('breakdance_global_settings_property_paths_to_whitelist_in_flat_props_append', []);

    return array_merge_recursive(
        $typographyProps,
        [
            'settings.woocommerce.other.stack_vertically_at',
            'settings.forms.submit_button.styles.size.full_width_at',
            'builderPrefix'
        ],
        $additionalProps
    );
}
