<?php

namespace Breakdance\Interactions\Triggers;

use Breakdance\Interactions\InteractionTrigger;

class ScrollIntoView extends InteractionTrigger
{
    /**
     * Returns the displayable label of the trigger.
     *
     * @return string
     */
    public static function name()
    {
        return 'Scroll Into View';
    }

    /**
     * URL friendly slug of the trigger.
     *
     * @return string
     */
    public static function slug()
    {
        return 'scroll_into_view';
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
