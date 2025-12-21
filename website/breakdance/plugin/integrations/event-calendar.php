<?php

namespace Breakdance\Integrations\EventCalendar;

add_action('tribe_plugins_loaded', function() {
    /**
     * Class Service_Provider
     */
    class Service_Provider extends \TEC\Common\Contracts\Service_Provider
    {
        /**
         * Registers the bindings and hooks the filters required for the Breakdance integrations to work.
         *
         * @since 5.14.5
         */
        public function register()
        {
            add_filter('tribe_events_views_v2_assets_should_enqueue_frontend', [$this, 'should_enqueue_frontend'], 10, 2);
        }

        /**
         * Checks if we should enqueue frontend assets on Breakdance.
         *
         * @return bool Whether or not to enqueue assets.
         * @since 5.14.5
         *
         */
        public function should_enqueue_frontend()
        {
            // Bail if views v2 isn't active
            if (!tribe_events_views_v2_is_enabled()) {
                return false;
            }

            return true;
        }
    }

    /**
     * @psalm-suppress UndefinedFunction
     * @psalm-suppress MissingDependency
     */
    tribe_register_provider( Service_Provider::class );
});
