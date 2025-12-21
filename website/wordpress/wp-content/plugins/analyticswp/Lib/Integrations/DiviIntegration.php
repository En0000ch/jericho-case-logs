<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\SuperSimpleWP;

/**
 * Responsibilities
 * [ ] Add tracking capabilities to Divi modules
 * [ ] Track form submissions
 * [ ] Track button clicks
 * [ ] Track CTA interactions
 */
class DiviIntegration implements IntegrationInterface
{
    const SLUG = 'divi';

    /**
     * Supported Divi modules for tracking
     */
    private static $supported_modules = [
        'et_pb_button',
        'et_pb_contact_form',
        'et_pb_cta'
    ];

    public static function is_available(): bool
    {
        return class_exists('ET_Builder_Plugin') || self::is_divi_theme_active();
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Divi',
            'description' => 'Track button clicks, form submissions, and CTA interactions in Divi',
            'category' => 'Site Builder',
        ];
    }

    public static function add_hooks(): void
    {
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        // Add tracking options to modules
        add_filter('et_builder_get_parent_modules', [self::class, 'add_tracking_options']);

        // Add tracking to module output
        add_filter('do_shortcode_tag', [self::class, 'add_tracking'], 9999, 4);
    }

    /**
     * Check if Divi theme is active
     */
    private static function is_divi_theme_active(): bool
    {
        try {
            $theme = wp_get_theme();
            return 'Divi' === $theme->name || 'Divi' === $theme->parent_theme;
        } catch (\Throwable $th) {
            error_log($th);
            return false;
        }
    }

    /**
     * Add tracking options to Divi modules
     *
     * @param array $modules
     * @return array
     */
    public static function add_tracking_options(array $modules): array
    {
        static $is_applied = false;

        if ($is_applied || empty($modules)) {
            return $modules;
        }

        foreach ($modules as $module_slug => $module) {
            if (!in_array($module_slug, self::$supported_modules) || !isset($module->fields_unprocessed)) {
                continue;
            }

            $fields_list = $module->fields_unprocessed;

            // Determine the appropriate toggle section for each module
            switch ($module_slug) {
                case 'et_pb_button':
                    $toggle_slug = 'link';
                    break;
                case 'et_pb_contact_form':
                    $toggle_slug = 'main_content';
                    break;
                default:
                    $toggle_slug = 'link_options';
                    break;
            }

            // Add tracking fields
            $fields_list['analyticswp_tracking'] = [
                'label' => 'Enable AnalyticsWP Tracking',
                'type' => 'yes_no_button',
                'options' => [
                    'off' => 'No',
                    'on' => 'Yes'
                ],
                'affects' => [
                    'analyticswp_event_name',
                    'analyticswp_event_properties'
                ],
                'toggle_slug' => $toggle_slug
            ];

            $fields_list['analyticswp_event_name'] = [
                'label' => 'Event Name',
                'type' => 'text',
                'toggle_slug' => $toggle_slug,
                'depends_show_if' => 'on',
                'depends_on' => [
                    'analyticswp_tracking'
                ],
                'description' => 'Custom event name (e.g., button_click, form_submit)'
            ];

            $fields_list['analyticswp_event_properties'] = [
                'label' => 'Event Properties (JSON)',
                'type' => 'text',
                'toggle_slug' => $toggle_slug,
                'depends_show_if' => 'on',
                'depends_on' => [
                    'analyticswp_tracking'
                ],
                'description' => 'Additional properties in JSON format'
            ];

            $modules[$module_slug]->fields_unprocessed = $fields_list;
        }

        $is_applied = true;
        return $modules;
    }

    /**
     * Add tracking to module output
     *
     * @param string $output
     * @param string $tag
     * @param array $attr
     * @param array $m
     * @return string
     */
    public static function add_tracking(string $output, string $tag, array $attr, array $m): string
    {
        if (
            !in_array($tag, self::$supported_modules) ||
            empty($attr['analyticswp_tracking']) ||
            $attr['analyticswp_tracking'] !== 'on'
        ) {
            return $output;
        }

        // Parse custom properties
        $event_properties = [];
        if (!empty($attr['analyticswp_event_properties'])) {
            try {
                $custom_properties = json_decode($attr['analyticswp_event_properties'], true);
                if (is_array($custom_properties)) {
                    $event_properties = $custom_properties;
                }
            } catch (\Exception $e) {
                error_log('AnalyticsWP: Error parsing event properties JSON - ' . $e->getMessage());
            }
        }

        switch ($tag) {
            case 'et_pb_contact_form':
                return self::add_form_tracking($output, $attr, $event_properties);

            case 'et_pb_button':
                return self::add_button_tracking($output, $attr, $event_properties);

            case 'et_pb_cta':
                return self::add_cta_tracking($output, $attr, $event_properties);

            default:
                return $output;
        }
    }

    /**
     * Add tracking to contact form
     *
     * @param string $output
     * @param array $attr
     * @param array $event_properties
     * @return string
     */
    private static function add_form_tracking(string $output, array $attr, array $event_properties): string
    {
        $event_name = !empty($attr['analyticswp_event_name']) ? $attr['analyticswp_event_name'] : 'form_submission';
        $unique_event = 'analyticswp_form_' . ($attr['_unique_id'] ?? uniqid());

        // Add form-specific properties
        $properties = array_merge($event_properties, [
            'form_id' => $attr['_unique_id'] ?? '',
            'title' => $attr['title'] ?? '',
            'module_type' => 'contact_form'
        ]);

        // Add tracking script
        $script = sprintf(
            '<script>document.addEventListener("%s", function() { AnalyticsWP.event("%s", %s); });</script>',
            esc_js($unique_event),
            esc_js($event_name),
            json_encode($properties)
        );

        // Add event trigger to submit button
        $output = str_replace(
            '<button type="submit"',
            sprintf('<button type="submit" onclick="document.dispatchEvent(new CustomEvent(\'%s\'));"', esc_js($unique_event)),
            $output
        );

        return $output . $script;
    }

    /**
     * Add tracking to button
     *
     * @param string $output
     * @param array $attr
     * @param array $event_properties
     * @return string
     */
    private static function add_button_tracking(string $output, array $attr, array $event_properties): string
    {
        $event_name = !empty($attr['analyticswp_event_name']) ? $attr['analyticswp_event_name'] : 'button_click';

        // Add button-specific properties
        $properties = array_merge($event_properties, [
            'button_url' => $attr['button_url'] ?? '',
            'button_text' => $attr['button_text'] ?? '',
            'module_type' => 'button'
        ]);

        // Add tracking script
        $script = sprintf(
            '<script>
                document.querySelector("a.et_pb_button[href=\'%s\']").addEventListener("click", function() {
                    AnalyticsWP.event("%s", %s);
                });
            </script>',
            esc_js($attr['button_url'] ?? ''),
            esc_js($event_name),
            json_encode($properties)
        );

        return $output . $script;
    }

    /**
     * Add tracking to CTA
     *
     * @param string $output
     * @param array $attr
     * @param array $event_properties
     * @return string
     */
    private static function add_cta_tracking(string $output, array $attr, array $event_properties): string
    {
        $event_name = !empty($attr['analyticswp_event_name']) ? $attr['analyticswp_event_name'] : 'cta_click';

        // Add CTA-specific properties
        $properties = array_merge($event_properties, [
            'button_url' => $attr['button_url'] ?? '',
            'button_text' => $attr['button_text'] ?? '',
            'title' => $attr['title'] ?? '',
            'module_type' => 'cta'
        ]);

        // Add tracking script
        $script = sprintf(
            '<script>
                document.querySelector("a.et_pb_promo_button[href=\'%s\']").addEventListener("click", function() {
                    AnalyticsWP.event("%s", %s);
                });
            </script>',
            esc_js($attr['button_url'] ?? ''),
            esc_js($event_name),
            json_encode($properties)
        );

        return $output . $script;
    }
}
