<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\URLs;

/**
 * AnalyticsWP Integration for Everest Forms
 * 
 * This integration automatically tracks form submissions in Everest Forms as conversions in AnalyticsWP.
 * Each form submission is tracked with:
 * - Form ID and title
 * - Entry ID as the conversion ID
 * - All form field values
 * - Standard AnalyticsWP tracking data (user info, UTM params, etc)
 * 
 * Features:
 * - Automatic form submission tracking
 * - Support for both AJAX and non-AJAX form submissions
 * - Journey viewer in the Everest Forms entry details screen
 * - Entry columns showing AnalyticsWP journey data
 * 
 * Implementation Details:
 * - Form submissions are tracked as conversions using track_server_event()
 * - Uses entry ID as conversion_id for journey linking
 * - Hooks into both AJAX and regular form submission flows
 */
class EverestFormsIntegration implements IntegrationInterface
{
    const SLUG = 'everest-forms';

    /**
     * Check if the integration is available
     *
     * @return bool
     */
    public static function is_available(): bool
    {
        return class_exists('EverestForms');
    }

    /**
     * Get integration details
     *
     * @return array
     */
    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Everest Forms',
            'description' => 'Track form submissions in Everest Forms',
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
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        // Track successful form submissions - both AJAX and regular
        add_action('everest_forms_after_success_message', [self::class, 'track_regular_form_submission'], 10, 2);
        add_action('everest_forms_frontend_output_after', [self::class, 'setup_ajax_tracking'], 10, 2);
    }

    /**
     * Track a form submission for regular (non-AJAX) forms
     * 
     * @param array $form_data The form configuration data
     * @param array $entry The submitted entry data
     * @return void
     */
    public static function track_regular_form_submission($form_data, $entry): void
    {
        try {
            // Don't track if this is an AJAX form
            if (!empty($form_data['settings']['ajax_form_submission'])) {
                return;
            }

            self::track_submission($form_data, $entry);
        } catch (\Throwable $th) {
            error_log('AnalyticsWP EverestForms Integration Error: ' . $th->getMessage());
        }
    }

    /**
     * Add tracking code for AJAX form submissions
     * 
     * @param array $form_data The form configuration data
     * @param \WP_Post $form The form post object
     * @return void
     */
    public static function setup_ajax_tracking($form_data, $form): void
    {
        try {
            // Only proceed if this is an AJAX form
            if (empty($form_data['settings']['ajax_form_submission'])) {
                return;
            }

            // Get form fields to track
            $fields = [];
            foreach ($form_data['form_fields'] as $field) {
                if (!empty($field['label'])) {
                    $fields[$field['id']] = sanitize_title($field['label']);
                }
            }

            // Add tracking script
?>
            <script>
                document.addEventListener('everest_forms_ajax_submission_success', function(e) {
                    if (e.detail && e.detail.response && e.detail.response.form_id == <?php echo esc_js($form_data['id']); ?>) {
                        AnalyticsWP.event('everest_form_submission', {
                            form_id: '<?php echo esc_js($form_data['id']); ?>',
                            form_title: '<?php echo esc_js($form_data['settings']['form_title']); ?>',
                            conversion_type: 'everest_forms',
                            conversion_id: e.detail.response.entry_id
                        });
                    }
                });
            </script>
<?php
        } catch (\Throwable $th) {
            error_log('AnalyticsWP EverestForms Integration Error: ' . $th->getMessage());
        }
    }

    /**
     * Track a form submission in AnalyticsWP
     * 
     * @param array $form_data The form configuration data
     * @param array $entry The submitted entry data
     * @return void
     */
    private static function track_submission($form_data, $entry): void
    {
        // Prepare event properties
        $properties = [
            'form_id' => $form_data['id'],
            'form_title' => $form_data['settings']['form_title'],
        ];

        // Add form field values
        foreach ($form_data['form_fields'] as $field) {
            if (!empty($field['label']) && isset($entry['form_fields'][$field['id']])) {
                $field_key = 'field_' . sanitize_title($field['label']);
                $properties[$field_key] = $entry['form_fields'][$field['id']];
            }
        }

        // Track the event
        Event::track_server_event('everest_form_submission', $properties);
    }
}
