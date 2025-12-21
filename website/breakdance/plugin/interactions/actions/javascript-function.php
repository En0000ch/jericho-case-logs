<?php

namespace Breakdance\Interactions\Actions;

use Breakdance\Interactions\InteractionAction;
use function Breakdance\Elements\control;

class JavascriptFunction extends InteractionAction
{
    /**
     * Returns the displayable label of the action.
     *
     * @return string
     */
    public static function name()
    {
        return 'JavaScript Function';
    }

    /**
     * URL friendly slug of the action.
     *
     * @return string
     */
    public static function slug()
    {
        return 'javascript_function';
    }

    /**
     * Get controls for the builder
     * @return Control[]
     */
    public static function controls()
    {
        return [
            control('js_function_name', 'JS Function Name', [
                'type' => 'text',
                'layout' => 'vertical',
                'placeholder' => 'fn(maybeEvent, target, options)'
            ])
        ];
    }
}
