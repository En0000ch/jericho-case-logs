<?php

namespace Breakdance\BreakdanceOxygen;

/**
 * to disable the presets functionality in oxygen, we do the following:
 * 1. make the stylesheet tag blank, which prevents output on the frontend
 * 2. prevent output in the builder by making the JS handlePresetsChanged
 *    function do nothing if the builderMode is oxygen
 * 3. we also disable the associated UI elements, which is handled elsewhere
 */

/** @psalm-suppress UndefinedConstant */
if (BREAKDANCE_MODE == 'oxygen') {
    add_filter(
        '__breakdance_presets_css_stylesheet_tag',
        function () {
            return '';
        }
    );
}
