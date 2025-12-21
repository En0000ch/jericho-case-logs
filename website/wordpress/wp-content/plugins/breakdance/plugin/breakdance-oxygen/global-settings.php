<?php

namespace Breakdance\BreakdanceOxygen;

use function Breakdance\GlobalSettings\OTHER_DEFAULT_CSS;

/**
 * to disable the default breakdance global settings functionality in oxygen, we do the following:
 * 1. disable the default global settings css template
 * 2. disable the default control sections (the UI won't show the "Global Settings" in the ... menu if there are no control sections)
 * 3. make the stylesheet tag blank if the css template is blank, which prevents output on the frontend
 *      - we check that the css template is blank before making the stylesheet tag blank because
 *        add-ons like breakdance-elements-for-oxygen will re-enable global settings functionality
 */

/** @psalm-suppress UndefinedConstant */
if (BREAKDANCE_MODE == 'oxygen') {

    add_filter(
        'breakdance_global_settings_css_template_enable_default_template',
        /**
         * @param boolean $enabled
         */
        function ($enabled) {
            return false;
        },
        1000
    );

    add_filter(
        'breakdance_global_settings_enable_default_control_sections',
        /**
         * @param boolean $enabled
         */
        function ($enabled) {
            return false;
        },
        1000
    );

    add_filter(
        '__breakdance_global_settings_css_stylesheet_tag',
        /**
         * @param string $tag
         * @return string
         */
        function ($tag) {

            $template = \Breakdance\GlobalSettings\globalSettingsCssTemplate();

            if (trim($template)) {
                return $tag;
            }

            return '';
        }
    );
}
