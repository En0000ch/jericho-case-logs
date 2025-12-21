<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\SuperSimpleWP;
use AnalyticsWP\Lib\Validators;

/**
 * AnalyticsWP Integration for WPForms
 * 
 * This integration allows you to track form submissions and conversions from WPForms.
 * 
 * Features:
 * - Track form submissions as events or conversions
 * - Add custom properties to tracked events
 * - View form submission journeys in WPForms admin
 * - Configure tracking per form
 * 
 * Setup Instructions:
 * 1. Install and activate both WPForms and AnalyticsWP
 * 2. Edit any form in WPForms
 * 3. Go to Settings > AnalyticsWP Tracking
 * 4. Enable tracking and configure options
 * 5. Save the form
 */
class WPFormsIntegration implements IntegrationInterface
{

    const SLUG = 'wpforms';

    public static function is_available(): bool
    {
        return class_exists('WPForms\WPForms');
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'WPForms',
            'description' => 'Track form submissions and conversions from WPForms',
            'category' => 'Forms',
        ];
    }

    public static function add_hooks(): void
    {
        // Check if woocommerce is active on this site
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        // Add settings to form builder
        add_filter('wpforms_builder_settings_sections', [self::class, 'builder_settings_sections'], 10, 2);
        add_filter('wpforms_form_settings_panel_content', [self::class, 'admin_panel'], 10, 2);

        // Track form submissions
        add_action('wpforms_process_complete', [self::class, 'track_form_submission'], 10, 4);

        // Add journey link to entries list
        add_filter('wpforms_entry_table_columns', [self::class, 'add_journey_column']);
        add_filter('wpforms_entry_table_column_value', [self::class, 'populate_journey_column'], 10, 4);

        // Add journey metabox to entry details
        add_action('wpforms_entry_details_content', [self::class, 'add_journey_metabox'], 20, 3);
    }

    /**
     * Add AnalyticsWP section to form settings
     */
    public static function builder_settings_sections($sections, $form_data): array
    {
        $sections['analyticswp'] = esc_html__('AnalyticsWP Tracking', 'analyticswp');
        return $sections;
    }

    /**
     * Render the AnalyticsWP settings panel content
     */
    public static function admin_panel($panel)
    {
        echo '<div class="wpforms-panel-content-section wpforms-panel-content-section-analyticswp" data-panel="analyticswp">';

        echo '<div class="wpforms-panel-content-section-title">';
        esc_html_e('AnalyticsWP Tracking', 'analyticswp');
        echo '</div>';

        wpforms_panel_field(
            'checkbox',
            'settings',
            'analyticswp_enable_tracking',
            $panel->form_data,
            esc_html__('Enable analytics tracking', 'analyticswp')
        );

        wpforms_panel_field(
            'checkbox',
            'settings',
            'analyticswp_is_conversion',
            $panel->form_data,
            esc_html__('Track as conversion. (Requires paid version of WPForms and entry storage enabled.)', 'analyticswp')
        );

        wpforms_panel_field(
            'text',
            'settings',
            'analyticswp_event_name',
            $panel->form_data,
            esc_html__('Event Name', 'analyticswp'),
            [
                'default' => 'form_submission'
            ]
        );

        echo '</div>';
    }

    /**
     * Track form submission in AnalyticsWP
     */
    public static function track_form_submission($fields, $entry, $form_data, $entry_id): void
    {
        try {
            // Check if tracking is enabled for this form
            if (empty($form_data['settings']['analyticswp_enable_tracking'])) {
                return;
            }

            // Prepare event properties
            $properties = [
                'form_id' => $form_data['id'],
                'form_title' => $form_data['settings']['form_title'],
                'entry_id' => $entry_id,
                'unique_event_identifier' => "wpforms_submission_{$entry_id}"
            ];

            // Add form field values as properties
            foreach ($fields as $field) {
                if (!empty($field['name']) && isset($field['value'])) {
                    $field_key = sanitize_title($field['name']);
                    $properties["field_{$field_key}"] = $field['value'];
                }
            }

            // Check if this should be tracked as a conversion
            // check to see if $entry_id is a valid number, and not zero

            $is_entry_id_valid = is_numeric($entry_id) && $entry_id > 0;

            if (!empty($form_data['settings']['analyticswp_is_conversion']) && $is_entry_id_valid) {
                $properties['conversion_type'] = 'wpforms';
                $properties['conversion_id'] = (string)$entry_id;
            }

            // Get custom event name or use default
            $event_name = !empty($form_data['settings']['analyticswp_event_name'])
                ? $form_data['settings']['analyticswp_event_name']
                : 'form_submission';

            // Track the event
            Event::track_server_event($event_name, $properties);
        } catch (\Throwable $th) {
            error_log('AnalyticsWP WPForms Integration Error: ' . $th->getMessage());
        }
    }

    /**
     * Add journey column to entries table
     */
    public static function add_journey_column($columns): array
    {
        $columns['analyticswp_journey'] = 'AnalyticsWP Journey';
        return $columns;
    }

    /**
     * Populate journey column with link
     */
    public static function populate_journey_column($value, $entry, $column_id, $form_data): string
    {
        if ($column_id !== 'analyticswp_journey') {
            return $value;
        }

        $entry_id = Validators::int(is_object($entry) ? $entry->entry_id : $entry['entry_id']);

        $journey_path = \AnalyticsWP\Lib\URLs::admin_journey_path_for_event_where_condition([
            'conversion_type' => 'wpforms',
            'conversion_id' => $entry_id
        ]);

        if (!$journey_path) {
            return 'No journey found';
        }

        return sprintf(
            '<a href="%s" class="button button-secondary">View Journey</a>',
            esc_url($journey_path)
        );
    }

    /**
     * Add journey metabox to entry details page
     */
    public static function add_journey_metabox($entry, $form_data, $meta): void
    {
        $entry_id = Validators::int(is_object($entry) ? $entry->entry_id : $entry['entry_id']);

        $journey_path = \AnalyticsWP\Lib\URLs::admin_journey_path_for_event_where_condition([
            'conversion_type' => 'wpforms',
            'conversion_id' => $entry_id
        ]);

        if (!$journey_path) {
            return;
        }

?>
        <div class="postbox">
            <h2 class="hndle">AnalyticsWP Journey</h2>
            <div class="inside">
                <a href="<?php echo esc_url($journey_path); ?>" class="button button-secondary">
                    View User Journey
                </a>
            </div>
        </div>
<?php
    }
}
