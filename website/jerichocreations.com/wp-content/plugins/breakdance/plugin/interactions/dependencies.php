<?php

namespace Breakdance\Interactions;

add_filter('breakdance_element_dependencies', '\Breakdance\Interactions\addDependencies', 100, 1);

/**
 * @param ElementDependenciesAndConditions[] $deps
 * @return ElementDependenciesAndConditions[]
 */
function addDependencies($deps)
{
    $condition = ['propertyPath' => 'settings.interactions.interactions'];

    $deps[] = [
        "frontendCondition" => $condition,
        "builderCondition"  => $condition,
        "scripts" => [
            "%%BREAKDANCE_ELEMENTS_PLUGIN_URL%%dependencies-files/breakdance-interactions@1/dist/interactions.umd.js",
        ]
    ];

    return $deps;
}
