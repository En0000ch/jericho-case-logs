<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\SuperSimpleWP;

/**
 * Responsibilities
 * [ ] Track form submissions
 * [x] Track button clicks
 * [x] Track text link interactions
 */
class BricksIntegration implements IntegrationInterface
{
    const SLUG = 'bricks-builder';

    public static function is_available(): bool
    {
        $theme = wp_get_theme();
        return ('Bricks' === $theme->name || 'Bricks' === $theme->parent_theme);
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Bricks Builder',
            'description' => 'Track button clicks, form submissions, and CTA interactions in Bricks Builder',
            'category' => 'Site Builder',
        ];
    }

    public static function add_hooks(): void
    {
        // Check if Bricks is available and enabled
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        // Add controls to elements
        add_filter('bricks/elements/form/control_groups', [self::class, 'add_tracking_groups']);
        add_filter('bricks/elements/form/controls', [self::class, 'add_tracking_controls']);
        add_filter('bricks/elements/button/control_groups', [self::class, 'add_tracking_groups']);
        add_filter('bricks/elements/button/controls', [self::class, 'add_tracking_controls']);
        add_filter('bricks/elements/text-link/control_groups', [self::class, 'add_tracking_groups']);
        add_filter('bricks/elements/text-link/controls', [self::class, 'add_tracking_controls']);

        // Add tracking functionality
        add_action('bricks/frontend/before_render_data', [self::class, 'add_tracking'], 10, 2);
        add_filter('bricks/element/render_attributes', [self::class, 'add_tracking_attributes'], 10, 3);

        // Handle form submissions
        // add_action('bricks/form/submit', [self::class, 'track_form_submission'], 10, 3);
    }

    /**
     * Add tracking control groups to Bricks elements
     *
     * @param array $control_groups
     * @return array
     */
    public static function add_tracking_groups(array $control_groups): array
    {
        $control_groups['analyticswp'] = [
            'tab' => 'content',
            'title' => esc_html__('AnalyticsWP Tracking', 'analyticswp'),
        ];

        return $control_groups;
    }

    /**
     * Add tracking controls to Bricks elements
     *
     * @param array $controls
     * @return array
     */
    public static function add_tracking_controls(array $controls): array
    {
        $controls['enable_tracking'] = [
            'tab' => 'content',
            'group' => 'analyticswp',
            'label' => esc_html__('Enable Tracking', 'analyticswp'),
            'type' => 'checkbox',
            'default' => true,
            'inline' => false,
        ];

        $controls['event_name'] = [
            'tab' => 'content',
            'group' => 'analyticswp',
            'label' => esc_html__('Event Name', 'analyticswp'),
            'type' => 'text',
            'placeholder' => esc_html__('e.g., button_click, form_submit', 'analyticswp'),
        ];

        $controls['event_properties'] = [
            'tab' => 'content',
            'group' => 'analyticswp',
            'label' => esc_html__('Event Properties (JSON)', 'analyticswp'),
            'type' => 'textarea',
            'placeholder' => '{"property": "value"}',
        ];

        return $controls;
    }

    /**
     * Add tracking to elements before render
     *
     * @param array $elements
     * @param string $area
     * @return void
     */
    public static function add_tracking(array $elements, string $area): void
    {
        try {
            if (empty($elements)) {
                return;
            }

            foreach ($elements as $element) {
                if (empty($element['settings']['enable_tracking'])) {
                    continue;
                }

                $event_name = !empty($element['settings']['event_name'])
                    ? $element['settings']['event_name']
                    : 'bricks_interaction';

                $event_properties = [];
                if (!empty($element['settings']['event_properties'])) {
                    try {
                        $custom_properties = json_decode($element['settings']['event_properties'], true);
                        if (is_array($custom_properties)) {
                            $event_properties = array_merge($event_properties, $custom_properties);
                        }
                    } catch (\Exception $e) {
                        error_log('AnalyticsWP: Error parsing event properties JSON - ' . $e->getMessage());
                    }
                }

                // Add element-specific properties
                $event_properties['element_type'] = $element['name'];
                $event_properties['element_id'] = $element['id'];

                switch ($element['name']) {
                    case 'form':
                        self::setup_form_tracking($element, $event_name, $event_properties);
                        break;

                    case 'button':
                        self::setup_button_tracking($element, $event_name, $event_properties);
                        break;

                    case 'text-link':
                        self::setup_link_tracking($element, $event_name, $event_properties);
                        break;
                }
            }
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Setup button tracking
     *
     * @param array $element
     * @param string $event_name
     * @param array $properties
     * @return void
     */
    private static function setup_button_tracking(array $element, string $event_name, array $properties): void
    {
        try {
            // Add button-specific properties
            $properties['button_text'] = $element['settings']['text'] ?? '';
            $properties['button_url'] = self::get_element_url($element);

            // Add tracking script
            $script = "
            document.querySelector('[data-analyticswp=\"brxe-{$element['id']}\"]')
                .addEventListener('click', function(e) {
                    AnalyticsWP.event('{$event_name}', " . json_encode($properties) . ");
                });
        ";

            wp_add_inline_script('analyticswp', $script);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Setup text link tracking
     *
     * @param array $element
     * @param string $event_name
     * @param array $properties
     * @return void
     */
    private static function setup_link_tracking(array $element, string $event_name, array $properties): void
    {
        try {
            // Add link-specific properties
            $properties['link_text'] = $element['settings']['text'] ?? '';
            $properties['link_url'] = self::get_element_url($element);

            // Add tracking script
            $script = "
            document.querySelector('[data-analyticswp=\"brxe-{$element['id']}\"]')
                .addEventListener('click', function(e) {
                    AnalyticsWP.event('{$event_name}', " . json_encode($properties) . ");
                });
        ";

            wp_add_inline_script('analyticswp', $script);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Add tracking attributes to elements
     *
     * @param array $attributes
     * @param string $key
     * @param object $element
     * @return array
     */
    public static function add_tracking_attributes(array $attributes, string $key, object $element): array
    {
        try {
            if (!empty($element->settings['enable_tracking'])) {
                $attributes['_root']['data-analyticswp'] = $element->get_element_attribute_id();
            }
            return $attributes;
        } catch (\Throwable $th) {
            error_log($th);
        }
    }


    /**
     * Setup form tracking
     *
     * @param array $element
     * @param string $event_name
     * @param array $properties
     * @return void
     */
    private static function setup_form_tracking(array $element, string $event_name, array $properties): void
    {
        try {
            // Add form-specific properties
            $properties['form_name'] = $element['settings']['submissionFormName'] ?? '';
            $properties['form_id'] = $element['id'];

            // Add tracking script
            $script = "
            document.addEventListener('bricks/form/submit', function(event) {
                if (event.detail.elementId === '{$element['id']}') {
                    // Get all form fields
                    const form = document.querySelector('#brxe-{$element['id']}');
                    const formData = new FormData(form);
                    const fields = {};
                    
                    // Convert FormData to object, excluding any file inputs
                    formData.forEach((value, key) => {
                        if (!(value instanceof File)) {
                            fields[key] = value;
                        }
                    });

                    // Merge form fields with existing properties
                    const eventProperties = " . json_encode($properties) . ";
                    eventProperties.fields = fields;
                    
                    // Track the event
                    AnalyticsWP.event('{$event_name}', eventProperties);
                }
            });
        ";

            wp_add_inline_script('analyticswp', $script);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Get URL from element settings
     *
     * @param array $element
     * @return string
     */
    private static function get_element_url(array $element): string
    {
        try {
            if (!empty($element['settings']['link']['url'])) {
                return $element['settings']['link']['url'];
            }

            if (
                !empty($element['settings']['link']['type'])
                && $element['settings']['link']['type'] === 'internal'
                && !empty($element['settings']['link']['postId'])
            ) {
                return get_permalink($element['settings']['link']['postId']);
            }

            return '';
        } catch (\Throwable $th) {
            error_log($th);
            return '';
        }
    }
}
