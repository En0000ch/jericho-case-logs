<?php

namespace Breakdance\Interactions\Actions;

use Breakdance\Interactions\InteractionAction;

class ShowElement extends InteractionAction
{
    /**
     * Returns the displayable label of the action.
     *
     * @return string
     */
    public static function name()
    {
        return 'Show Element';
    }

    /**
     * URL friendly slug of the action.
     *
     * @return string
     */
    public static function slug()
    {
        return 'show_element';
    }
}
