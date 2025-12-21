<?php

namespace Breakdance\Interactions\Actions;

use Breakdance\Interactions\InteractionAction;
use function Breakdance\Elements\control;

class ScrollTo extends InteractionAction
{
    /**
     * Returns the displayable label of the action.
     *
     * @return string
     */
    public static function name()
    {
        return 'Scroll To';
    }

    /**
     * URL friendly slug of the action.
     *
     * @return string
     */
    public static function slug()
    {
        return 'scroll_to';
    }

    /**
     * Get controls for the builder
     * @return Control[]
     */
    public static function controls()
    {
        return [
            control('scroll_offset', 'Scroll Offset', [
                'type' => 'number'
            ]),
            control('scroll_delay', 'Scroll Delay', [
                'type' => 'unit',
                'unitOptions' => [
                    'types' => ['ms', 's']
                ],
            ]),
        ];
    }
}
