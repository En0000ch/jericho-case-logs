<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\SuperSimpleWP;

/**
 * AnalyticsWP Integration for Fluent Forms
 * 
 * This integration allows you to track form submissions and conversions from Fluent Forms.
 * 
 * Features:
 * - Automatically tracks all form submissions
 * - Option to track submissions as conversions
 * - Adds form analytics data to the Fluent Forms entry view
 * - Links form entries back to user journeys
 * - Customizable event names and properties
 * 
 * To use this integration:
 * 1. Navigate to Fluent Forms > Forms
 * 2. Edit a form
 * 3. Go to Settings > AnalyticsWP
 * 4. Enable tracking and configure options
 * 
 * The integration will then automatically track all submissions for the configured forms.
 */
class FluentFormsIntegration implements IntegrationInterface
{
    const SLUG = 'fluent-forms';

    public static function is_available(): bool
    {
        return defined('FLUENTFORM');
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Fluent Forms',
            'description' => 'Track form submissions and conversions with Fluent Forms',
            'category' => 'Forms',
        ];
    }

    /**
     * Initialize the integration by setting up all necessary hooks
     * 
     * @return void
     */
    public static function add_hooks(): void
    {
        // Check if Fluent Forms is active and this integration is enabled
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }


        // Track form submissions
        add_action('fluentform/submission_inserted', [self::class, 'track_form_submission'], 10, 3);
    }


    /**
     * Track form submissions in AnalyticsWP
     * 
     * @param int $entryId Entry ID
     * @param array $formData Form submission data
     * @param \FluentForm\App\Models\Form $form Form object
     * @return void
     */
    public static function track_form_submission($entryId, $formData, $form): void
    {
        try {
            // Prepare event properties
            $properties = [
                'form_id' => $form->id,
                'form_title' => $form->title,
                'entry_id' => $entryId,
                'unique_event_identifier' => 'fluent_forms_submission_' . $entryId
            ];

            // Add form field values
            foreach ($formData as $field_name => $value) {
                if (is_string($value) || is_numeric($value)) {
                    $properties['field_' . sanitize_title($field_name)] = $value;
                }
            }

            // Check if this should be tracked as a conversion
            // confirm that $entryID is an integer and not empty or null or 0
            $isEntryIDValid = is_int($entryId) && !empty($entryId);

            if ($isEntryIDValid) {
                $properties['conversion_type'] = 'fluent_forms';
                $properties['conversion_id'] = $entryId;
            }

            // Get custom event name if set
            $event_name = 'fluent_form_submission';

            // Track the event
            Event::track_server_event($event_name, $properties);
        } catch (\Throwable $th) {
            error_log('AnalyticsWP Fluent Forms Integration Error: ' . $th->getMessage());
        }
    }

    /**
     * Add analytics card to entry details page
     * 
     * @param array $cards Current cards
     * @param array $entry Entry data
     * @return array Modified cards array
     */
    public static function add_analytics_card($cards, $entry): array
    {
        try {
            $entry_id = $entry->id;

            // Get journey URL from AnalyticsWP
            $journey_path = \AnalyticsWP\Lib\URLs::admin_journey_path_for_event_where_condition([
                'conversion_type' => 'fluent_forms',
                'conversion_id' => $entry_id
            ]);

            if ($journey_path) {
                $cards[] = [
                    'title' => 'AnalyticsWP',
                    'content' => sprintf(
                        '<a href="%s" class="button button-secondary">%s</a>',
                        esc_url($journey_path),
                        esc_html__('View User Journey', 'analyticswp')
                    )
                ];
            }
        } catch (\Throwable $th) {
            error_log('AnalyticsWP Fluent Forms Integration Error: ' . $th->getMessage());
        }

        return $cards;
    }
}
