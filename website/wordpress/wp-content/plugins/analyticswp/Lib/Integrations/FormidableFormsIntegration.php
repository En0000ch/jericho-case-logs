<?php

/**
 * AnalyticsWP - Formidable Forms Integration
 * 
 * This integration adds AnalyticsWP tracking capabilities to Formidable Forms:
 * - Automatically tracks all form submissions
 * - Records form submissions as conversions in AnalyticsWP
 * - Adds journey view links to the Formidable Forms entry detail page
 * - Includes form-specific data in tracking events
 * 
 * Usage:
 * 1. Install and activate both AnalyticsWP and Formidable Forms
 * 2. The integration will automatically track all form submissions
 * 3. View submission analytics in your AnalyticsWP dashboard
 * 4. Click "View Journey" links in your form entty view to see the user's path
 */

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\URLs;

class FormidableFormsIntegration implements IntegrationInterface
{
    const SLUG = 'formidable-forms';

    public static function is_available(): bool
    {
        return class_exists('FrmForm');
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Formidable Forms',
            'description' => 'Automatically track Formidable Forms submissions and view user journeys associated with form entries.',
            'category' => 'Forms',
        ];
    }

    /**
     * Initialize the integration
     * Sets up all WordPress hooks for form tracking
     */
    public static function add_hooks(): void
    {
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        // Track form submissions - both AJAX and regular
        add_filter('frm_success_filter', [self::class, 'track_submission'], 10, 2);

        // Add journey link to entry detail page
        add_action('frm_after_show_entry', [self::class, 'add_journey_link_to_entry']);
    }

    /**
     * Track successful form submissions
     *
     * @param array $actions Form success actions
     * @param object $form Form object
     * @return array Unmodified actions
     */
    public static function track_submission($actions, $form)
    {
        if (!isset($_POST) || empty($form)) {
            return $actions;
        }

        try {
            // Get the entry ID from the form submission
            $entry_id = \FrmEntry::get_id_by_key($_POST['item_key']);
            if (!$entry_id) {
                return $actions;
            }

            // Track the conversion
            Event::track_server_event('form_submission', [
                'conversion_type' => 'formidable_forms',
                'conversion_id' => $entry_id,
                'form_id' => $form->id,
                'form_title' => $form->name,
                'form_type' => 'formidable'
            ]);
        } catch (\Throwable $th) {
            error_log($th);
        }

        return $actions;
    }

    /**
     * Add journey link to entry detail page
     *
     * @param object $entry Form entry
     * @return void
     */
    public static function add_journey_link_to_entry($entry)
    {
        $journey_url = URLs::admin_journey_path_for_event_where_condition([
            'conversion_type' => 'formidable_forms',
            'conversion_id' => $entry->id
        ]);

        if ($journey_url) {
            echo '<div class="analyticswp-journey-link" style="margin: 20px 0;">';
            echo '<h3>' . esc_html__('AnalyticsWP Journey', 'analyticswp') . '</h3>';
            echo sprintf(
                '<a href="%s" class="button button-primary">%s</a>',
                esc_url($journey_url),
                esc_html__('View User Journey', 'analyticswp')
            );
            echo '</div>';
        }
    }
}
