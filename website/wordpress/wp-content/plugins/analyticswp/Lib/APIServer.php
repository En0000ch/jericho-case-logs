<?php

namespace AnalyticsWP\Lib;

/**
 * @psalm-import-type DateRangeValue from DateRange
 * 
 * @psalm-type Device = value-of<APIServer::DEVICES>
 * @psalm-type TopStatName = value-of<APIServer::TOP_STAT_NAMES>
 *
 * @psalm-type TopStat = array{
 *   name: TopStatName,
 *   value: int|float,
 *   change: int|float,
 *   comparison_value: int|float
 * }
 * 
 * @psalm-type TopStats = list<TopStat>
 *
 * @psalm-type ChartDataPoint = array{x: string, y: int|float}
 * 
 * @psalm-type ChartData = array{
 *   "Unique people": ChartDataPoint[],
 *   "Total pageviews": ChartDataPoint[],
 *   "Views per person": ChartDataPoint[],
 *   "Window shoppers rate": ChartDataPoint[],
 * }
 *
 * @psalm-type TopSource = array{
 *   source: string,
 *   visits: int,
 *   bounceRate: int,
 * }
 *
 * @psalm-type TopPage = array{
 *   pageUrl: string,
 *   visits: int,
 * }
 *
 * @psalm-type TopCountry = array{
 *   country: string,
 *   visits: int
 * }
 *
 * @psalm-type TopDevice = array{
 *   device: Device,
 *   visits: int
 * }
 *
 * @psalm-type MainGraphData = array{
 *   currently_selected_top_stat_name: TopStatName,
 *   top_stats: TopStat[],
 *   chart_data: ChartData
 * }
 * 
 * @psalm-type DashboardFilters = array{
 *   date_range: DateRangeValue,
 *   custom_date_range: array{start_date: string, end_date: string},
 *   custom_sql: string
 * }
 * 
 * @psalm-type SavedFilter = array{
 *   id: string,
 *   name: string,
 *   description: string,
 *   created_at: string,
 *   created_by_user_id: int,
 *   filters: DashboardFilters
 * }
 *
 * @psalm-type StatsDashboardData = array{
 *   is_loading: bool,
 *   filters: DashboardFilters,
 *   main_graph: MainGraphData,
 *   top_sources: TopSource[],
 *   top_pages: TopPage[],
 *   top_countries: TopCountry[],
 *   top_devices: TopDevice[],
 *   debug: array,
 *   saved_filters: SavedFilter[],
 * }
 */
class APIServer
{
    const CACHE_DURATION_IN_SECONDS = 300;

    const TOP_STAT_NAMES = [
        "Unique people",
        "Total pageviews",
        "Views per person",
        "Window shoppers rate"
    ];

    const DEVICES = [
        "tablet",
        "mobile",
        "desktop",
        "unknown"
    ];

    public static function init(): void
    {
        add_action('wp_ajax_analyticswp_get_stats', [self::class, 'handle_get_stats']);
        add_action('wp_ajax_nopriv_analyticswp_get_stats', [self::class, 'handle_get_stats']);

        add_action('wp_ajax_analyticswp_get_realtime_stats', [self::class, 'handle_get_realtime_stats']);
        add_action('wp_ajax_nopriv_analyticswp_get_realtime_stats', [self::class, 'handle_get_realtime_stats']);

        add_action('wp_ajax_analyticswp_get_live_events', [self::class, 'handle_get_live_events']);
        add_action('wp_ajax_nopriv_analyticswp_get_live_events', [self::class, 'handle_get_live_events']);

        add_action('wp_ajax_analyticswp_save_filter', [SavedFilters::class, 'handle_ajax_save_filter']);
        add_action('wp_ajax_nopriv_analyticswp_save_filter', [SavedFilters::class, 'handle_ajax_save_filter']);

        add_action('wp_ajax_analyticswp_delete_filter', [SavedFilters::class, 'handle_ajax_delete_filter']);
        add_action('wp_ajax_nopriv_analyticswp_delete_filter', [SavedFilters::class, 'handle_ajax_delete_filter']);

        add_action('wp_ajax_analyticswp_get_integrations', [self::class, 'handle_ajax_get_integrations']);
        add_action('wp_ajax_nopriv_analyticswp_get_integrations', [self::class, 'handle_ajax_get_integrations']);

        add_action('wp_ajax_analyticswp_update_integration', [self::class, 'handle_ajax_update_integration']);
        add_action('wp_ajax_nopriv_analyticswp_update_integration', [self::class, 'handle_ajax_update_integration']);

        add_action('wp_ajax_analyticswp_get_client_sites', [self::class, 'handle_ajax_get_client_sites']);
        add_action('wp_ajax_nopriv_analyticswp_get_client_sites', [self::class, 'handle_ajax_get_client_sites']);
        add_action('wp_ajax_analyticswp_save_client_sites', [self::class, 'handle_ajax_save_client_sites']);
        add_action('wp_ajax_nopriv_analyticswp_save_client_sites', [self::class, 'handle_ajax_save_client_sites']);

        add_action('wp_ajax_analyticswp_get_site_report', [self::class, 'handle_ajax_get_site_report']);
        add_action('wp_ajax_nopriv_analyticswp_get_site_report', [self::class, 'handle_ajax_get_site_report']);

        add_action('wp_ajax_analyticswp_get_available_tags', [self::class, 'handle_ajax_get_available_tags']);
        add_action('wp_ajax_nopriv_analyticswp_get_available_tags', [self::class, 'handle_ajax_get_available_tags']);
        add_action('wp_ajax_analyticswp_save_available_tags', [self::class, 'handle_ajax_save_available_tags']);
        add_action('wp_ajax_nopriv_analyticswp_save_available_tags', [self::class, 'handle_ajax_save_available_tags']);

        // Agency Mode Email Reports Template
        add_action('wp_ajax_analyticswp_get_email_report_template', [self::class, 'handle_ajax_get_email_report_template']);
        add_action('wp_ajax_nopriv_analyticswp_get_email_report_template', [self::class, 'handle_ajax_get_email_report_template']);
        add_action('wp_ajax_analyticswp_save_email_report_template', [self::class, 'handle_ajax_save_email_report_template']);
        add_action('wp_ajax_nopriv_analyticswp_save_email_report_template', [self::class, 'handle_ajax_save_email_report_template']);
        add_action('wp_ajax_analyticswp_reset_default_email_report_template', [self::class, 'handle_ajax_reset_default_email_report_template']);
        add_action('wp_ajax_nopriv_analyticswp_reset_default_email_report_template', [self::class, 'handle_ajax_reset_default_email_report_template']);
        add_action('wp_ajax_analyticswp_send_test_report_email', [self::class, 'handle_ajax_send_test_report_email']);
        add_action('wp_ajax_nopriv_analyticswp_send_test_report_email', [self::class, 'handle_ajax_send_test_report_email']);
    }

    private static function set_cors_headers(): void
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
        header("Content-Type: application/json");
    }

    private static function handle_preflight(): void
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            self::set_cors_headers();
            exit(0);
        }
    }


    public static function handle_get_stats(): void
    {
        self::set_cors_headers();
        self::handle_preflight();
        // check that the user is a logged in administrator (has the manage_options capability)
        // TODO figure this out. It doesn't work in development mode for some reason...but maybe works in production mode (?)
        // if (!current_user_can('manage_options')) {
        //     wp_send_json_error(array('message' => 'You do not have permission to access this resource.'));
        // }


        // TODO Check nonce for security
        // check_ajax_referer('analyticswp_get_stats', 'nonce');
        $agency_mode_timestamp = $_POST['agency_mode_timestamp'];
        $agency_mode_hash = $_POST['agency_mode_hash'];
        $site_license_key = License::get_license_key();
        $hash_matches = $agency_mode_hash === hash_hmac('sha256', $agency_mode_timestamp, $site_license_key);

        if (!$hash_matches) {
            wp_send_json_error(array('message' => 'Invalid request. hash does not match.'));
        }

        $date_range = DateRange::fromPost($_POST);

        if (!($date_range instanceof DateRange)) {
            // If it's a string, it's an error message
            wp_send_json_error(array('message' => $date_range));
        }

        // customSQL (from filter builder)
        // v2.1.5 removing custom_sql entirely due to sql injection risk.
        //   https://patchstack.com/database/report-preview/483c93fd-7e69-4a18-a850-20571e588874
        //
        $custom_sql = Validators::str(isset($_POST['custom_sql']) ? wp_unslash($_POST['custom_sql']) : '');

        // HERE THE $custom_sql is now json representation
        $safe_custom_sql = Validators::str(isset($_POST['safe_custom_sql']) ? wp_unslash($_POST['safe_custom_sql']) : '');
        if ($safe_custom_sql === '') {
            // do nothing
        } else {
            // Decode the JSON string into an associative array
            $json_decoded_safe_custom_sql = json_decode($safe_custom_sql, true);
            $where_clause_from_safe_custom_sql_input = ReactQueryBuilderParser::buildWhereClauseFromQueryBuilderJson($json_decoded_safe_custom_sql);
            // Here i just override it so that it works with the rest of the system below. I don't want to go change 30 functions.
            $custom_sql = $where_clause_from_safe_custom_sql_input;
        }



        if (Event::count() <= 5000) { // Don't cache if the db is small.
            $stats_dashboard_data = APIServerRealData::generateStatsDashboardData($date_range, $custom_sql);
        } else {
            /** @psalm-suppress PossiblyInvalidArgument - This one is ok. */
            $stats_dashboard_data = Utils::cache_function_result([APIServerRealData::class, 'generateStatsDashboardData'], [$date_range, $custom_sql], self::CACHE_DURATION_IN_SECONDS);
        }

        $current_user = wp_get_current_user();
        if ($current_user->ID === 0) {
            $stats_dashboard_data['admin_username'] = 'there';
        } else {
            $stats_dashboard_data['admin_username'] = $current_user->user_login;
        }

        $stats_dashboard_data['analyticswp_version'] = ANALYTICSWP_VERSION;

        // return the response
        wp_send_json_success($stats_dashboard_data);
    }

    public static function handle_get_realtime_stats(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        // TODO Check nonce for security
        // check_ajax_referer('analyticswp_nonce', 'nonce');

        $realtime_data = APIServerRealData::generateRealtimeStatsDashboardData();

        // return the response
        wp_send_json_success($realtime_data);
    }


    public static function handle_get_live_events(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        // get start_time and end_time from the request
        $start_time = isset($_POST['start_time']) ? ($_POST['start_time']) : null;
        $end_time = isset($_POST['end_time']) ? ($_POST['end_time']) : null;

        // TODO Check nonce for security
        // check_ajax_referer('analyticswp_nonce', 'nonce');
        $data = Event::getBetweenTimes($start_time, $end_time);

        // return the response
        wp_send_json_success($data);
    }


    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////
    public static function handle_ajax_get_integrations(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        $integrations = Integrations::get_integrations();

        // return the response
        wp_send_json_success($integrations);
    }

    public static function handle_ajax_update_integration(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        $integration_data = isset($_POST['integration_data']) ?  json_decode(stripslashes($_POST['integration_data']), true) : null;

        if (!is_array($integration_data)) {
            wp_send_json_error(array('message' => 'Invalid integration data.'));
        }

        $integration_slug = Validators::str($integration_data['slug']);
        $integration_enabled = (bool)($integration_data['enabled']);

        $integrations = Integrations::update_integration_is_enabled($integration_slug, $integration_enabled);

        // return the response
        wp_send_json_success($integrations);
    }

    //////////////////////////////////////////////////
    // Client Management
    //////////////////////////////////////////////////
    public static function handle_ajax_get_client_sites(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        $client_sites = AgencyMode::get_client_sites();

        // return the response
        wp_send_json_success($client_sites);
    }

    public static function handle_ajax_save_client_sites(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        if (!isset($_POST['clientSites'])) {
            wp_send_json_error(array('message' => 'No client sites data provided.'));
        }

        // Get and decode the JSON data
        $client_sites_json = stripslashes($_POST['clientSites']);
        $client_sites_data = json_decode($client_sites_json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($client_sites_data)) {
            wp_send_json_error(array('message' => 'Invalid client sites data.'));
        }

        // Loop through each client site and save it
        foreach ($client_sites_data as $site_data) {
            if (!isset($site_data['siteUrl'])) {
                // Skip any entry missing the unique identifier
                continue;
            }

            $url = sanitize_text_field($site_data['siteUrl']);

            $saved = AgencyMode::save_client_site_data($url, $site_data);
            if (!$saved) {
                wp_send_json_error(array('message' => "Failed to save client site data for {$url}."));
            }
        }

        $client_sites = AgencyMode::get_client_sites();
        wp_send_json_success($client_sites);
    }


    public static function handle_ajax_get_site_report(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        // params are siteUrl and reportType
        $report_type = isset($_POST['reportType']) ?  Validators::str($_POST['reportType']) : null;
        $client_site_url = isset($_POST['client_site_url']) ?  Validators::str($_POST['client_site_url']) : null;

        if (is_null($report_type) || is_null($client_site_url)) {
            wp_send_json_error(array('message' => 'Invalid client_site_url or reportType.'));
        }

        $client_site = AgencyMode::get_client_site_data_from_url($client_site_url);
        $site_report = AgencyMode::get_site_report($report_type, $client_site);

        // return the response
        wp_send_json_success($site_report);
    }


    public static function handle_ajax_save_available_tags(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        if (!isset($_POST['tags'])) {
            wp_send_json_error(array('message' => 'No tags data provided.'));
        }

        // Get and decode the JSON data
        $tags_json = stripslashes($_POST['tags']);
        $tags_data = json_decode($tags_json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($tags_data)) {
            wp_send_json_error(array('message' => 'Invalid tags data.'));
        }

        $saved = AgencyMode::save_available_tags($tags_data);

        if (!$saved) {
            wp_send_json_error(array('message' => "Failed to save tag data."));
        }

        $tags = AgencyMode::get_available_tags();
        wp_send_json_success($tags);
    }


    public static function handle_ajax_get_available_tags(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        $tags = AgencyMode::get_available_tags();

        // return the response
        wp_send_json_success($tags);
    }

    // Email Report Template
    public static function handle_ajax_get_email_report_template(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        $template = AgencyMode::get_email_report_template();

        // return the response
        wp_send_json_success($template);
    }

    public static function handle_ajax_save_email_report_template(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        if (!isset($_POST['template'])) {
            wp_send_json_error(array('message' => 'No template data provided.'));
        }
        // Template will be one string, that will be like HTML with {{}} placeholders etc

        $template = stripslashes($_POST['template']);

        $saved = AgencyMode::save_email_report_template($template);

        if (!$saved) {
            wp_send_json_error(array('message' => "Failed to save email report template."));
        }

        $template = AgencyMode::get_email_report_template();

        wp_send_json_success($template);
    }

    public static function handle_ajax_reset_default_email_report_template(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        $reset = AgencyMode::reset_default_email_report_template();

        if (!$reset) {
            wp_send_json_error(array('message' => "Failed to reset default email report template."));
        }

        $template = AgencyMode::get_email_report_template();

        wp_send_json_success($template);
    }

    //handle_ajax_send_test_report_email
    public static function handle_ajax_send_test_report_email(): void
    {
        self::set_cors_headers();
        self::handle_preflight();

        // client_site_url: siteUrl,
        // reportType: reportType,
        // email: emailAddress

        if (!isset($_POST['email']) || !isset($_POST['client_site_url']) || !isset($_POST['reportType'])) {
            wp_send_json_error(array('message' => 'Missing required parameters.'));
        }

        $email = stripslashes($_POST['email']);
        $client_site_url = stripslashes($_POST['client_site_url']);
        $reportType = stripslashes($_POST['reportType']);

        $sent = AgencyMode::send_test_report_email($email, $client_site_url, $reportType);

        if (!$sent) {
            wp_send_json_error(array('message' => "Failed to send test report email."));
        }

        wp_send_json_success(array('message' => "Test report email sent to {$email}."));
    }
}
