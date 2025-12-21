<?php

namespace AnalyticsWP\Lib;

use AnalyticsWP\Lib\Integrations\BeaverBuilderIntegration;
use AnalyticsWP\Lib\Integrations\BricksIntegration;
use AnalyticsWP\Lib\Integrations\ContactForm7Integration;
use AnalyticsWP\Lib\Integrations\DiviIntegration;
use AnalyticsWP\Lib\Integrations\EDDIntegration;
use AnalyticsWP\Lib\Integrations\ElementorIntegration;
use AnalyticsWP\Lib\Integrations\EverestFormsIntegration;
use AnalyticsWP\Lib\Integrations\FluentFormsIntegration;
use AnalyticsWP\Lib\Integrations\FormidableFormsIntegration;
use AnalyticsWP\Lib\Integrations\ForminatorIntegration;
use AnalyticsWP\Lib\Integrations\GravityFormsIntegration;
use AnalyticsWP\Lib\Integrations\NinjaFormsIntegration;
use AnalyticsWP\Lib\Integrations\SureCartIntegration;
use AnalyticsWP\Lib\Integrations\WooCommerceIntegration;
use AnalyticsWP\Lib\Integrations\WPFormsIntegration;

class Integrations
{
    public static function all_integration_classes()
    {
        return [
            WooCommerceIntegration::class,
            EDDIntegration::class,
            ElementorIntegration::class,
            SureCartIntegration::class,
            BricksIntegration::class,
            DiviIntegration::class,
            BeaverBuilderIntegration::class,
            GravityFormsIntegration::class,
            ContactForm7Integration::class,
            WPFormsIntegration::class,
            FluentFormsIntegration::class,
            ForminatorIntegration::class,
            FormidableFormsIntegration::class,
            NinjaFormsIntegration::class,
            EverestFormsIntegration::class,
        ];
    }

    public static function init(): void
    {
        foreach (self::all_integration_classes() as $integration_class) {
            $integration_class::add_hooks();
        }
    }

    public static function get_integrations()
    {
        // for each of the integration_classes, call class::get_integration_description()

        $integrations = [];

        foreach (self::all_integration_classes() as $integration_class) {
            $description = $integration_class::get_integration_description();
            $description['isEnabled'] = self::is_integration_enabled($description['slug']);
            $integrations[] = $description;
        }

        return $integrations;
    }

    public static function update_integration_is_enabled($slug, $isEnabled)
    {
        $disabled_integrations = self::array_of_disabled_integration_slugs();

        if ($isEnabled) {
            $key = array_search($slug, $disabled_integrations);
            if ($key !== false) {
                unset($disabled_integrations[$key]);
            }
        } else {
            $disabled_integrations[] = $slug;
        }

        update_option('analyticswp_disabled_integrations', $disabled_integrations);

        // Return All Integrations
        return self::get_integrations();
    }

    public static function is_integration_enabled($slug)
    {
        return !in_array($slug, self::array_of_disabled_integration_slugs());
    }

    public static function array_of_disabled_integration_slugs()
    {
        return get_option('analyticswp_disabled_integrations', []);
    }
}
