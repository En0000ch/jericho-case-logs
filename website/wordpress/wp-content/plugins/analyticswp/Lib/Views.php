<?php

namespace AnalyticsWP\Lib;

/**
 * @psalm-import-type MenuPagesArgs from \AnalyticsWP\Lib\SuperSimpleWP
 * @psalm-import-type JourneyEvent from Event
 * @psalm-import-type IdentifiedJourney from Event
 * @psalm-import-type AnonymousJourney from Event
 * @psalm-import-type SourceData from Event
 * 
 * @psalm-type JourneyRenderingOptions = array{
 *   highlight_event_id: int
 * }
 */
class Views
{
    const IS_PRODUCTION_MODE = true;

    /**
     * @return MenuPagesArgs
     */
    public static function menu_pages()
    {
        $common_roles_function = function (): bool {
            $allowed_roles = Validators::array_of_string_keys_and_values(SuperSimpleWP::get_setting('analyticswp', 'admin-access-user-roles'));
            return SuperSimpleWP::does_current_user_have_admin_access($allowed_roles);
        };

        $deactivated_page_check =
            /**
             * @param callable(): string $render_function
             */
            function ($render_function): void {
                if (!License::is_active()) {
                    echo Views::render_deactivated_page(true);
                }
                echo $render_function();
            };

        $menu_pages = array(
            'main' => array(
                'title' => 'Dashboard',
                'menu_title' => 'Dashboard',
                'capability' => 'read',
                'roles_function' => $common_roles_function,
                'function' => function () use ($deactivated_page_check) {
                    $deactivated_page_check(function () {
                        return Views::render_stats_main_page();
                    });
                },
            ),

            'awp_live_events' => array(
                'title' => 'Live Events',
                'menu_title' => 'Live Events',
                'capability' => 'read',
                'roles_function' => $common_roles_function,
                'function' => function () use ($deactivated_page_check) {
                    $deactivated_page_check(function () {
                        return Views::render_live_events_page();
                    });
                },
            ),
            'awp_integrations' => array(
                'title' => 'Integrations',
                'menu_title' => 'Integrations',
                'capability' => 'read',
                'roles_function' => $common_roles_function,
                'function' => function () use ($deactivated_page_check) {
                    $deactivated_page_check(function () {
                        return Views::render_integrations_page();
                    });
                },
            ),
            'Journeys' => array(
                'title' => 'Journeys',
                'menu_title' => 'Journeys',
                'capability' => 'read',
                'roles_function' => $common_roles_function,
                'function' => function () use ($deactivated_page_check) {
                    $deactivated_page_check(function () {
                        return Views::render_dots_page();
                    });
                },
            ),
            'anonymous_journeys' => array(
                'title' => 'Anonymous Journeys',
                'menu_title' => 'Anonymous Journeys',
                'capability' => 'read',
                'roles_function' => $common_roles_function,
                'hidden' => true,
                'function' => function () use ($deactivated_page_check) {
                    $deactivated_page_check(function () {
                        return Views::render_anonymous_journeys_page();
                    });
                },
            ),
            'event_id_journeys' => array(
                'title' => 'Event ID Journeys',
                'menu_title' => 'Event ID Journeys',
                'capability' => 'read',
                'roles_function' => $common_roles_function,
                'hidden' => true,
                'function' => function () use ($deactivated_page_check) {
                    $deactivated_page_check(function () {
                        return Views::render_event_id_journeys_page();
                    });
                },
            ),
            'user_journeys' => array(
                'title' => 'User Journeys',
                'menu_title' => 'User Journeys',
                'capability' => 'read',
                'roles_function' => $common_roles_function,
                'hidden' => true,
                'function' => function () use ($deactivated_page_check) {
                    $deactivated_page_check(function () {
                        return Views::render_user_journeys_page();
                    });
                },
            ),
            'conversion_journeys' => array(
                'title' => 'Conversion Journeys (WC Orders)',
                'menu_title' => 'Conversion Journeys (WC Orders)',
                'capability' => 'read',
                'roles_function' => $common_roles_function,
                'hidden' => true,
                'function' => function () use ($deactivated_page_check) {
                    $deactivated_page_check(function () {
                        return Views::render_conversion_journeys_page();
                    });
                },
            ),
            'analyticswp-license-key-options' => array(
                'title' => 'License',
                'menu_title' => 'License',
                'capability' => 'manage_options',
                'roles_function' => $common_roles_function,
                'function' => function () {
                    echo '<style>.postbox { padding: 20px; }</style>';
                    $interface = new \AnalyticsWP\Lib\WooSoftwareLicense\WOO_SLT_Options_Interface;
                    if (License::is_analyticswp_activated()) {
                        $interface->licence_deactivate_form();
                    } else {
                        $interface->licence_form();
                    }
                },
            ),
            'analyticswp_welcome' => array(
                'title' => 'Analytics WP - Welcome',
                'menu_title' => 'Welcome / Help',
                'capability' => 'read',
                'roles_function' => $common_roles_function,
                'hidden' => false,
                'function' => function () {
                    echo Views::render_welcome_page();
                },
            ),


        );

        if (AgencyMode::is_agency_mode_enabled()) {
            $menu_pages_to_add = [
                'agency_mode_dashboard' => array(
                    'title' => 'Agency Mode - Dashboard',
                    'menu_title' => 'Dashboard <span style="display: inline-block; font-size: 10px; background-color: #46e986; color: black; padding: 2px 4px; border-radius: 10px; font-weight: 500;">Agency</span>',
                    'capability' => 'read',
                    'roles_function' => $common_roles_function,
                    'function' => function () use ($deactivated_page_check) {
                        $deactivated_page_check(function () {
                            return Views::render_stats_main_page(true);
                        });
                    },
                ),
                'agency_mode_client_management' => array(
                    'title' => 'Agency Mode - Client Management',
                    'menu_title' => 'Clients <span style="display: inline-block; font-size: 10px; background-color: #46e986; color: black; padding: 2px 4px; border-radius: 10px; font-weight: 500;">Agency</span>',
                    'capability' => 'read',
                    'roles_function' => $common_roles_function,
                    'function' => function () use ($deactivated_page_check) {
                        $deactivated_page_check(function () {
                            return Views::render_client_management_page();
                        });
                    },
                ),
            ];
        } else {
            if ((bool)SuperSimpleWP::get_setting('analyticswp', 'agency_mode_landing_page_is_hidden')) {
                $menu_pages_to_add = [];
            } else {
                $menu_pages_to_add = [
                    'agency_mode_landing_page' => array(
                        'title' => 'Agency Mode [new]',
                        'menu_title' => 'For agencies <span style="display: inline-block; font-size: 10px; background-color: #46e986; color: black; padding: 2px 4px; border-radius: 10px; font-weight: 500;">New</span>',
                        'capability' => 'read',
                        'roles_function' => $common_roles_function,
                        'function' => function () use ($deactivated_page_check) {
                            $deactivated_page_check(function () {
                                return Views::render_agency_mode_landing_page();
                            });
                        },
                    ),
                ];
            }
        }

        return array_merge($menu_pages, $menu_pages_to_add);
    }

    /**
     * Handles redirection to the AnalyticsWP welcome screen after plugin activation.
     *
     * This function checks whether certain conditions are met to redirect the user
     * to the AnalyticsWP welcome page upon plugin activation. It avoids redirection
     * if the activation is happening via WP-CLI or a multi-site activation.
     *
     * @return void
     */
    public static function maybe_redirect_to_analyticswp_welcome_screen(): void
    {
        // Return early if the command is run via WP-CLI or during multi-site activation
        if ((defined('WP_CLI') && WP_CLI) || isset($_GET['activate-multi'])) {
            return;
        }

        // Check if there is a transient indicating the need for redirection
        $should_redirect = (bool)get_transient('analyticswp_redirect_to_welcome_page');
        if ($should_redirect) {
            // Delete the transient to avoid repeated redirections
            delete_transient('analyticswp_redirect_to_welcome_page');

            // Check if the option to prevent redirection is not set and perform the redirection
            $prevent_redirection = (bool)get_option('analyticswp_dont_redirect_to_homescreen_after_install');
            if (!$prevent_redirection) {
                wp_safe_redirect(admin_url('admin.php?page=analyticswp_welcome'));
                exit; // Ensure the script stops executing after the redirection
            }
        }
    }

    /**
     * @return string
     */
    public static function render_admin_styles()
    {
        ob_start();
?>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Libre+Baskerville:opsz@12..24&family=Inter:wght@400;500;600&display=swap');
            @import url('https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&display=swap');

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

            .font-code {
                font-family: "Fira Code", monospace;
                font-optical-sizing: auto;
                font-weight: 400;
                font-style: normal;
            }


            /** Navbar style */

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



            .histogram {
                display: flex;
                align-items: flex-end;
                width: 40%;
                height: 200px;
                border: 1px solid #ccc;
                background: #f8f1ea;
                padding-top: 20px;
            }

            .page-wrapper {
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            }

            .page-wrapper .container {
                display: flex;
                flex-direction: row;
                gap: 2rem;
            }

            .main-content {
                flex: 3;
                background: #ffff;
                padding: 2rem;
                border: 1px solid #E3E3E3;
                border-radius: 10px;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            }

            .sidebar {
                flex: 1;
            }


            .awp-filters {
                display: flex;
                gap: 3px;
                align-items: flex-end;
                margin: 1rem 0;
            }

            .awp-filters label {
                display: flex;
                flex-direction: column;
                gap: 3px;
            }

            @media (max-width: 960px) {
                .page-wrapper>.container {
                    flex-direction: column;
                }

                .main-content {
                    max-width: 100%;
                }
            }

            .section-header {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .section-header h2,
            .section-header p {
                margin: 0;
            }

            .section-header h2 {
                font-size: 24px;
                font-weight: 500;
                line-height: 120%;
            }

            h1,
            h2,
            h3,
            .stat-number {
                font-family: "Libre Baskerville", serif;
            }

            .stat-item {
                display: flex;
                flex-direction: row;
                gap: 2px;
                align-items: center;
                justify-items: center;
                font-size: 11px;
                line-height: 110%;
            }

            .stat-item .tooltip {
                width: 14px;
                height: 14px;
            }

            .stat-item .tooltip svg {
                opacity: .5;
            }

            .bar {
                flex: 1;
                margin: 0 1px;
                background-color: #d7ccc6;
            }


            .analyticswp-section-title {
                color: #2a2a2a;
                font-size: 28px;
                margin-bottom: 25px;
            }


            .event-journey {
                display: block;
                background: #f8fbff;
                position: relative;
                padding: 0 2rem;
                border: 1px solid #c2d5eb;
                border-radius: 5px;
                max-height: 600px;
                overflow-y: scroll;
            }

            .no-journey {
                display: flex;
                padding: 1rem;
                border-radius: 10px;
                gap: 5px;
                align-items: center;
                color: #934b54;
                font-size: 12px;
                background: #ffe7ea;
                width: fit-content;
            }

            .no-journey svg {
                width: 16px;
                height: 16px;
                stroke-width: 2px;
            }


            .event-journey li.event-journey-parent:first-child {
                padding-top: 2rem;
            }

            .event-journey li.event-journey-parent:last-child {
                padding-bottom: 2rem;
            }

            .event-journey li.event-journey-parent:first-child:after {
                position: absolute;
                top: 36px;
                bottom: 0;
                left: -5px;
                display: block;
                width: 6px;
                height: 6px;
                border-radius: 11px;
                border: 3px solid #c2d5eb;
                content: "";
                background: #f8fbff;
            }

            .event-journey li.event-journey-parent {
                position: relative;
                padding: 10px 20px;
                margin-bottom: 0;
            }

            .event-journey li.event-journey-parent:before {
                position: absolute;
                top: 0;
                bottom: 0;
                left: 0;
                display: block;
                width: 2px;
                content: "";
                background: #c2d5eb;
            }


            .event-journey li.event-journey-parent:after {
                position: absolute;
                top: 14px;
                bottom: 0;
                left: -5px;
                display: block;
                width: 6px;
                height: 6px;
                border-radius: 11px;
                border: 3px solid #c2d5eb;
                content: "";
                background: #f8fbff;
            }

            ul.event-journey-child li {
                font-size: 12px;
                margin-bottom: 0;
            }

            ul.event-journey-child {
                margin-top: 0.525rem;
            }

            .analyticswp_journey_time_diff {
                color: #7291b7;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                width: 100%;
                gap: 2px;
                font-size: 0.9em;
                line-height: 20px;
                padding: 5px 16px;
                position: relative;
                border-left: 2px dashed #c2d5eb;
                font-weight: 400;
            }

            .analyticswp_journey_time_diff svg {
                height: 16px;
                width: 16px;
                stroke-width: 2px;
                color: #c2d5eb;
            }



            .pill {
                display: inline-block;
                padding: 4px 4px;
                border-radius: 4px;
                font-size: 11px;
                line-height: 11px;
                text-transform: capitalize;
            }

            .pill.blue {
                background: #9ae4ff;
            }

            .pill.purple {
                background: #e4c2ff;
            }

            .pill.green {
                background: #66f39e;
            }

            .user-card {
                display: flex;
                flex-direction: column;
                max-width: 100%;
                margin: 1.5rem 0;
            }

            .user-card .username {
                font-size: 14px;
                font-weight: 500;
            }

            .user-card-details {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 30px;
            }

            .detail-row {
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .detail-row svg {
                width: 24px;
                height: 24px;
                opacity: .75;
                background: #e1e1e1;
                padding: 5px;
                border-radius: 5px;

            }

            .detail-row strong {
                font-size: 11px;
                font-weight: 500;
                opacity: .75;
                line-height: 1;
            }

            .detail-row>div {
                font-size: .8rem;
                font-weight: 500;
                display: flex;
                gap: 2px;
                flex-direction: column;
            }


            .detail-row i {
                margin-right: 5px;
            }

            .detail-row div {
                margin-left: 5px;
            }

            /* stats page */
            .analyticswp-dot-legend {
                padding: 1rem;
                display: flex;
                justify-content: end;
                gap: 20px;
                font-weight: 500;
                font-size: 0.725rem;
            }

            .analyticswp-dot-legend-row {
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .analyticswp-dot-legend-row.total {
                margin-right: auto;
            }

            .analyticswp-dot-legend .analyticswp-dot {
                width: 16px;
                height: 16px;
            }

            .analyticswp-dot-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, 30px);
                gap: 5px;
                padding: 40px;
                background: #ffff;
                padding: 1rem;
                border: 1px dashed #bfbfbf;
                border-radius: .875rem;
            }

            .analyticswp-dot-grid .tooltip {
                display: flex;
            }

            .analyticswp-dot-grid.hide-anonymous .anonymous {
                display: none !important;
            }

            .analyticswp-dot {
                display: flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                color: #313131;
                width: 30px;
                height: 30px;
                border-radius: 30px;
                font-size: 12px;
                line-height: 1;
                text-transform: uppercase;
                outline: 3px solid transparent;
                box-sizing: border-box;
            }

            .analyticswp-dot:hover {
                color: #313131;
                outline-color: rgba(0, 0, 0, .1);
                -webkit-transition: all 200ms ease;
                transition: all 200ms ease;
            }

            .analyticswp-dot.anonymous {
                background-color: #d7ccc6;
            }

            .dot-tooltip-type.anonymous {
                color: #d7ccc6;
            }

            .analyticswp-dot.identified {
                background-color: #9ae4ff;
            }

            .dot-tooltip-type.identified {
                color: #9ae4ff;
            }

            .analyticswp-dot.identified_with_conversion {
                background-color: #66f39e;
            }

            .dot-tooltip-type.identified_with_conversion {
                color: #66f39e;
            }




            .stats-container {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                width: 180px;
            }

            .stat-number {
                font-size: 1.875rem;
                line-height: 2.25rem;
            }

            .stat {
                width: 100%;
                display: flex;
                flex-direction: column;
                text-align: left;
                border: 1px solid #E0CDC1;
                padding: 0.75rem;
                box-sizing: border-box;
                border-radius: 8px;
                gap: 0.75rem;
            }


            .stats-container h2 {
                font-size: 2em;
                margin-bottom: 10px;
            }

            .stats-container p {
                font-size: 1em;
                opacity: 0.8;
            }

            /* Tooltips */
            .tooltip {
                position: relative;
                cursor: pointer;
            }

            .tooltip-text {
                display: none;
                position: absolute;
                left: 20%;
                transform: translateX(-20%) translateY(-10px);
                bottom: 25px;
                background-color: #000;
                color: #fff;
                padding: 1rem;
                border-radius: 10px;
                z-index: 1;
                font-size: 12px;
                line-height: 20px;
                width: 240px;
            }

            .tooltip-content {
                display: flex;
                flex-direction: column;
                gap: 2px;
            }

            .tooltip:hover .tooltip-text {
                display: block;
            }

            .analyticswp-helper-component-1 {
                border-radius: 10px;
            }

            .analyticswp-helper-component-1 a {
                color: rgb(3, 105, 161);

                text-decoration: underline;
                font-weight: 500;
            }

            .analyticswp-helper-component-1 a:hover {
                text-decoration: underline;
            }

            .form-container {
                margin: 1rem 0;
            }

            .form-container label {
                display: block;
                margin-bottom: 0.5rem;
                font-size: 12px;
                font-weight: 500;
            }

            .agency-mode-pill {
                display: inline-block;
                font-size: 10px;
                background-color: yellow;
                color: black;
                padding: 2px 6px;
                border-radius: 10px;
                font-weight: bold;
            }
        </style>

    <?php
        return ob_get_clean();
    }


    /**
     * Undocumented function
     *
     * @param array{
     *  type: 'anonymous'|'identified'|'anonymous_with_conversion'|'identified_with_conversion',
     *  id: string,
     *  admin_url: string,
     *  conversion_count: int,
     *  event_count: int,
     *  user_login: string,
     *  user_email: string,
     *  first_event_timestamp: string,
     *  last_event_timestamp: string,
     * } $data
     * 
     * @return string
     */
    public static function generate_dot_tooltip($data)
    {
        // Start the tooltip HTML content.
        $html = '<div class="tooltip-content">';

        // Add type.
        $type = $data['type'];
        $human_readable_type = ucwords(str_replace('_', ' ', $type));
        $html .= '<span class="dot-tooltip-type ' . $type . '">' . $human_readable_type . '</span>';

        // Add event count.
        $html .= '<span>Event Count: ' . htmlspecialchars((string)$data['event_count']) . '</span>';

        // Add first_event_timestamp and last_event_timestamp.
        // Like this: "First Event: 2021-08-01 12:00:00"
        // Like this: "Last Event: 2021-08-01 12:00:00"
        $html .= '<span>First Event: ' . htmlspecialchars($data['first_event_timestamp']) . '</span>';
        $html .= '<span>Last Event: ' . htmlspecialchars($data['last_event_timestamp']) . '</span>';


        // Check if type is 'identified' or 'identified_with_conversion' to add user info.
        if ($data['type'] === 'identified' || $data['type'] === 'identified_with_conversion') {
            $html .= '<span>User Login: ' . htmlspecialchars($data['user_login']) . '</span>';
            $html .= '<span>User Email: ' . htmlspecialchars($data['user_email']) . '</span>';
        }

        // Additional info for 'identified_with_conversion'.
        if ($data['type'] === 'identified_with_conversion') {
            // Assume $data includes 'total_conversion_value' for version 2 enhancement.
            // This is a placeholder value; adjust as necessary to match your actual data structure.
            // Add conversion count.
            $html .= '<span>Conversion Count: ' . htmlspecialchars((string)$data['conversion_count']) . '</span>';
        }

        // Add a faded out unique_session_id (id) at the bottom for only anonymous journeys.
        if ($data['type'] === 'anonymous') {
            $html .= '<span style="opacity: 0.6;">Unique Session ID: ' . htmlspecialchars($data['id']) . '</span>';
        }

        // Close the tooltip HTML content.
        $html .= '</div>';

        return $html;
    }


    /**
     * @return string
     */
    public static function render_helper_component_1()
    {
        ob_start();
        $users_page_url = admin_url('users.php');
        $users_page_link = "<a href='$users_page_url'>WordPress users page</a>";

        // TODO-WOOCOMMERCE-INTEGRATION this only is relevant if WooCommerce is installed.
        $orders_page_url = admin_url('edit.php?post_type=shop_order');
        $orders_page_link = "<a href='$orders_page_url'>WooCommerce orders page</a>";

        $stats_page_url = admin_url('admin.php?page=analyticswp');
        $stats_page_link = "<a href='$stats_page_url'>AnalyticsWP Dashboard page</a>";


    ?>
        <div class="analyticswp-helper-component-1">

            <h3>Helpful tips</h3>
            <p>
                Want to find a particular user? Head over to the <?php echo ($users_page_link) ?>, then click on the 'View Journey' link that AnalyticsWP adds to the user row.
            </p>
            <p>
                Need to check out site-wide stats like total visitors, pageviews, traffic sources, and more? Take a look at the <?php echo ($stats_page_link) ?>.
            </p>
            <p>
                Looking for a specific WooCommerce order? You can use the <?php echo ($orders_page_link) ?>, and then click on the 'View Journey' link that AnalyticsWP adds to the order row. Additionally, AnalyticsWP includes a section in your WooCommerce admin notification email templates with a link to the order journey.
            </p>
        </div>


    <?php
        return ob_get_clean();
    }


    /**
     * Renders a flexible grid of dots representing the journey.
     * 
     * @param array{date_range: 'Last 30 days'|'Last 7 days'|'All time', min_events: int} $filters The current filters from the URL.
     *
     * @return string - The html
     */
    public static function render_journey_dots($filters)
    {
        $data = Event::get_journey_dots_data($filters);

        // Initialize counts
        $count_anonymous = 0;
        $count_identified = 0;
        $count_purchases = 0;

        // Loop over $data to count each type
        foreach ($data as $journey) {
            switch ($journey['type']) {
                case 'anonymous':
                    $count_anonymous++;
                    break;
                case 'identified':
                    $count_identified++;
                    break;
                case 'anonymous_with_conversion':
                case 'identified_with_conversion':
                    $count_purchases++;
                    break;
            }
        }

        // Total journeys
        $count_journeys = count($data);

        // Compute conversion rate
        if ($count_journeys > 0) {
            $conversion_rate = round(($count_purchases / $count_journeys) * 100);
        } else {
            $conversion_rate = 0;
        }

        $dotColors = [
            'anonymous' => '#d7ccc6',
            'identified' => '#9ae4ff',
            'identified_with_conversion' => '#66f39e',
            'anonymous_with_conversion' => '#66f39e'
        ];

        // Add a legend with counts
        $dotColors_anonymous = $dotColors['anonymous'];
        $dotColors_identified = $dotColors['identified'];
        $dotColors_identified_with_conversion = $dotColors['identified_with_conversion'];

        $html = "<div class='analyticswp-dot-legend'>
    <div class='analyticswp-dot-legend-row total'>{$count_journeys} journeys in total</div>
    <div class='analyticswp-dot-legend-row'>
        <div class='analyticswp-dot anonymous' style='background-color: {$dotColors_anonymous};'></div>
        <span>Anonymous ({$count_anonymous})</span>
    </div>
    <div class='analyticswp-dot-legend-row'>
        <div class='analyticswp-dot identified' style='background-color: {$dotColors_identified};'></div>
        <span>Identified ({$count_identified})</span>
    </div>
    <div class='analyticswp-dot-legend-row'>
        <div class='analyticswp-dot identified_with_conversion' style='background-color: {$dotColors_identified_with_conversion};'></div>
        <span>Made a purchase ({$count_purchases} / Conversion rate {$conversion_rate}%)</span>
    </div>
</div>";

        // add a helpful docs section that explains some things about the plugin

        $html .= '<div class="analyticswp-dot-grid">';

        if (empty($data)) {
            $html .= '<p style="width: 400px">No journeys match your current filters.</p>';
        }


        // Loop through journey data to produce HTML dots
        foreach ($data as $journey) {
            $journey_url = $journey['admin_url'];
            $type = $journey['type'];

            $color = $dotColors[$journey['type']];

            /** Get the first 2 letters of the user_login if its not empty */
            $content_inside_of_dot = $journey['user_login'] ? substr($journey['user_login'], 0, 2) : '';

            if ($journey['type'] === 'anonymous_with_conversion') {
                $dot_html = "<a href='$journey_url' class='analyticswp-dot $type' style='background-color: {$color}; border: 7px solid $dotColors_anonymous'>$content_inside_of_dot</a>";
            } else {
                $dot_html = "<a href='$journey_url' class='analyticswp-dot $type' style='background-color: {$color};'>$content_inside_of_dot</a>";
            }

            // make the tooltip
            // all
            // - event count
            // - conversion count
            // if type is 'identified' or 'identified_with_conversion' also add:
            // - user_login
            // - user_email
            // if type is 'identified_with_conversion' also add:
            // - conversion count
            // v2
            // - "initials" for identified users?
            // - $ total conversion value

            $tooltip_html = self::generate_dot_tooltip($journey);
            $html .= self::wrapped_tt($dot_html, $tooltip_html, $type);
        }

        $html .= '</div>';  // End dot-grid div

        return $html;
    }

    /**
     * Returns an HTML representation of a list of journey events.
     *
     * @param array<int, JourneyEvent> $journey_events
     * @param JourneyRenderingOptions|null $options
     * 
     * @return string
     */
    public static function journey_events_to_html($journey_events, $options = null)
    {
        /////////////////////////////////
        // Free version stuff
        $is_analytics_wp_active = License::is_active();
        $max_events_on_free_version = 10;
        /////////////////////////////////

        if (empty($journey_events)) {
            return '<div class="no-journey"> <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"> <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /> </svg> <span>No journey found.</span> </div>';
        }


        $html = '';

        // if the free version is active, limit the number of events to 10
        if (!$is_analytics_wp_active && count($journey_events) > $max_events_on_free_version) {
            $total_events = count($journey_events);
            $number_of_events_hidden = count($journey_events) - $max_events_on_free_version;
            $journey_events = array_slice($journey_events, -1 * $max_events_on_free_version);
            // TODO Update:
            $html .= "<div><h2>Only showing you the $max_events_on_free_version most recent events.</br></br></br> <span style='color: red;'>🔒 Activate AnalyticsWP to see all $total_events events in this journey</span></h2></div>";
        }

        $html .= '<ul class="event-journey">';

        $previous_timestamp = null;

        foreach ($journey_events as $event) {
            $referrer_html = $is_analytics_wp_active ? '<li><strong>Referrer:</strong> <a href="' . esc_url($event['referrer']) . '">' . esc_url($event['referrer']) . '</a></li>' : '<li><strong>Referrer:</strong> <span style="color: red;">🔒 Activate AnalyticsWP</span></li>';

            $timestamp = strtotime($event['timestamp']);
            if (is_int($previous_timestamp)) {
                $time_diff = human_time_diff($previous_timestamp, $timestamp);
                $html .= '
              <span class="analyticswp_journey_time_diff font-code"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd" />
              </svg>              
              ' . $time_diff . '</span>';
            }
            $previous_timestamp = $timestamp;

            if ($options && $event['event_id'] == $options['highlight_event_id']) {
                $html .= '<li class="event-journey-parent" style="background: gold;"> <span style="font-weight: bold; display: block;">This is the selected event with ID = ' . $event['event_id'] . '</span> </br>';
            } else {
                $html .= '<li class="event-journey-parent">';
            }
            // $html .= '<li class="event-journey-parent">';

            $html .= '<span class="pill font-code';
            $html .= ($event['event_type'] == 'conversion') ? ' green' : (($event['event_type'] == 'pageview') ? ' blue' : ' purple');
            $html .= '">' . esc_html($event['event_type']) . '</span>';

            $html .= '<ul class="event-journey-child">'; // Start nested <ul>

            $html .= '<li><strong>Timestamp:</strong> ' . esc_html($event['timestamp']) . '</li>';

            if ($event['referrer']) {
                // $html .= '<li><strong>Referrer:</strong> <a href="' . esc_url($event['referrer']) . '">' . esc_url($event['referrer']) . '</a></li>';
                $html .= $referrer_html;
            }

            $html .= '<li><strong>URL:</strong> <a href="' . esc_url($event['page_url']) . '">' . esc_url($event['page_url']) . '</a></li>';

            $html .= '<li><strong>Device:</strong> ' . ucfirst(esc_html($event['device_type'])) . '</li>';

            if ($event['event_type'] == 'conversion' && $event['conversion_type']) {
                $html .= '<li><strong>Conversion Type:</strong> ' . esc_html($event['conversion_type']) . '</li>';

                if (isset($event['conversion_id'])) {

                    // TODO-WOOCOMMERCE-INTEGRATION This is only relevant if WooCommerce is installed.
                    // Should this be part of the WooCommerceIntegration.php ? probably
                    // For example, if it's an 'edd_order' then we should also link to the order in the EDD admin, etc.
                    if ($event['conversion_type'] == 'woocommerce_order') {
                        $html .= '<li><strong>Conversion ID:</strong> <a href="' . esc_url(admin_url('post.php?post=' . $event['conversion_id'] . '&action=edit')) . '">' . esc_html((string)$event['conversion_id']) . '</a></li>';
                    } else {
                        $html .= '<li><strong>Conversion ID:</strong> ' . esc_html((string)$event['conversion_id']) . '</li>';
                    }
                }
            }
            // Render event_properties_json which is an arbitrary JSON object
            if (isset($event['event_properties_json']) && !empty(json_decode($event['event_properties_json']))) {
                $html .= '<li><strong>Event Properties:</strong> <pre style="margin: 0; margin-left: 10px;">' . esc_html($event['event_properties_json']) . '</pre></li>';
            }
            $html .= '</ul>';
            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * @param IdentifiedJourney $identified_journey
     * @param JourneyRenderingOptions|null $options
     * 
     * @return string
     */
    public static function render_identified_journey_html($identified_journey, $options = null)
    {
        $html = Views::html_card_for_user($identified_journey['user_id'], count($identified_journey['events']));
        $html .= Views::journey_events_to_html($identified_journey['events'], $options);

        return $html;
    }

    /**
     * @param AnonymousJourney $anonymous_journey
     * @param JourneyRenderingOptions|null $options
     * 
     * @return string
     */
    public static function render_anonymous_journey_html($anonymous_journey, $options = null)
    {
        return Views::journey_events_to_html($anonymous_journey['events'], $options);
    }

    /**
     * Returns an HTML representation of a WordPress user card.
     *
     * @param int $user_id
     * @param null|int $total_event
     * 
     * @return string
     */
    public static function html_card_for_user($user_id, $total_event = null)
    {
        $user = get_user_by('id', $user_id);

        if (!$user) {
            return '<p>No user found with this ID.</p>';
        }

        // Use a heredoc for cleaner HTML generation
        ob_start();
    ?>
        <hr>
        <div class="user-card">
            <div class="username"></div>
            <div class="user-card-details">
                <div class="detail-row">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                        <path d="M10 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.465 14.493a1.23 1.23 0 0 0 .41 1.412A9.957 9.957 0 0 0 10 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 0 0-13.074.003Z" />
                    </svg>

                    <div><strong>Username</strong><a href="<?php echo admin_url('user-edit.php?user_id=' . $user_id); ?>"><?php echo $user->display_name ?></a></div>
                </div>
                <div class="detail-row">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    <div><strong>Email</strong><?php echo $user->user_email ?></div>
                </div>
                <div class="detail-row">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                    <div><strong>Registered</strong> <?php echo $user->user_registered ?></div>
                </div>
                <?php if ($total_event !== null) { ?>
                    <div class="detail-row">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
                        </svg>
                        <div><strong>Total events</strong> <?php echo $total_event ?></div>
                    </div>
                <?php } ?>
            </div>
        </div>

    <?php
        return ob_get_clean();
    }


    /**
     * Renders a page that allows the input of a unique_session_id, and then displays that journey.
     * 
     * @return string
     */
    public static function render_anonymous_journeys_page()
    {
        ob_start(); // Start output buffering
    ?>
        <?php
        $unique_session_id = isset($_GET['unique_session_id']) ? Validators::str($_GET['unique_session_id']) : null;

        // TODO styles
        echo self::render_admin_styles();
        ?>

        <div class="page-wrapper">
            <?= self::render_navbar() ?>
            <div class="container" style="gap:0">
                <div class="main-content">
                    <?= self::render_section_header("Anonymous journey", "Explore the journey of an anonymous person on your site.") ?>
                    <div class="form-container">
                        <form method="get">
                            <label for="unique_session_id">Unique session ID</label>
                            <input type="hidden" name="page" value="anonymous_journeys">
                            <input style="width:240px;" type="text" name="unique_session_id" id="unique_session_id" value="<?= (string)$unique_session_id ?>">
                            <input class="button button-primary" type="submit" value="View Journey">
                        </form>
                    </div>
                    <?php
                    // If unique_session_id is set, display the user's journey
                    if (!is_null($unique_session_id)) {
                        $journey = Event::get_journey_for_unique_session_id($unique_session_id);

                        if ($journey && isset($journey['unique_session_id'])) {
                            echo self::render_anonymous_journey_html($journey);
                        } else {
                    ?>
                            <div class="no-journey">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                                <span>No journey(s) found for <span class="font-code">Unique Session ID = <?= esc_html($unique_session_id) ?></span>.</span>
                            </div>
                </div>
            </div>
        </div>
        </div>

<?php
                        }
                    }

                    return ob_get_clean(); // Get and clean the buffer
                }

                /**
                 * Renders a page that allows the input of a unique_session_id, and then displays that journey.
                 * 
                 * @return string
                 */
                public static function render_event_id_journeys_page()
                {
                    ob_start(); // Start output buffering
?>
<?php
                    $event_id = isset($_GET['event_id']) ? Validators::int($_GET['event_id']) : null;

                    // TODO styles
                    echo self::render_admin_styles();

?>

<div class="page-wrapper">
    <?= self::render_navbar() ?>
    <div class="container" style="gap:0">
        <div class="main-content">
            <?= self::render_section_header("Journey for specific event", "Explore the journey which contains a specific event.") ?>
            <div class="form-container">
                <form method="get">
                    <label for="event_id">Event ID</label>
                    <input type="hidden" name="page" value="event_id_journeys">
                    <input style="width:240px;" type="text" name="event_id" id="event_id" value="<?= (string)$event_id ?>">
                    <input class="button button-primary" type="submit" value="View Journey">
                </form>
            </div>
            <?php
                    // If event_id is set, display the user's journey
                    if (!is_null($event_id)) {
                        $journey = Event::get_journey_for_event_id($event_id);

                        if ($journey && isset($journey['user_id'])) {
                            echo self::render_identified_journey_html($journey, ['highlight_event_id' => $event_id]);
                        } elseif ($journey && isset($journey['unique_session_id'])) {
                            echo self::render_anonymous_journey_html($journey, ['highlight_event_id' => $event_id]);
                        } else {
            ?>
                    <div class="no-journey">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <span>No journey(s) found which contain and Event with <span class="font-code">Event ID = <?= esc_html((string)$event_id) ?></span>.</span>
                    </div>
        </div>
    </div>
</div>
</div>

<?php
                        }
                    }

                    return ob_get_clean(); // Get and clean the buffer
                }



                /**
                 * Renders a page that allows the input of a user_id, and then displays that user's journey.
                 * 
                 * @return string
                 */
                public static function render_user_journeys_page()
                {
                    ob_start();
?>
<?php
                    $user_id = isset($_GET['user_id']) ? Validators::int($_GET['user_id']) : null;
?>

<!-- Render admin styles -->
<?= self::render_admin_styles() ?>
<div class="page-wrapper">
    <?= self::render_navbar() ?>
    <div class="container" style="gap:0">
        <div class="main-content">
            <?= self::render_section_header("Journey", "Explore the journey of a specific user on your site.") ?>
            <div class="form-container">
                <form method="get">
                    <label for="user_id">WordPress User ID</label>
                    <input type="hidden" name="page" value="user_journeys">
                    <input type="text" name="user_id" id="user_id" value="<?= (string)$user_id ?>">
                    <input class="button button-primary" type="submit" value="View Journey">
                </form>
            </div>
            <?php
                    // If user_id is set, display the user's journey
                    if (!is_null($user_id)) {

                        $journey = Event::get_journey_for_user_id($user_id);

                        if ($journey && isset($journey['user_id'])) {
                            echo self::render_identified_journey_html($journey);
                        } else {
            ?>
                    <div class="no-journey">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <span>No journey(s) found for the WordPress user with <span class="font-code">'user_id = <?= esc_html((string)$user_id) ?></span>'.</span>
                    </div>
        </div>
    </div>
</div>

<?php
                        }
                    }

                    $html = ob_get_clean();

                    return $html;
                }



                /**
                 * Renders a page that allows the input of a conversion_id, and then displays that conversions's journey.
                 * 
                 * @return string
                 */
                public static function render_conversion_journeys_page()
                {
                    ob_start();
                    $conversion_id = isset($_GET['conversion_id']) ? Validators::int($_GET['conversion_id']) : null;
                    $conversion_type = isset($_GET['conversion_type']) ? Validators::str($_GET['conversion_type']) : null;
?>

<?= self::render_admin_styles() ?>
<div class="page-wrapper">
    <?= self::render_navbar() ?>
    <div class="container" style="gap:0">
        <div class="main-content">
            <?= self::render_section_header("Conversion journey", "Explore the journey of a user with a specific Conversion event on your site.") ?>

            <div class="form-container">
                <form method="get">
                    <label for="conversion_id">Enter Conversion ID:</label>
                    <input type="hidden" name="page" value="conversion_journeys">
                    <input type="text" name="conversion_id" id="conversion_id" value="<?= (string)$conversion_id ?>">
                    <label for="conversion_type">Conversion Type:</label>
                    <?php echo self::render_select_input_for_conversion_type($conversion_type) ?>
                    <input class="button button-primary" type="submit" value="View Journey">
                </form>
            </div>

            <?php
                    // If conversion_id is set, display the journey
                    if (!is_null($conversion_id) && !is_null($conversion_type)) {
                        $journey = Event::get_journey_for_conversion_id($conversion_id, $conversion_type);

                        if ($journey) {
                            echo self::render_anonymous_or_identified_journey_html($journey);
                        } else {
            ?>
                    <div class="no-journey">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <span>No journey(s) found for conversion_id <?= esc_html((string)$conversion_id) ?>.</span>
                    </div>
            <?php
                        }
                    }
            ?>

        </div>
    </div>
</div>

<?php
                    $html = ob_get_clean();
                    return $html;
                }

                public static function render_select_input_for_conversion_type($selected_conversion_type)
                {
                    $conversion_types = Event::get_unique_conversion_types();

                    $html = '<select name="conversion_type" id="conversion_type">';
                    foreach ($conversion_types as $conversion_type) {
                        $selected = $selected_conversion_type === $conversion_type ? 'selected' : '';
                        $html .= "<option value=\"$conversion_type\" $selected>$conversion_type</option>";
                    }
                    $html .= '</select>';

                    return $html;
                }



                /**
                 * @param AnonymousJourney|IdentifiedJourney $journey
                 * 
                 * @return string
                 */
                public static function render_anonymous_or_identified_journey_html($journey)
                {
                    if (Event::is_identified_journey($journey)) {
                        return self::render_identified_journey_html($journey);
                    } elseif (Event::is_anonymous_journey($journey)) {
                        return self::render_anonymous_journey_html($journey);
                    } else {
                        return '<p>Unknown journey type.</p>';
                    }
                }


                /**
                 * @param SourceData $source_data
                 * @return string
                 */
                public static function render_source_data_html($source_data)
                {
                    $output = '<div class="source-data-list">';
                    $fields = [
                        'referrer' => 'Referrer',
                        'landing_page' => 'Landing page',
                        'utm_source' => 'Source',
                        'utm_medium' => 'Medium',
                        'utm_campaign' => 'Campaign',
                        'utm_term' => 'Term',
                        'source_event_id' => 'Source Event ID',
                    ];

                    foreach ($fields as $key => $label) {
                        $value = $source_data[$key];
                        $is_non_empty_string = is_string($value) && !empty($value);
                        if ($is_non_empty_string) {
                            $escaped_value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                            $truncated_value = mb_strimwidth($escaped_value, 0, 80, '...');
                            $output .= "<div><strong>$label:</strong> <span class=\"analyticswp-truncate\" title=\"$value\">$truncated_value</span></div>";
                        } elseif ($key === 'referrer') { // If referrer is empty, show "Direct"
                            $output .= "<div><strong>$label:</strong> <em>Direct</em></div>";
                        }
                    }

                    $output .= '</div>';

                    return $output;
                }

                /**
                 * @return string
                 */
                public static function render_aggregate_stats()
                {
                    $aggregate_stats_data = Event::get_aggregate_stats_data();
                    $totalJourneysTooltip = self::tt("The cumulative count of all people tracked on the platform, both identified and anonymous.");
                    $identifiedJourneysTooltip = self::tt("The number of people where the person has been specifically identified, for instance, by logging in.");
                    $anonymousJourneysTooltip = self::tt("The number of people where the persons's identity remains unknown.");
                    $totalEventsTooltip = self::tt("The aggregate count of all user interactions or actions tracked on the platform, such as page views and conversions.");

                    ob_start();
?>
    <div class="stats-container">
        <div class="stat">
            <div class="stat-item">Total people <?php echo ($totalJourneysTooltip) ?></div>
            <div class="stat-number"><?php echo (number_format($aggregate_stats_data['total_journeys'])) ?></div>
        </div>
        <div class="stat">
            <div class="stat-item">Identified people <?php echo ($identifiedJourneysTooltip) ?></div>
            <div class="stat-number"><?php echo (number_format($aggregate_stats_data['identified_journeys'])) ?></div>
        </div>
        <div class="stat">
            <div class="stat-item">Anonymous people <?php echo ($anonymousJourneysTooltip) ?></div>
            <div class="stat-number"><?php echo (number_format($aggregate_stats_data['anonymous_journeys'])) ?></div>
        </div>
        <div class="stat">
            <div class="stat-item">Total events <?php echo ($totalEventsTooltip) ?></div>
            <div class="stat-number"><?php echo (number_format($aggregate_stats_data['total_events'])) ?></div>
        </div>
    </div>

<?php
                    return ob_get_clean();
                }

                /**
                 * Renders a tooltip (tt)
                 *
                 * @param string $string
                 * 
                 * @return string
                 */
                public static function tt($string)
                {
                    return '<div class="tooltip">
        <svg xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4">
        <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0Zm-6 3.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.293 5.293a1 1 0 1 1 .99 1.667c-.459.134-1.033.566-1.033 1.29v.25a.75.75 0 1 0 1.5 0v-.115a2.5 2.5 0 1 0-2.518-4.153.75.75 0 1 0 1.061 1.06Z" clip-rule="evenodd" />
        </svg>

                  <span class="tooltip-text">' . htmlspecialchars($string) . '</span>
               </div>';
                }

                /**
                 * @param string $inner_elem
                 * @param string $tooltip_content
                 * @param string $class_string_to_add
                 * @return string
                 */
                public static function wrapped_tt($inner_elem, $tooltip_content, $class_string_to_add = '')
                {
                    return '<span class="tooltip ' . $class_string_to_add . '">' . $inner_elem . '
                  <span class="tooltip-text">' . ($tooltip_content) . '</span>
               </span>';
                }

                /**
                 * Render the navbar component.
                 *
                 * @return string HTML content of the navbar.
                 */
                public static function render_navbar()
                {
                    ob_start();
?>
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
                <span class="opaque">Journeys</span>
            </div>
        </div>
        <div class="opaque">
            <?php
                    echo ('v' . ANALYTICSWP_VERSION) ?>
        </div>
    </div>
<?php
                    return ob_get_clean();
                }


                /**
                 * Render the page header component.
                 *
                 * @param string $title The title of the page header.
                 * @param string $description The description of the page header.
                 * @return string HTML content of the page header.
                 */
                public static function render_section_header($title, $description)
                {
                    ob_start();
?>
    <div class="section-header">
        <h2><?php echo htmlspecialchars($title); ?></h2>
        <p><?php echo htmlspecialchars($description); ?></p>
    </div>
<?php
                    return ob_get_clean();
                }

                /**
                 * Generates HTML for the journey filters form based on current URL parameters.
                 * 
                 * @param array{date_range: 'Last 30 days'|'Last 7 days'|'All time', min_events: int} $current_args The current filters from the URL.
                 * 
                 * @return string The HTML markup for the filters form.
                 */
                public static function render_journey_filters($current_args): string
                {
                    // Define date range options
                    $dateRangeOptions = [
                        'Last 30 days' => 'Last 30 days',
                        'Last 7 days' => 'Last 7 days',
                        'All time' => 'All time',
                    ];

                    // Get current values
                    $currentRange = $current_args['date_range'];
                    $currentMinEvents = $current_args['min_events'];

                    // Start building the form HTML
                    $formHtml = '<form class="awp-filters" method="GET" action="">';

                    // Preserve existing query parameters, excluding date_range and min_events
                    foreach ($_GET as $key => $value) {
                        if (!in_array($key, ['date_range', 'min_events']) && is_string($value)) {
                            $formHtml .= sprintf(
                                '<input type="hidden" name="%s" value="%s"/>',
                                $key,
                                $value
                            );
                        }
                    }

                    // Add the date range select field
                    $formHtml .= '<label for="min-events">Date range<select name="date_range">';
                    foreach ($dateRangeOptions as $value => $label) {
                        $selected = $currentRange === $value ? ' selected' : '';
                        $formHtml .= sprintf(
                            '<option value="%s"%s>%s</option>',
                            esc_attr($value),
                            $selected,
                            esc_html($label)
                        );
                    }
                    $formHtml .= '</select></label>';

                    // Add the minimum events input field
                    $formHtml .= sprintf(
                        '<label for="min-events">Minimum events<input type="number" name="min_events" placeholder="Minimum events" value="%s"/></label>',
                        esc_attr(strval($currentMinEvents))
                    );

                    // Add the submit button
                    $formHtml .= '<input class="button button-secondary" type="submit" value="Filter"/>';

                    // Close the form tag
                    $formHtml .= '</form>';

                    return $formHtml;
                }

                /**
                 * Render the People & Journeys page.
                 *
                 * @return string HTML content of the page.
                 */
                public static function render_dots_page()
                {
                    $date_range = isset($_GET['date_range']) ? $_GET['date_range'] : 'Last 30 days';
                    $date_range = Validators::one_of(['Last 30 days', 'Last 7 days', 'All time'], 'Last 30 days', $date_range);
                    // get filters from url params. filters include:
                    $filters = [
                        'date_range' => $date_range,
                        'min_events' => isset($_GET['min_events']) ? Validators::int($_GET['min_events']) : 0,
                    ];

                    ob_start();

?>
    <!-- Render admin styles -->
    <?php echo self::render_admin_styles(); ?>
    <div class="page-wrapper">
        <?php echo self::render_navbar(); ?>
        <div class="container">
            <!-- Render aggregate stats -->
            <?php echo self::render_aggregate_stats(); ?>
            <div class="main-content">
                <!-- Page header -->
                <?php echo self::render_section_header("Journeys", "Explore visitor activity on your site here. Click on any dot to view that visitor's journey."); ?>

                <!-- Render journey filters -->
                <?php echo self::render_journey_filters($filters); ?>

                <!-- Render journey dots -->
                <?php echo self::render_journey_dots($filters); ?>
            </div>

            <div class="sidebar">
                <?php echo self::render_helper_component_1(); ?>
            </div>
        </div>
    </div>
<?php

                    // Get the buffered output and clean the buffer
                    $html = ob_get_clean();

                    return $html;
                }




                /**
                 * @param bool $is_agency_mode
                 * @return string
                 */
                public static function render_stats_main_page($is_agency_mode = false)
                {
                    $html = '';

                    $html .= "
        <style>
          #wpcontent {
            padding-left: 0px !important;
          }
          #wpfooter {display: none!important;}
          #wpbody-content {padding-bottom: 0!important;}
        </style>
        ";

                    ////////////////////////////////////////////////
                    // Production
                    ////////////////////////////////////////////////
                    // Load the main JavaScript file of the React app
                    // Path to the react-app.html
                    // Use an iframe to embed the React app

                    $nonce = wp_create_nonce('analyticswp_get_stats');
                    $admin_ajax_url = admin_url('admin-ajax.php');
                    $encoded_admin_ajax_url = base64_encode($admin_ajax_url);

                    $is_active = License::is_active();

                    // Get the current complete URL
                    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    // URL encode it to safely use in query string
                    $encoded_current_url = urlencode($current_url);

                    // $site_license_key = License::get_license_key();
                    // $agency_mode_timestamp = time();
                    // $agency_mode_hash = hash_hmac('sha256', $agency_mode_timestamp, $site_license_key);
                    [
                        'agency_mode_timestamp' => $agency_mode_timestamp,
                        'agency_mode_hash' => $agency_mode_hash
                    ] = AgencyMode::generate_timestamp_and_hash();

                    /** 
                     * @psalm-suppress RedundantCondition - This one is ok.
                     * @psalm-suppress TypeDoesNotContainType - This one is ok. */
                    if (self::IS_PRODUCTION_MODE) {
                        $reactapphtmlpath = plugin_dir_url(__file__) . 'react-app.html' .
                            '?analyticswp_get_stats_nonce=' . $nonce .
                            '&admin_ajax_url=' . $encoded_admin_ajax_url .
                            '&parent_url=' . $encoded_current_url .  // Add the current URL;
                            '&agency_mode_timestamp=' . $agency_mode_timestamp .
                            '&agency_mode_hash=' . $agency_mode_hash;

                        if (!$is_active) {
                            $reactapphtmlpath .= '&license_status=inactive';
                        }
                        if ($is_agency_mode) {
                            $reactapphtmlpath .= '&agency_mode=true';
                        }
                        $html .= '<iframe src="' . $reactapphtmlpath . '" style="width: 100%; height: 1500px;"></iframe>';
                    } else {
                        ////////////////////////////////////////////////
                        // Development
                        ////////////////////////////////////////////////
                        // iframe in from: http://localhost:5173/
                        $reactapphtmlpath = 'http://localhost:5173/' .
                            '?analyticswp_get_stats_nonce=' . $nonce .
                            '&parent_url=' . $encoded_current_url .  // Add the current URL
                            '&agency_mode_timestamp=' . $agency_mode_timestamp .
                            '&agency_mode_hash=' . $agency_mode_hash;
                        if (!$is_active) {
                            $reactapphtmlpath .= '&license_status=inactive';
                        }
                        if ($is_agency_mode) {
                            $reactapphtmlpath .= '&agency_mode=true';
                        }
                        $html .= '<div style="position:fixed; bottom:20px; background:black; padding:20px; color:white; right: 20px; font-size:14px;">DEVELOPMENT MODE</div><iframe src="' . $reactapphtmlpath . '" width="100%" height="1500px" style="border: 1px dashed blue;"></iframe>';
                    }
                    return $html;
                }


                /**
                 * @return string
                 */
                public static function render_client_management_page()
                {
                    $reactPage = 'client_management';

                    $html = '';

                    $html .= "
        <style>
          #wpcontent {
            padding-left: 0px !important;
          }
          #wpfooter {display: none!important;}
          #wpbody-content {padding-bottom: 0!important;}
        </style>
        ";

                    ////////////////////////////////////////////////
                    // Production
                    ////////////////////////////////////////////////
                    // Load the main JavaScript file of the React app
                    // Path to the react-app.html
                    // Use an iframe to embed the React app

                    $nonce = wp_create_nonce('analyticswp_get_stats');
                    $admin_ajax_url = admin_url('admin-ajax.php');
                    $encoded_admin_ajax_url = base64_encode($admin_ajax_url);

                    $is_active = License::is_active();


                    // Get the current complete URL
                    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    // URL encode it to safely use in query string
                    $encoded_current_url = urlencode($current_url);

                    /** 
                     * @psalm-suppress RedundantCondition - This one is ok.
                     * @psalm-suppress TypeDoesNotContainType - This one is ok. */
                    if (self::IS_PRODUCTION_MODE) {
                        $reactapphtmlpath = plugin_dir_url(__file__) . 'react-app.html' .
                            '?analyticswp_get_stats_nonce=' . $nonce .
                            '&admin_ajax_url=' . $encoded_admin_ajax_url .
                            '&parent_url=' . $encoded_current_url .  // Add the current URL;
                            '&react_page=' . $reactPage;

                        if (!$is_active) {
                            $reactapphtmlpath .= '&license_status=inactive';
                        }
                        $html .= '<iframe src="' . $reactapphtmlpath . '" style="width: 100%; height: 1500px;"></iframe>';
                    } else {
                        ////////////////////////////////////////////////
                        // Development
                        ////////////////////////////////////////////////
                        // iframe in from: http://localhost:5173/
                        $reactapphtmlpath = 'http://localhost:5173/' .
                            '?analyticswp_get_stats_nonce=' . $nonce .
                            '&parent_url=' . $encoded_current_url .  // Add the current URL
                            '&react_page=' . $reactPage;
                        if (!$is_active) {
                            $reactapphtmlpath .= '&license_status=inactive';
                        }
                        $html .= '<div style="position:fixed; bottom:20px; background:black; padding:20px; color:white; right: 20px; font-size:14px;">DEVELOPMENT MODE</div><iframe src="' . $reactapphtmlpath . '" width="100%" height="1500px" style="border: 1px dashed blue;"></iframe>';
                    }



                    return $html;
                }



                /**
                 * @return string
                 */
                public static function render_live_events_page()
                {
                    $html = '';

                    $html .= "
        <style>
          #wpcontent {
            padding-left: 0px !important;
          }
          #wpfooter {display: none!important;}
          #wpbody-content {padding-bottom: 0!important;}
        </style>
        ";

                    ////////////////////////////////////////////////
                    // Production
                    ////////////////////////////////////////////////
                    // Load the main JavaScript file of the React app
                    // Path to the react-app.html
                    // Use an iframe to embed the React app

                    $nonce = wp_create_nonce('analyticswp_get_stats');
                    $admin_ajax_url = admin_url('admin-ajax.php');
                    $encoded_admin_ajax_url = base64_encode($admin_ajax_url);

                    $is_active = License::is_active();


                    // Get the current complete URL
                    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    // URL encode it to safely use in query string
                    $encoded_current_url = urlencode($current_url);

                    $reactPage = 'live-events';

                    /** 
                     * @psalm-suppress RedundantCondition - This one is ok.
                     * @psalm-suppress TypeDoesNotContainType - This one is ok. */
                    if (self::IS_PRODUCTION_MODE) {
                        $reactapphtmlpath = plugin_dir_url(__file__) . 'react-app.html' .
                            '?analyticswp_get_stats_nonce=' . $nonce .
                            '&react_page=' . $reactPage .
                            '&admin_ajax_url=' . $encoded_admin_ajax_url .
                            '&parent_url=' . $encoded_current_url;  // Add the current URL;

                        if (!$is_active) {
                            $reactapphtmlpath .= '&license_status=inactive';
                        }
                        $html .= '<iframe src="' . $reactapphtmlpath . '" style="width: 100%; height: 1500px;"></iframe>';
                    } else {
                        ////////////////////////////////////////////////
                        // Development
                        ////////////////////////////////////////////////
                        // iframe in from: http://localhost:5173/
                        $reactapphtmlpath = 'http://localhost:5173/' .
                            '?analyticswp_get_stats_nonce=' . $nonce .
                            '&react_page=' . $reactPage .
                            '&parent_url=' . $encoded_current_url;  // Add the current URL
                        if (!$is_active) {
                            $reactapphtmlpath .= '&license_status=inactive';
                        }
                        $html .= '<div style="position:fixed; bottom:20px; background:black; padding:20px; color:white; right: 20px; font-size:14px;">DEVELOPMENT MODE</div><iframe src="' . $reactapphtmlpath . '" width="100%" height="1500px" style="border: 1px dashed blue;"></iframe>';
                    }



                    return $html;
                }

                /**
                 * @return string
                 */
                public static function render_integrations_page()
                {
                    $html = '';

                    $html .= "
        <style>
          #wpcontent {
            padding-left: 0px !important;
          }
          #wpfooter {display: none!important;}
          #wpbody-content {padding-bottom: 0!important;}
        </style>
        ";

                    ////////////////////////////////////////////////
                    // Production
                    ////////////////////////////////////////////////
                    // Load the main JavaScript file of the React app
                    // Path to the react-app.html
                    // Use an iframe to embed the React app

                    $nonce = wp_create_nonce('analyticswp_get_stats');
                    $admin_ajax_url = admin_url('admin-ajax.php');
                    $encoded_admin_ajax_url = base64_encode($admin_ajax_url);

                    $is_active = License::is_active();


                    // Get the current complete URL
                    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    // URL encode it to safely use in query string
                    $encoded_current_url = urlencode($current_url);

                    $reactPage = 'integrations';

                    /** 
                     * @psalm-suppress RedundantCondition - This one is ok.
                     * @psalm-suppress TypeDoesNotContainType - This one is ok. */
                    if (self::IS_PRODUCTION_MODE) {
                        $reactapphtmlpath = plugin_dir_url(__file__) . 'react-app.html' .
                            '?analyticswp_get_stats_nonce=' . $nonce .
                            '&react_page=' . $reactPage .
                            '&admin_ajax_url=' . $encoded_admin_ajax_url .
                            '&parent_url=' . $encoded_current_url;  // Add the current URL;

                        if (!$is_active) {
                            $reactapphtmlpath .= '&license_status=inactive';
                        }
                        $html .= '<iframe src="' . $reactapphtmlpath . '" style="width: 100%; height: 1500px;"></iframe>';
                    } else {
                        ////////////////////////////////////////////////
                        // Development
                        ////////////////////////////////////////////////
                        // iframe in from: http://localhost:5173/
                        $reactapphtmlpath = 'http://localhost:5173/' .
                            '?analyticswp_get_stats_nonce=' . $nonce .
                            '&react_page=' . $reactPage .
                            '&parent_url=' . $encoded_current_url;  // Add the current URL
                        if (!$is_active) {
                            $reactapphtmlpath .= '&license_status=inactive';
                        }
                        $html .= '<div style="position:fixed; bottom:20px; background:black; padding:20px; color:white; right: 20px; font-size:14px;">DEVELOPMENT MODE</div><iframe src="' . $reactapphtmlpath . '" width="100%" height="1500px" style="border: 1px dashed blue;"></iframe>';
                    }



                    return $html;
                }


                /**
                 * Renders the deactivated page for AnalyticsWP.
                 *
                 * @param bool $header_mode Whether to adjust styling for header mode.
                 * @return string The HTML content for the deactivated page.
                 */
                public static function render_deactivated_page(bool $header_mode = false): string
                {
                    $margin_top = $header_mode ? '20px' : '200px';
                    $style = "
                <style>
                    .notice, div.updated {
                        display: none;
                    }
                    .awp-inactive-notice {
                        max-width: 100%;
                        padding: 20px 40px;
                        background: #1d2327;
                        border: 1px solid #ddd;
                        border-radius: 0;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 20px;
                        box-sizing: border-box;
                    }
                    .awp-inactive-notice .left {
                        display: flex;
                        align-items: center;
                        gap: 16px;
                        flex-grow: 1;
                    }
                    .awp-inactive-notice .icon {
                        flex-shrink: 0;
                    }
                    .awp-inactive-notice .section-title {
                        color: #fff;
                        font-size: 14px;
                        font-weight: 600;
                        margin: 0;
                    }
                    .awp-inactive-notice .message {
                        font-size: 14px;
                        font-weight: 400;
                        line-height: 1.6;
                        color: rgb(176, 176, 176);
                        margin: 0;
                    }
                    .awp-inactive-notice .link {
                        width: 100%;
                        display: block;
                        padding: 12px 25px;
                        background-color: #46E986;
                        color: #131313;
                        text-decoration: none;
                        border-radius: 8px;
                        font-size:14px;
                        transition: background-color 0.3s ease;
                        box-sizing: border-box;
                        text-align:center;
                    }
                    .awp-inactive-notice .link:hover {
                        background-color: rgb(88, 220, 141);
                        color: #131313;
                    }
                    .awp-inactive-notice .footer-message {
                        font-size: 11px;
                        color: rgba(255, 255, 255, 0.6);
                        margin-top: 10px;
                        line-height:18px;
                        margin:6px 0;
                    }
                </style>";

                    $svg_icon = '
                <svg class="icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 12V18M18 24H18.015M4.5 18C4.5 19.7728 4.84919 21.5283 5.52763 23.1662C6.20606 24.8041 7.20047 26.2923 8.45406 27.5459C9.70765 28.7995 11.1959 29.7939 12.8338 30.4724C14.4717 31.1508 16.2272 31.5 18 31.5C19.7728 31.5 21.5283 31.1508 23.1662 30.4724C24.8041 29.7939 26.2923 28.7995 27.5459 27.5459C28.7995 26.2923 29.7939 24.8041 30.4724 23.1662C31.1508 21.5283 31.5 19.7728 31.5 18C31.5 14.4196 30.0777 10.9858 27.5459 8.45406C25.0142 5.92232 21.5804 4.5 18 4.5C14.4196 4.5 10.9858 5.92232 8.45406 8.45406C5.92232 10.9858 4.5 14.4196 4.5 18Z" stroke="#46E986" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>';

                    $html = $style;
                    $html .= '<div class="awp-inactive-notice">';
                    $html .= '<div class="left">';
                    $html .= $svg_icon;
                    $html .= '<div>';
                    $html .= '<h2 class="section-title">AnalyticsWP is deactivated</h2>';
                    $html .= '<p class="message">You are just one step away from unlocking the full potential of AnalyticsWP. Activate your license now to see all your analytics data.</p>';
                    $html .= '</div>';
                    $html .= '</div>';
                    $html .= '<div>';
                    $html .= '<a href="' . esc_url(admin_url('admin.php?page=analyticswp-license-key-options')) . '" class="link">Activate now</a>';
                    if ($header_mode) {
                        $html .= '<p class="footer-message">You can use some limited functionality below.</p>';
                    }
                    $html .= '</div>';
                    $html .= '</div>';

                    return $html;
                }

                public static function render_welcome_page(): string
                {
                    ob_start();
?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Libre+Baskerville:opsz@12..24&family=Inter:wght@400;500;600&display=swap');

        .notice.has-action.trial {
            display: none;
        }


        body {
            background: #181A1B;
            color: #fff;
            font-family: "Inter", sans-serif;
        }

        h1 {
            font-size: 40px;
            font-weight: 300;
            line-height: 50px;
            font-family: "Libre Baskerville", serif;
            color: #fff;
        }

        .analytics-wp-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .analyticswp-container-welcome {
            margin-top: 40px;
            max-width: 800px;
            display: flex;
            gap: 20px;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .embed-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            max-width: 100%;
            margin: 40px;
            border: 10px solid #f4dfca;
        }

        .analytics-wp-action {
            display: flex;
            justify-content: center;
        }

        .analyticswp-link {
            font-weight: 500;
            padding: 1rem;
            font-size: .875rem;
            color: #131313;
            background: #46E987;
            text-decoration: none;
            border-radius: 1rem;
            transition: all 200ms ease-in-out;
        }

        .analyticswp-link:hover {
            color: white;
            background: #3CD478;
        }

        .embed-container iframe,
        .embed-container object,
        .embed-container embed {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        p {
            color: rgba(255, 255, 255, .8);
            font-size: 13px;
            line-height: 20px;
            font-weight: 400;
        }

        #wpfooter {
            display: none;
        }

        .analyticswp-logo {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .mike-pulse {
            border-left: 1px solid rgba(255, 255, 255, .2);
            padding-left: 20px;
            height: 20px;
            display: flex;
            align-items: center;
        }

        .dot {
            width: 10px;
            height: 10px;
            background-color: #3CD478;
            border-radius: 50%;
            animation: pulse 1s infinite alternate;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            100% {
                transform: scale(1.5);
                opacity: .5;
            }
        }
    </style>
    <div class="analytics-wp-wrapper">

        <div class="analyticswp-container-welcome">


            <div class="analyticswp-logo">
                <svg width="198" height="19" viewBox="0 0 198 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M79.1463 10.6313C79.1463 10.0572 79.0876 9.54827 78.9702 9.1046C78.8658 8.64788 78.6831 8.26293 78.4221 7.94975C78.1742 7.63657 77.8414 7.39516 77.4239 7.22552C77.0193 7.05588 76.5104 6.97106 75.8971 6.97106C75.3099 6.97106 74.7814 7.06241 74.3116 7.2451C73.8549 7.41473 73.47 7.66267 73.1568 7.9889C72.8436 8.30208 72.6022 8.6805 72.4326 9.12417C72.276 9.56784 72.1977 10.0507 72.1977 10.5726V15.7597H69.7901V5.32687L72.1781 5.07241V8.26293H72.2172C72.3216 7.87145 72.4782 7.4865 72.687 7.10808C72.8958 6.7166 73.1698 6.3708 73.5091 6.07067C73.8614 5.75749 74.2921 5.50956 74.801 5.32687C75.3229 5.13113 75.9493 5.03326 76.6801 5.03326C77.5804 5.03326 78.3373 5.16376 78.9506 5.42474C79.577 5.67267 80.0794 6.025 80.4578 6.48172C80.8493 6.93844 81.1298 7.49303 81.2995 8.14549C81.4691 8.79794 81.5539 9.51565 81.5539 10.2986V15.7597H79.1463V10.6313Z" fill="white" />
                    <path d="M95.9406 15.7597H93.5526V13.7827L93.5722 12.8628H93.5135C93.396 13.2151 93.2199 13.5739 92.985 13.9393C92.7631 14.3047 92.4695 14.6374 92.1041 14.9376C91.7518 15.2247 91.3212 15.4595 90.8123 15.6422C90.3034 15.8249 89.7096 15.9163 89.0311 15.9163C88.209 15.9163 87.4782 15.7858 86.8388 15.5248C86.1994 15.2507 85.6579 14.8788 85.2142 14.4091C84.7836 13.9263 84.4508 13.3651 84.2159 12.7257C83.9941 12.0733 83.8832 11.3752 83.8832 10.6313V10.3182C83.8832 9.57437 83.9941 8.88276 84.2159 8.24336C84.4508 7.5909 84.7836 7.02978 85.2142 6.56001C85.6579 6.0772 86.1994 5.7053 86.8388 5.44431C87.4782 5.17028 88.209 5.03326 89.0311 5.03326C89.7096 5.03326 90.3034 5.12461 90.8123 5.3073C91.3212 5.48998 91.7518 5.73139 92.1041 6.03152C92.4695 6.31861 92.7631 6.64483 92.985 7.01021C93.2199 7.37559 93.396 7.73444 93.5135 8.08677H93.5722L93.5526 5.32687L95.9406 5.07241V15.7597ZM86.2712 10.5726C86.2712 11.5905 86.5778 12.4126 87.1911 13.0389C87.8045 13.6653 88.6787 13.9785 89.814 13.9785C90.3751 13.9785 90.8841 13.8871 91.3408 13.7044C91.7975 13.5087 92.189 13.2542 92.5152 12.9411C92.8414 12.6279 93.0959 12.2625 93.2786 11.8449C93.4613 11.4143 93.5526 10.9641 93.5526 10.4943V10.3573C93.5265 9.9006 93.4221 9.46998 93.2394 9.06545C93.0567 8.64788 92.8023 8.28903 92.4761 7.9889C92.1498 7.67572 91.7583 7.42778 91.3016 7.2451C90.858 7.06241 90.3621 6.97106 89.814 6.97106C88.6787 6.97106 87.8045 7.28424 87.1911 7.9106C86.5778 8.53696 86.2712 9.35906 86.2712 10.3769V10.5726Z" fill="white" />
                    <path d="M98.9595 2.39081L101.367 2.13635V15.7597H98.9595V2.39081Z" fill="white" />
                    <path d="M110.985 15.4073C110.763 15.9424 110.515 16.4056 110.241 16.7971C109.98 17.1885 109.674 17.5083 109.321 17.7562C108.982 18.0172 108.597 18.2064 108.166 18.3238C107.736 18.4413 107.246 18.5 106.698 18.5C106.085 18.5 105.589 18.4282 105.211 18.2847C104.845 18.1542 104.552 17.9845 104.33 17.7758V15.838H104.408C104.63 16.112 104.904 16.3273 105.23 16.4839C105.57 16.6535 105.948 16.7384 106.366 16.7384H106.444C106.927 16.7384 107.299 16.6405 107.56 16.4447C107.821 16.249 107.951 15.988 107.951 15.6618C107.951 15.4139 107.905 15.1725 107.814 14.9376C107.736 14.7027 107.644 14.4743 107.54 14.2525L103.136 5.32687L105.661 5.07241L109.595 13.5674L113.177 5.32687L115.722 5.07241L110.985 15.4073Z" fill="white" />
                    <path d="M120.697 7.06893V11.7079C120.697 12.4909 120.827 13.0389 121.088 13.3521C121.349 13.6522 121.8 13.8023 122.439 13.8023C122.909 13.8023 123.268 13.7501 123.516 13.6457C123.777 13.5283 123.972 13.4173 124.103 13.313H124.201V15.3486C124.031 15.4922 123.77 15.6226 123.418 15.7401C123.078 15.8575 122.628 15.9163 122.067 15.9163C120.84 15.9163 119.901 15.6096 119.248 14.9963C118.609 14.3699 118.289 13.4239 118.289 12.1581V7.06893H116.586V5.11156H118.289V2.84101L120.286 2.58655H120.697V5.11156H124.103V7.06893H120.697Z" fill="white" />
                    <path d="M126.294 4.035V2.39081L128.702 2.13635V3.78055L126.294 4.035ZM126.294 5.32687L128.702 5.07241V15.7597H126.294V5.32687Z" fill="white" />
                    <path d="M133.504 10.5922C133.504 11.61 133.883 12.4256 134.64 13.0389C135.396 13.6392 136.473 13.9393 137.869 13.9393C138.248 13.9393 138.62 13.9067 138.985 13.8414C139.363 13.7762 139.716 13.6849 140.042 13.5674C140.368 13.45 140.662 13.3195 140.923 13.1759C141.184 13.0193 141.399 12.8497 141.569 12.667H141.647V14.8006C141.308 15.0876 140.805 15.3486 140.14 15.5835C139.474 15.8053 138.6 15.9163 137.517 15.9163C136.525 15.9163 135.631 15.7923 134.835 15.5444C134.039 15.2964 133.361 14.9441 132.8 14.4874C132.239 14.0307 131.808 13.4761 131.508 12.8236C131.208 12.1581 131.058 11.4143 131.058 10.5922V10.3573C131.058 9.53522 131.208 8.79794 131.508 8.14549C131.808 7.47998 132.239 6.91887 132.8 6.46215C133.361 6.00543 134.039 5.6531 134.835 5.40516C135.631 5.15723 136.525 5.03326 137.517 5.03326C138.574 5.03326 139.442 5.15071 140.12 5.38559C140.799 5.60743 141.308 5.86189 141.647 6.14897V8.2825H141.569C141.399 8.11286 141.184 7.94975 140.923 7.79316C140.662 7.63657 140.368 7.49955 140.042 7.38211C139.729 7.26467 139.383 7.17333 139.004 7.10808C138.639 7.04283 138.261 7.01021 137.869 7.01021C136.473 7.01021 135.396 7.31687 134.64 7.93018C133.883 8.53044 133.504 9.33948 133.504 10.3573V10.5922Z" fill="white" />
                    <path d="M148.727 14.0372C149.197 14.0372 149.634 14.0111 150.039 13.9589C150.456 13.8936 150.815 13.8023 151.115 13.6849C151.428 13.5674 151.67 13.4173 151.839 13.2347C152.009 13.052 152.094 12.8301 152.094 12.5691C152.094 12.3343 152.035 12.1451 151.918 12.0015C151.8 11.8449 151.624 11.721 151.389 11.6296C151.154 11.5252 150.861 11.4469 150.508 11.3947C150.156 11.3295 149.745 11.2708 149.275 11.2186L147.2 11.0228C146.13 10.9184 145.302 10.6379 144.715 10.1812C144.127 9.72443 143.834 9.09155 143.834 8.2825C143.834 7.30382 144.258 6.52087 145.106 5.93366C145.954 5.33339 147.253 5.03326 149.001 5.03326C150.019 5.03326 150.919 5.09851 151.702 5.229C152.498 5.34644 153.118 5.51608 153.562 5.73792V7.67572H153.484C153.236 7.51913 152.929 7.38864 152.564 7.28424C152.211 7.17985 151.839 7.10155 151.448 7.04936C151.056 6.98411 150.658 6.93844 150.254 6.91234C149.862 6.88624 149.504 6.87319 149.177 6.87319C148.238 6.87319 147.514 6.97106 147.005 7.1668C146.496 7.34949 146.241 7.66267 146.241 8.10634C146.241 8.48476 146.43 8.75227 146.809 8.90886C147.187 9.06545 147.84 9.18942 148.766 9.28076L150.841 9.4765C151.389 9.5287 151.898 9.62657 152.368 9.77011C152.838 9.9006 153.236 10.0833 153.562 10.3182C153.901 10.5531 154.162 10.8401 154.345 11.1794C154.541 11.5187 154.638 11.9232 154.638 12.393C154.638 13.5152 154.149 14.383 153.17 14.9963C152.192 15.6096 150.763 15.9163 148.884 15.9163C147.527 15.9163 146.424 15.8314 145.576 15.6618C144.741 15.4791 144.095 15.2442 143.638 14.9571V12.8236H143.716C144.134 13.189 144.747 13.4826 145.556 13.7044C146.378 13.9263 147.435 14.0372 148.727 14.0372Z" fill="white" />
                    <path d="M168.288 4.9354L163.943 15.7597H161.378L155.565 2.29294H158.364L162.788 12.8823L167.094 2.29294H169.58L173.867 12.8628L178.31 2.29294H180.933L175.139 15.7597H172.633L168.288 4.9354Z" fill="white" />
                    <path d="M182.803 2.29294H191.376C192.224 2.29294 192.994 2.37776 193.686 2.5474C194.391 2.70399 194.991 2.95192 195.487 3.2912C195.982 3.63048 196.367 4.06763 196.641 4.60264C196.916 5.12461 197.053 5.75749 197.053 6.50129V6.63831C197.053 7.38211 196.916 8.015 196.641 8.53696C196.367 9.05893 195.976 9.48955 195.467 9.82883C194.971 10.1551 194.371 10.3965 193.666 10.5531C192.975 10.7096 192.205 10.7879 191.357 10.7879H185.328V15.7597H182.803V2.29294ZM185.328 4.4069V8.71313H190.867C192.081 8.71313 192.994 8.57611 193.608 8.30208C194.221 8.02804 194.528 7.47998 194.528 6.65788V6.52087C194.528 5.69877 194.221 5.14418 193.608 4.8571C192.994 4.55697 192.081 4.4069 190.867 4.4069H185.328Z" fill="white" />
                    <path d="M51.6942 15.7597L58.3493 2.29297H61.3636L68.0187 15.7597H65.2392L63.595 12.3734H55.9613L54.3367 15.7597H51.6942ZM56.8617 10.4748H62.6946L59.7782 4.4265L56.8617 10.4748Z" fill="white" />
                    <path d="M2.2652 16.8636L0 14.6384L14.1691 0.814685L16.4805 3.03996L2.2652 16.8636ZM15.3017 16.549L6.84184 8.32218L9.24572 6.00699L17.6824 14.2338L15.3017 16.549ZM15.3017 16.549V0.814685H18.5377V16.549H15.3017ZM14.1691 3.96154V0.814685H15.5559V3.96154H14.1691Z" fill="white" />
                    <path d="M18.5255 16.549V12.1209L30.152 0.814685H33.388V12.1209L45.315 0.5L47.6033 2.72528L33.388 16.549H30.152V5.24276L18.5255 16.549Z" fill="white" />
                </svg>

                <div class="mike-pulse">
                    <div class="dot"></div>
                </div>
            </div>

            <h1 class="analyticswp-section-title">There is nothing you need to set up, AnalyticsWP is ready to go.</h1>

            <div class="analytics-wp-action">
                <a href="<?php echo esc_url(admin_url('admin.php?page=analyticswp')) ?>" class="analyticswp-link">Go to dashboard</a>
            </div>
            <p>Please watch this quick video, it will explain everything you need to know.</p>

            <!-- Emebedded youtube video -->
            <div class='embed-container'>
                <iframe src='https://www.youtube.com/embed/RAQYNPFc0Mw?si=Ulg3dFAyoFP2eQs2' frameborder='0' allowfullscreen></iframe>
            </div>

        </div>
    </div>

    </div>

<?php
                    return ob_get_clean();
                }


                public static function render_agency_mode_landing_page(): string
                {
                    ob_start();
?>





    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Libre+Baskerville:ital@0;1&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        #wpcontent {
            padding-left: 0 !important;
        }

        body {
            min-height: 100vh;
            background-color: #fff;
            font-family: 'Inter', sans-serif;
        }

        header {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .logo {
            width: 200px;
        }

        .button-agency {
            background-color: #46e986;
            color: #0e0e0e;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: background-color 0.3s;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
        }

        .button-agency:hover {
            background-color: #3acc73;
            color: #0e0e0e;
        }

        .hero {
            text-align: center;
            max-width: 48rem;
            margin: 3rem auto 0;
            padding: 1.5rem;
        }

        .hero h2 {
            font-family: 'Libre Baskerville', serif;
            line-height: 140%;
            font-size: 3rem;
            color: #171717;
            font-weight: 500;
            margin: 20px 0;
        }

        .hero p {
            font-family: 'Inter', sans-serif;
            font-size: 1.125rem;
            color: #5A5A5A;
            margin-top: 1rem;
        }

        .features {
            max-width: 64rem;
            margin: 0 auto 0;
            padding: 1.5rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .features {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .feature-card {
            padding: 1.5rem;
            border: 1px solid rgba(209, 213, 219, 0.6);
            border-radius: 0.75rem;
            text-align: center;
            display: flex;
            gap: 5px;
            flex-direction: column;
        }

        .feature-icon {
            font-size: 1.875rem;
            color: #46e986;
            line-height: 140%;
            margin-bottom: 10px;
        }

        .feature-card h3 {
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            margin-top: 1rem;
            line-height: 120%;
            margin: 0;
        }

        .feature-card p {
            font-family: 'Inter', sans-serif;
            color: #4b5563;
            margin-top: 0.5rem;
        }

        .pricing {
            display: flex;
            flex-direction: column;
            gap: 20px;
            text-align: center;
            max-width: 64rem;
            margin: 3rem auto 0;
            padding: 3rem;
            border: 1px solid rgb(215, 215, 215);
            border-radius: 20px;
            background: linear-gradient(0deg, rgba(255, 255, 255, 1) 0%, rgba(255, 233, 214, 1) 99.48717948717949%);
        }

        .pricing h2 {
            font-family: "Libre Baskerville", serif;
            font-size: 1.5rem;
            color: #171717;
            font-weight: 500;
            margin: 0;
        }

        .pricing p {
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            color: #5A5A5A;
            margin: 0;
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0.5rem;
        }

        .pricing .button-agency {
            align-self: center;
        }

        .logo-image {
            width: 32px;
            height: 32px;
        }

        .border-divider {
            width: 1px;
            height: 24px;
            background-color: #d2c0b2;
            margin: 0 0.5rem;
        }

        .agency-text {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 16px;
            color: #374151;
        }
    </style>
    </head>

    <body>
        <header>
            <img src="https://analyticswp.com/wp-content/uploads/2024/02/brand.svg" alt="AnalyticsWP Logo" class="logo">
            <div style="text-align: right;">
                <div style="margin-top: 10px; margin-bottom: 20px;">
                    <a href="https://analyticswp.com/agency" class="button-agency">Get Agency Mode</a>
                </div>
                <div>
                    <small style="opacity: 1;">Note: You can hide this page by going to Settings and checking 'Hide the Agency Mode explanation page'.</small>
                </div>
            </div>
        </header>

        <div class="hero">
            <h2><i>Empower</i> your agency with AnalyticsWP</h2>
            <p>Running a WordPress business? Track all your clients in one dashboard, automate reports, and grow your agency with ease.</p>
        </div>
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 3V21H21M20 18V21M16 16V21M12 13V21M8 16V21M3 11C9 11 8 6 12 6C16 6 15 11 21 11" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h3>Aggregated Insights</h3>
                <p>Monitor all your clients' performance from a single dashboard.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 5H6C5.46957 5 4.96086 5.21071 4.58579 5.58579C4.21071 5.96086 4 6.46957 4 7V19C4 19.5304 4.21071 20.0391 4.58579 20.4142C4.96086 20.7893 5.46957 21 6 21H11.697M8 5C8 4.46957 8.21071 3.96086 8.58579 3.58579C8.96086 3.21071 9.46957 3 10 3H12C12.5304 3 13.0391 3.21071 13.4142 3.58579C13.7893 3.96086 14 4.46957 14 5M8 5C8 5.53043 8.21071 6.03914 8.58579 6.41421C8.96086 6.78929 9.46957 7 10 7H12C12.5304 7 13.0391 6.78929 13.4142 6.41421C13.7893 6.03914 14 5.53043 14 5M18 14V18H22M18 14C19.0609 14 20.0783 14.4214 20.8284 15.1716C21.5786 15.9217 22 16.9391 22 18M18 14C16.9391 14 15.9217 14.4214 15.1716 15.1716C14.4214 15.9217 14 16.9391 14 18C14 19.0609 14.4214 20.0783 15.1716 20.8284C15.9217 21.5786 16.9391 22 18 22C19.0609 22 20.0783 21.5786 20.8284 20.8284C21.5786 20.0783 22 19.0609 22 18M18 11V7C18 6.46957 17.7893 5.96086 17.4142 5.58579C17.0391 5.21071 16.5304 5 16 5H14M8 11H12M8 15H11" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h3>Automated Whitelabeled Reports</h3>
                <p>Send detailed, white-labeled, analytics reports to your clients on a weekly or monthly basis.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 21V19C6 17.9391 6.42143 16.9217 7.17157 16.1716C7.92172 15.4214 8.93913 15 10 15H10.5M8 7C8 8.06087 8.42143 9.07828 9.17157 9.82843C9.92172 10.5786 10.9391 11 12 11C13.0609 11 14.0783 10.5786 14.8284 9.82843C15.5786 9.07828 16 8.06087 16 7C16 5.93913 15.5786 4.92172 14.8284 4.17157C14.0783 3.42143 13.0609 3 12 3C10.9391 3 9.92172 3.42143 9.17157 4.17157C8.42143 4.92172 8 5.93913 8 7ZM17.8 20.817L15.628 21.955C15.5635 21.9885 15.491 22.0035 15.4186 21.9982C15.3461 21.9929 15.2765 21.9676 15.2176 21.9251C15.1587 21.8826 15.1127 21.8245 15.0849 21.7574C15.0571 21.6903 15.0485 21.6167 15.06 21.545L15.475 19.134L13.718 17.427C13.6656 17.3763 13.6284 17.3119 13.6108 17.2411C13.5933 17.1703 13.5959 17.096 13.6186 17.0266C13.6412 16.9573 13.6829 16.8957 13.7388 16.8489C13.7948 16.8021 13.8627 16.772 13.935 16.762L16.363 16.41L17.449 14.217C17.4815 14.1517 17.5315 14.0967 17.5935 14.0583C17.6556 14.0199 17.727 13.9995 17.8 13.9995C17.8729 13.9995 17.9444 14.0199 18.0064 14.0583C18.0685 14.0967 18.1185 14.1517 18.151 14.217L19.237 16.41L21.665 16.762C21.737 16.7723 21.8047 16.8027 21.8604 16.8495C21.9162 16.8963 21.9576 16.9578 21.9802 17.027C22.0028 17.0962 22.0056 17.1703 21.9882 17.241C21.9708 17.3117 21.9341 17.3761 21.882 17.427L20.125 19.134L20.539 21.544C20.5514 21.6158 20.5434 21.6898 20.516 21.7573C20.4885 21.8249 20.4426 21.8834 20.3836 21.9262C20.3245 21.969 20.2547 21.9944 20.1819 21.9995C20.1092 22.0046 20.0364 21.9891 19.972 21.955L17.8 20.817Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h3>Client Grouping</h3>
                <p>Easily organize and manage your clients with tagging and grouping.</p>
            </div>
        </div>

        <div class="pricing">
            <div class="logo-container">
                <img src="https://analyticswp.com/wp-content/uploads/2024/02/Icon.svg" alt="Analytics Icon" class="logo-image">
                <div class="border-divider"></div>
                <span class="agency-text">Agency</span>
            </div>
            <h2>Get agency mode for <i>today</i>, with one-click setup.</h2>
            <p>Unlock premium analytics tools designed for agencies managing multiple WordPress sites.</p>
            <a href="https://analyticswp.com/agency" class="button-agency">Get Agency Mode</a>
        </div>

    </body>




<?php
                    return ob_get_clean();
                }
            }
