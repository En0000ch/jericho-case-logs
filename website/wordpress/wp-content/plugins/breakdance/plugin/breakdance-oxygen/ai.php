<?php

namespace Breakdance\BreakdanceOxygen;

add_filter(
    'breakdance_ai_enabled',

    /**
     * @param boolean $aiEnabled
     */
    function ($aiEnabled) {
        if (BREAKDANCE_MODE === 'oxygen') {
            return false;
        }

        return $aiEnabled;
    },
    1000000
);
