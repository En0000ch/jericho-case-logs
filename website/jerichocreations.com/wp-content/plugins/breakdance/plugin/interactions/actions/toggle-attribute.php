<?php

namespace Breakdance\Interactions\Actions;

use Breakdance\Interactions\InteractionAction;
use function Breakdance\Elements\control;

class ToggleAttribute extends InteractionAction
{
    /**
     * Returns the displayable label of the action.
     *
     * @return string
     */
    public static function name()
    {
        return 'Toggle Attribute';
    }

    /**
     * URL friendly slug of the action.
     *
     * @return string
     */
    public static function slug()
    {
        return 'toggle_attribute';
    }

    /**
     * Get controls for the builder
     * @return Control[]
     */
    public static function controls()
    {
        return [
            control('attribute_name', 'Attribute Name', [
                'type' => 'text',
                'layout' => 'vertical',
            ]),
            control('attribute_value', 'Attribute Value', [
                'type' => 'text',
                'layout' => 'vertical',
            ])
        ];
    }
}
