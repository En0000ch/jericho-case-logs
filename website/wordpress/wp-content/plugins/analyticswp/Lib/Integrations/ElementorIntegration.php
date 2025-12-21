<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\SuperSimpleWP;
use AnalyticsWP\Lib\Validators;

/**
 * Responsibilities
 * [ ] Add tracking capabilities to Elementor widgets
 * [ ] Track form submissions
 * [ ] Track button clicks
 * [ ] Track CTA interactions
 */
class ElementorIntegration implements IntegrationInterface
{
    const SLUG = 'elementor';

    public static function is_available(): bool
    {
        return did_action('elementor/loaded') > 0;
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Elementor',
            'description' => 'Track button clicks, form submissions, and CTA interactions in Elementor',
            'category' => 'Site Builder',
        ];
    }

    public static function add_hooks(): void
    {
        // Check if Elementor is active and integration is enabled
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        add_action('elementor/element/after_section_end', [self::class, 'add_tracking_controls'], 999, 2);

        // Add controls to widgets
        add_action('elementor/frontend/after_render', [self::class, 'add_widget_tracking']);
        add_action('elementor_pro/forms/new_record', [self::class, 'track_form_submission'], 10, 2);
    }


    /**
     * Add tracking controls to Elementor widgets
     *
     * @param \Elementor\Controls_Stack $element
     * @param string $section_id
     * @return void
     */
    public static function add_tracking_controls($element, $section_id): void
    {
        try {
            // Only add to specific widgets and sections
            if (!in_array($element->get_name(), ['form', 'button', 'call-to-action'])) {
                return;
            }

            $sections_to_hook = [
                'form' => 'section_form_options',
                'button' => 'section_button',
                'call-to-action' => 'section_content'
            ];

            if ($section_id !== $sections_to_hook[$element->get_name()]) {
                return;
            }

            $element->start_controls_section(
                'analyticswp_tracking_section',
                [
                    'label' => 'AnalyticsWP Tracking',
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );

            $element->add_control(
                'enable_tracking',
                [
                    'label' => 'Enable Tracking',
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'default' => '',
                    'label_on' => 'Yes',
                    'label_off' => 'No',
                ]
            );

            $element->add_control(
                'event_name',
                [
                    'label' => 'Event Name',
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => '',
                    'placeholder' => 'e.g., button_click, form_submit',
                    'condition' => [
                        'enable_tracking' => 'yes',
                    ],
                ]
            );

            $element->add_control(
                'event_properties',
                [
                    'label' => 'Event Properties (JSON)',
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'default' => '',
                    'placeholder' => '{"property": "value"}',
                    'condition' => [
                        'enable_tracking' => 'yes',
                    ],
                    'description' => 'Add additional properties in JSON format',
                ]
            );

            $element->end_controls_section();
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Add tracking to widget render
     *
     * @param \Elementor\Widget_Base $widget
     * @return void
     */
    public static function add_widget_tracking($widget): void
    {
        try {
            if (!method_exists($widget, 'get_settings_for_display')) {
                return;
            }

            $settings = $widget->get_settings_for_display();

            if (empty($settings['enable_tracking']) || $settings['enable_tracking'] !== 'yes') {
                return;
            }

            $event_name = !empty($settings['event_name']) ? $settings['event_name'] : 'elementor_interaction';
            $event_properties = [];

            // Parse custom properties if provided
            if (!empty($settings['event_properties'])) {
                try {
                    $custom_properties = json_decode($settings['event_properties'], true);
                    if (is_array($custom_properties)) {
                        $event_properties = array_merge($event_properties, $custom_properties);
                    }
                } catch (\Exception $e) {
                    error_log('AnalyticsWP: Error parsing event properties JSON - ' . $e->getMessage());
                }
            }

            // Add widget-specific properties
            $event_properties['widget_type'] = $widget->get_name();
            $event_properties['widget_id'] = $widget->get_id();
            $event_properties['element_id'] = $widget->get_id();

            switch ($widget->get_name()) {
                case 'button':
                    self::setup_button_tracking($widget, $event_properties);
                    break;

                case 'call-to-action':
                    self::setup_cta_tracking($widget, $event_properties);
                    break;
            }
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Setup button tracking
     *
     * @param \Elementor\Widget_Base $widget
     * @param array $properties
     * @return void
     */
    private static function setup_button_tracking($widget, array $properties): void
    {
        try {
            $settings = $widget->get_settings_for_display();

            // Add button-specific properties
            $properties['button_text'] = $settings['text'] ?? '';
            $properties['button_link'] = $settings['link']['url'] ?? '';

            $event_name = !empty($settings['event_name']) ? $settings['event_name'] : 'button_click';

            // Add tracking script
            $script = "
            jQuery('.elementor-element-{$widget->get_id()} a, .elementor-element-{$widget->get_id()} button').on('click', function(e) {
                AnalyticsWP.event('" . $event_name . "', " . json_encode($properties) . ");
            });
        ";

            add_action('wp_enqueue_scripts', function () use ($script) {
                wp_add_inline_script('analyticswp', $script);
            }, 999);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Setup CTA tracking
     *
     * @param \Elementor\Widget_Base $widget
     * @param array $properties
     * @return void
     */
    private static function setup_cta_tracking($widget, array $properties): void
    {
        try {
            $settings = $widget->get_settings_for_display();

            // Add CTA-specific properties
            $properties['cta_title'] = $settings['title'] ?? '';
            $properties['cta_description'] = $settings['description'] ?? '';
            $properties['cta_button_text'] = $settings['button'] ?? '';

            $event_name = !empty($settings['event_name']) ? $settings['event_name'] : 'cta_click';

            // Add tracking script
            $script = "
            jQuery('.elementor-element-{$widget->get_id()} a').on('click', function(e) {
            AnalyticsWP.event('" . $event_name . "', " . json_encode($properties) . ");
            });
        ";

            add_action('wp_enqueue_scripts', function () use ($script) {
                wp_add_inline_script('analyticswp', $script);
            }, 999);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Track form submissions
     *
     * @param \ElementorPro\Modules\Forms\Submissions\Form_Record $record
     * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
     * @return void
     */
    public static function track_form_submission($record, $ajax_handler): void
    {
        try {
            $form_data = $record->get_formatted_data();
            $form_settings = $record->get('form_settings');

            if (empty($form_settings['enable_tracking']) || $form_settings['enable_tracking'] !== 'yes') {
                return;
            }

            $properties = [
                'form_name' => $form_settings['form_name'] ?? '',
                'form_id' => $record->get_form_settings('id'),
                'fields' => $form_data
            ];

            Event::track_server_event(
                'form_submission',
                array_merge(
                    $properties,
                    json_decode($form_settings['event_properties'] ?? '{}', true) ?? []
                )
            );
        } catch (\Throwable $th) {
            error_log($th);
        }
    }
}
