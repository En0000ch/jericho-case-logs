<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Integrations;

/**
 * Responsibilities
 * [ ] Add tracking capabilities to BB modules
 * [ ] Track form submissions
 * [ ] Track button clicks
 * [ ] Track subscribe form interactions
 */
class BeaverBuilderIntegration implements IntegrationInterface
{

    const SLUG = 'beaver-builder';

    public static function is_available(): bool
    {
        return class_exists('FLBuilder');
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Beaver Builder',
            'description' => 'Track button clicks, form submissions, and CTA interactions in Beaver Builder',
            'category' => 'Site Builder',
        ];
    }

    public static function add_hooks(): void
    {
        // Check if woocommerce is active on this site
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        // Add settings to modules
        add_filter('fl_builder_register_settings_form', [self::class, 'add_tracking_settings'], 10, 2);

        // Add tracking to module output
        add_filter('fl_builder_module_attributes', [self::class, 'add_module_tracking'], 10, 2);
    }

    /**
     * Add tracking settings to BB modules
     *
     * @param array $form The form settings array
     * @param string $id The module ID
     * @return array Modified form settings
     */
    public static function add_tracking_settings(array $form, string $id): array
    {
        try {
            // Only add tracking to specific modules
            if (!in_array($id, ['button', 'contact-form', 'subscribe-form', 'buttons_form'])) {
                return $form;
            }

            $tracking_fields = [
                'enable_tracking' => [
                    'type' => 'select',
                    'label' => 'Enable AnalyticsWP Tracking',
                    'default' => 'no',
                    'options' => [
                        'yes' => 'Yes',
                        'no' => 'No'
                    ],
                    'toggle' => [
                        'yes' => [
                            'fields' => ['event_name', 'event_properties']
                        ]
                    ]
                ],
                'event_name' => [
                    'type' => 'text',
                    'label' => 'Event Name',
                    'default' => '',
                    'placeholder' => 'e.g., button_click, form_submit'
                ],
                'event_properties' => [
                    'type' => 'textarea',
                    'label' => 'Event Properties (JSON)',
                    'default' => '',
                    'placeholder' => '{"property": "value"}',
                    'description' => 'Add additional properties in JSON format'
                ]
            ];

            // Add settings to different module types
            if ($id === 'buttons_form') {
                // For button groups, add as a new tab
                $form['tabs']['analyticswp'] = [
                    'title' => 'AnalyticsWP Tracking',
                    'sections' => [
                        'general' => [
                            'fields' => $tracking_fields
                        ]
                    ]
                ];
            } else {
                // For single modules, add as a new section
                $form['analyticswp'] = [
                    'title' => 'AnalyticsWP Tracking',
                    'sections' => [
                        'general' => [
                            'fields' => $tracking_fields
                        ]
                    ]
                ];
            }

            return $form;
        } catch (\Throwable $th) {
            error_log($th);
            return $form;
        }
    }

    /**
     * Add tracking to module output
     *
     * @param array $attributes The module attributes
     * @param object $module The module instance
     * @return array Modified attributes
     */
    public static function add_module_tracking(array $attributes, object $module): array
    {
        try {
            $settings = $module->settings;
            $type = $module->slug;

            if (empty($settings->enable_tracking) || $settings->enable_tracking !== 'yes') {
                return $attributes;
            }

            $event_name = !empty($settings->event_name) ? $settings->event_name : self::get_default_event_name($type);
            $event_properties = self::parse_event_properties($settings->event_properties ?? '');

            // Add module-specific properties
            $event_properties['module_type'] = $type;
            $event_properties['module_id'] = $module->node;

            switch ($type) {
                case 'contact-form':
                    self::setup_contact_form_tracking($module, $event_name, $event_properties);
                    break;

                case 'subscribe-form':
                    self::setup_subscribe_form_tracking($module, $event_name, $event_properties);
                    break;

                case 'button':
                    self::setup_button_tracking($module, $event_name, $event_properties);
                    break;

                case 'button-group':
                    self::setup_button_group_tracking($module, $event_name, $event_properties);
                    break;
            }

            return $attributes;
        } catch (\Throwable $th) {
            error_log($th);
            return $attributes;
        }
    }

    /**
     * Get default event name based on module type
     *
     * @param string $module_type
     * @return string
     */
    private static function get_default_event_name(string $module_type): string
    {
        $defaults = [
            'contact-form' => 'form_submission',
            'subscribe-form' => 'newsletter_signup',
            'button' => 'button_click',
            'button-group' => 'button_click'
        ];

        return $defaults[$module_type] ?? 'interaction';
    }

    /**
     * Parse JSON event properties
     *
     * @param string $json_properties
     * @return array
     */
    private static function parse_event_properties(string $json_properties): array
    {
        if (empty($json_properties)) {
            return [];
        }

        try {
            $properties = json_decode($json_properties, true);
            return is_array($properties) ? $properties : [];
        } catch (\Exception $e) {
            error_log('AnalyticsWP: Error parsing event properties JSON - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Setup contact form tracking
     *
     * @param object $module
     * @param string $event_name
     * @param array $properties
     * @return void
     */
    private static function setup_contact_form_tracking($module, string $event_name, array $properties): void
    {
        try {
            $properties['form_name'] = $module->name ?? '';
            $properties['form_id'] = $module->node;

            // Add tracking script
            $script = "
            jQuery('.fl-node-{$module->node} .fl-contact-form').on('submit', function(e) {
                AnalyticsWP.event('" . esc_js($event_name) . "', " . json_encode($properties) . ");
            });
        ";

            wp_add_inline_script('analyticswp', $script);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Setup subscribe form tracking
     *
     * @param object $module
     * @param string $event_name
     * @param array $properties
     * @return void
     */
    private static function setup_subscribe_form_tracking($module, string $event_name, array $properties): void
    {
        try {
            $properties['form_name'] = $module->name ?? '';
            $properties['form_id'] = $module->node;

            // Add tracking script
            $script = "
            jQuery('.fl-node-{$module->node} .fl-subscribe-form').on('submit', function(e) {
                AnalyticsWP.event('" . esc_js($event_name) . "', " . json_encode($properties) . ");
            });
        ";

            wp_add_inline_script('analyticswp', $script);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Setup button tracking
     *
     * @param object $module
     * @param string $event_name
     * @param array $properties
     * @return void
     */
    private static function setup_button_tracking($module, string $event_name, array $properties): void
    {
        try {
            $settings = $module->settings;

            $properties['button_text'] = $settings->text ?? '';
            $properties['button_link'] = $settings->link ?? '';

            // Add tracking script
            $script = "
            jQuery('.fl-node-{$module->node} a.fl-button').on('click', function(e) {
                AnalyticsWP.event('" . esc_js($event_name) . "', " . json_encode($properties) . ");
            });
        ";

            wp_add_inline_script('analyticswp', $script);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Setup button group tracking
     *
     * @param object $module
     * @param string $event_name
     * @param array $properties
     * @return void
     */
    private static function setup_button_group_tracking($module, string $event_name, array $properties): void
    {
        try {
            $settings = $module->settings;

            if (!empty($settings->items)) {
                foreach ($settings->items as $index => $button) {
                    $button_properties = $properties;
                    $button_properties['button_text'] = $button->text ?? '';
                    $button_properties['button_link'] = $button->link ?? '';
                    $button_properties['button_index'] = $index;

                    // Add tracking script for each button
                    $script = "
                    jQuery('.fl-button-group-button-{$module->node}-{$index} a').on('click', function(e) {
                        AnalyticsWP.event('" . esc_js($event_name) . "', " . json_encode($button_properties) . ");
                    });
                ";

                    wp_add_inline_script('analyticswp', $script);
                }
            }
        } catch (\Throwable $th) {
            error_log($th);
        }
    }
}
