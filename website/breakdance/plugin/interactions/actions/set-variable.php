<?php

namespace Breakdance\Interactions\Actions;

use Breakdance\Interactions\InteractionAction;
use function Breakdance\Elements\control;

class SetVariable extends InteractionAction
{
    /**
     * Returns the displayable label of the action.
     *
     * @return string
     */
    public static function name()
    {
        return 'Set Variable';
    }

    /**
     * URL friendly slug of the action.
     *
     * @return string
     */
    public static function slug()
    {
        return 'set_variable';
    }

    /**
     * Get controls for the builder
     * @return Control[]
     */
    public static function controls()
    {
        return [
            control('variable_name', 'Variable Name', [
                'type' => 'text',
                'layout' => 'vertical',
            ]),
            control('variable_value', 'Variable Value', [
                'type' => 'text',
                'layout' => 'vertical',
            ])
        ];
    }
}
