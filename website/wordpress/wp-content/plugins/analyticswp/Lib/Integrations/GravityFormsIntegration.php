<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\SuperSimpleWP;

/**
 * Responsibilities
 * [ ] Add tracking capabilities to Gravity Forms
 * [ ] Track form submissions
 * [ ] Track conversions with unique IDs
 * [ ] Journeys don't work unless it's a conversion: https://www.loom.com/share/855b6ec099d746f587038b21a8a2cc85
 */
class GravityFormsIntegration implements IntegrationInterface
{
    const SLUG = 'gravity-forms';

    public static function is_available(): bool
    {
        return class_exists('GFForms');
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Gravity Forms',
            'description' => 'Track form submissions and conversions within Gravity Forms',
            'category' => 'Forms',
        ];
    }

    public static function add_hooks(): void
    {
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }


        // Add settings to form editor
        add_filter('gform_form_settings_fields', [self::class, 'add_form_settings'], 10, 2);

        // Add tracking to form submission
        add_filter('gform_submit_button', [self::class, 'add_submit_button_tracking'], 10, 2);

        // Track successful form submissions
        add_action('gform_after_submission', [self::class, 'track_form_submission'], 10, 2);

        add_filter('gform_entry_detail_meta_boxes', [self::class, 'add_analytics_metabox'], 10, 3);

        add_action('gform_entries_first_column_actions', function ($form_id, $field_id, $value, $entry) {
            $entry_id = $entry['id'];
            // Get the journey URL from AnalyticsWP using the unique identifier
            $journey_path = \AnalyticsWP\Lib\URLs::admin_journey_path_for_event_where_condition(
                [
                    'conversion_type' => 'gravity_forms',
                    'conversion_id' => $entry_id
                ]
            );
            echo "| <a href='{$journey_path}'>View AnalyticsWP Journey</a>";
        }, 10, 4);
    }

    /**
     * Add analytics tracking settings to Gravity Forms form settings
     *
     * @param array $fields
     * @param array $form
     * @return array
     */
    public static function add_form_settings($fields, $form): array
    {
        $fields['analyticswp_settings'] = [
            'title' => 'AnalyticsWP Tracking',
            'fields' => [
                [
                    'label' => 'Enable Analytics Tracking',
                    'type' => 'toggle',
                    'name' => 'analyticswp_enable_tracking',
                    'choices' => [
                        [
                            'label' => 'Enable tracking for this form',
                            'name' => 'analyticswp_enable_tracking',
                            'value' => '1'
                        ]
                    ]
                ],
                [
                    'label' => 'Track as Conversion. If you enable this option, the form submission will count as a conversion within AnalyticsWP, with the conversion ID set to the form entry ID.',
                    'type' => 'toggle',
                    'name' => 'analyticswp_is_conversion',
                    'choices' => [
                        [
                            'label' => 'Track this form submission as a conversion',
                            'name' => 'analyticswp_is_conversion',
                            'value' => '1'
                        ]
                    ],
                    'dependency' => [
                        'live' => true,
                        'fields' => [
                            [
                                'field' => 'analyticswp_enable_tracking',
                            ]
                        ]
                    ]
                ],
                [
                    'label' => 'Event Name',
                    'type' => 'text',
                    'name' => 'analyticswp_event_name',
                    'description' => 'Custom event name for this form (default: form_submission)',
                    'dependency' => [
                        'live' => true,
                        'fields' => [
                            [
                                'field' => 'analyticswp_enable_tracking',
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $fields;
    }

    /**
     * Add tracking code to form submit button
     *
     * @param string $button_html
     * @param array $form
     * @return string
     */
    public static function add_submit_button_tracking($button_html, $form): string
    {
        try {
            // Check if tracking is enabled for this form
            if (empty($form['analyticswp_enable_tracking'])) {
                return $button_html;
            }

            // Parse button HTML
            $dom = new \DOMDocument();
            $dom->loadHTML($button_html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $input = $dom->getElementsByTagName('input')->item(0);

            if (!$input) {
                return $button_html;
            }

            // Add tracking to onclick
            $onclick = $input->getAttribute('onclick');
            $onclick .= sprintf(
                "if (jQuery('#gform_%d')[0].checkValidity()) { 
                AnalyticsWP.event('form_submission_started', {
                    form_id: '%d',
                    form_title: '%s'
                }); 
            }",
                $form['id'],
                $form['id'],
                esc_js($form['title'])
            );

            $input->setAttribute('onclick', $onclick);

            return $dom->saveHTML();
        } catch (\Throwable $th) {
            error_log($th);
            return $button_html;
        }
    }

    /**
     * Track successful form submission
     *
     * @param array $entry
     * @param array $form
     * @return void
     */
    public static function track_form_submission($entry, $form): void
    {
        try {
            // Check if tracking is enabled
            if (empty($form['analyticswp_enable_tracking'])) {
                return;
            }

            // Prepare event properties
            $properties = [
                'form_id' => $form['id'],
                'form_title' => $form['title'],
                'entry_id' => $entry['id']
            ];

            // Add form field values
            foreach ($form['fields'] as $field) {
                if (!empty($field['label']) && isset($entry[$field['id']])) {
                    // Sanitize field name for use as property key
                    $field_key = sanitize_title($field['label']);
                    $properties['field_' . $field_key] = $entry[$field['id']];
                }
            }

            // Check if this should be tracked as a conversion
            if (!empty($form['analyticswp_is_conversion'])) {
                $properties['conversion_type'] = 'gravity_forms';
                $properties['conversion_id'] = (int)$entry['id'];
            }

            // Get event name (use custom or default)
            $event_name = !empty($form['analyticswp_event_name'])
                ? $form['analyticswp_event_name']
                : 'form_submission';

            // Track the event
            Event::track_server_event($event_name, $properties);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Add AnalyticsWP metabox to entry detail page
     *
     * @param array $meta_boxes The properties for the meta boxes
     * @param array $entry The entry currently being viewed/edited
     * @param array $form The form object used to process the current entry
     * @return array
     */
    public static function add_analytics_metabox($meta_boxes, $entry, $form): array
    {
        try {


            // Only add the metabox if this form has analytics tracking enabled
            if (!empty($form['analyticswp_enable_tracking'])) {
                $meta_boxes['analyticswp'] = [
                    'title' => 'AnalyticsWP',
                    'callback' => [self::class, 'render_analytics_metabox'],
                    'context' => 'side',
                    'callback_args' => [
                        'entry' => $entry,
                        'form' => $form
                    ]
                ];
            }

            return $meta_boxes;
        } catch (\Throwable $th) {
            error_log($th);
            return $meta_boxes;
        }
    }

    /**
     * Render the content of the AnalyticsWP metabox
     *
     * @param array $args Contains 'entry' and 'form' objects
     * @return void
     */
    public static function render_analytics_metabox($args): void
    {

        try {
            $entry = $args['entry'];
            $form = $args['form'];

            // Generate the journey URL (placeholder for now)
            $entry_id = $entry['id'];

            $journey_path = \AnalyticsWP\Lib\URLs::admin_journey_path_for_event_where_condition(
                [
                    'conversion_type' => 'gravity_forms',
                    'conversion_id' => $entry_id
                ]
            );

            if (empty($journey_path)) {
                echo esc_html__('No journey found for this form submission.', 'analyticswp');
                return;
            }

            echo sprintf(
                '<a href="%s" class="button button-secondary">%s</a>',
                esc_url($journey_path),
                esc_html__('View Analytics Journey', 'analyticswp')
            );
        } catch (\Throwable $th) {
            error_log($th);
        }
    }
}
