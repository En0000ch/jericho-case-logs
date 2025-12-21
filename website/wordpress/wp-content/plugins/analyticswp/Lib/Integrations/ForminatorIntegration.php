<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\SuperSimpleWP;

/**
 * AnalyticsWP Integration for Forminator Forms
 * 
 * This integration automatically tracks form submissions in Forminator as events in AnalyticsWP.
 * Each form submission is tracked as a conversion event with additional metadata about the form.
 * 
 * Features:
 * - Automatic tracking of all Forminator form submissions
 * - Tracks both regular forms and quiz submissions
 * - Adds AnalyticsWP journey links to the Forminator submissions page
 * - Includes form metadata with each tracked event
 * 
 * Usage:
 * 1. Install and activate both AnalyticsWP and Forminator
 * 2. All form submissions will be automatically tracked
 * 3. View submission journeys in the Forminator entries list
 */
class ForminatorIntegration implements IntegrationInterface
{
    const SLUG = 'forminator';

    public static function is_available(): bool
    {
        return class_exists('Forminator');
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Forminator',
            'description' => 'Track Forminator form submissions as events in AnalyticsWP',
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
        // Check if forminator is active and integration is enabled
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        // Track form submissions
        add_action('forminator_form_submit_response', [self::class, 'track_form_submission'], 10, 2);
        add_action('forminator_form_ajax_submit_response', [self::class, 'track_form_submission'], 10, 2);
    }

    /**
     * Track a form submission in AnalyticsWP
     * 
     * @param array $response The form entry data
     * @param int $form_id The ID of the submitted form
     * @return void
     */
    public static function track_form_submission($response, $form_id): void
    {
        try {
            $entry = \Forminator_Form_Entry_Model::get_latest_entry_by_form_id($form_id);
            if (!($entry instanceof \Forminator_Form_Entry_Model)) {
                return;
            }

            // Get the form model
            $form_model = \Forminator_Form_Model::model()->load($form_id);
            if (!$form_model) {
                return;
            }

            // Prepare event properties
            $properties = [
                'form_id' => $form_id,
                'form_name' => $form_model->name,
                'entry_id' => $entry->entry_id,
                'conversion_type' => 'forminator_form',
                'conversion_id' => (int)$entry->entry_id
            ];

            // Add form field values
            foreach ($entry->meta_data as $field_key => $field_value) {
                if (is_string($field_value['value']) || is_numeric($field_value['value'])) {
                    $properties['field_' . sanitize_title($field_key)] = $field_value['value'];
                }
            }

            // Track the conversion event
            Event::track_server_event('form_submission', $properties);
        } catch (\Throwable $th) {
            error_log('AnalyticsWP Forminator Integration Error: ' . $th->getMessage());
        }
    }
}
