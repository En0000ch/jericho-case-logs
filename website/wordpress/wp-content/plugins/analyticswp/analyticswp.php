<?php
require_once __DIR__ . '/vendor/autoload.php';

define('ANALYTICSWP_VERSION', '2.2.0');

use AnalyticsWP\Lib\APIServer;
use AnalyticsWP\Lib\Core;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\Integrations\BeaverBuilderIntegration;
use AnalyticsWP\Lib\Integrations\BricksIntegration;
use AnalyticsWP\Lib\Integrations\DiviIntegration;
use AnalyticsWP\Lib\Integrations\EDDIntegration;
use AnalyticsWP\Lib\Integrations\ElementorIntegration;
use AnalyticsWP\Lib\Integrations\GravityFormsIntegration;
use AnalyticsWP\Lib\Integrations\SureCartIntegration;
use AnalyticsWP\Lib\Integrations\ContactForm7Integration;
use AnalyticsWP\Lib\Integrations\EverestFormsIntegration;
use AnalyticsWP\Lib\Integrations\FluentFormsIntegration;
use AnalyticsWP\Lib\Integrations\FormidableFormsIntegration;
use AnalyticsWP\Lib\Integrations\ForminatorIntegration;
use AnalyticsWP\Lib\Integrations\NinjaFormsIntegration;
use AnalyticsWP\Lib\License;
use AnalyticsWP\Lib\Settings;
use AnalyticsWP\Lib\SuperSimpleWP;
use AnalyticsWP\Lib\Views;
use AnalyticsWP\Lib\Integrations\WooCommerceIntegration;
use AnalyticsWP\Lib\Integrations\WPFormsIntegration;
use AnalyticsWP\Lib\WordPressIntegration;

/**
 * Plugin Name: AnalyticsWP
 * Plugin URI: https://www.analyticswp.com/
 * Description: AnalyticsWP. Analytics for WordPress.
 * Version: 2.2.0
 * Author: Solid Plugins
 * Author URI: https://www.analyticswp.com/
 * License: GPL2
 */
class AnalyticsWP_Plugin
{
	public static function init(): void
	{
		SuperSimpleWP::register_plugin(
			array(
				'slug' => 'analyticswp',
				'name' => 'AnalyticsWP',
				'menu_pages' => Views::menu_pages(),
				'settings' => Settings::settings_description(),
				'on_plugins_loaded' => function () {
					Core::init();
					APIServer::init();
					Integrations::init();
					License::init(__FILE__);

					add_action('admin_init', function () {
						Views::maybe_redirect_to_analyticswp_welcome_screen();
					});

					// Sending of the email reports. Should probably move this somewhere else.
					if (wp_next_scheduled('analyticswp_trigger_email_reports') === false) {
						$next_hour = strtotime(date('Y-m-d H:00:00', strtotime('+1 hour')));
						wp_schedule_event($next_hour, 'hourly', 'analyticswp_trigger_email_reports');
					}
					// Hook our updated trigger function into the scheduled event.
					add_action('analyticswp_trigger_email_reports', ['AnalyticsWP\Lib\AgencyMode', 'trigger_scheduled_email_reports']);
				}
			)
		);
	}
}

register_activation_hook(__FILE__, function () {
	set_transient('analyticswp_redirect_to_welcome_page', true, 30);
});

register_deactivation_hook(__FILE__, function () {
	$timestamp = wp_next_scheduled('analyticswp_trigger_email_reports');
	if ($timestamp !== false) {
		wp_unschedule_event($timestamp, 'analyticswp_trigger_email_reports');
	}
});

AnalyticsWP_Plugin::init();
