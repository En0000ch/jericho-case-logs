<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;

class NinjaFormsIntegration implements IntegrationInterface
{
    const SLUG = 'ninja-forms';

    public static function is_available(): bool
    {
        return class_exists('Ninja_Forms');
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Ninja Forms',
            'description' => 'Track form submissions in Ninja Forms',
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

        add_action('ninja_forms_after_submission', [self::class, 'my_ninja_forms_after_submission']);
    }

    public static function my_ninja_forms_after_submission($form_data)
    {
        try {
            $form_id = $form_data['form_id'];
            $sub_id = (int)$form_data['actions']['save']['sub_id'];
            $fields_by_key = $form_data['fields_by_key'];

            $submitted_fields = [];

            foreach ($fields_by_key as $name => $data) {
                $value = $data['value'];
                $submitted_fields['field-' . $name] = $value;
                // Do stuff.
            }

            $data_to_send = array_merge([
                'conversion_type' => 'ninja_forms',
                'conversion_id' => $sub_id,
                'form_id' => $form_id,
                'form_type' => 'ninja_forms',
            ], $submitted_fields);

            Event::track_server_event('ninja_form_submission', [
                $data_to_send
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $form_data;
        }
        // Do stuff.
    }
}
