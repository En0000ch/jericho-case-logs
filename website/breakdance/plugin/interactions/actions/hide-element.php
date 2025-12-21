<?php

namespace Breakdance\Interactions\Actions;

use Breakdance\Interactions\InteractionAction;

class HideElement extends InteractionAction
{
    /**
     * Returns the displayable label of the action.
     *
     * @return string
     */
    public static function name()
    {
        return 'Hide Element';
    }

    /**
     * URL friendly slug of the action.
     *
     * @return string
     */
    public static function slug()
    {
        return 'hide_element';
    }
}
