<?php



/**
 * Plugin Name: Breakdance
 * Plugin URI: https://breakdance.com/
 * Description: The Breakdance website builder for WordPress makes it easy to create incredible websites with 100+ elements, mega menu builder, form builder, WooCommerce, dynamic data, and much more.
 * Author: Breakdance
 * Version: 2.5.2
 * Author URI: https://breakdance.com/
 */

define('BREAKDANCE_MODE', 'breakdance');

/*
to end users: editing BREAKDANCE_MODE on your own will result in site breakage 
and potential for data loss that can't be reversed without a backup. 
there's a lot of oxygen and breakdance-specific code that is loaded by our
build system simply setting this to oxygen on a breakdance install won't magically
turn your breakdance install into an oxygen install or vice versa
*/

if (!defined('BREAKDANCE_MODE')) {
    die('For development, please define BREAKDANCE_MODE in wp-config.php, i.e. define("BREAKDANCE_MODE", "oxygen") or define("BREAKDANCE_MODE", "breakdance")');
}

if (
    function_exists('\Breakdance\MigrationMode\isBreakdanceEnabledForRequest')
    &&
    !\Breakdance\MigrationMode\isBreakdanceEnabledForRequest()
) {
    return;
}

const __BREAKDANCE_PLUGIN_FILE__ = __FILE__;
const __BREAKDANCE_DIR__ = __DIR__;
const __BREAKDANCE_MIN_PHP_VERSION__ = '7.4';
const __BREAKDANCE_VERSION = '2.5.2';
// const __BREAKDANCE_BETA_EXPIRATION = 'September 20 2022'; // comment this out for no expiration
const __BREAKDANCE_CLEAR_CSS_CACHE_FLAG__ = 15;
const __BREAKDANCE_AI_SUPPORTED__ = true;

if (!version_compare(PHP_VERSION, __BREAKDANCE_MIN_PHP_VERSION__, '>=')) {
    add_action('admin_notices', 'breakdance_add_unsupported_php_version_admin_notice');

    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    deactivate_plugins(__BREAKDANCE_PLUGIN_FILE__);
    // TODO: Add WP version check
} else if (defined('__BREAKDANCE_BETA_EXPIRATION') && time() > strtotime(__BREAKDANCE_BETA_EXPIRATION)) {

    add_action('admin_notices', 'breakdance_add_beta_expiration_admin_notice');
} else {

        require_once __DIR__ . '/subplugins/breakdance-elements/plugin.php'; // This line was added programmatically by build tool
        
    require_once __DIR__ . '/subplugins/breakdance-woocommerce/plugin.php'; // This line was added programmatically by build tool
        


    require_once __DIR__ . '/plugin/base.php';

    add_action('plugins_loaded', function () {
        do_action('before_breakdance_loaded');
        bdox_run_action('breakdance_loaded');
    });
}

function breakdance_add_unsupported_php_version_admin_notice()
{
    $message_text = sprintf(
        \Breakdance\BreakdanceOxygen\Strings\__bdox('plugin_name') . ' requires PHP of version >= %s and is unable to run with the version installed on this server (%s). Please switch to a modern web host.',
        __BREAKDANCE_MIN_PHP_VERSION__,
        PHP_VERSION
    );
    echo sprintf('<div class="error"><p>%s</p></div>', $message_text);
}

function breakdance_add_beta_expiration_admin_notice()
{
    $domain = BREAKDANCE_MODE === 'oxygen' ? 'oxygenbuilder.com' : 'breakdance.com';
    echo '<div class="error"><p>This ' . \Breakdance\BreakdanceOxygen\Strings\__bdox('plugin_name') . ' pre-release version is expired. Please get the latest version at <a href="https://' . $domain . '/">' . $domain . '</a>.</p></div>';
}
