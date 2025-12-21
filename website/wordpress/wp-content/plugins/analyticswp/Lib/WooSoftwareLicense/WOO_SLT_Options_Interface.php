<?php

namespace AnalyticsWP\Lib\WooSoftwareLicense;

use AnalyticsWP\Lib\License;
use AnalyticsWP\Lib\URLs;
use AnalyticsWP\Lib\Validators;

class WOO_SLT_Options_Interface
{

    /** @var WOO_SLT_Licence */
    var $licence;

    /**
     * @param WOO_SLT_Licence|null $woo_slt_licence_instance
     */
    function __construct($woo_slt_licence_instance = null)
    {
        if ($woo_slt_licence_instance === null) {
            $woo_slt_licence_instance = new WOO_SLT_Licence();
        }

        $this->licence = $woo_slt_licence_instance;

        if (isset($_GET['page']) && ($_GET['page'] == 'analyticswp-license-key'  ||  $_GET['page'] == 'analyticswp-license-key-options')) {
            add_action('init', array($this, 'options_update'), 1);
        }

        if (!License::is_analyticswp_activated() && !License::is_on_keyless_free_trial()) {
            add_action('admin_notices', array($this, 'admin_no_key_notices'));
            add_action('network_admin_notices', array($this, 'admin_no_key_notices'));
        }

        // Currently on a free trial notice:
        $deactivated_component = '';
        if (License::is_on_keyless_free_trial() && !License::is_analyticswp_activated_and_not_expired()) {
            $keyless_trial_ends_at = License::get_keyless_free_trial_end_timestamp();
            $expires_in = human_time_diff($keyless_trial_ends_at, time());
            $deactivated_component = "<div class='notice has-action trial' style='width: calc(100% - 20px);'>" . "<div class='content'><div class='lead'><p><strong>You are currently on your free trial of AnalyticsWP</strong></div>" . " <div class='plain'><p>It expires in <b>" . $expires_in . "</b>. <a href='https://analyticswp.com/pricing/' target='_blank'>Purchase and activate</a> this plugin as soon as possible. It will not function once this trial expires.</p></div></div>" . "</br>" . "<div class='actions'>" . "<a class='button small primary' href='" . admin_url('admin.php?page=analyticswp-license-key-options') . "'>" . __("Manage Your License", 'analyticswp') . "</a>" . "</p></div>" . "</div>";
        }

        // Activated but expired:
        if (License::is_analyticswp_activated()) {
            if (License::is_analyticswp_activated_but_expired()) {
                $deactivated_component .= "<div class='notice has-action trial' style='width: calc(100% - 20px);'>"
                    . "<div class='content'>" . "<div class='lead'>" . __("AnalyticsWP is expired.", 'analyticswp') . "</div><div class='plain'>" . __("Please renew AnalyticsWP for the plugin to function correctly. You will not receive any security updates, nor be able to fully use this plugin while your license is expired.", 'analyticswp') . "</div></div>" . "<div class='actions'>" . "<a class='button small primary' href='" . admin_url('admin.php?page=analyticswp-license-key-options') . "'>" . __("Manage your license", 'analyticswp') . "</a>" . "</div>" . "</div>";
            }
        }

        if (!empty($deactivated_component)) {
            add_action('admin_notices', function () use ($deactivated_component) {
                echo $deactivated_component;
            });
            add_action('network_admin_notices', function () use ($deactivated_component) {
                echo $deactivated_component;
            });
        }

        add_action('admin_notices', [self::class, 'admin_notices_static']);

        /** @psalm-suppress InvalidReturnStatement */
        return $deactivated_component;
    }

    function __destruct() {}


    /**
     * @return void
     */
    function options_interface()
    {
        if (!WOO_SLT_Licence::licence_key_verify() && !is_multisite()) {
            $this->licence_form();
            return;
        }

        if (!WOO_SLT_Licence::licence_key_verify() && is_multisite()) {
            $this->licence_multisite_require_nottice();
            return;
        }
    }

    /**
     * @return void
     */
    function options_update()
    {

        if (isset($_POST['analyticswp_slt_licence_form_submit'])) {
            $this->licence_form_submit();
            return;
        }
    }

    /**
     * @return void
     */
    function load_dependencies() {}

    /**
     * @return void
     */
    function admin_notices()
    {
        global $slt_form_submit_messages;

        if ($slt_form_submit_messages == '')
            return;

        $messages = Validators::array_of_string($slt_form_submit_messages);

        if (count($messages) > 0) {
            echo "<div id='notice' class='updated fade'><p>" . implode("</p><p>", $messages)  . "</p></div>";
        }
    }

    /**
     * @return void
     */
    public static function admin_notices_static()
    {
        global $slt_form_submit_messages;

        if ($slt_form_submit_messages == '')
            return;

        $messages = Validators::array_of_string($slt_form_submit_messages);

        if (count($messages) > 0) {
            echo "<div id='notice' class='updated fade'><p>" . implode("</p><p>", $messages)  . "</p></div>";
        }
    }

    /**
     * Hits the API and attempts to activate the license key.
     * Returns true if the activation was successful.
     * Returns an array of error messages if the activation failed.
     *
     * @param string $license_key
     * 
     * @return true|string[]
     */
    public static function handle_activating_license($license_key)
    {
        $license_key = sanitize_key(trim($license_key));
        $error_messages = [];

        if ($license_key == '') {
            /** @psalm-suppress MixedArrayAssignment */
            $error_messages[] = __("Licence Key can't be empty", 'analyticswp');
            return $error_messages;
        }

        //build the request query
        $args = array(
            'woo_sl_action'     => 'activate',
            'licence_key'       => $license_key,
            'product_unique_id' => ANALYTICSWP_WOO_SLT_PRODUCT_ID,
            'domain'            => ANALYTICSWP_WOO_SLT_INSTANCE,
            'cache_bust'        => time()
        );
        $request_uri    = ANALYTICSWP_WOO_SLT_APP_API_URL . '?' . http_build_query($args, '', '&');
        $data           = wp_remote_get($request_uri);

        if (($data instanceof \WP_Error) || $data['response']['code'] != 200) {
            /**
             * @psalm-suppress MixedOperand
             * @psalm-suppress MixedArrayAssignment
             * @psalm-suppress MixedArrayAccess
             */
            $error_messages[] = __('There was a problem connecting to ', 'analyticswp') . ANALYTICSWP_WOO_SLT_APP_API_URL;
            return $error_messages;
        }

        /**
         * @psalm-suppress MixedAssignment
         */
        $response_block = json_decode($data['body']);
        //retrieve the last message within the $response_block
        /**
         * @psalm-suppress MixedArrayAccess
         * @psalm-suppress MixedAssignment
         * @psalm-suppress MixedArgument
         */
        $response_block = $response_block[count($response_block) - 1];

        /**
         * @psalm-suppress MixedPropertyFetch
         */
        if (isset($response_block->status)) {
            if ($response_block->status == 'success' && ($response_block->status_code == 's100' || $response_block->status_code == 's101')) {
                /**
                 * @psalm-suppress MixedArrayAssignment
                 * @psalm-suppress MixedAssignment
                 */
                $error_messages[] = $response_block->message;

                $license_data = WOO_SLT_Licence::get_license_data();
                if ($license_data == false) {
                    $license_data = [];
                }

                //save the license
                $license_data['key']          = $license_key;
                $license_data['last_check']   = time();

                WOO_SLT_Licence::update_license_data($license_data);
            } else {
                $error_messages[] = __('There was a problem activating the licence: ', 'analyticswp') . (string)$response_block->message;
                return $error_messages;
            }
        } else {
            $error_messages[] = __('There was a problem with the data block received from ' . ANALYTICSWP_WOO_SLT_APP_API_URL, 'analyticswp');
            return $error_messages;
        }

        return true;
    }


    /**
     * @return void
     */
    function admin_print_styles()
    {
        wp_register_style('wooslt_admin', ANALYTICSWP_WOO_SLT_URL . '/css/admin.css');
        wp_enqueue_style('wooslt_admin');
    }

    /**
     * @return void
     */
    function admin_print_scripts() {}


    /**
     * @return void
     */
    function admin_no_key_notices()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $screen = get_current_screen();
        if (is_null($screen)) {
            return;
        }

        if (is_multisite()) {
            if ($screen->id == 'settings_page_analyticswp-license-key-network') {
                return;
            }
?>
            <div class="updated fade">
                <p><?php _e("AnalyticsWP is inactive. There may be critical security updates available. Please enter your", 'analyticswp') ?> <a href="<?php echo URLs::admin_path('analyticswp-license-key-options'); ?>"><?php _e("License Key", 'analyticswp') ?></a></p>
            </div>
        <?php
        } else {
            if ($screen->id == 'settings_page_analyticswp-license-key-options') {
                return;
            }
        ?>
            <div class="updated fade">
                <p><?php _e("AnalyticsWP is inactive. There may be critical security updates available. Please enter your", 'analyticswp') ?> <a href="<?php echo URLs::admin_path('analyticswp-license-key-options'); ?>"><?php _e("License Key", 'analyticswp') ?></a></p>
            </div>
        <?php
        }
    }

    /**
     * @return void
     */
    function licence_form_submit()
    {
        global $slt_form_submit_messages;

        //check for refresh request
        if (isset($_POST['analyticswp_slt_licence_form_submit']) && isset($_POST['analyticswp_slt_licence_refresh']) && (wp_verify_nonce(Validators::str($_POST['analyticswp_slt_license_nonce']), 'analyticswp_slt_license') !== false)) {
            // $slt_form_submit_messages = $this->refresh_license();
            WOO_SLT_Licence::run_status_check(true);
            /**
             * @psalm-suppress PossiblyUndefinedArrayOffset
             */
            $current_url    =   'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            wp_redirect($current_url);
            die();
        }

        //check for de-activation request
        if (isset($_POST['analyticswp_slt_licence_form_submit']) && isset($_POST['analyticswp_slt_licence_deactivate']) && (wp_verify_nonce(Validators::str($_POST['analyticswp_slt_license_nonce']), 'analyticswp_slt_license') !== false)) {

            $license_data = WOO_SLT_Licence::get_license_data();
            if ($license_data == false) {
                $license_key = '';
            } else {
                $license_key = $license_data['key'];
            }

            //build the request query
            $args = array(
                'woo_sl_action'         => 'deactivate',
                'licence_key'           => $license_key,
                'product_unique_id'     => ANALYTICSWP_WOO_SLT_PRODUCT_ID,
                'domain'                => ANALYTICSWP_WOO_SLT_INSTANCE,
                'cache_bust'           => time()
            );
            $request_uri    = ANALYTICSWP_WOO_SLT_APP_API_URL . '?' . http_build_query($args, '', '&');
            $data           = wp_remote_get($request_uri);

            if (($data instanceof \WP_Error) || !isset($data['response']) || $data['response']['code'] != 200) {
                /**
                 * @psalm-suppress MixedOperand
                 * @psalm-suppress MixedArrayAssignment
                 * @psalm-suppress MixedArrayAccess
                 */
                $slt_form_submit_messages[] .= __('There was a problem connecting to ', 'analyticswp') . ANALYTICSWP_WOO_SLT_APP_API_URL;
                return;
            }

            /**
             * @psalm-suppress MixedAssignment
             */
            $response_block = json_decode($data['body']);

            //retrieve the last message within the $response_block
            /**
             * @psalm-suppress MixedArrayAccess
             * @psalm-suppress MixedAssignment
             * @psalm-suppress MixedArgument
             */
            $response_block = $response_block[count($response_block) - 1];

            /**
             * @psalm-suppress MixedPropertyFetch
             */
            if (isset($response_block->status)) {
                if ($response_block->status == 'success' && $response_block->status_code == 's201') {
                    //the license is active and the software is active
                    /**
                     * @psalm-suppress MixedAssignment
                     * @psalm-suppress MixedArrayAssignment
                     */
                    $slt_form_submit_messages[] = $response_block->message;

                    $license_data = WOO_SLT_Licence::get_license_data();
                    if ($license_data == false) {
                        $license_data = [];
                    }

                    //save the license
                    $license_data['key']          = '';
                    $license_data['last_check']   = time();

                    WOO_SLT_Licence::update_license_data($license_data);
                } else //if message code is e104  force de-activation
                    if ($response_block->status_code == 'e002' || $response_block->status_code == 'e104') {
                        $license_data = WOO_SLT_Licence::get_license_data();
                        if ($license_data == false) {
                            $license_data = [];
                        }

                        //save the license
                        $license_data['key']          = '';
                        $license_data['last_check']   = time();

                        WOO_SLT_Licence::update_license_data($license_data);
                    } else {
                        /** @psalm-suppress MixedArrayAssignment */
                        $slt_form_submit_messages[] = __('There was a problem deactivating the licence: ', 'analyticswp') . (string)$response_block->message;

                        return;
                    }
            } else {
                /** @psalm-suppress MixedArrayAssignment */
                $slt_form_submit_messages[] = __('There was a problem with the data block received from ' . ANALYTICSWP_WOO_SLT_APP_API_URL, 'analyticswp');
                return;
            }

            //redirect
            /**
             * @psalm-suppress PossiblyUndefinedArrayOffset
             */
            $current_url    =   'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

            wp_redirect($current_url);
            die();
        }



        // Handle the submit/activate license key
        if (isset($_POST['analyticswp_slt_licence_form_submit']) && (wp_verify_nonce(Validators::str($_POST['analyticswp_slt_license_nonce']), 'analyticswp_slt_license') !== false)) {

            $license_key = isset($_POST['license_key']) ? sanitize_key(trim(Validators::str($_POST['license_key']))) : '';
            ///////////////////////////////////// /////////////////////////////////////
            // START License Activation Logic - Extract
            ///////////////////////////////////// /////////////////////////////////////
            $true_or_error_messages = self::handle_activating_license($license_key);
            if (is_array($true_or_error_messages)) {
                $slt_form_submit_messages = $true_or_error_messages;
                return;
            }
            ///////////////////////////////////// /////////////////////////////////////
            // END License Activation Logic - Extract
            ///////////////////////////////////// /////////////////////////////////////

            //redirect
            /**
             * @psalm-suppress PossiblyUndefinedArrayOffset
             */
            $current_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            wp_redirect($current_url);
            die();
        }
    }

    /**
     * @return void
     */
    function licence_form()
    {
        ?>

        <style>
            /** Navbar style */

            @import url('https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;500&family=Inter:wght@400;500;600&display=swap');


            .navbar {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 0.75rem;
                padding: 0.75rem 1.25rem;
                font-size: 0.75rem;
                line-height: 1rem;
            }

            .logo {
                display: flex;
                flex-direction: row;
                align-items: center;
                gap: 0.5rem;
            }

            .logo-wrapper {
                width: 40px;
                height: 40px;
            }

            .logo-text {
                display: flex;
                flex-direction: column;
            }

            .page-wrapper {
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            }

            body {
                background: #FBF5F2 !important;
            }

            #wpcontent {
                font-family: 'Inter', sans-serif;
                padding: 2rem !important;
            }

            #wpfooter {
                display: none;
            }

            /** Global Styles */
            .font-medium {
                font-weight: 500;
            }

            .opaque {
                color: rgba(115, 115, 115);
                font-size: 0.75rem;
            }

            .explain {
                margin: 5px 0;
                font-size: 12px;
                line-height: 16px;
            }

            .postbox {
                background: #ffff;
                padding: 2rem;
                gap: 20px;
                display: flex;
                flex-direction: column;
                border: 1px solid #E3E3E3;
                border-radius: 10px;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            }

            h2 {
                font-size: 24px;
                font-weight: 500;
                margin: 0;
                line-height: 120%;
                font-family: "Libre Baskerville", serif;
            }

            .label {
                display: block;
                font-size: 12px;
                font-weight: 600;
                margin-bottom: 8px;
            }

            .postbox .text-input {
                min-width: 300px;
            }

            .submit {
                margin: 0;
                padding: 0;
            }
        </style>
        <div class="page-wrapper">
            <div class="navbar">
                <div class="logo">
                    <div class="logo-wrapper">
                        <svg viewBox="0 0 60 61" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21.9469 26.0392V33.8004L17.9618 29.9145L21.9469 26.0392Z" fill="black"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15 0.144287C6.71573 0.144287 0 6.86002 0 15.1443V45.1443C0 53.4286 6.71573 60.1443 15 60.1443H45C53.2843 60.1443 60 53.4286 60 45.1443V15.1443C60 6.86002 53.2843 0.144287 45 0.144287H15ZM21.9469 38.4583L15.5615 32.2487L8.85091 38.7744L6.57537 36.539L20.8091 22.6522H25.1977V33.9981L36.8651 22.6522H40.1158V34.01L52.0973 22.336L54.396 24.5715L40.1158 38.4583H36.8651V27.1005L25.1977 38.4464V38.4583H21.9469Z" fill="black"></path>
                        </svg>
                    </div>
                    <div class="logo-text">
                        <span class="font-medium">AnalyticsWP</span>
                        <span class="opaque">License</span>
                    </div>
                </div>
                <div class="opaque">
                    <?php
                    echo ('v' . ANALYTICSWP_VERSION) ?>
                </div>
            </div>
            <form id="form_data" name="form" method="post">
                <div class="postbox">
                    <h2>Activate AnalyticsWP</h2>
                    <?php wp_nonce_field('analyticswp_slt_license', 'analyticswp_slt_license_nonce'); ?>
                    <input type="hidden" name="analyticswp_slt_licence_form_submit" value="true" />
                    <div class="section section-text">
                        <span class="label"><?php _e("License key", 'analyticswp') ?></span>
                        <div class="option">
                            <div class="controls">
                                <input type="text" value="" name="license_key" class="text-input">
                            </div>
                            <div class="explain"><?php _e("Please enter in the license key you got when you bought AnalyticsWP. If you can't find it, just go to your", 'analyticswp') ?> <a href="https://analyticswp.com/my-account/" target="_blank"><?php _e("account page", 'analyticswp') ?></a>.
                            </div>
                        </div>
                    </div>

                    <div class="submit">
                        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Confirm', 'analyticswp') ?>">
                    </div>
                </div>

            </form>
        </div>
    <?php

    }

    /**
     * @return void
     */
    function licence_deactivate_form()
    {
        $license_data = WOO_SLT_Licence::get_license_data();
        if (!$license_data) {
            return;
        }

    ?>

        <style>
            /** Navbar style */

            @import url('https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;500&family=Inter:wght@400;500;600&display=swap');


            .navbar {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 0.75rem;
                padding: 0.75rem 1.25rem;
                font-size: 0.75rem;
                line-height: 1rem;
            }

            .logo {
                display: flex;
                flex-direction: row;
                align-items: center;
                gap: 0.5rem;
            }

            .logo-wrapper {
                width: 40px;
                height: 40px;
            }

            .logo-text {
                display: flex;
                flex-direction: column;
            }

            .page-wrapper {
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            }

            body {
                background: #FBF5F2 !important;
            }

            #wpcontent {
                font-family: 'Inter', sans-serif;
                padding: 2rem !important;
            }

            #wpfooter {
                display: none;
            }

            /** Global Styles */
            .font-medium {
                font-weight: 500;
            }

            .opaque {
                color: rgba(115, 115, 115);
                font-size: 0.75rem;
            }

            .explain {
                margin: 5px 0;
                font-size: 12px;
                line-height: 16px;
            }

            .postbox {
                background: #ffff;
                padding: 2rem;
                gap: 20px;
                display: flex;
                flex-direction: column;
                border: 1px solid #E3E3E3;
                border-radius: 10px;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            }

            h2 {
                font-size: 24px;
                font-weight: 500;
                margin: 0;
                line-height: 120%;
                font-family: "Libre Baskerville", serif;
            }

            .label {
                display: block;
                font-size: 12px;
                font-weight: 600;
                margin-bottom: 8px;
            }

            .postbox .text-input {
                min-width: 300px;
            }

            .submit {
                margin: 0;
                padding: 0;
            }
        </style>

        <div class="page-wrapper">
            <div class="navbar">
                <div class="logo">
                    <div class="logo-wrapper">
                        <svg viewBox="0 0 60 61" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21.9469 26.0392V33.8004L17.9618 29.9145L21.9469 26.0392Z" fill="black"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15 0.144287C6.71573 0.144287 0 6.86002 0 15.1443V45.1443C0 53.4286 6.71573 60.1443 15 60.1443H45C53.2843 60.1443 60 53.4286 60 45.1443V15.1443C60 6.86002 53.2843 0.144287 45 0.144287H15ZM21.9469 38.4583L15.5615 32.2487L8.85091 38.7744L6.57537 36.539L20.8091 22.6522H25.1977V33.9981L36.8651 22.6522H40.1158V34.01L52.0973 22.336L54.396 24.5715L40.1158 38.4583H36.8651V27.1005L25.1977 38.4464V38.4583H21.9469Z" fill="black"></path>
                        </svg>
                    </div>
                    <div class="logo-text">
                        <span class="font-medium">AnalyticsWP</span>
                        <span class="opaque">License</span>
                    </div>
                </div>
                <div class="opaque">
                    <?php
                    echo ('v' . ANALYTICSWP_VERSION) ?>
                </div>
            </div>
            <div id="form_data">
                <div class="postbox">
                    <form id="form_data" name="form" method="post">
                        <?php wp_nonce_field('analyticswp_slt_license', 'analyticswp_slt_license_nonce'); ?>
                        <input type="hidden" name="analyticswp_slt_licence_form_submit" value="true" />
                        <input type="hidden" name="analyticswp_slt_licence_deactivate" value="true" />
                        <input type="hidden" name="analyticswp_slt_licence_refresh" value="true" />
                        <div class="section section-text ">
                            <h4 class="heading"><?php _e("License Key", 'analyticswp') ?> <?php if (License::is_analyticswp_activated_but_expired()) {
                                                                                                echo ("<div class='sld_badge alert'>" . __("Expired", 'analyticswp') . "</div>");
                                                                                            } ?></h4>
                            <div class="option">
                                <div class="controls">
                                    <?php
                                    /** @psalm-suppress DocblockTypeContradiction */
                                    if ($this->licence::is_test_instance()) {
                                    ?>
                                        <p>Local instance, no key applied.</p>
                                    <?php
                                    } else {
                                    ?>
                                        <p>
                                            <b><?php echo substr($license_data['key'], 0, 12) ?>-xxxxxxxx-xxxxxxxx</b> &nbsp;&nbsp;&nbsp;

                                            <a class="button-secondary" title="Deactivate" href="javascript: void(0)" onclick="jQuery(this).closest('form').find('input[name=&quot;analyticswp_slt_licence_refresh&quot;]').remove(); jQuery(this).closest('form').submit();"><?php _e('Deactivate', 'analyticswp') ?></a>
                                            <a class="button-secondary" title="Refresh" href="javascript: void(0)" onclick="jQuery(this).closest('form').find('input[name=&quot;analyticswp_slt_licence_deactivate&quot;]').remove(); jQuery(this).closest('form').submit();"><?php _e('Refresh', 'analyticswp') ?></a>
                                        </p>
                                    <?php } ?>
                                </div>
                                <div class="explain"><?php _e("Manage your license key from", 'analyticswp') ?> <a href="https://analyticswp.com/my-account/" target="_blank"><?php _e('My Account', 'analyticswp') ?></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>



    <?php
    }

    /**
     * @return void
     */
    function licence_multisite_require_nottice()
    {
    ?>
        <div class="wrap">
            <h1><?php _e("AnalyticsWP - License Key", 'analyticswp') ?><br />&nbsp;</h1>
            <div id="form_data">
                <div class="postbox">
                    <div class="section section-text ">
                        <h4 class="heading"><?php _e("License Key Required", 'analyticswp') ?>!</h4>
                        <div class="option">
                            <div class="explain"><?php _e("Enter the License Key you got when bought this product. If you lost the key, you can always retrieve it from", 'analyticswp') ?> <a href="https://analyticswp.com/my-account/" target="_blank"><?php _e("My Account", 'analyticswp') ?></a><br />
                                <?php _e("More keys can be generated from", 'analyticswp') ?> <a href="https://analyticswp.com/my-account/" target="_blank"><?php _e("My Account", 'analyticswp') ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php

    }
}



?>