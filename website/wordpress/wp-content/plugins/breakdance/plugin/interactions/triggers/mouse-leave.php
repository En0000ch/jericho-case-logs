<?php

namespace Breakdance\Interactions\Triggers;

use Breakdance\Interactions\InteractionTrigger;

class MouseLeave extends InteractionTrigger
{
    /**
     * Returns the displayable label of the trigger.
     *
     * @return string
     */
    public static function name()
    {
        return 'Mouse Leave';
    }

    /**
     * URL friendly slug of the trigger.
     *
     * @return string
     */
    public static function slug()
    {
        return 'mouse_leave';
    }

    /**
     * Specifies elements for which this trigger is available.
     *
     * @return string[]
     */
    public function availableFor()
    {
        return [];
    }
}
