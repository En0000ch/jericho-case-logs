<?php

// @psalm-ignore-file

namespace Breakdance\Singularity\Brand;

use function Breakdance\Data\get_global_option;
use function Breakdance\Data\set_global_option;

add_action('breakdance_loaded', function () {

    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_get_brand_settings',
        'Breakdance\Singularity\Brand\getBrandSettings',
        'edit',
        true
    );

    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_save_brand_settings',
        'Breakdance\Singularity\Brand\saveBrandSettings',
        'edit',
        false,
        [
            'args' => [
                'settings' => FILTER_UNSAFE_RAW
            ]
        ]
    );
});


/**
 * Save the brand settings.
 *
 * @param string $settings JSON encoded settings.
 * @return array The merged settings with defaults.
 */
function saveBrandSettings($settings)
{
    /** @var array $settings */
    $settings = json_decode((string)$settings, true);
    set_global_option('breakdance_singularity_brand_settings_json_string', $settings);
    return getBrandSettings();
}



/**
 * @return array
 */
function getBrandSettings()
{
    /** @var array $settings */
    $settings = get_global_option('breakdance_singularity_brand_settings_json_string');

    if (empty($settings)) {
        $settings = [];
    }

    $defaults = [
        'color' => [
            'primary' => null,
            'neutral' => null,
            'secondary' => [],
        ],
    ];

    return array_merge($defaults, $settings);
}
