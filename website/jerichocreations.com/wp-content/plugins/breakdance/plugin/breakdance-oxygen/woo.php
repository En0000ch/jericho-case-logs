<?php

namespace Breakdance\BreakdanceOxygen;

/** @psalm-suppress UndefinedConstant */
if (BREAKDANCE_MODE == 'oxygen') {
    add_filter(
        'breakdance_woo_integration_enabled',
        /**
         * @param boolean $enabled
         */
        function ($enabled) {
            return false;
        },
        1000
    );
}
