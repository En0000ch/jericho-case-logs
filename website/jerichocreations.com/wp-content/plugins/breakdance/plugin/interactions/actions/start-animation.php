<?php

namespace Breakdance\Interactions\Actions;

use Breakdance\Interactions\InteractionAction;
use function Breakdance\Elements\control;

class StartAnimation extends InteractionAction
{
    /**
     * Returns the displayable label of the action.
     *
     * @return string
     */
    public static function name()
    {
        return 'Start Animation';
    }

    /**
     * URL friendly slug of the action.
     *
     * @return string
     */
    public static function slug()
    {
        return 'start_animation';
    }

    /**
     * Get controls for the builder
     * @return Control[]
     */
    public static function controls()
    {
        return [
            control('animation', 'Animation', [
                'type' => 'dropdown',
                'layout' => 'vertical',
                'searchable' => true,
                'items' => [
                    [ 'text' => 'Bounce', 'value' => 'bounce' ],
                    [ 'text' => 'Flash', 'value' => 'flash' ],
                    [ 'text' => 'Pulse', 'value' => 'pulse' ],
                    [ 'text' => 'Rubber Band', 'value' => 'rubberBand' ],
                    [ 'text' => 'Shake', 'value' => 'shake' ],
                    [ 'text' => 'Swing', 'value' => 'swing' ],
                    [ 'text' => 'Tada', 'value' => 'tada' ],
                    [ 'text' => 'Wobble', 'value' => 'wobble' ],
                    [ 'text' => 'Jello', 'value' => 'jello' ],
                    [ 'text' => 'Bounce In', 'value' => 'bounceIn' ],
                    [ 'text' => 'Bounce In Down', 'value' => 'bounceInDown' ],
                    [ 'text' => 'Bounce In Left', 'value' => 'bounceInLeft' ],
                    [ 'text' => 'Bounce In Right', 'value' => 'bounceInRight' ],
                    [ 'text' => 'Bounce In Up', 'value' => 'bounceInUp' ],
                    [ 'text' => 'Bounce Out', 'value' => 'bounceOut' ],
                    [ 'text' => 'Bounce Out Down', 'value' => 'bounceOutDown' ],
                    [ 'text' => 'Bounce Out Left', 'value' => 'bounceOutLeft' ],
                    [ 'text' => 'Bounce Out Right', 'value' => 'bounceOutRight' ],
                    [ 'text' => 'Bounce Out Up', 'value' => 'bounceOutUp' ],
                    [ 'text' => 'Fade In', 'value' => 'fadeIn' ],
                    [ 'text' => 'Fade In Down', 'value' => 'fadeInDown' ],
                    [ 'text' => 'Fade In Down Big', 'value' => 'fadeInDownBig' ],
                    [ 'text' => 'Fade In Left', 'value' => 'fadeInLeft' ],
                    [ 'text' => 'Fade In Left Big', 'value' => 'fadeInLeftBig' ],
                    [ 'text' => 'Fade In Right', 'value' => 'fadeInRight' ],
                    [ 'text' => 'Fade In Right Big', 'value' => 'fadeInRightBig' ],
                    [ 'text' => 'Fade In Up', 'value' => 'fadeInUp' ],
                    [ 'text' => 'Fade In Up Big', 'value' => 'fadeInUpBig' ],
                    [ 'text' => 'Fade Out', 'value' => 'fadeOut' ],
                    [ 'text' => 'Fade Out Down', 'value' => 'fadeOutDown' ],
                    [ 'text' => 'Fade Out Down Big', 'value' => 'fadeOutDownBig' ],
                    [ 'text' => 'Fade Out Left', 'value' => 'fadeOutLeft' ],
                    [ 'text' => 'Fade Out Left Big', 'value' => 'fadeOutLeftBig' ],
                    [ 'text' => 'Fade Out Right', 'value' => 'fadeOutRight' ],
                    [ 'text' => 'Fade Out Right Big', 'value' => 'fadeOutRightBig' ],
                    [ 'text' => 'Fade Out Up', 'value' => 'fadeOutUp' ],
                    [ 'text' => 'Fade Out Up Big', 'value' => 'fadeOutUpBig' ],
                    [ 'text' => 'Flip', 'value' => 'flip' ],
                    [ 'text' => 'Flip In X', 'value' => 'flipInX' ],
                    [ 'text' => 'Flip In Y', 'value' => 'flipInY' ],
                    [ 'text' => 'Flip Out X', 'value' => 'flipOutX' ],
                    [ 'text' => 'Flip Out Y', 'value' => 'flipOutY' ],
                    [ 'text' => 'Light Speed In', 'value' => 'lightSpeedIn' ],
                    [ 'text' => 'Light Speed Out', 'value' => 'lightSpeedOut' ],
                    [ 'text' => 'Rotate In', 'value' => 'rotateIn' ],
                    [ 'text' => 'Rotate In Down Left', 'value' => 'rotateInDownLeft' ],
                    [ 'text' => 'Rotate In Down Right', 'value' => 'rotateInDownRight' ],
                    [ 'text' => 'Rotate In Up Left', 'value' => 'rotateInUpLeft' ],
                    [ 'text' => 'Rotate In Up Right', 'value' => 'rotateInUpRight' ],
                ]
            ]),
            control('animation_duration', 'Duration', [
                'type' => 'unit',
                'unitOptions' => [
                    'types' => ['ms', 's']
                ],
            ]),
            control('animation_delay', 'Delay', [
                'type' => 'unit',
                'unitOptions' => [
                    'types' => ['ms', 's']
                ],
            ]),
        ];
    }
}
