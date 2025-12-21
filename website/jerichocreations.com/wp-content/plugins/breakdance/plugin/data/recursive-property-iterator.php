<?php

namespace Breakdance\Data;

use function Breakdance\Config\Breakpoints\get_breakpoints;

class RecursivePropertyIterator extends \RecursiveArrayIterator
{
    public function hasChildren(): bool
    {
        /** @var mixed $property */
        $property = $this->current();
        if (!is_array($property)) {
            return false;
        }

        // If this is an array with number, style, unit keys we don't need to descend
        if (isset($property['number'], $property['style'], $property['unit'])) {
            return false;
        }

        /** @psalm-suppress MixedArgumentTypeCoercion */
        if (str_ends_with($this->key(), '_dynamic_meta')) {
            return false;
        }

        return true;
    }
}
