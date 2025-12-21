<?php

namespace AnalyticsWP\Lib;

/**
 * @psalm-import-type SettingsArgs from \AnalyticsWP\Lib\SuperSimpleWP
 */
class Settings
{
    /**
     * @return SettingsArgs
     */
    public static function settings_description()
    {
        $settings = array(
            'disabled-tracking-user-roles' => array(
                'type' => 'wp-user-roles',
                'label' => 'Disable tracking for these user roles.',
                'description' => 'Exclude certain user roles from being tracked. Select the user roles you want to exclude from tracking.',
                'tab' => 'General'
            ),
            'admin-access-user-roles' => array(
                'type' => 'wp-user-roles-without-subscriber',
                'label' => 'Give these user roles access to the AnalyticsWP admin pages.',
                'description' => 'Select the user roles that should have access to the AnalyticsWP admin pages. Only administrators will have access to this settings page.',
                'default' => ['administrator' => '1'],
                'tab' => 'General'
            ),
            'how_did_you_hear' => array(
                'type' => 'radio',
                'label' => 'Add a "How did you hear about us?" question to the checkout page.',
                'description' => 'This will add a field to your WooCommerce checkout form, and track the value in AnalyticsWP.',
                'options' => array_merge(['disable' => 'Disable'], Integrations\WooCommerceIntegration::get_wc_field_positions()),
                'tab' => 'General'
            ),
            'enable_woocommerce_order_notification_source_section' => array(
                'type' => 'checkbox',
                'label' => 'Enable WooCommerce Email Order Notification Source Section',
                'description' => 'This will add a section to the WooCommerce order notification email that shows the source of the order.',
                'default' => '1',
                'tab' => 'General'
            ),
            'agency_mode_landing_page_is_hidden' => array(
                'type' => 'checkbox',
                'label' => 'Hide the Agency Mode explanation page.',
                'description' => 'The Agency Mode explanation page will be hidden from the plugin menu. This will not have any affect on the functionality of the plugin, besides removing this page.',
                'tab' => 'General'
            ),
        );


        if (AgencyMode::is_agency_mode_enabled()) {
            $agency_mode_settings = AgencyMode::agency_mode_specific_settings();
            $settings = array_merge($settings, $agency_mode_settings);
        }

        return $settings;
    }
}
