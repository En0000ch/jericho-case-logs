<?php

namespace Breakdance\Interactions;

abstract class InteractionAction implements \JsonSerializable
{

    /**
     * Get the displayable label of the action.
     * @return string
     */
    abstract public static function name();

    /**
     * Get the URL friendly slug of the action.
     * @return string
     */
    abstract public static function slug();

    /**
     * Get controls for the builder
     * @return Control[]
     */
    public static function controls()
    {
        return [];
    }

    /**
     * Get the JavaScript function name associated with this trigger.
     * @return string
     */
    public static function jsFunctionName()
    {
        return static::slug();
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => static::name(),
            'slug' => static::slug(),
            'jsFunctionName' => static::jsFunctionName(),
            'controls' => static::controls()
        ];
    }
}

abstract class InteractionTrigger implements \JsonSerializable
{

    /**
     * Get the displayable label of the trigger.
     * @return string
     */
    abstract public static function name();

    /**
     * Get the URL friendly slug of the trigger.
     * @return string
     */
    abstract public static function slug();

    /**
     * Get the JavaScript function name associated with this trigger.
     * @return string
     */
    public static function jsFunctionName()
    {
        return static::slug();
    }

    /**
     * Get controls for the builder
     * @return Control[]
     */
    public static function controls()
    {
        return [];
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => static::name(),
            'slug' => static::slug(),
            'controls' => static::controls(),
            'jsFunctionName' => static::jsFunctionName()
        ];
    }
}
