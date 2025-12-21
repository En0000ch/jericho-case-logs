<?php

namespace Breakdance\Variables;

use function Breakdance\Elements\control;
use function Breakdance\Elements\controlSection;

/**
 * @param Control[] $controls
 * @return Control[]
 */
function addControls($controls)
{
    if (BREAKDANCE_MODE !== 'oxygen') return $controls;

    $controls['settingsSections'][] = controlSection(
        'override_variables',
        'Override Variables',
        [
            control('override_variables', 'Override Variables', [
                'type' => 'override_variables',
                'layout' => 'vertical',
            ])
        ],
        ['isExternal' => true]
    );

    /** @var Control[] $controls */
    return $controls;
}

add_filter('breakdance_element_controls', 'Breakdance\Variables\addControls', 69, 2);


add_filter('breakdance_element_css_template', '\Breakdance\Variables\addCssTemplate', 100, 1);

/**
 * @return string
 */
function cssTemplateForElement()
{
    $macro = (string)file_get_contents(dirname(__FILE__) . "/template/macro.twig");
    $template = (string)file_get_contents(dirname(__FILE__) . "/template/element.twig");

    return $macro . "\n\n" . $template;
}

/**
 * @param string $cssTemplate
 * @return string
 */
function addCssTemplate($cssTemplate)
{
    return $cssTemplate . "\n\n" . cssTemplateForElement();
}

/**
 * @param string[] $props
 * @return string[]
 */
function elementPropertyPathsToWhitelist($props)
{
    return array_merge($props, [
        'settings.override_variables.override_variables[].type',
        'settings.override_variables.override_variables[].label',
        'settings.override_variables.override_variables[].cssVariableName',
    ]);
}

add_filter('breakdance_element_property_paths_to_whitelist_in_flat_props', '\Breakdance\Variables\elementPropertyPathsToWhitelist', 10, 1);
