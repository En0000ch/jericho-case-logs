<?php

namespace Breakdance\Interactions\Actions;

use Breakdance\Interactions\InteractionAction;
use function Breakdance\Elements\control;

class ControlSlider extends InteractionAction
{
    /**
     * Returns the displayable label of the action.
     *
     * @return string
     */
    public static function name()
    {
        return 'Control Slider';
    }

    /**
     * URL friendly slug of the action.
     *
     * @return string
     */
    public static function slug()
    {
        return 'control_slider';
    }

    /**
     * Get controls for the builder
     * @return Control[]
     */
    public static function controls()
    {
        return [
            control('slider_action', 'Action', [
                'type' => 'dropdown',
                'layout' => 'vertical',
                'items' => [
                    [ 'text' => 'Next Slide', 'value' => 'next' ],
                    [ 'text' => 'Previous Slide', 'value' => 'prev' ],
                    [ 'text' => 'Go to Slide', 'value' => 'goto']
                ],
            ]),
        ];
    }
}
