<?php

namespace Breakdance\Interactions;

add_filter('breakdance_element_attributes', '\Breakdance\Interactions\addAttributes', 100, 1);

/**
 * @param ElementAttribute[] $attributes
 * @return array
 */
function addAttributes($attributes)
{
    $attributes[] = [
        "name" => "data-interactions",
        "template" => "{{ settings.interactions.interactions ? settings.interactions.interactions|json_encode : '' }}",
    ];

    return $attributes;
}
