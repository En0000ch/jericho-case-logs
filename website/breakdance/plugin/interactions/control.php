<?php

namespace Breakdance\Interactions;

use function Breakdance\Elements\control;
use function Breakdance\Elements\controlSection;

add_filter('breakdance_element_controls', 'Breakdance\Interactions\addControls', 70, 2);

/**
 * @param Control[] $controls
 * @return Control[]
 */
function addControls($controls)
{
    if (BREAKDANCE_MODE !== 'oxygen') {
        return $controls;
    }

    $controls['settingsSections'][] = controlSection(
        'interactions',
        'Interactions',
        [
            control('interactions', 'Interactions',
                [
                    'type' => 'interactions',
                    'layout' => 'vertical',
                    'noLabel' => true
                ]
            ),
        ],
        ['isExternal' => true]
    );

    /** @var Control[] $controls */
    return $controls;
}
