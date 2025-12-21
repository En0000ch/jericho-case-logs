<?php

namespace Breakdance\Interactions\Triggers;

use Breakdance\Interactions\InteractionTrigger;

class TabChange extends InteractionTrigger
{
    /**
     * Returns the displayable label of the trigger.
     *
     * @return string
     */
    public static function name()
    {
        return 'Tab Change';
    }

    /**
     * URL friendly slug of the trigger.
     *
     * @return string
     */
    public static function slug()
    {
        return 'tab_change';
    }

    /**
     * Specifies elements for which this trigger is available.
     *
     * @return string[]
     */
    public function availableFor()
    {
        // Return an array of elements this trigger is applicable to
        // Example: return ['\EssentialElements\FormBuilder'];
        return ['\EssentialElements\Tabs', '\EssentialElements\AdvancedTabs'];
    }
}
