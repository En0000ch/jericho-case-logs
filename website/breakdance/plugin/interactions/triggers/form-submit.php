<?php

namespace Breakdance\Interactions\Triggers;

use Breakdance\Interactions\InteractionTrigger;

class FormSubmit extends InteractionTrigger
{
    /**
     * Returns the displayable label of the trigger.
     *
     * @return string
     */
    public static function name()
    {
        return 'Form Submit';
    }

    /**
     * URL friendly slug of the trigger.
     *
     * @return string
     */
    public static function slug()
    {
        return 'form_submit';
    }

    /**
     * Specifies elements for which this trigger is available.
     *
     * @return string[]
     */
    public function availableFor()
    {
        return ['\EssentialElements\FormBuilder'];
    }
}
