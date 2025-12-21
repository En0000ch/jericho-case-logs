<?php

namespace Breakdance\BreakdanceOxygen;

add_filter(
    'breakdance_default_design_library_providers_json_url',
    /**
     * @param string $url
     */
    function ($url) {
        if (BREAKDANCE_MODE === 'oxygen') {
            return 'https://oxygenbuilder.com/wp-content/uploads/breakdance/design_sets/design_library_providers.json';
        }

        return $url;
    },
    1000000
);
