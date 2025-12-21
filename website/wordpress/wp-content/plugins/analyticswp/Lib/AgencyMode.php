<?php

namespace AnalyticsWP\Lib;

/**
 * @psalm-import-type SettingsArgs from \AnalyticsWP\Lib\SuperSimpleWP
 * 
 * @psalm-type Tag = array{
 *  id: string,
 *  name: string,
 * }
 * 
 * @psalm-type ClientSite = array{
 *   siteUrl: string,
 *   businessName: string,
 *   address: string,
 *   logoUrl: string,
 *   reportRecipients: string,
 *   weeklyReportsEnabled: bool,
 *   monthlyReportsEnabled: bool,
 *   assignedColor: string,
 *   tagId: string,
 * }
 */
class AgencyMode
{
    /**
     * @return boolean
     */
    public static function is_agency_mode_enabled()
    {
        if (class_exists('\AnalyticsWP_Agency_Mode_Plugin')) {
            return \AnalyticsWP_Agency_Mode_Plugin::is_enabled();
        } else {
            return false;
        }
    }

    /**
     * @return array{agency_mode_timestamp: int, agency_mode_hash: string}
     */
    public static function generate_timestamp_and_hash()
    {
        $site_license_key = License::get_license_key();
        $agency_mode_timestamp = time();
        $agency_mode_hash = hash_hmac('sha256', (string)$agency_mode_timestamp, $site_license_key);

        return [
            'agency_mode_timestamp' => $agency_mode_timestamp,
            'agency_mode_hash' => $agency_mode_hash
        ];
    }

    /**
     * @return SettingsArgs
     */
    public static function agency_mode_specific_settings()
    {
        return [
            'weekly_email_report_subject_line' => [
                'type' => 'text',
                'label' => 'Weekly Email Report Subject Line',
                'description' => 'The subject line for the weekly email report.',
                'default' => 'Your Weekly Analytics Report',
                'tab' => 'Agency Mode'
            ],
            'monthly_email_report_subject_line' => [
                'type' => 'text',
                'label' => 'Monthly Email Report Subject Line',
                'description' => 'The subject line for the monthly email report.',
                'default' => 'Your Monthly Analytics Report',
                'tab' => 'Agency Mode'
            ],
            'email_report_sending_time' => [
                'type' => 'time',
                'label' => 'Email Report Sending Time',
                'description' => 'The time of day when the email report will be sent. For reference, the current time of this server is ' . current_time('H:i') . '.',
                'default' => '09:00',
                'tab' => 'Agency Mode',
                'attributes' => array(
                    'step' => '3600', // Set step to 3600 seconds (1 hour)
                    // 'pattern' => '[0-9]{2}:00',  // Only allow HH:00 format
                )
            ],
            'email_report_sending_day' => [
                'type' => 'select',
                'label' => 'Email Report Sending Day (Weekly)',
                'description' => 'The day of the week when the weekly email report will be sent.',
                'options' => [
                    'monday' => 'Monday',
                    'tuesday' => 'Tuesday',
                    'wednesday' => 'Wednesday',
                    'thursday' => 'Thursday',
                    'friday' => 'Friday',
                    'saturday' => 'Saturday',
                    'sunday' => 'Sunday',
                ],
                'default' => 'monday',
                'tab' => 'Agency Mode'
            ],
            'email_report_sending_day_monthly' => [
                'type' => 'select',
                'label' => 'Email Report Sending Day (Monthly)',
                'description' => 'The day of the month when the monthly email report will be sent.',
                'options' => [
                    'first' => 'First',
                    'last' => 'Last'
                ],
                'default' => 'first',
                'tab' => 'Agency Mode'
            ],
            // variables to be used in the email report template
            // text fields: agency_name, agency_contact, agency_support_email
            'agency_name' => [
                'type' => 'text',
                'label' => 'Agency Name',
                'description' => 'The name of your agency that will be displayed in the email report.',
                'default' => 'Your favorite agency',
                'tab' => 'Agency Mode'
            ],
            'agency_contact' => [
                'type' => 'textarea',
                'label' => 'Agency Contact Information',
                'description' => 'The contact information for your agency that will be displayed in the email report.',
                'default' => 'Contact us at 123-456-7890',
                'tab' => 'Agency Mode'
            ],
            'agency_support_email' => [
                'type' => 'text',
                'label' => 'Agency Support Email',
                'description' => 'The support email for your agency that will be displayed in the email report.',
                'default' => get_option('admin_email'),
                'tab' => 'Agency Mode'
            ],
            // 'us'(default) or 'eu' 
            'agency_mode_date_formatting' => [
                'type' => 'select',
                'label' => 'Email Report Date Format',
                'description' => 'Choose the date format for the email report.',
                'options' => [
                    'us' => 'MM/DD/YYYY',
                    'eu' => 'DD/MM/YYYY'
                ],
                'default' => 'us',
                'tab' => 'Agency Mode'
            ]
        ];
    }

    /**
     * @return array<ClientSite>
     */
    public static function get_client_sites()
    {
        $urls = self::get_client_sites_urls();

        $client_sites = array_map(function ($url) {
            return self::get_client_site_data_from_url($url);
        }, $urls);

        return $client_sites;
    }

    /**
     * @return string
     */
    public static function generate_random_hex_color()
    {
        return '#' . substr(md5((string)rand()), 0, 6);
    }

    /**
     * @param string $url
     * @return ClientSite
     */
    public static function get_client_site_data_from_url(string $url)
    {
        $site_data_defaults = [
            'siteUrl' => $url,
            'businessName' => '',
            'address' => '',
            'logoUrl' => '',
            'reportRecipients' => '',
            'weeklyReportsEnabled' => false,
            'monthlyReportsEnabled' => false,
            'assignedColor' => self::generate_random_hex_color(),
            'tagId' => '',
        ];

        // get from options table. create it if it doesn't exist

        if (!get_option('analyticswp_client_sites' . '-' . $url)) {
            update_option('analyticswp_client_sites' . '-' . $url, $site_data_defaults);
        }

        $site_data = Validators::arr(get_option('analyticswp_client_sites' . '-' . $url));


        // validate the data
        foreach ($site_data_defaults as $key => $default) {
            if (!isset($site_data[$key])) {
                $site_data[$key] = $default;
            }

            // Type validation
            if (is_bool($default) && !is_bool($site_data[$key])) {
                $site_data[$key] = filter_var($site_data[$key], FILTER_VALIDATE_BOOLEAN);
            } elseif (is_string($default) && !is_string($site_data[$key])) {
                $site_data[$key] = strval($default);
            }

            // Sanitize strings
            if (is_string($site_data[$key])) {
                $site_data[$key] = sanitize_text_field($site_data[$key]);
            }
        }

        /** @var ClientSite */
        return $site_data;
    }

    /**
     * @param string $url
     * @param ClientSite $site_data
     * 
     * @return bool
     */
    public static function save_client_site_data($url, $site_data)
    {
        // TODO Validate the data first
        update_option('analyticswp_client_sites' . '-' . $url, $site_data);

        return true;
    }



    /**
     * Makes an API request to fetch the list of client sites for a license key.
     * 
     * https://analyticswp.com/wp-json/analyticswp/v1/license-domains?analyticswp_license_key=<LICENSE_KEY>
     *
     * @return string[]|false Array of domain names associated with the license, or false on failure
     */
    public static function make_api_request_for_client_sites_list()
    {
        try {
            // Get license key from configuration or environment
            $license_key = License::get_license_key();
            if (empty($license_key)) {
                return false;
            }

            $api_url = 'https://analyticswp.com/wp-json/analyticswp/v1/license-domains';
            $request_url = add_query_arg([
                'analyticswp_license_key' => $license_key,
                'cache_bust' => time(),
            ], $api_url);

            $response = wp_remote_get($request_url, [
                'timeout' => 15,
                'sslverify' => true,
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);

            // Check for HTTP errors
            if (is_wp_error($response)) {
                return false;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                return false;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            // Validate response structure
            if (
                !is_array($data) ||
                !isset($data['success']) ||
                !isset($data['domains']) ||
                !is_array($data['domains'])
            ) {
                return false;
            }

            if (!$data['success']) {
                return false;
            }

            // Ensure all domains are strings
            return array_map('strval', $data['domains']);
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * @return array<string>
     */
    public static function get_client_sites_urls()
    {
        return Validators::array_of_string(self::make_api_request_for_client_sites_list());
    }



    /**
     * @param ClientSite $client_site
     * @return string
     */
    public static function get_ajax_url_for_client_site($client_site)
    {
        $siteURL = $client_site['siteUrl'];
        if (empty($siteURL)) {
            throw new \InvalidArgumentException('Site URL cannot be empty');
        }

        // Trim whitespace
        $cleanURL = trim($siteURL);

        // Check if URL already has a protocol
        $hasProtocol = (bool)preg_match('/^[a-zA-Z]+:\/\//', $cleanURL);

        // Add https:// if no protocol exists
        if (!$hasProtocol) {
            // Remove any leading slashes before adding https://
            $cleanURL = preg_replace('/^\/+/', '', $cleanURL);
            $cleanURL = "https://{$cleanURL}";
        }

        // Remove any trailing slashes
        $cleanURL = rtrim($cleanURL, '/');

        // Ensure the URL is valid
        if (!filter_var($cleanURL, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format');
        }

        // Append the WordPress admin-ajax path
        // Using path combination logic to handle potential double slashes
        return preg_replace('/([^:]\/)\/+/', '$1', $cleanURL . '/wp-admin/admin-ajax.php');
    }

    /**
     * @param ClientSite $client_site
     * @return string
     */
    public static function get_dashboard_url_for_client_site($client_site)
    {
        // Given a client site, we must implement a robust method to determine the dashboard URL.
        // example:
        //  http://analyticswpdev.com/wp-admin/admin.php?page=analyticswp

        $siteURL = $client_site['siteUrl'];

        if (empty($siteURL)) {
            throw new \InvalidArgumentException('Site URL cannot be empty');
        }

        // Trim whitespace
        $cleanURL = trim($siteURL);

        // Check if URL already has a protocol
        $hasProtocol = (bool)preg_match('/^[a-zA-Z]+:\/\//', $cleanURL);

        // Add https:// if no protocol exists
        if (!$hasProtocol) {
            // Remove any leading slashes before adding https://
            $cleanURL = preg_replace('/^\/+/', '', $cleanURL);
            $cleanURL = "https://{$cleanURL}";
        }

        // Remove any trailing slashes
        $cleanURL = rtrim($cleanURL, '/');
        return $cleanURL . '/wp-admin/admin.php?page=analyticswp';
    }

    /**
     * Generate an HTML analytics report for the given report type using a templated layout.
     *
     * This function performs the following steps:
     * 1. Determines the appropriate date range based on the report type (weekly or monthly).
     * 2. Generates dashboard data by calling APIServerRealData::generateStatsDashboardData().
     * 3. Prepares additional computed values for each top statistic, such as CSS classes,
     *    arrow symbols, and absolute change values, to keep the template logic simple.
     * 4. Merges all data into a rendering context which includes the computed period text
     *    and human-readable date range.
     * 5. Loads a default Mustache-style HTML template that contains double-bracket tags.
     * 6. Renders the final HTML by interpolating the context values into the template.
     *
     * @param 'weekly'|'monthly' $report_type The type of report to generate ('weekly' or 'monthly').
     * @param ClientSite $client_site The URL of the client site to generate the report for.
     *
     * @return string The final rendered HTML report.
     *
     * @throws \Exception If there is an issue rendering the template.
     */
    public static function get_site_report($report_type, $client_site)
    {

        // Determine the date range based on the report type.
        if ($report_type === 'monthly') {
            // $date_range = new DateRange('Last 30 days');
            $date_range = 'Last 30 days';
        } else {
            // Default to weekly if report type is not monthly.
            // $date_range = new DateRange('Last 7 days');
            $date_range = 'Last 7 days';
        }

        // Generate the dashboard data using the selected date range.
        // This returns an array of type StatsDashboardData.

        // this here needs to come from the client site's server API
        // This needs to be a POST request to $client_site_ajax_url with:    action: 'analyticswp_get_stats', date_range: dateRange,
        // MAKE THE POST REQUEST TO THE CLIENT SITE'S SERVER API
        $client_site_ajax_url = self::get_ajax_url_for_client_site($client_site);

        // $site_license_key = License::get_license_key();
        // $agency_mode_timestamp = time();
        // $agency_mode_hash = hash_hmac('sha256', $agency_mode_timestamp, $site_license_key);

        [
            'agency_mode_timestamp' => $agency_mode_timestamp,
            'agency_mode_hash' => $agency_mode_hash
        ] = AgencyMode::generate_timestamp_and_hash();

        $client_site_dashboard_data_response = wp_remote_post($client_site_ajax_url, [
            'timeout' => 60,
            'body' => [
                'action' => 'analyticswp_get_stats',
                'date_range' => $date_range,
                'agency_mode_timestamp' => $agency_mode_timestamp,
                'agency_mode_hash' => $agency_mode_hash
            ],
        ]);

        $client_site_dashboard_data = wp_remote_retrieve_body($client_site_dashboard_data_response);
        // parse the response, data
        $dashboard_data = json_decode($client_site_dashboard_data, true)['data'];
        // $dashboard_data = APIServerRealData::generateStatsDashboardData($date_range);

        // Determine the display text for the period based on the report type.
        $period_text = $report_type === 'monthly' ? 'Monthly' : 'Weekly';

        // Precompute additional display values for each top stat.
        // This ensures that the template only needs to render pre-formatted values.
        foreach ($dashboard_data['main_graph']['top_stats'] as &$stat) {
            $stat['change_class'] = $stat['change'] >= 0 ? 'change-positive' : 'change-negative';
            $stat['change_symbol'] = $stat['change'] >= 0 ? '↑' : '↓';
            $stat['change_formatted'] = abs($stat['change']);
        }
        unset($stat);


        $context = new EmailReportContext($dashboard_data, $report_type, $client_site);

        // Load the default template. This template can be user-editable in the future.
        $template = self::get_email_report_template();

        // Render the template using a Mustache-style templating engine.
        // Ensure that the Mustache library is installed and autoloaded.
        $mustache = new \Mustache_Engine;
        $email_body = $mustache->render($template, $context);
        return $email_body;
    }

    /**
     * Retrieve the default Mustache template for rendering the analytics report.
     *
     * This template defines the complete HTML layout and includes placeholders (using double
     * bracket tags) for all dynamic data such as:
     *  - The report period and human-readable date range.
     *  - The key statistics section, where each statistic displays its name, value, and
     *    formatted change (with associated styling).
     *  - Sections for top traffic sources, most visited pages, and device distribution,
     *    each rendered as HTML tables.
     *
     * The template is intended to be fully customizable by users via a template editing feature.
     *
     * @return string The default HTML template as a string.
     */
    protected static function getDefaultReportTemplate()
    {
        return <<<'TEMPLATE'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Analytics Report</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; -webkit-font-smoothing: antialiased; background-color: #f5f5f5;">
    <!-- Outer Container -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 30px 10px;">
                <!-- Main Content Container - 700px -->
                <table border="0" cellpadding="0" cellspacing="0" width="700" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Logo & Header Section -->
                    <tr>
                        <td style="padding: 40px 50px; text-align: center; border-top: 10px solid {{accent_color}}; border-radius: 8px 8px 0 0;">
                            <img src="{{business_logo_url}}" alt="{{business_name}}" style="max-width: 180px; height: auto;"/>
                            <h1 style="color: #2d3748; margin: 20px 0 0 0; font-size: 28px; font-weight: 600;">Analytics Report</h1>
                        </td>
                    </tr>

                    <!-- Report Period -->
                    <tr>
                        <td style="padding: 30px 50px 20px 50px;">
                            <table border="0" cellpadding="15" cellspacing="0" width="100%" style="background-color: #f8fafc; border-radius: 6px;">
                                <tr>
                                    <td style="font-size: 15px; line-height: 1.5; color: #4a5568;">
                                        <strong style="color: #2d3748;">Report Period:</strong> {{human_readable_date_range}}<br/>
                                        <strong style="color: #2d3748;">Report Type:</strong> {{report_type_capitalized}}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Key Metrics -->
                    <tr>
                    <td style="padding: 0 50px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td width="50%" style="padding: 0 5px 10px 0;">
                            <table border="0" cellpadding="20" cellspacing="0" width="100%" border-radius: 6px;">
                                <tr>
                                <td align="center" style="border-top: 4px solid {{accent_color}} !important; border:1px solid #d4d4d4">
                                    <div style="font-size: 32px; font-weight: 600; color: #4a5568; line-height: 1;">
                                    {{unique_people_stats.value}}
                                    </div>
                                    <div style="font-size: 14px; color: #4a5568; margin-top: 5px;">
                                    {{unique_people_stats.name}}
                                    </div>
                                    <div style="font-size: 13px; margin-top: 8px; color: {{unique_people_stats.change_color}};">
                                    {{unique_people_stats.change_symbol}} {{unique_people_stats.change_formatted}}% vs previous period
                                    </div>
                                </td>
                                </tr>
                            </table>
                            </td>
                            <td width="50%" style="padding: 0 0 10px 5px;">
                            <table border="0" cellpadding="20" cellspacing="0" width="100%" border-radius: 6px;">
                                <tr>
                                <td align="center" style="border-top: 4px solid {{accent_color}} !important; border:1px solid #d4d4d4">
                                    <div style="font-size: 32px; font-weight: 600; color: #4a5568; line-height: 1;">
                                    {{total_pageviews_stats.value}}
                                    </div>
                                    <div style="font-size: 14px; color: #4a5568; margin-top: 5px;">
                                    {{total_pageviews_stats.name}}
                                    </div>
                                    <div style="font-size: 13px; margin-top: 8px; color: {{total_pageviews_stats.change_color}};">
                                    {{total_pageviews_stats.change_symbol}} {{total_pageviews_stats.change_formatted}}% vs previous period
                                    </div>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%" style="padding: 10px 5px 0 0;">
                            <table border="0" cellpadding="20" cellspacing="0" width="100%" border-radius: 6px;">
                                <tr>
                                <td align="center" style="border-top: 4px solid {{accent_color}} !important; border:1px solid #d4d4d4">
                                    <div style="font-size: 32px; font-weight: 600; color: #4a5568; line-height: 1;">
                                    {{views_per_person_stats.value}}
                                    </div>
                                    <div style="font-size: 14px; color: #4a5568; margin-top: 5px;">
                                    {{views_per_person_stats.name}}
                                    </div>
                                    <div style="font-size: 13px; margin-top: 8px; color: {{views_per_person_stats.change_color}};">
                                    {{views_per_person_stats.change_symbol}} {{views_per_person_stats.change_formatted}}% vs previous period
                                    </div>
                                </td>
                                </tr>
                            </table>
                            </td>
                            <td width="50%" style="padding: 10px 0 0 5px;">
                            <table border="0" cellpadding="20" cellspacing="0" width="100%" border-radius: 6px;">
                                <tr>
                                <td align="center" style="border-top: 4px solid {{accent_color}} !important; border:1px solid #d4d4d4">
                                    <div style="font-size: 32px; font-weight: 600; color: #4a5568; line-height: 1;">
                                    {{window_shoppers_rate_stats.value}}%
                                    </div>
                                    <div style="font-size: 14px; color: #4a5568; margin-top: 5px;">
                                    {{window_shoppers_rate_stats.name}}
                                    </div>
                                    <div style="font-size: 13px; margin-top: 8px; color: {{window_shoppers_rate_stats.change_color}};">
                                    {{window_shoppers_rate_stats.change_symbol}} {{window_shoppers_rate_stats.change_formatted}}% vs previous period
                                    </div>
                                </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>

                    <!-- Top Pages Section -->
                    <tr>
                        <td style="padding: 30px 50px 20px 50px;">
                            <h2 style="color: #4a5568; font-size: 20px; margin: 0 0 15px 0; font-weight: 600;">Top 5 Pages</h2>
                            <table border="0" cellpadding="12" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                <tr style="background-color: #f8fafc;">
                                    <th align="left" style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0; font-weight: 600;">Page</th>
                                    <th align="right" style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0; font-weight: 600;">Views</th>
                                </tr>
                                {{#top_pages}}
                                <tr>
                                    <td style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0;">{{pageUrl}}</td>
                                    <td align="right" style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0;">{{visits}}</td>
                                </tr>
                                {{/top_pages}}
                            </table>
                        </td>
                    </tr>

                    <!-- Top Referrers Section -->
                    <tr>
                        <td style="padding: 0 50px 20px 50px;">
                            <h2 style="color: #4a5568;font-size: 20px; margin: 0 0 15px 0; font-weight: 600;">Top 5 Referrers</h2>
                            <table border="0" cellpadding="12" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                <tr style="background-color: #f8fafc;">
                                    <th align="left" style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0; font-weight: 600;">Source</th>
                                    <th align="right" style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0; font-weight: 600;">Visitors</th>
                                </tr>
                                {{#top_sources}}
                                <tr>
                                    <td style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0;">{{source}}</td>
                                    <td align="right" style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0;">{{visits}}</td>
                                </tr>
                                {{/top_sources}}
                            </table>
                        </td>
                    </tr>

                    <!-- Devices Section -->
                    <tr>
                        <td style="padding: 0 50px 30px 50px;">
                            <h2 style="color: #4a5568; font-size: 20px; margin: 0 0 15px 0; font-weight: 600;">Device Usage</h2>
                            <table border="0" cellpadding="12" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                <tr style="background-color: #f8fafc;">
                                    <th align="left" style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0; font-weight: 600;">Device Type</th>
                                    <th align="right" style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0; font-weight: 600;">Visits</th>
                                </tr>
                                {{#top_devices}}
                                <tr>
                                    <td style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0;">{{device}}</td>
                                    <td align="right" style="font-size: 14px; color: #4a5568; border-bottom: 1px solid #e2e8f0;">{{visits}}</td>
                                </tr>
                                {{/top_devices}}
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 50px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 8px 8px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="color: #4a5568; font-size: 13px; line-height: 1.6;">
                                        <strong style="color: #4a5568; font-size: 15px;">{{business_name}}</strong><br/>
                                        {{business_address}}<br/><br/>
                                        <strong style="color: #2d3748;">Report prepared by:</strong><br/>
                                        {{agency_name}}<br/>
                                        {{agency_contact}}<br/><br/>
                                        For more detailed analytics and insights, please contact us at <a href="mailto:{{support_email}}" style="color: {{accent_color}}; text-decoration: none;">{{support_email}}</a> or visit your dashboard at <a href="{{dashboard_url}}" style="color: {{accent_color}}; text-decoration: none;">{{dashboard_url}}</a><br/><br/>
                                        <span style="color: #718096;">© {{current_year}} {{business_name}}. All rights reserved.</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
TEMPLATE;
    }

    /**
     * @return string
     */
    public static function get_email_report_template()
    {
        $default = self::getDefaultReportTemplate();
        return get_option('analyticswp_email_report_template', $default);
    }

    /**
     * @param string $template
     * @return bool
     */
    public static function save_email_report_template($template)
    {
        // TODO Validate the data first
        update_option('analyticswp_email_report_template', $template);
        return true;
    }

    /**
     * @return bool
     */
    public static function reset_default_email_report_template()
    {
        $default = self::getDefaultReportTemplate();
        update_option('analyticswp_email_report_template', $default);
        return true;
    }

    /**
     * Send an email report for a specific client site.
     *
     * @param 'weekly'|'monthly' $report_type 'weekly' or 'monthly'
     * @param ClientSite  $client_site The client site data array.
     *
     * @return bool True if the email was sent successfully, false otherwise.
     */
    public static function send_email_report($report_type, $client_site)
    {
        // Ensure that the client site has report recipients set.
        if (empty($client_site['reportRecipients'])) {
            return false;
        }

        try {
            // Generate the report HTML content using the site's URL.
            $email_body = self::get_site_report($report_type, $client_site);
            if (empty($email_body)) {
                return false;
            }

            // Determine the subject line based on the report type.
            $subject_key = $report_type === 'monthly' ? 'monthly_email_report_subject_line' : 'weekly_email_report_subject_line';
            $subject_line = (string)SuperSimpleWP::get_setting('analyticswp', $subject_key);

            // Parse recipients (assumed to be a comma-separated string).
            $recipients = array_map('trim', explode(',', $client_site['reportRecipients']));

            // Set email headers to ensure the content is rendered as HTML.
            $headers = ['Content-Type: text/html; charset=UTF-8'];

            // Send the email using wp_mail.
            $result = wp_mail($recipients, $subject_line, $email_body, $headers);

            return $result;
        } catch (\Exception $e) {
            // Optionally log the exception (for debugging or error tracking).
            // error_log('Error sending email report: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email reports to all client sites that have enabled reports.
     *
     * This function will check each client site for the appropriate setting:
     *  - For a 'monthly' report, it will only send if monthlyReportsEnabled is true.
     *  - For any other type (defaulting to 'weekly'), it will only send if weeklyReportsEnabled is true.
     *
     * @param string $report_type 'weekly' or 'monthly'
     *
     * @return array An associative array with client site URLs as keys and a boolean value indicating the success of the email send.
     */
    public static function send_all_email_reports($report_type)
    {
        $results = [];
        $client_sites = self::get_client_sites();

        foreach ($client_sites as $client_site) {
            if (
                ($report_type === 'monthly' && $client_site['monthlyReportsEnabled']) ||
                ($report_type !== 'monthly' && $client_site['weeklyReportsEnabled'])
            ) {
                $results[$client_site['siteUrl']] = self::send_email_report($report_type, $client_site);
            }
        }

        return $results;
    }

    /**
     * Checks if the current time is within an acceptable window after the configured sending time
     * and triggers email report sending accordingly. Uses transients to prevent duplicate sends.
     *
     * @return void
     */
    public static function trigger_scheduled_email_reports()
    {
        // Retrieve the configured sending time (e.g., "09:00")
        $sending_time = (string)SuperSimpleWP::get_setting('analyticswp', 'email_report_sending_time'); // "HH:MM" format

        // Define a window in seconds (e.g., 10 minutes)
        $buffer_after_in_seconds = 20 * 60;
        $buffer_before_in_seconds = 5 * 60;


        // Build today's scheduled timestamp using the site's timezone.
        $today = current_time('Y-m-d');
        $scheduled_datetime = $today . ' ' . $sending_time . ':00';
        $scheduled_timestamp = strtotime($scheduled_datetime);
        $current_timestamp = current_time('timestamp');

        // Only proceed if the current time is within the window
        if (($current_timestamp < ($scheduled_timestamp - $buffer_before_in_seconds)) || ($current_timestamp > ($scheduled_timestamp + $buffer_after_in_seconds))) {
            return;
        }

        // -----------------------------
        // Check Weekly Report Condition
        // -----------------------------
        // Retrieve the weekly report sending day (e.g., "monday")
        $weekly_day = strtolower(SuperSimpleWP::get_setting('analyticswp', 'email_report_sending_day'));
        $today_day  = strtolower(current_time('l')); // e.g., "monday", "tuesday", etc.

        if ($today_day === $weekly_day) {
            self::send_all_email_reports('weekly');
        }

        // ------------------------------
        // Check Monthly Report Condition
        // ------------------------------
        // Retrieve the monthly report sending setting (expected "first" or "last")
        $monthly_setting = strtolower(SuperSimpleWP::get_setting('analyticswp', 'email_report_sending_day_monthly'));
        $today_date  = (int) current_time('j'); // Current day of the month (1 to 31)
        $days_in_month = (int) date('t', current_time('timestamp')); // Total days in current month

        if ($monthly_setting === 'first' && $today_date === 1) {
            self::send_all_email_reports('monthly');
        } elseif ($monthly_setting === 'last' && $today_date === $days_in_month) {
            self::send_all_email_reports('monthly');
        }
    }

    /**
     * @param string $email_address
     * @param string $client_site_url
     * @param 'weekly'|'monthly' $report_type
     * @return void
     */
    public static function send_test_report_email($email_address, $client_site_url, $report_type)
    {
        $client_site = self::get_client_site_data_from_url($client_site_url);
        if (!$client_site) {
            return false;
        }

        $client_site['reportRecipients'] = $email_address;
        return self::send_email_report($report_type, $client_site);
    }


    ////////////////////////////////////
    // Tag Management
    ////////////////////////////////////

    /**
     * @return Tag[]
     */
    public static function get_available_tags()
    {
        $available_tags = Validators::arr(get_option('analyticswp_available_tags', []));

        $validated_tags = array_map(function ($tag) {
            if (!isset($tag['id']) || !isset($tag['name'])) {
                return null;
            }
            return [
                'id' => (string)$tag['id'],
                'name' => (string)$tag['name'],
            ];
        }, $available_tags);

        return array_filter($validated_tags);
    }

    /**
     * @param Tag[] $tags
     * @return Tag[]
     */
    public static function save_available_tags($tags)
    {
        // validate the data, remove any tags that don't have an ID or name or any extra fields
        $validated_tags = array_map(function ($tag) {
            if (!isset($tag['id']) || !isset($tag['name'])) {
                return null;
            }
            return [
                'id' => (string)$tag['id'],
                'name' => (string)$tag['name'],
            ];
        }, Validators::arr($tags));

        // remove any null values
        $validated_tags = array_filter($validated_tags);

        // save the tags to the options table
        update_option('analyticswp_available_tags', $validated_tags);

        /**
         * @var Tag[]
         */
        $available_tags = get_option('analyticswp_available_tags');

        // NOW WE NEED TO HANDLE THE "FOREIGN KEY" RELATIONSHIP BETWEEN THE TAGS AND THE CLIENT SITES
        // WE NEED TO MAKE SURE THAT THE TAGS ARE REMOVED FROM THE CLIENT SITES IF THEY ARE NO LONGER AVAILABLE
        $all_client_sites = self::get_client_sites();
        foreach ($all_client_sites as $client_site) {
            if (!in_array($client_site['tagId'], array_column($available_tags, 'id'))) {
                $client_site['tagId'] = '';
                self::save_client_site_data($client_site['siteUrl'], $client_site);
            }
        }

        /**
         * @var Tag[]
         */
        return $available_tags;
    }
}
