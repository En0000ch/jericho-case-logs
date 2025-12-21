<?php

namespace Breakdance\Interactions\Triggers;

use Breakdance\Interactions\InteractionTrigger;
use function Breakdance\Elements\control;

class KeyUp extends InteractionTrigger
{
    /**
     * Returns the displayable label of the trigger.
     *
     * @return string
     */
    public static function name()
    {
        return 'Key Up';
    }

    /**
     * URL friendly slug of the trigger.
     *
     * @return string
     */
    public static function slug()
    {
        return 'key_up';
    }

    /**
     * Get controls for the builder
     * @return Control[]
     */
    public static function controls()
    {
        return [
            control('key', 'Key', [
                'type' => 'text',
                'layout' => 'vertical',
            ]),
            control('ctrl_key', 'CTRL', [
                'type' => 'toggle',
                'layout' => 'inline',
            ]),
            control('shift_key', 'Shift', [
                'type' => 'toggle',
                'layout' => 'inline',
            ]),
        ];
    }
}
