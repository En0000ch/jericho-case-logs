<?php

namespace Breakdance\Interactions\Actions;

use Breakdance\Interactions\InteractionAction;

class ToggleElement extends InteractionAction
{
    /**
     * Returns the displayable label of the action.
     *
     * @return string
     */
    public static function name()
    {
        return 'Toggle Element';
    }

    /**
     * URL friendly slug of the action.
     *
     * @return string
     */
    public static function slug()
    {
        return 'toggle_element';
    }
}
