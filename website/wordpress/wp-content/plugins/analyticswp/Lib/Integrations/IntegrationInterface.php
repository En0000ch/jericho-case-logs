<?php

namespace AnalyticsWP\Lib\Integrations;

/**
 * @psalm-type IntegrationDescription = array{
 *     slug: string,
 *     isAvailable: bool,
 *     name: string,
 *     description: string,
 *     category: string,
 * }
 */
interface IntegrationInterface
{
    /**
     * Checks if this integration is available.
     *
     * @return bool
     */
    public static function is_available();

    /**
     * Returns the integration description.
     *
     * @return IntegrationDescription
     */
    public static function get_integration_description();

    /**
     * Adds necessary WordPress hooks.
     *
     * @return void
     */
    public static function add_hooks(): void;
}
