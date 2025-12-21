<?php

namespace Breakdance\Interactions\Actions;

use Breakdance\Interactions\InteractionAction;
use function Breakdance\Elements\control;

class ControlPopup extends InteractionAction
{
    /**
     * Returns the displayable label of the action.
     *
     * @return string
     */
    public static function name()
    {
        return 'Control Popup';
    }

    /**
     * URL friendly slug of the action.
     *
     * @return string
     */
    public static function slug()
    {
        return 'control_popup';
    }

    /**
     * Get controls for the builder
     * @return Control[]
     */
    public static function controls()
    {
        return [
            control('popup', 'Popup', [
                'type' => 'text',
                'layout' => 'vertical',
            ]),
            control('popup_action', 'Popup Action', [
                'type' => 'button_bar',
                'layout' => 'vertical',
                'items' => [
                    ['value' => 'open', 'text' => 'Open'],
                    ['value' => 'close', 'text' => 'Close'],
                    ['value' => 'toggle', 'text' => 'Toggle']
                ]
            ])
        ];
    }
}
