<?php

// @psalm-ignore-file

namespace Breakdance\Admin\SettingsPage;

use function Breakdance\BreakdanceOxygen\Strings\__bdox;

add_action('admin_menu', function () {

    if (BREAKDANCE_MODE === 'breakdance') {
        add_submenu_page('breakdance', 'Settings', 'Settings', 'manage_options', __bdox('admin_page_settings_slug'), 'Breakdance\Admin\SettingsPage\display_page');
    }

    if (BREAKDANCE_MODE === 'oxygen') {
        add_submenu_page('oxygen', 'Settings', 'Settings', 'manage_options', __bdox('admin_page_settings_slug'), 'Breakdance\Admin\SettingsPage\display_page');
    }
});

function admin_notice_success()
{
    $is_breakdance_settings = array_key_exists('page', $_GET) ?
        sanitize_text_field((string) $_GET['page']) == __bdox('admin_page_settings_slug') :
        false;

    if (!$is_breakdance_settings || !array_key_exists('submit', $_POST)) {
        return;
    }

?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Settings saved.'); ?></p>
    </div>
<?php
}

add_action('admin_notices', '\Breakdance\Admin\SettingsPage\admin_notice_success');

/**
 * @param string|null $activeTabSlug
 * @return string
 */
function tab_html($activeTabSlug = null)
{

    $tabs = SettingsPageController::getInstance()->tabs;
    usort(
        $tabs,
        /**
         * @param array{name:string,slug:string,order:int} $a 
         * @param array{name:string,slug:string,order:int} $b 
         * @psalm-suppress InvalidScalarArgument
         */
        function ($a, $b) {
            return $a['order'] > $b['order'] ? 1 : -1;
        }
    );

    $admin_page_settings_slug = __bdox('admin_page_settings_slug');

    return '
    <nav class="breakdance-admin__nav">'
        .
        implode(
            array_map(
                function ($tab) use ($activeTabSlug, $admin_page_settings_slug) {

                    $activeClass = $tab['slug'] === $activeTabSlug ? 'is-active' : '';

                    return <<<HTML
                <a href="?page={$admin_page_settings_slug}&tab={$tab['slug']}" class="breakdance-admin__tab {$activeClass}">{$tab['name']}</a>
                HTML;
                },
                $tabs
            )
        )
        . '</nav>';
}

function display_page()
{

    $activeTabSlug = isset($_GET['tab']) ? (string) $_GET['tab'] : 'license';
    /*
    actually verify this is an activeTabSlug using get_tabs...
    make the first tab the default tab instead of hardcoding
     */

?>

    <style>
        .breakdance-admin {
            display: flex;
            flex-direction: row;
        }

        .breakdance-admin__tabs {
            width: 200px;
            flex-shrink: 0;
            padding-right: 32px;
            padding-left: 16px;

        }

        .breakdance-admin__content {
            width: 100%;
        }

        .breakdance-admin__nav {
            display: flex;
            flex-direction: column;
            margin-top: 20px;
            margin-left: -12px;
        }

        .breakdance-admin__title {
            font-weight: 400;
            color: #666666;

        }

        .breakdance-admin__tab {
            display: block;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            color: #666666;
            padding: 8px 12px;

            border-radius: 2px;
            transition: all .3s ease;
            outline: 0;
            border: 0;
            border-left: 3px solid transparent;
        }

        .breakdance-admin__tab.is-active {
            color: #000;
            background-color: rgba(255, 255, 255, 0.5);
            /* height: 100%; */
            /* border-color: #2271b1; */
        }

        .breakdance-admin__tab:hover {
            color: #000;

        }

        .form-table {
            margin-top: 20px;
            max-width: 900px;
        }

        .form-table th,
        .form-table td {
            border: 1px solid #f0f0f1;
            padding: 20px;
            border-radius: 4px;
            background-color: rgba(255, 255, 255, 1);
        }

        table.form-table {
            border-collapse: collapse;
        }

        .valign-th-middle {
            vertical-align: middle !important;
        }

        .padded-notice {
            margin-top: 20px !important;
            margin-bottom: 20px !important;
        }

        .breakdance-admin-highlight-row th,
        .breakdance-admin-highlight-row td {
            background: #fff8e1;
        }
    </style>


    <div class="wrap">
        <section class="breakdance-admin">
            <div class="breakdance-admin__tabs">
                <h3 class="breakdance-admin__title"><?php echo __bdox('plugin_name'); ?></h3>
                <?php echo tab_html($activeTabSlug); ?>
            </div>
            <div class="breakdance-admin__content">
                <?php bdox_run_action('breakdance_admin_settings_page_tabs_' . $activeTabSlug . '_tab'); ?>
            </div>
        </section>
    </div>

<?php
}

?>