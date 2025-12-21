<?php

/**
 * AnalyticsWP - Contact Form 7 Integration
 * 
 * This integration tracks Contact Form 7 form submissions in AnalyticsWP and provides
 * visibility into the user journey that led to each form submission.
 * 
 * Features:
 * - Automatically tracks all CF7 form submissions
 * - Adds AnalyticsWP metadata box to CF7 form editor
 * - Adds form-specific settings to enable/disable tracking per form
 * - Includes form ID and title in tracked events for detailed analytics
 * 
 * Setup:
 * 1. Ensure both Contact Form 7 and AnalyticsWP are installed and activated
 * 2. This integration will automatically begin tracking all form submissions
 * 3. Configure per-form tracking settings in the CF7 form editor
 */

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\SuperSimpleWP;

class ContactForm7Integration implements IntegrationInterface
{
    const SLUG = 'contact-form-7';

    public static function is_available(): bool
    {
        return class_exists('WPCF7');
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Contact Form 7',
            'description' => 'Track CF7 form submissions in AnalyticsWP',
            'category' => 'Forms',
        ];
    }

    public static function add_hooks(): void
    {
        // Check if Contact Form 7 is available and if the integration is enabled
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        // The hooks for tracking will be added here
        add_action('wpcf7_before_send_mail', [self::class, 'track_form_submission']);
        add_action('wpcf7_editor_panels', [self::class, 'add_analytics_panel']);
        add_action('wpcf7_save_contact_form', [self::class, 'save_analytics_settings']);
    }

    /**
     * Track form submission in AnalyticsWP
     * 
     * @param \WPCF7_ContactForm $contact_form The submitted form object
     * @return array{error: string}|int|null The tracking result
     */
    public static function track_form_submission($contact_form)
    {
        // Get form details
        $form_id = $contact_form->id();
        $form_title = $contact_form->title();

        // Check if tracking is enabled for this form
        $tracking_enabled = get_post_meta($form_id, '_analyticswp_tracking_enabled', true);
        if ($tracking_enabled === 'no') {
            return null;
        }

        // Generate unique identifier for this submission
        $unique_id = 'cf7_submission_' . $form_id . '_' . time();

        // Track the event
        $event =  Event::track_server_event('contact_form_7_submission', [
            'form_id' => $form_id,
            'form_title' => $form_title,
            'unique_event_identifier' => $unique_id
        ]);


        return $event;
    }

    /**
     * Add AnalyticsWP settings panel to CF7 form editor
     * 
     * @param array<string, array{title: string, callback: callable}> $panels Existing panels
     * @return array<string, array{title: string, callback: callable}> Modified panels
     */
    public static function add_analytics_panel($panels)
    {
        $panels['analyticswp-settings'] = [
            'title' => 'AnalyticsWP Settings',
            'callback' => [self::class, 'render_analytics_panel']
        ];
        return $panels;
    }

    /**
     * Render the AnalyticsWP settings panel content
     * 
     * @param \WPCF7_ContactForm $contact_form The form being edited
     * @return void
     */
    public static function render_analytics_panel($contact_form)
    {
        $form_id = $contact_form->id();
        $tracking_enabled = get_post_meta($form_id, '_analyticswp_tracking_enabled', true);
        if ($tracking_enabled === '') {
            $tracking_enabled = 'yes'; // Enable by default
        }
?>
        <h2>AnalyticsWP Tracking Settings</h2>
        <fieldset>
            <legend>Event Tracking</legend>
            <label>
                <input type="checkbox" name="analyticswp_tracking_enabled" value="yes"
                    <?php checked($tracking_enabled, 'yes'); ?>>
                Enable submission tracking for this form
            </label>
        </fieldset>
<?php
    }

    /**
     * Save AnalyticsWP settings when form is saved
     * 
     * @param \WPCF7_ContactForm $contact_form The form being saved
     * @return void
     */
    public static function save_analytics_settings($contact_form)
    {
        $form_id = $contact_form->id();
        $tracking_enabled = isset($_POST['analyticswp_tracking_enabled']) ? 'yes' : 'no';
        update_post_meta($form_id, '_analyticswp_tracking_enabled', $tracking_enabled);
    }
}
