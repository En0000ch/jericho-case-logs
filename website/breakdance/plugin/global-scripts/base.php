<?php

namespace Breakdance\GlobalScripts;

use Breakdance\Render\BlockCounterManager;
use Breakdance\Render\ScriptAndStyleHolder;
use function Breakdance\Subscription\getSubscriptionMode;

add_action('breakdance_loaded', '\Breakdance\GlobalScripts\enqueue');

function enqueue()
{
    $breakdance = json_encode([
        'homeUrl' => home_url(),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'elementsPluginUrl' => defined('BREAKDANCE_ELEMENTS_PLUGIN_URL') ? BREAKDANCE_ELEMENTS_PLUGIN_URL : null,
        'BASE_BREAKPOINT_ID' => BASE_BREAKPOINT_ID,
        'breakpoints' => \Breakdance\Config\Breakpoints\get_breakpoints(),
        'subscriptionMode' => getSubscriptionMode()
    ]);

    /**
     * breakdance-utils.js and oxygen-utils.js scripts are exactly the same, except for
     * five lines added to the end of the oxygen-utils.js version to also make all APIs available on
     * window.OxygenFrontend
     */

    $url = BREAKDANCE_PLUGIN_URL . "plugin/global-scripts/breakdance-utils.js";

    $js = <<<JS
    if (!window.BreakdanceFrontend) {
        window.BreakdanceFrontend = {}
    }

    window.BreakdanceFrontend.data = {$breakdance}
JS;

    if (BREAKDANCE_MODE === 'oxygen') {
        $js = <<<JS
        if (!window.BreakdanceFrontend) {
            window.BreakdanceFrontend = {}
        }

        window.BreakdanceFrontend.data = {$breakdance}

        if (!window.OxygenFrontend) {
            window.OxygenFrontend = {}
        }

        window.OxygenFrontend.data = {$breakdance}
JS;

        $url = BREAKDANCE_PLUGIN_URL . "plugin/global-scripts/oxygen-utils.js";
    }

    $dependencies = [
        'scripts' => [$url],
        'inlineScripts' => [
            $js
        ]
    ];

    ScriptAndStyleHolder::getInstance()->append($dependencies);
}

function enqueueAdminbarScript()
{
    if (!\Breakdance\Permissions\hasPermission('full')) {
        return;
    }

    if (!is_admin_bar_showing()) {
        return;
    }

    $url = BREAKDANCE_PLUGIN_URL . "plugin/global-scripts/admin-bar.js";
    $blocksInPage = BlockCounterManager::getInstance()->getBlocks();

    wp_enqueue_script( 'breakdance-admin-bar', $url );
    wp_localize_script( 'breakdance-admin-bar', 'breakdanceAdminbar', [
        'blocksInPage' => $blocksInPage,
    ]);
}
add_action('wp_footer', '\Breakdance\GlobalScripts\enqueueAdminbarScript', 10);
