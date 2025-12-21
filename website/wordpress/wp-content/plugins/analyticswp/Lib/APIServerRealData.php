<?php

namespace AnalyticsWP\Lib;

/**
 * @psalm-import-type DateRangeValue from DateRange
 * @psalm-import-type TopStatName from APIServer
 * @psalm-import-type TopStat from APIServer
 * @psalm-import-type TopStats from APIServer
 * @psalm-import-type TopSource from APIServer
 * @psalm-import-type TopCountry from APIServer
 * @psalm-import-type TopPage from APIServer
 * @psalm-import-type TopDevice from APIServer
 * @psalm-import-type ChartDataPoint from APIServer
 * @psalm-import-type ChartData from APIServer
 * @psalm-import-type MainGraphData from APIServer
 * @psalm-import-type StatsDashboardData from APIServer
 */
class APIServerRealData
{


    /**
     * Fills in missing dates in chart data points with zeros.
     * 
     * Example:
     * 
     * fillChartDataPointsWithEmptyDates('Last 7 days', [
     *   ['x' => '2021-01-01', 'y' => 1],
     *   ['x' => '2021-01-03', 'y' => 2],
     * ])
     * 
     * returns:
     * 
     * [
     *  ['x' => '2021-01-01', 'y' => 1],
     *  ['x' => '2021-01-02', 'y' => 0],
     *  ['x' => '2021-01-03', 'y' => 2],
     *  ['x' => '2021-01-04', 'y' => 0],
     *  ['x' => '2021-01-05', 'y' => 0],
     *  ['x' => '2021-01-06', 'y' => 0],
     *  ['x' => '2021-01-07', 'y' => 0],
     * ]
     *
     * @param DateRange $date_range
     * 
     * @param ChartDataPoint[] $chart_data_points
     * 
     * @return ChartDataPoint[]
     */
    private static function fillChartDataPointsWithEmptyDates($date_range, $chart_data_points)
    {
        $array_of_date_string = $date_range->toArrayOfDateString();

        $output = [];

        foreach ($array_of_date_string as $date_string) {
            $found = false;
            foreach ($chart_data_points as $chart_data_point) {
                if ($chart_data_point['x'] === $date_string) {
                    $output[] = $chart_data_point;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $output[] = [
                    'x' => $date_string,
                    'y' => 0,
                ];
            }
        }

        return $output;
    }


    /**
     * Validates the custom SQL against a blocklist of dangerous keywords.
     *
     * @param string $custom_sql The SQL snippet provided by the user.
     * @return bool True if safe; false if a dangerous keyword is found.
     */
    public static function is_custom_sql_safe(string $custom_sql): bool
    {
        // Define a blocklist of dangerous keywords and characters.
        $blocklist = [
            'WHERE',
            'SELECT',
            'DROP',
            'SLEEP',
            'INSERT',
            'UPDATE',
            'DELETE',
            'ALTER',
            'CREATE',
            'TRUNCATE',
            'REPLACE',
            'GRANT',
            'REVOKE',
            'UNION',
            'JOIN',
            'LEFT',
            'RIGHT',
            'INNER',
            'OUTER',
            'FULL',
            '--',    // SQL comment marker
            ';',     // Statement delimiter
            '/*',
            '*/'
        ];

        // Check each blocklisted item against the custom SQL (case-insensitive)
        foreach ($blocklist as $keyword) {
            if (stripos($custom_sql, $keyword) !== false) {
                return false;
            }
        }
        return true;
    }

    public static function build_where_clause($date_range, $custom_sql = '', $is_comparison = false)
    {
        $date_query = $is_comparison ? $date_range->toSqlForComparison() : $date_range->toSql();
        $where_clause = "1=1 $date_query";

        // v2.1.5 removing custom_sql entirely due to sql injection risk.
        //   https://patchstack.com/database/report-preview/483c93fd-7e69-4a18-a850-20571e588874
        //
        if (!empty($custom_sql)) {
            // Check the custom SQL against the blocklist.
            if (!self::is_custom_sql_safe($custom_sql)) {
                wp_send_json_error(array('message' => 'Custom SQL contains disallowed keywords.'));
                // Alternatively, you could simply return or throw an exception.
            }

            // If the SQL passes the blocklist check, append it to the WHERE clause.
            $where_clause .= " AND (" . $custom_sql . ")";
        }

        return $where_clause;
    }


    /**
     * @param DateRange $date_range
     * @param string $custom_sql
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return ChartData
     */
    private static function computeChartData($date_range, $custom_sql = '')
    {
        global $wpdb;

        $table_name = Event::table_name();

        $dataPoints = [];
        /////////////////////////////////////////
        // Unique people
        /////////////////////////////////////////
        $where_clause = self::build_where_clause($date_range, $custom_sql);

        $tz_offset = Timezone::get_wp_timezone_offset();

        $sql = "/* TAG4 */ SELECT 
                    DATE(DATE_ADD(timestamp, INTERVAL '$tz_offset' HOUR_MINUTE)) as date, 
                    COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id ELSE unique_session_id END) as unique_visitors
                FROM $table_name
                WHERE $where_clause
                GROUP BY DATE(DATE_ADD(timestamp, INTERVAL '$tz_offset' HOUR_MINUTE));";

        $results = $wpdb->get_results($sql, 'ARRAY_A');

        $validated_results = Validators::wpdb_results($results, 'date', 'unique_visitors');

        $unique_visitors_chart_data = array_map(function ($row) {
            return [
                'x' => $row['date'],
                'y' => (int)$row['unique_visitors'],
            ];
        }, $validated_results);


        $unique_visitors_chart_data = self::fillChartDataPointsWithEmptyDates($date_range, $unique_visitors_chart_data);

        $dataPoints['Unique people'] = $unique_visitors_chart_data;

        /////////////////////////////////////////
        // Total Pageviews
        /////////////////////////////////////////
        $sql = "/* TAG5 */ SELECT 
                    DATE(DATE_ADD(timestamp, INTERVAL '$tz_offset' HOUR_MINUTE)) AS date, 
                    COUNT(*) AS total_pageviews
                FROM 
                    $table_name
                WHERE 
                    $where_clause AND event_type = 'pageview'
                GROUP BY 
                    DATE(DATE_ADD(timestamp, INTERVAL '$tz_offset' HOUR_MINUTE))
                ORDER BY 
                    DATE(timestamp) ASC;";

        $results = $wpdb->get_results($sql, 'ARRAY_A');

        $validated_results = Validators::wpdb_results($results, 'date', 'total_pageviews');

        $total_pageviews_chart_data = array_map(function ($row) {
            return [
                'x' => $row['date'],
                'y' => (int)$row['total_pageviews'],
            ];
        }, $validated_results);

        $total_pageviews_chart_data = self::fillChartDataPointsWithEmptyDates($date_range, $total_pageviews_chart_data);
        $dataPoints['Total pageviews'] = $total_pageviews_chart_data;

        /////////////////////////////////////////
        // Views per person
        /////////////////////////////////////////
        $view_per_visitor_chart_data = array_map(function ($unique_visitors_data_point, $total_pageviews_data_point) {
            return [
                'x' => $unique_visitors_data_point['x'],
                'y' => $unique_visitors_data_point['y'] > 0 ? $total_pageviews_data_point['y'] / $unique_visitors_data_point['y'] : 0,
            ];
        }, $unique_visitors_chart_data, $total_pageviews_chart_data);

        $dataPoints['Views per person'] = $view_per_visitor_chart_data;


        /////////////////////////////////////////
        // Window shoppers rate
        /////////////////////////////////////////
        $sql = "/* TAG6 */ SELECT 
                    e.date,
                    COALESCE(SUM(e.single_page_session), 0) AS single_page_visitors,
                    COALESCE(SUM(e.total_visitors), 0) AS total_visitors,
                    CASE WHEN COALESCE(SUM(e.total_visitors), 0) > 0 
                        THEN COALESCE(SUM(e.single_page_session), 0) / COALESCE(SUM(e.total_visitors), 0)
                        ELSE 0 
                    END AS daily_bounce_rate
                FROM 
                    (
                        SELECT 
                            DATE(DATE_ADD(timestamp, INTERVAL '$tz_offset' HOUR_MINUTE)) as date,
                            CASE 
                                WHEN COUNT(*) = 1 THEN 1 
                                ELSE 0 
                            END as single_page_session,
                            1 as total_visitors
                        FROM 
                            $table_name
                        WHERE 
                            $where_clause AND event_type = 'pageview'
                        GROUP BY 
                            DATE(DATE_ADD(timestamp, INTERVAL '$tz_offset' HOUR_MINUTE)),
                            CASE 
                                WHEN user_id IS NOT NULL THEN user_id
                                ELSE unique_session_id 
                            END
                    ) e
                GROUP BY 
                    e.date;";

        $results = $wpdb->get_results($sql, 'ARRAY_A');
        $validated_results = Validators::wpdb_results($results, 'date', 'daily_bounce_rate');

        $bounce_rate_chart_data = array_map(function ($row) {
            return [
                'x' => $row['date'],
                'y' => (float)$row['daily_bounce_rate'] * 100.0,
            ];
        }, $validated_results);

        $bounce_rate_chart_data = self::fillChartDataPointsWithEmptyDates($date_range, $bounce_rate_chart_data);
        $dataPoints['Window shoppers rate'] = $bounce_rate_chart_data;

        /////////////////////////////////////////
        /////////////////////////////////////////

        return $dataPoints;
    }

    /**
     * @param DateRange $date_range
     * @param string $custom_sql
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return TopSource[]
     */
    private static function computeTopSources($date_range, $custom_sql = '')
    {
        global $wpdb;
        $table_name = Event::table_name();
        $limit = 200;

        // $date_query = $date_range->toSql();
        $where_clause = self::build_where_clause($date_range, $custom_sql);


        // TODO-Test this on $site_url exclusion on some real website
        $site_url = get_site_url();

        $query = " /* TAG7 */
        SELECT COALESCE(first_visits.referrer, 'Direct / None') AS referrer,
                COUNT(*)                                         AS visits,
                ( COUNT(CASE
                        WHEN event_count = 1 THEN 1
                        end) / COUNT(*) )                      AS bounce_rate
        FROM   (SELECT first_events.visitor_id,
                        wp.referrer,
                        first_events.first_visit,
                        first_events.event_count
                FROM   (SELECT CASE
                                WHEN user_id IS NOT NULL THEN user_id
                                ELSE unique_session_id
                                end            AS visitor_id,
                                MIN(timestamp) AS first_visit,
                                COUNT(*)       AS event_count
                        FROM   $table_name
                        WHERE  event_type = 'pageview'
                                AND ( referrer NOT LIKE '$site_url%'
                                    OR referrer IS NULL )
                                AND $where_clause
                        GROUP  BY CASE
                                    WHEN user_id IS NOT NULL THEN user_id
                                    ELSE unique_session_id
                                end) AS first_events
                        INNER JOIN $table_name wp
                                ON first_events.visitor_id = CASE
                                                            WHEN
                                wp.user_id IS NOT NULL THEN
                                                            wp.user_id
                                                            ELSE wp.unique_session_id
                                                            end
                                AND first_events.first_visit = wp.timestamp
                WHERE  wp.event_type = 'pageview') AS first_visits
        GROUP  BY referrer
        ORDER  BY visits DESC
        LIMIT  %d; 
        ";


        $results = $wpdb->get_results((string)$wpdb->prepare($query, $limit), 'ARRAY_A');
        $validated_results = Validators::wpdb_results($results, 'referrer', 'visits', 'bounce_rate');

        // Process the results
        $output = [];
        foreach ($validated_results as $row) {
            $output[] = [
                'source' => $row['referrer'] ?? 'Direct / None',
                'visits' => intval($row['visits']),
                'bounceRate' => (int)(floatval($row['bounce_rate']) * 100.0),
            ];
        };

        return $output;
    }


    /**
     * @param DateRange $date_range
     * @param string $custom_sql
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return TopPage[]
     */
    private static function computeTopPages($date_range, $custom_sql = '')
    {
        global $wpdb;
        $table_name = Event::table_name();
        $limit = 200;

        $where_clause = self::build_where_clause($date_range, $custom_sql);

        // Fetch data from the database
        $query = " /* TAG8 */
        SELECT SUBSTRING_INDEX(page_url, '?', 1) AS page_path, COUNT(*) AS visits
        FROM $table_name
        WHERE $where_clause
        GROUP BY page_path
        ORDER BY visits DESC
        LIMIT %d
    ";

        $results = $wpdb->get_results((string)$wpdb->prepare($query, $limit), 'ARRAY_A');
        $validated_results = Validators::wpdb_results($results, 'page_path', 'visits');

        // Process the results
        $output = [];
        foreach ($validated_results as $row) {
            $output[] = [
                'pageUrl' => $row['page_path'],
                'visits' => intval($row['visits']),
            ];
        };

        return $output;
    }


    /**
     * @param DateRange $date_range
     * @param string $custom_sql
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return TopCountry[]
     */
    private static function computeTopCountries($date_range, $custom_sql = '')
    {
        return [[
            'country' => 'This feature is coming soon.',
            'visits' => 0 //Event::count_total_pageviews($date_range),
        ]];
    }


    /**
     * @param DateRange $date_range
     * @param string $custom_sql
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return TopDevice[]
     */
    private static function computeTopDevices($date_range, $custom_sql = '')
    {
        global $wpdb;
        $table_name = Event::table_name();
        $limit = 50;

        $where_clause = self::build_where_clause($date_range, $custom_sql);

        // Fetch data from the database
        $query = " /* TAG9 */
            SELECT device_type, COUNT(*) AS visits
            FROM $table_name
            WHERE $where_clause
            GROUP BY device_type
            ORDER BY visits DESC
            LIMIT %d
        ";

        $results = $wpdb->get_results((string)$wpdb->prepare($query, $limit), 'ARRAY_A');
        $validated_results = Validators::wpdb_results($results, 'device_type', 'visits');

        // Process the results
        $output = [];
        foreach ($validated_results as $row) {
            $output[] = [
                'device' => Validators::one_of(APIServer::DEVICES, 'unknown', $row['device_type']),
                'visits' => intval($row['visits']),
            ];
        };

        return $output;
    }



    /**
     * Computes the percentage difference between two numbers.
     * 
     * Handles the case where the denominator might be zero.
     * 
     * Rounds the number to two decimal places.
     *
     * @param int|float $a
     * @param int|float $b
     * 
     * @return float
     */
    private static function percentage_diff($a, $b)
    {
        if ($b == 0) {
            return 0.0;
        }

        return round((($a - $b) / $b) * 100.0, 2);
    }


    /**
     * @param DateRange $date_range
     * @param string $custom_sql
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return TopStats
     */
    private static function computeTopStats($date_range, $custom_sql = '')
    {
        global $wpdb;
        $table_name = Event::table_name();

        $where_clause = self::build_where_clause($date_range, $custom_sql);
        $where_clause_comparison = self::build_where_clause($date_range, $custom_sql, true);

        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////

        $query = "/* TAG1 */ SELECT 
    (
        SELECT COUNT(DISTINCT user_id) 
        FROM $table_name 
        WHERE user_id IS NOT NULL AND ($where_clause)
        AND event_type = 'pageview'
    ) 
    + 
    (
        SELECT COUNT(DISTINCT unique_session_id) 
        FROM $table_name 
        WHERE user_id IS NULL AND ($where_clause)
        AND event_type = 'pageview'
    ) AS unique_visitors";

        //     SELECT 
        //     COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) +
        //     COUNT(DISTINCT CASE WHEN user_id IS NULL THEN unique_session_id END) AS unique_visitors
        // FROM wp_analytics_wp_events_big
        // WHERE event_type = 'pageview' AND (1=1);

        $query = "/* TAG1 */ SELECT
            COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN user_id END) +
            COUNT(DISTINCT CASE WHEN user_id IS NULL THEN unique_session_id END) AS unique_visitors
            FROM $table_name
            WHERE event_type = 'pageview' AND ($where_clause)";

        $count_unique_visitors = (int)$wpdb->get_var($query);
        // var dump the explain analyze of this query

        $query_with_comparison = str_replace($where_clause, $where_clause_comparison, $query);
        $count_unique_visitors_comparison = (int)$wpdb->get_var($query_with_comparison);

        $unique_visitors = [
            'name' => 'Unique people',
            'value' => $count_unique_visitors,
            'change' => self::percentage_diff($count_unique_visitors, $count_unique_visitors_comparison),
            'comparison_value' => $count_unique_visitors_comparison,
        ];

        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
        $page_view_data = Event::count_total_pageviews($date_range, $custom_sql, true);
        $count_total_pageviews = $page_view_data['count'];
        $count_total_pageviews_comparison = $page_view_data['comparison_count'];

        $total_pageviews = [
            'name' => 'Total pageviews',
            'value' => $count_total_pageviews,
            'change' => self::percentage_diff($count_total_pageviews, $count_total_pageviews_comparison),
            'comparison_value' => $count_total_pageviews_comparison,
        ];

        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
        $count_views_per_visitor = $count_unique_visitors > 0 ? $count_total_pageviews / $count_unique_visitors : 0;
        $count_views_per_visitor_comparison = $count_unique_visitors_comparison > 0 ? $count_total_pageviews_comparison / $count_unique_visitors_comparison : 0;

        $views_per_visit = [
            'name' => 'Views per person',
            'value' => $count_views_per_visitor,
            'change' => self::percentage_diff($count_views_per_visitor, $count_views_per_visitor_comparison),
            'comparison_value' => $count_views_per_visitor_comparison,
        ];

        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
        // The percentage of visitors with a single page view. 
        // A visitor "bounces" away and leaves your site after only viewing a single page.

        $single_pageview_visitors_query = "/* TAG2 */ SELECT 
    (
        SELECT COUNT(DISTINCT user_id) 
        FROM 
        (
            SELECT user_id
            FROM $table_name 
            WHERE event_type = 'pageview' AND user_id IS NOT NULL AND ($where_clause)
            GROUP BY user_id 
            HAVING COUNT(*) = 1
        ) AS single_page_user_sessions
    )
    +
    (
        SELECT COUNT(DISTINCT unique_session_id) 
        FROM 
        (
            SELECT unique_session_id
            FROM $table_name 
            WHERE event_type = 'pageview' AND user_id IS NULL AND ($where_clause)
            GROUP BY unique_session_id 
            HAVING COUNT(*) = 1
        ) AS single_page_unique_sessions
    ) AS total_single_pageview_visitors;";

        $count_single_pageview_visitors = (int)$wpdb->get_var($single_pageview_visitors_query);

        $single_pageview_visitors_query_comparison = str_replace($where_clause, $where_clause_comparison, $single_pageview_visitors_query);
        $count_single_pageview_visitors_comparison = (int)$wpdb->get_var($single_pageview_visitors_query_comparison);

        $bounce_rate = $count_unique_visitors > 0 ? ($count_single_pageview_visitors / $count_unique_visitors) * 100.0 : 0;
        $bounce_rate_comparison = $count_unique_visitors_comparison > 0 ? ($count_single_pageview_visitors_comparison / $count_unique_visitors_comparison) * 100.0 : 0;

        $bounce_rate = [
            'name' => 'Window shoppers rate',
            'value' => $bounce_rate,
            'change' => self::percentage_diff($bounce_rate, $bounce_rate_comparison),
            'comparison_value' => $bounce_rate_comparison,
        ];

        //////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////
        return [
            $unique_visitors,
            $total_pageviews,
            $views_per_visit,
            $bounce_rate
        ];
    }


    /**
     * @param DateRange $date_range
     * @param string $custom_sql
     * 
     * @return MainGraphData
     */
    private static function computeMainGraphData($date_range, $custom_sql = '')
    {
        // TODO the server should not return a selected top stat name.
        // It should just be the one currently selected by the front end. 
        $selectedTopStatName = APIServer::TOP_STAT_NAMES[array_rand(APIServer::TOP_STAT_NAMES)];

        $topStats = self::computeTopStats($date_range, $custom_sql);

        $chartData = self::computeChartData($date_range, $custom_sql);

        return [
            'currently_selected_top_stat_name' => $selectedTopStatName,
            'top_stats' => $topStats,
            'chart_data' => $chartData,
        ];
    }

    /**
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return int
     */
    public static function generateRealtimeStatsDashboardData()
    {
        global $wpdb;

        $table_name = Event::table_name();

        $dateRange = new DateRange('Realtime');
        $date_query = $dateRange->toSql();

        $unique_visitors_query = "/* TAG3 */ SELECT 
        (
            SELECT COUNT(DISTINCT user_id) 
            FROM $table_name 
            WHERE user_id IS NOT NULL $date_query
            AND event_type = 'pageview'
        ) 
        + 
        (
            SELECT COUNT(DISTINCT unique_session_id) 
            FROM $table_name 
            WHERE user_id IS NULL $date_query
            AND event_type = 'pageview'
        ) AS unique_visitors;";

        $count_unique_visitors = (int)$wpdb->get_var($unique_visitors_query);

        return $count_unique_visitors;
    }


    /**
     * Generates random stats dashboard data.
     *
     * @param DateRange $date_range
     * @param string $custom_sql
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return StatsDashboardData
     */
    public static function generateStatsDashboardData($date_range, $custom_sql = '')
    {
        global $wpdb;
        // return [];
        $time_start = microtime(true);

        $top_pages = self::computeTopPages($date_range, $custom_sql);
        $top_sources = self::computeTopSources($date_range, $custom_sql);
        $top_countries = self::computeTopCountries($date_range, $custom_sql);
        $top_devices = self::computeTopDevices($date_range, $custom_sql);

        $top_time_taken = microtime(true) - $time_start;


        $main_graph = self::computeMainGraphData($date_range, $custom_sql);

        $query_time = microtime(true) - $time_start;
        $total_events_in_table = Event::count();

        $queries = Validators::arr($wpdb->queries);

        // find the queries that contain the string "analytics_wp_events"
        $relevant_queries = array_filter($queries, function ($query) {
            $query = Validators::arr($query);
            $query_0 = isset($query[0]) ? (string)$query[0] : '';
            return strpos($query_0, 'analytics_wp_events') !== false && strpos($query_0, 'CREATE') === false && strpos($query_0, 'indexIsThere') === false;
        });

        $saved_filters = SavedFilters::get_saved_filters();

        return [
            'is_loading' => false,
            'filters' => [
                'date_range' => $date_range->range,
                'custom_date_range' => [
                    'start_date' => $date_range->start_date ?? '-',
                    'end_date' => $date_range->end_date ?? '-',
                ],
                'custom_sql' => $custom_sql,
            ],
            'main_graph' => $main_graph,
            'top_sources' => $top_sources,
            'top_pages' => $top_pages,
            'top_countries' => $top_countries,
            'top_devices' => $top_devices,
            // Debug info, not actually used by front end
            // but I can see it in the network response in js console.
            'debug' => [
                'query_time' => $query_time,
                'top_time_taken' => $top_time_taken,
                'total_events_in_table' => $total_events_in_table,
                'relevant_queries' => $relevant_queries,
            ],
            'saved_filters' => $saved_filters,
        ];
    }
}
