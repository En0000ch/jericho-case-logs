<?php

namespace AnalyticsWP\Lib;

/**
 * 
 * @psalm-import-type DateRangeValue from \AnalyticsWP\Lib\DateRange
 * 
 * @psalm-type JourneyEvent = array{
 *         event_id: string,
 *         timestamp: string,
 *         event_type: string,
 *         page_url: string,
 *         referrer: string,
 *         device_type: string,
 *         conversion_type: string,
 *         conversion_id?: int,
 *         event_properties_json?: string
 * }
 * 
 * @psalm-type EventInsertData = array{
 *         event_type: string,
 *         unique_session_id: string,
 *         conversion_type?: string|null,
 *         conversion_id?: int|null,
 *         referrer?: string|null,
 *         page_url?: string|null,
 *         device_type?: string|null,
 *         user_agent?: string|null,
 *         ip_address?: string|null,
 *         utm_source?: string|null,
 *         utm_medium?: string|null,
 *         utm_campaign?: string|null,
 *         utm_term?: string|null,
 *         utm_content?: string|null,
 *         user_id?: int|null,
 *         user_email?: string|null,
 *         timestamp?: string|null,
 *         event_properties_json?: string|null,
 *         unique_event_identifier?: string|null
 * }
 * 
 * @psalm-type EventData = EventInsertData & array{id: int}
 * 
 * @psalm-type IdentifiedJourney = array{
 *     user_id: int,
 *     unique_session_id?: string,
 *     events: list<JourneyEvent>
 * }
 * 
 * @psalm-type AnonymousJourney = array{
 *     unique_session_id: string,
 *     user_id?: int,
 *     events: list<JourneyEvent>
 * }
 * 
 * @psalm-type SourceData = array{
 *      referrer: string|null,
 *      landing_page: string|null,
 *      utm_source: string|null,
 *      utm_medium: string|null,
 *      utm_campaign: string|null,
 *      utm_term: string|null,
 *      source_event_id: int|null,
 * }
 * 
 * 
 */
class Event
{
    /**
     * @var string
     */
    private static $table_name = 'analytics_wp_events';
    // private static $table_name = 'analytics_wp_events_big';

    /**
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return string
     */
    public static function table_name()
    {
        global $wpdb;

        return $wpdb->prefix . self::$table_name;
    }


    /**
     * @return array<array-key, mixed>|false Table creation results, or false.
     */
    public static function create_table_if_necessary()
    {
        if (defined('ANALYTICSWP_VERSION')) {
            $current_version = ANALYTICSWP_VERSION;
            $most_recent_version_for_create_table = Validators::str(get_option('analyticswp_version_create_table', '1.0.0'));
            if (version_compare($current_version, $most_recent_version_for_create_table, '>')) {
                // create the table, run any necessary migrations
                $table_creation_results = self::create_table();
                // update the version in the database
                update_option('analyticswp_version_create_table', $current_version);

                return $table_creation_results;
            }
            return false;
        }
        return false;
    }

    /**
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return array<array-key, mixed> dbDelta result
     */
    public static function create_table()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = self::table_name();

        // Support for versions below 5.6, we can't use the `DEFAULT CURRENT_TIMESTAMP` syntax
        // So, we check the version and use the appropriate syntax

        $mysql_version = $wpdb->db_version();
        if (!is_null($mysql_version) && version_compare($mysql_version, '5.6', '<')) {
            $timestamp_default = 'NULL';
        } else {
            $timestamp_default = 'CURRENT_TIMESTAMP';
        }

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            unique_session_id VARCHAR(255) NOT NULL,
            event_type VARCHAR(255) NOT NULL,
            conversion_type VARCHAR(255) DEFAULT NULL,
            conversion_id BIGINT(20) UNSIGNED DEFAULT NULL,
            referrer TEXT DEFAULT NULL,
            page_url TEXT DEFAULT NULL,
            device_type VARCHAR(50) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            utm_source VARCHAR(255) DEFAULT NULL,
            utm_medium VARCHAR(255) DEFAULT NULL,
            utm_campaign VARCHAR(255) DEFAULT NULL,
            utm_term VARCHAR(255) DEFAULT NULL,
            utm_content TEXT DEFAULT NULL,
            user_id BIGINT(20) UNSIGNED DEFAULT NULL,
            user_email VARCHAR(255) DEFAULT NULL,
            unique_event_identifier VARCHAR(255) DEFAULT NULL,
            event_properties_json longtext DEFAULT NULL,
            timestamp DATETIME DEFAULT $timestamp_default,
            PRIMARY KEY (id)
        ) $charset_collate;";


        if (defined('ABSPATH')) {
            $path = ABSPATH . 'wp-admin/includes/upgrade.php';
            if (file_exists($path)) {
                require_once($path);
            }
        }

        $db_delta_result = dbDelta($sql);

        // Indexes
        if (!self::index_exists($table_name, "idx_usession")) {
            $index_query = "CREATE INDEX idx_usession ON {$table_name} (unique_session_id)";
            $wpdb->query($index_query);
        }

        if (!self::index_exists($table_name, "idx_event_type")) {
            $index_query = "CREATE INDEX idx_event_type ON {$table_name} (event_type)";
            $wpdb->query($index_query);
        }

        if (!self::index_exists($table_name, "idx_user_id")) {
            $index_query = "CREATE INDEX idx_user_id ON {$table_name} (user_id)";
            $wpdb->query($index_query);
        }
        // timestamp index
        if (!self::index_exists($table_name, "idx_timestamp")) {
            $index_query = "CREATE INDEX idx_timestamp ON {$table_name} (timestamp)";
            $wpdb->query($index_query);
        }

        if (!self::index_exists($table_name, "idx_user_id_timestamp")) {
            $index_query = "CREATE INDEX idx_user_id_timestamp ON {$table_name} (user_id, timestamp);";
            $wpdb->query($index_query);
        }

        if (!self::index_exists($table_name, "idx_session_user_timestamp")) {
            $index_query = "CREATE INDEX idx_session_user_timestamp ON {$table_name} (unique_session_id, user_id, timestamp);";
            $wpdb->query($index_query);
        }

        // CREATE INDEX idx_event_type_timestamp ON analytics_wp_events (event_type, timestamp);
        // CREATE INDEX idx_referrer_pageview ON analytics_wp_events (referrer, event_type, timestamp);

        // no diff on time
        if (!self::index_exists($table_name, "idx_event_type_timestamp")) {
            $index_query = "CREATE INDEX idx_event_type_timestamp ON {$table_name} (event_type, timestamp);";
            $wpdb->query($index_query);
        }

        // New composite indexes for improved unique visitor queries:
        // dropped from ~19s to ~16s
        if (!self::index_exists($table_name, "idx_event_type_user_id_timestamp")) {
            $index_query = "CREATE INDEX idx_event_type_user_id_timestamp ON {$table_name} (event_type, user_id, timestamp)";
            $wpdb->query($index_query);
        }

        return $db_delta_result;
    }

    /**
     * @param string $table_name
     * @param string $index_name
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return bool
     */
    public static function index_exists($table_name, $index_name)
    {
        global $wpdb;

        $query = (string)$wpdb->prepare(
            "SELECT COUNT(1) indexIsThere 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE table_schema=DATABASE() 
            AND table_name=%s 
            AND index_name=%s;",
            $table_name,
            $index_name
        );

        // If the result is 0, then the index doesn't exist
        return ($wpdb->get_var($query) == 0) ? false : true;
    }

    /**
     * Attempts to check that a value is a valid EventData object.
     * It doesn't actually do it all thought, it's just a basic check at this point.
     * 
     * We need to consider performance implications of using this. 
     * 
     * What if we want to get a giant list of events and cast them all to EventData objects?
     * 
     * 
     * @param mixed $val
     * @return EventData|null
     */
    public static function cast($val)
    {
        if (is_null($val) || empty($val) || !is_array($val)) {
            return null;
        }

        /** @var EventData */
        return $val;
    }

    /**
     * Undocumented function
     *
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @param string $sql
     * @return EventData|null
     */
    public static function db_get_row($sql)
    {
        global $wpdb;

        return self::cast($wpdb->get_row($sql, 'ARRAY_A'));
    }

    /**
     * @param EventInsertData $data
     * @param bool $force_timestamp
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return array{error: non-empty-string}|int
     */
    public static function insert($data, $force_timestamp = true)
    {
        global $wpdb;
        $table_name = self::table_name();

        if ($force_timestamp) {
            $data['timestamp'] = current_time('mysql', true);
        }

        $result = $wpdb->insert($table_name, $data);

        if ($result === false) {
            $error = 'Error inserting data into ' . $table_name . ': ' . $wpdb->last_error;
            error_log($error);
            return [
                'error' => $error,
            ];
        }

        $unique_session_id = $data['unique_session_id'];
        $user_id = isset($data['user_id']) ? $data['user_id'] : null;
        self::identify_user($unique_session_id, $user_id);

        return $wpdb->insert_id;
    }


    /**
     * @param mixed $data
     * @return EventInsertData|null
     */
    public static function validate_event_insert_data($data)
    {
        // Basic checks, array, null, etc
        if (!is_array($data) || empty($data)) {
            return null;
        }

        // Initialize an array for validated data
        $validatedData = [];

        // Validate `event_type`
        if (isset($data['event_type']) && is_string($data['event_type'])) {
            $validatedData['event_type'] = $data['event_type'];
        } else {
            return null;
        }

        // Validate `unique_session_id`
        if (isset($data['unique_session_id']) && is_string($data['unique_session_id'])) {
            $validatedData['unique_session_id'] = $data['unique_session_id'];
        } else {
            return null;
        }

        // Validate `conversion_type`
        if (isset($data['conversion_type'])) {
            if ($data['conversion_type'] === null || is_string($data['conversion_type'])) {
                $validatedData['conversion_type'] = $data['conversion_type'];
            } else {
                return null;
            }
        }

        // Validate `conversion_id`
        if (isset($data['conversion_id'])) {
            if ($data['conversion_id'] === null || is_int($data['conversion_id'])) {
                $validatedData['conversion_id'] = $data['conversion_id'];
            } else {
                return null;
            }
        }

        // Validate `referrer`
        if (isset($data['referrer'])) {
            if ($data['referrer'] === null || is_string($data['referrer'])) {
                $validatedData['referrer'] = $data['referrer'];
            } else {
                return null;
            }
        }

        // Validate `page_url`
        if (isset($data['page_url'])) {
            if ($data['page_url'] === null || is_string($data['page_url'])) {
                $validatedData['page_url'] = $data['page_url'];
            } else {
                return null;
            }
        }

        // Validate `device_type`
        if (isset($data['device_type'])) {
            if ($data['device_type'] === null || is_string($data['device_type'])) {
                $validatedData['device_type'] = $data['device_type'];
            } else {
                return null;
            }
        }

        // Validate `user_agent`
        if (isset($data['user_agent'])) {
            if ($data['user_agent'] === null || is_string($data['user_agent'])) {
                $validatedData['user_agent'] = $data['user_agent'];
            } else {
                return null;
            }
        }

        // Validate `ip_address`
        if (isset($data['ip_address'])) {
            if ($data['ip_address'] === null || is_string($data['ip_address'])) {
                $validatedData['ip_address'] = $data['ip_address'];
            } else {
                return null;
            }
        }

        // Validate `utm_source`
        if (isset($data['utm_source'])) {
            if ($data['utm_source'] === null || is_string($data['utm_source'])) {
                $validatedData['utm_source'] = $data['utm_source'];
            } else {
                return null;
            }
        }

        // Validate `utm_medium`
        if (isset($data['utm_medium'])) {
            if ($data['utm_medium'] === null || is_string($data['utm_medium'])) {
                $validatedData['utm_medium'] = $data['utm_medium'];
            } else {
                return null;
            }
        }

        // Validate `utm_campaign`
        if (isset($data['utm_campaign'])) {
            if ($data['utm_campaign'] === null || is_string($data['utm_campaign'])) {
                $validatedData['utm_campaign'] = $data['utm_campaign'];
            } else {
                return null;
            }
        }

        // Validate `utm_term`
        if (isset($data['utm_term'])) {
            if ($data['utm_term'] === null || is_string($data['utm_term'])) {
                $validatedData['utm_term'] = $data['utm_term'];
            } else {
                return null;
            }
        }

        // Validate `utm_content`
        if (isset($data['utm_content'])) {
            if ($data['utm_content'] === null || is_string($data['utm_content'])) {
                $validatedData['utm_content'] = $data['utm_content'];
            } else {
                return null;
            }
        }

        // Validate `user_id`
        if (isset($data['user_id'])) {
            if ($data['user_id'] === null || is_int($data['user_id'])) {
                $validatedData['user_id'] = $data['user_id'];
            } else {
                return null;
            }
        }

        // Validate `user_email`
        if (isset($data['user_email'])) {
            if ($data['user_email'] === null || is_string($data['user_email'])) {
                $validatedData['user_email'] = $data['user_email'];
            } else {
                return null;
            }
        }

        // Validate `timestamp`
        if (isset($data['timestamp'])) {
            if ($data['timestamp'] === null || is_string($data['timestamp'])) {
                $validatedData['timestamp'] = $data['timestamp'];
            } else {
                return null;
            }
        }

        // Validate `event_properties_json`
        if (isset($data['event_properties_json'])) {
            if ($data['event_properties_json'] === null || is_string($data['event_properties_json'])) {
                $validatedData['event_properties_json'] = $data['event_properties_json'];
            } else {
                return null;
            }
        }

        // Validate `unique_event_identifier`
        if (isset($data['unique_event_identifier'])) {
            if ($data['unique_event_identifier'] === null || is_string($data['unique_event_identifier'])) {
                $validatedData['unique_event_identifier'] = $data['unique_event_identifier'];
            } else {
                return null;
            }
        }

        return $validatedData;
    }

    /**
     * Identifies and updates user information across related events.
     *
     * This function performs two types of identification:
     * 1. "Backwards" identification: If a user_id is provided, it updates all previous
     *    events with the same unique_session_id to have this user_id.
     * 2. "Forwards" identification: If no user_id is provided, it looks for the most
     *    recent event with the same unique_session_id that has a user_id, and updates
     *    the current event with that user_id.
     *
     * This process ensures consistency of user identification across multiple events
     * in the same session, handling both authenticated and initially anonymous users.
     *
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @param string $unique_session_id
     * @param int|null $user_id
     * @return void
     */
    public static function identify_user($unique_session_id, $user_id)
    {
        global $wpdb;
        $table_name = self::table_name();

        // IDENTIFY "BACKWARDS
        if ($user_id !== null && $user_id !== 0) {
            // Identify "backwards"
            $update_sql = (string)$wpdb->prepare(
                "UPDATE `$table_name` SET `user_id` = %d WHERE `unique_session_id` = %s AND (`user_id` <> %d OR `user_id` IS NULL);",
                $user_id,
                $unique_session_id,
                $user_id
            );
            $wpdb->query($update_sql);

            $_error = $wpdb->last_error;
        } else {
            // IDENTIFY "FORWARDS"
            // Find the first event with this unique_session_id that has a user_id and update the current event with that user_id
            $most_recent_user_id_for_event_with_matching_unique_session_id = $wpdb->get_var(
                (string)$wpdb->prepare(
                    "SELECT `user_id` FROM `$table_name` WHERE `unique_session_id` = %s AND `user_id` IS NOT NULL AND `user_id` <> 0 ORDER BY `timestamp` ASC LIMIT 1",
                    $unique_session_id
                )
            );

            // if the most_recent_user_id_for_event_with_matching_unique_session_id is not null or 0, update the current event with that user_id 
            if ($most_recent_user_id_for_event_with_matching_unique_session_id !== null && $most_recent_user_id_for_event_with_matching_unique_session_id != 0) {
                $update_sql = (string)$wpdb->prepare(
                    "UPDATE `$table_name` SET `user_id` = %d WHERE `unique_session_id` = %s AND (`user_id` IS NULL OR `user_id` = 0);",
                    $most_recent_user_id_for_event_with_matching_unique_session_id,
                    $unique_session_id
                );
                $wpdb->query($update_sql);
                $_error = $wpdb->last_error;
            }
        }
    }

    /**
     * @param int $id
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return array|object|null|void
     */
    public static function find($id)
    {
        global $wpdb;
        return self::db_get_row((string)$wpdb->prepare("SELECT * FROM " . self::table_name() . " WHERE id = %d", $id));
    }

    /**
     * @param array<string,string> $conditions
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return EventData[]
     */
    public static function where($conditions)
    {
        global $wpdb;
        $table_name = self::table_name();

        $where_clauses = [];
        $where_values = [];

        foreach ($conditions as $column => $value) {
            $where_clauses[] = "$column = %s";
            $where_values[] = $value;
        }

        $where_sql = implode(' AND ', $where_clauses);

        $query = (string)$wpdb->prepare("SELECT * FROM $table_name WHERE $where_sql", ...$where_values);

        /** @var EventData[] */
        return Validators::arr($wpdb->get_results($query, 'ARRAY_A'));
    }

    /**
     * @param array<string,string> $conditions
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return EventData|null
     */
    public static function find_where($conditions)
    {
        $results = self::where($conditions);
        return count($results) > 0 ? $results[0] : null;
    }

    /**
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return int
     */
    public static function count()
    {
        global $wpdb;
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM " . self::table_name());
    }

    /**
     * Get events that occurred between two timestamps
     * 
     * @param string $start_time MySQL DATETIME format (YYYY-MM-DD HH:MM:SS)
     * @param string $end_time MySQL DATETIME format (YYYY-MM-DD HH:MM:SS)
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return EventData[]
     */
    public static function getBetweenTimes(string $start_time, string $end_time): array
    {
        global $wpdb;
        $table_name = self::table_name();

        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE timestamp BETWEEN %s AND %s 
             ORDER BY id DESC",  // Changed to order by ID
            $start_time,
            $end_time
        );

        /** @var EventData[] */
        return Validators::arr($wpdb->get_results($query, 'ARRAY_A'));
    }

    /**
     * @template T as bool
     *
     * @param DateRange $date_range
     * @param string $custom_sql
     * @param T $also_return_comparison
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return int | array{count: int, comparison_count: int}
     * @psalm-return (T is true ? array{count: int, comparison_count: int} : int)
     */
    public static function count_total_pageviews($date_range, $custom_sql = '', $also_return_comparison = false)
    {
        global $wpdb;
        $table_name = self::table_name();

        // $date_query = $date_range->toSql();
        $where_clause = APIServerRealData::build_where_clause($date_range, $custom_sql);
        $where_clause_for_comparison = APIServerRealData::build_where_clause($date_range, $custom_sql, true);

        $query = "/* TAG10 */ SELECT 
                COUNT(*) - COUNT(CASE WHEN event_type != 'pageview' THEN 1 END) 
             FROM
                $table_name
             WHERE $where_clause
            ";

        $count = (int)$wpdb->get_var($query);

        if ($also_return_comparison) {
            $comparison_query = str_replace($where_clause, $where_clause_for_comparison, $query);
            $comparison_count = (int)$wpdb->get_var($comparison_query);

            return [
                'count' => $count,
                'comparison_count' => $comparison_count
            ];
        } else {
            return $count;
        }
    }

    // Add more ORM methods as required...

    ////////////

    /**
     * @global \wpdb $wpdb The WordPress database class instance.
     *
     * @return array{error:non-empty-string}|int
     */
    public static function insert_random()
    {
        global $wpdb;

        // Possible device_type types
        $device_types = array('desktop', 'mobile', 'tablet');

        // Randomly select a device_type type
        $random_device_type = $device_types[array_rand($device_types)];

        // Randomly select an event type with a 1% chance of being 'conversion'
        $random_number = rand(1, 100);
        if ($random_number === 1) {
            $random_event_type = 'conversion';
        } else {
            $random_event_type = 'pageview';
        }

        // if the event type is 'conversion', select a conversion type (for now just support 'woocommerce_order')
        // we also need a conversion_id, which is the ID of a real WooCommerce order. If none exist in the DB just put 0.
        if ($random_event_type === 'conversion') {
            $conversion_types = array('woocommerce_order');
            $random_conversion_type = $conversion_types[array_rand($conversion_types)];
            $random_conversion_id = 0;
            switch ($random_conversion_type) {
                case 'woocommerce_order':
                    $table_name = $wpdb->prefix . 'posts';
                    $query = "SELECT ID FROM $table_name WHERE post_type = 'shop_order' ORDER BY RAND() LIMIT 1";
                    $random_conversion_id = (int)$wpdb->get_var($query);
                    break;
            }
        } else {
            $random_conversion_type = null;
            $random_conversion_id = null;
        }

        // Generate random IP address (this is a simple mockup, not a valid IP generation logic)
        $random_ip = mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255);

        // Sample User Agents (just for demonstration, ideally you'd have a bigger list or another method to generate them)
        $user_agents = array(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
            'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
            'Mozilla/5.0 (Windows NT 5.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko)',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) CriOS/56.0.2924.75 Mobile/14E5239e Safari/602.1'
        );
        $random_user_agent = $user_agents[array_rand($user_agents)];

        // Random timestamp, sometime between now and 1 month ago
        $seconds_in_a_month = 30 * 24 * 60 * 60; // approximately a month
        $random_offset = mt_rand(0, $seconds_in_a_month);
        $random_timestamp = date('Y-m-d H:i:s', time() - $random_offset);

        // Construct the data array
        $data = array(
            'unique_session_id' => (string)mt_rand(1, 50),
            'event_type' => $random_event_type,
            'page_url' => 'https://example.com/page' . mt_rand(1, 1000),
            'referrer' => 'https://google.com/search?q=example',
            'device_type' => $random_device_type,
            'user_agent' => $random_user_agent,
            'ip_address' => $random_ip,
            'timestamp' => $random_timestamp,
            'conversion_type' => $random_conversion_type,
            'conversion_id' => $random_conversion_id
        );

        // Insert the data
        return self::insert($data);
    }

    // Basic stats

    /**
     * @global \wpdb $wpdb The WordPress database class instance.
     *
     * @return int
     */
    public static function count_unique_sessions()
    {
        global $wpdb;
        return (int)$wpdb->get_var("SELECT COUNT(DISTINCT unique_session_id) FROM " . self::table_name());
    }

    /**
     * @global \wpdb $wpdb The WordPress database class instance.
     *
     * @return int
     */
    public static function count_conversions()
    {
        global $wpdb;
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM " . self::table_name() . " WHERE event_type = 'conversion'");
    }





    /**
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return array<string, int>
     */
    public static function get_events_per_day_data()
    {
        global $wpdb;

        $table_name = self::table_name();

        // Fetch pageview counts per day for the last 30 days
        $now = date('Y-m-d H:i:s');
        $results = $wpdb->get_results((string)$wpdb->prepare("
            SELECT DATE(timestamp) as date, COUNT(*) as count
            FROM $table_name
            WHERE event_type = 'pageview'
            GROUP BY DATE(timestamp)
            ORDER BY DATE(timestamp) ASC
        ", $now), 'ARRAY_A');

        $results = Validators::wpdb_results($results, 'date', 'count');

        $data = [];

        // Initialize array with 0 values for all days
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $data[$date] = 0;
        }

        // Populate the array with actual counts
        foreach ($results as $row) {
            $data[$row['date']] = intval($row['count']);
        }

        return $data;
    }

    /**
     * This function returns the count of Unique Journeys per day.
     * 
     * We define a unique journey in a given day if a day has at least one event with a user_id or unique_session_id.
     * 
     * user_id takes precedence over unique_session_id. If a user_id is present, the unique_session_id is ignored.
     * 
     * So once a user_id is counted, all events in that day with the same user_id are ignored (even if they have a unique_session_id).
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return array<string, int>
     */
    public static function get_journeys_per_day_data()
    {
        // TODO IDK IF THIS IS EVEN WORKING I HAVE NOT VERIFIED I JUST COPY-PASTED FROM CHAT GPT
        global $wpdb;

        $table_name = self::table_name();

        // Fetch unique journeys count per day for the last 30 days.
        // We'll use COALESCE to give preference to user_id over unique_session_id.
        // Then, we'll group by date and the COALESCE value to get unique counts.
        $results = $wpdb->get_results((string)$wpdb->prepare("
            SELECT DATE(timestamp) as date, COUNT(DISTINCT COALESCE(user_id, unique_session_id)) as count
            FROM $table_name
            WHERE COALESCE(user_id, unique_session_id) IS NOT NULL
            GROUP BY DATE(timestamp)
            ORDER BY DATE(timestamp) ASC
        "), 'ARRAY_A');

        $results = Validators::wpdb_results($results, 'date', 'count');

        $data = [];

        // Initialize array with 0 values for all days.
        for ($i = 59; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $data[$date] = 0;
        }

        // Populate the array with actual counts.
        foreach ($results as $row) {
            $data[$row['date']] = intval($row['count']);
        }

        // sort the array by date
        ksort($data);

        return $data;
    }


    /**
     * Returns data to render all Journeys as dots in a grid with different colors for:
     * - anonymous
     * - identified (but no conversion event within the journey)
     * - identified (with at least one conversion event within the journey)
     *
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @param array{date_range: 'Last 30 days'|'Last 7 days'|'All time', min_events: int} $filters The current filters from the URL.
     * 
     * @return array{
     *  type: 'anonymous'|'identified'|'anonymous_with_conversion'|'identified_with_conversion',
     *  id: string,
     *  admin_url: string,
     *  conversion_count: int,
     *  event_count: int,
     *  user_login: string,
     *  user_email: string,
     *  first_event_timestamp: string,
     *  last_event_timestamp: string
     * }[]
     * 
     */
    public static function get_journey_dots_data($filters)
    {

        global $wpdb;
        $table_name = self::table_name();
        $users_table_name = $wpdb->users; // This gets the correct 'users' table name with prefix

        $dateRange = new DateRange($filters['date_range']);
        $date_query = $dateRange->toSql();
        $minimum_event_count = $filters['min_events'];

        // Fetch unique journeys along with the presence of a conversion event.
        $results = $wpdb->get_results("
        SELECT 
            COALESCE(NULLIF(wpe.user_id, 0), NULLIF(wpe.unique_session_id, 0)) as journey_id,
            MAX(wpe.user_id IS NOT NULL AND wpe.user_id <> 0) as is_identified,
            SUM(CASE WHEN wpe.conversion_type IS NOT NULL THEN 1 ELSE 0 END) as conversion_count,
            COUNT(1) as event_count,
            MIN(wpe.timestamp) as first_event_timestamp,
            MAX(wpe.timestamp) as last_event_timestamp,
            wpu.user_login,
            wpu.user_email
        FROM $table_name wpe
        LEFT JOIN $users_table_name wpu ON wpe.user_id = wpu.ID
        WHERE ((wpe.user_id IS NOT NULL AND wpe.user_id <> 0) OR (wpe.unique_session_id IS NOT NULL AND wpe.unique_session_id <> 0)) $date_query
        GROUP BY COALESCE(NULLIF(wpe.user_id, 0), NULLIF(wpe.unique_session_id, 0)), wpu.user_login, wpu.user_email
        HAVING event_count >= $minimum_event_count 
        ", 'ARRAY_A');

        $results = Validators::wpdb_results(
            $results,
            'journey_id',
            'is_identified',
            'conversion_count',
            'event_count',
            'user_login',
            'user_email',
            'first_event_timestamp',
            'last_event_timestamp'
        );

        $data = [];

        // Classify the journeys based on the fetched data.
        foreach ($results as $row) {
            if (!$row['is_identified']) {
                $type = 'anonymous';
                $admin_url = admin_url('admin.php?page=anonymous_journeys&unique_session_id=' . $row['journey_id']);
                if ((int)$row['conversion_count'] > 0) {
                    $type = 'anonymous_with_conversion';
                    // TODO
                    $admin_url = admin_url('admin.php?page=anonymous_journeys&unique_session_id=' . $row['journey_id']);
                }
            } elseif ((int)$row['conversion_count'] > 0) {
                $type = 'identified_with_conversion';
                // TODO
                $admin_url = admin_url('admin.php?page=user_journeys&user_id=' . $row['journey_id']);
            } else {
                $type = 'identified';
                $admin_url = admin_url('admin.php?page=user_journeys&user_id=' . $row['journey_id']);
            }

            $data[] = [
                'type' => $type,
                'id' => $row['journey_id'],
                'admin_url' => $admin_url,
                'conversion_count' => (int)$row['conversion_count'],
                'event_count' => (int)$row['event_count'],
                'user_login' => $row['user_login'],
                'user_email' => $row['user_email'],
                'first_event_timestamp' => Timezone::t($row['first_event_timestamp']),
                'last_event_timestamp' => Timezone::t($row['last_event_timestamp'])
            ];
        }

        $data = array_reverse($data);
        return $data;
    }



    /**
     * Fetches and returns aggregate statistics data.
     * 
     * @global \wpdb $wpdb The WordPress database class instance. 
     * 
     * @return array{
     *  gross_revenue: float,
     *  total_journeys: int,
     *  identified_journeys: int,
     *  anonymous_journeys: int,
     *  total_events: int
     * }
     * 
     */
    public static function get_aggregate_stats_data()
    {
        global $wpdb;

        $table_name = self::table_name();

        // Assuming there's a 'revenue' column in the table for gross revenue. If not, this needs to be adjusted.
        $gross_revenue = '1000000';
        // $gross_revenue = $wpdb->get_var("SELECT SUM(revenue) FROM $table_name");

        // Total unique journeys count
        $total_journeys = (int)$wpdb->get_var("SELECT COUNT(DISTINCT COALESCE(user_id, unique_session_id)) FROM $table_name WHERE COALESCE(user_id, unique_session_id) IS NOT NULL");

        // Identified journeys count
        $identified_journeys = (int)$wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table_name WHERE user_id IS NOT NULL");

        // Anonymous journeys count
        $anonymous_journeys = $total_journeys - $identified_journeys;

        // Total events count
        $total_events = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        $aggregate_stats_data = [
            'gross_revenue' => floatval($gross_revenue),
            'total_journeys' => intval($total_journeys),
            'identified_journeys' => intval($identified_journeys),
            'anonymous_journeys' => intval($anonymous_journeys),
            'total_events' => intval($total_events)
        ];

        return $aggregate_stats_data;
    }



    /**
     * @global \wpdb $wpdb The WordPress database class instance. 
     * 
     * @return string
     */
    public static function timestamp_of_first_event()
    {
        global $wpdb;
        return (string)$wpdb->get_var("SELECT timestamp FROM " . self::table_name() . " ORDER BY timestamp ASC LIMIT 1");
    }

    /**
     * Retrieves all anonymous user journeys from the database.
     *
     * This function fetches the journeys of users who visited the site anonymously.
     * Each journey is grouped by the `unique_session_id` and consists of a series 
     * of events. Optionally, only journeys containing at least one
     * event of the specified type are returned.
     *
     * @param string|null $necessary_event_type The required event type to filter journeys.
     * 
     * @return AnonymousJourney[]
     * 
     */
    public static function get_all_anonymous_journeys(?string $necessary_event_type = null): array
    {
        $condition = "(user_id IS NULL OR user_id = 0)";
        $groupBy = "unique_session_id";
        return self::get_journeys($necessary_event_type, $condition, $groupBy);
    }

    /**
     * Retrieves all Identified user journeys from the database.
     *
     * This function fetches the journeys of users who have logged in to the site.
     * Each journey is grouped by the `user_id` and consists of a series 
     * of events. Optionally, only journeys containing at least one
     * event of the specified type are returned.
     *
     * @param string|null $necessary_event_type The required event type to filter journeys.
     * 
     * @return IdentifiedJourney[] 
     * 
     */
    public static function get_all_identified_journeys(?string $necessary_event_type = null): array
    {
        $condition = "(user_id IS NOT NULL AND user_id != 0)";
        $groupBy = "user_id";
        return self::get_journeys($necessary_event_type, $condition, $groupBy);
    }

    /**
     * Retrieves journeys based on the given parameters.
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @template TgroupBy of 'user_id'|'unique_session_id'
     *
     * @param string|null $necessary_event_type The required event type to filter journeys.
     * @param string|null $condition SQL condition for filtering user types (anonymous/identified).
     * @param TgroupBy $groupBy SQL grouping criteria (user_id/unique_session_id).
     * @param int|string|null $groupByValue Specific value of the $groupBY to fetch the journey for.
     * 
     * @psalm-return (TgroupBy is 'user_id' ? IdentifiedJourney[] : AnonymousJourney[])
     * 
     */
    private static function get_journeys(?string $necessary_event_type, ?string $condition = null, string $groupBy = 'unique_session_id', $groupByValue = null): array
    {
        global $wpdb;

        $groupBy = Validators::one_of(['unique_session_id', 'user_id'], 'unique_session_id', $groupBy);

        $table_name = self::table_name();

        $wpdb->query("SET SESSION group_concat_max_len = 1000000;");

        $sql = "
        SELECT $groupBy, 
            GROUP_CONCAT(
                CONCAT(id, '|||', 
                        timestamp, '|||', 
                        event_type, '|||', 
                        IFNULL(page_url, ''), '|||', 
                        IFNULL(referrer, ''), '|||',
                        IFNULL(device_type, ''), '|||',
                        IFNULL(conversion_type, ''), '|||',
                        IFNULL(conversion_id, ''), '|||',
                        IFNULL(event_properties_json, '')
                ) 
                ORDER BY timestamp ASC
                SEPARATOR '~~~'
            ) AS event_details
        FROM $table_name 
        WHERE 1=1
    ";

        if (!is_null($condition) && !empty($condition)) {
            $sql .= " AND ($condition)";
        }

        if (!is_null($groupByValue)) {
            $sql .= " AND $groupBy = %s";
            $sql = (string)$wpdb->prepare($sql, $groupByValue);
        } elseif (!is_null($necessary_event_type) && !empty($necessary_event_type)) {
            $sql .= " AND $groupBy IN (
            SELECT DISTINCT $groupBy
            FROM $table_name 
            WHERE event_type = %s
        )";
            $sql = (string)$wpdb->prepare($sql, $necessary_event_type);
        }

        $sql .= " GROUP BY $groupBy";


        $results = !is_null($groupByValue) ? [$wpdb->get_row($sql, 'ARRAY_A')] : $wpdb->get_results($sql, 'ARRAY_A');
        $results = Validators::wpdb_results($results, $groupBy, 'event_details');

        $journeys = array_map(function ($row) use ($groupBy) {
            $row;
            $journey = [];
            $events = explode('~~~', $row['event_details']);
            foreach ($events as $event) {
                if (empty($event)) {
                    continue;
                }
                $details = explode('|||', $event);
                $journey[] = [
                    'event_id' => $details[0],
                    'timestamp' => Timezone::t($details[1]),
                    'event_type' => $details[2],
                    'page_url' => $details[3],
                    'referrer' => $details[4],
                    'device_type' => $details[5],
                    'conversion_type' => $details[6],
                    'conversion_id' => (int)$details[7],
                    'event_properties_json' => $details[8]
                ];
            }

            $groupByValue = $groupBy === 'user_id' ? (int)$row[$groupBy] : $row[$groupBy];
            return [
                $groupBy => $groupByValue,
                'events' => $journey
            ];
        }, $results);

        /**
         * @var (TgroupBy is 'user_id' ? IdentifiedJourney[] : AnonymousJourney[])
         */
        return $journeys;
    }

    /**
     * Retrieves a journey from the database for the specified user_id.
     *
     * @param int $user_id The user's ID for which to retrieve the journey.
     * 
     * @return null|IdentifiedJourney
     * 
     */
    public static function get_journey_for_user_id(int $user_id)
    {
        $journeys = self::get_journeys(null, null, 'user_id', $user_id);
        if ($journeys[0]['user_id'] == 0) {
            return null;
        }
        return $journeys[0];
    }

    /**
     * Retrieves a journey from the database for the specified user_id.
     *
     * @param string $unique_session_id The unique_session_id for which to retrieve the journey.
     * 
     * @return null|AnonymousJourney
     * 
     */
    public static function get_journey_for_unique_session_id(string $unique_session_id)
    {
        $journeys = self::get_journeys(null, null, 'unique_session_id', $unique_session_id);
        if (empty($journeys)) {
            return null;
        }
        return $journeys[0];
    }

    /**
     * Retrieves a journey from the database for the specified conversion_id.
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     *
     * @param int $conversion_id The conversion's ID for which to retrieve the journey.
     * @param string $conversion_type The type of the conversion. Example: 'woocommerce_order', 'edd_order'
     * 
     * @return null|AnonymousJourney|IdentifiedJourney The journey containing the specified conversion_id or null if not found.
     * 
     */
    public static function get_journey_for_conversion_id(int $conversion_id, string $conversion_type)
    {
        // If the conversion_id is 0, return null
        if ($conversion_id == 0) {
            return null;
        }

        global $wpdb;

        $table_name = self::table_name();

        // First, identify the grouping identifier (user_id or unique_session_id) for the given conversion_id
        $sql_identify_grouping = "
        SELECT user_id, unique_session_id 
        FROM $table_name 
        WHERE conversion_id = %d AND conversion_type = %s
        LIMIT 1
    ";

        $sql_identify_grouping = (string)$wpdb->prepare($sql_identify_grouping, $conversion_id, $conversion_type);
        $grouping_info = $wpdb->get_row($sql_identify_grouping, 'ARRAY_A');

        if (is_null($grouping_info)) {
            return null;
        }

        $groupBy = $grouping_info['user_id'] ? 'user_id' : 'unique_session_id';
        $groupingValue = $grouping_info['user_id'] ? (int)$grouping_info['user_id'] : (string)$grouping_info['unique_session_id'];

        // Next, get the entire journey based on the identified grouping identifier
        $journeys = self::get_journeys(null, null, $groupBy, $groupingValue);
        // return just the first journey (there should only be one)
        return $journeys[0];
    }



    /**
     * Get the journey which contains the event with the specified event_id.
     *
     * @param integer $event_id
     * @return null|AnonymousJourney|IdentifiedJourney
     */
    public static function get_journey_for_event_id(int $event_id)
    {
        global $wpdb;

        $table_name = self::table_name();

        $event = Event::find($event_id);
        if ($event === null) {
            return null;
        }

        $event = (array)$event;

        $groupBy = $event['user_id'] ? 'user_id' : 'unique_session_id';

        $journeys = self::get_journeys(null, null, $groupBy, (string)$event[$groupBy]);

        return $journeys[0];
    }


    /**
     * @psalm-assert-if-true IdentifiedJourney $journey
     * @param AnonymousJourney|IdentifiedJourney $journey
     * 
     * @return boolean
     * 
     */
    public static function is_identified_journey($journey)
    {
        return isset($journey['user_id']) && ($journey['user_id'] !== 0);
    }

    /**
     * @psalm-assert-if-true AnonymousJourney $journey
     * 
     * @param AnonymousJourney|IdentifiedJourney $journey
     * 
     * @return boolean
     */
    public static function is_anonymous_journey($journey)
    {
        return isset($journey['unique_session_id']) && ($journey['unique_session_id'] !== '0');
    }

    ///////////////////////////////////////////////
    // Source 
    ///////////////////////////////////////////////
    /** 
     * @global \wpdb $wpdb The WordPress database class instance.
     *
     * @param int $user_id
     * @return null|SourceData
     */
    public static function source_for_user_id($user_id)
    {
        // Find the source for the journey which includes this conversion_id.
        // We need to find the very first event in the journey.

        // First, identify the grouping identifier (user_id or unique_session_id) for the given conversion_id

        // If the conversion_id is 0, return null
        if ($user_id == 0) {
            return null;
        }

        global $wpdb;

        $table_name = self::table_name();

        // Simply find the first Event in the database for this user_id

        $sql = "
        SELECT *
        FROM $table_name 
        WHERE user_id = %d
        ORDER BY timestamp ASC
        LIMIT 1
    ";

        $sql = (string)$wpdb->prepare($sql, $user_id);


        $first_event = Event::db_get_row($sql);


        if (is_null($first_event)) {
            return null;
        }

        return self::source_data_from_event_data($first_event);
    }




    /**
     * @param EventData $event_data
     * 
     * @return SourceData
     */
    public static function source_data_from_event_data($event_data)
    {
        return [
            'referrer' => $event_data['referrer'] ?? null,
            'landing_page' => $event_data['page_url'] ?? null,
            'utm_source' => $event_data['utm_source'] ?? null,
            'utm_medium' => $event_data['utm_medium'] ?? null,
            'utm_campaign' => $event_data['utm_campaign'] ?? null,
            'utm_term' => $event_data['utm_term'] ?? null,
            'source_event_id' => $event_data['id'] ?? null,
        ];
    }


    ///////////////////////////////////////////////
    // Event Tracking - Serverside
    ///////////////////////////////////////////////

    /**
     * Track an event from the server side
     * 
     * @param string $event_type
     * @param array $args
     * @param array|null $event_properties
     * 
     * @return array{error: non-empty-string}|int|null
     */
    public static function track_server_event_old($event_type, $args = array(), $event_properties = null)
    {
        try {
            // get unique_session_id from cookie [TODO what if it doesn't exist?]
            // TODO pick a better cookie name, this one will conflict with other plugins
            $unique_session_id = isset($_COOKIE['unique_session_id']) ? $_COOKIE['unique_session_id'] : null;

            // get user_id and user_email from current user, if available
            $user_id         = is_user_logged_in() ? get_current_user_id() : null;
            $user_email      = is_user_logged_in() ? wp_get_current_user()->user_email : null;

            // if the user is logged in, check if their role is excluded from tracking
            if (($user_id !== null && $user_id > 0) && Utils::is_current_user_excluded_from_tracking()) {
                return null;
            }


            // get page_url, referrer, user_agent, ip_address from $_SERVER
            $page_url        = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
            $referrer        = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
            $user_agent      = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
            $ip_address      = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;

            $data = array(
                'unique_session_id' => $unique_session_id,
                'event_type' => $event_type,
                'page_url' => $page_url,
                'referrer' => $referrer,
                'user_agent' => $user_agent,
                'ip_address' => $ip_address,
                'user_id' => $user_id,
                'user_email' => $user_email
            );

            // merge $args into $data

            // handle $event_properties
            // check for any special $event_properties
            if ($event_properties != null && isset($event_properties['unique_event_identifier'])) {
                $unique_event_identifier = (string)$event_properties['unique_event_identifier'];
            } else {
                $unique_event_identifier = null;
            }

            $event_properties_json = json_encode($event_properties);
            // if it's false, then the json_encode failed, set it to null
            if ($event_properties_json === false) {
                $event_properties_json = null;
            }

            $event_properties_data = [
                'unique_event_identifier' => $unique_event_identifier,
                'event_properties_json' => $event_properties_json
            ];

            $merged_data = array_merge($data, $args, $event_properties_data);
            $event_insert_data = self::validate_event_insert_data($merged_data);
            if (is_null($event_insert_data)) {
                return ['error' => 'Invalid event data'];
            }

            // handle the case of no unique_session_id
            if (!$event_insert_data['unique_session_id'] && (isset($event_insert_data['user_id']) && isset($event_insert_data['user_email']))) {
                // For now, generate one with the user_id + email
                $placeholder_unique_session_id = 'user_id-' . (string)$event_insert_data['user_id'] . '-user_email-' . $event_insert_data['user_email'];

                $event_insert_data['unique_session_id'] = $placeholder_unique_session_id;
            }

            // insert the event into the database
            return self::insert($event_insert_data);
        } catch (\Throwable $e) {
            // Handle or log the error based on your preference
            error_log("Error tracking event: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Track an event from the server side
     * 
     * @param string $event_type
     * @param array<string, mixed> $event_properties
     * 
     * @return array{error: non-empty-string}|int|null
     */
    public static function track_server_event($event_type, $event_properties = [])
    {
        try {
            // Define the significant keys that should be extracted from event_properties
            $significant_keys = [
                'unique_session_id',
                'conversion_type',
                'conversion_id',
                'referrer',
                'page_url',
                'device_type',
                'user_agent',
                'ip_address',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
                'user_id',
                'user_email',
                'unique_event_identifier',
                'timestamp'
            ];

            // Get default server-side values
            $server_data = [
                'unique_session_id' => $_COOKIE['unique_session_id'] ?? null,
                'page_url' => $_SERVER['HTTP_REFERER'] ?? null,
                'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_id' => is_user_logged_in() ? get_current_user_id() : null,
                'user_email' => is_user_logged_in() ? wp_get_current_user()->user_email : null,
            ];

            // Check if user should be excluded from tracking
            if (($server_data['user_id'] !== null && $server_data['user_id'] > 0) &&
                Utils::is_current_user_excluded_from_tracking()
            ) {
                return null;
            }

            // Extract significant properties
            $significant_data = [];
            foreach ($significant_keys as $key) {
                if (array_key_exists($key, $event_properties)) {
                    $significant_data[$key] = $event_properties[$key];
                    unset($event_properties[$key]);
                }
            }

            // Merge server data with significant data, giving preference to significant_data
            $event_data = array_merge($server_data, $significant_data);

            // Add event type
            $event_data['event_type'] = $event_type;

            // Handle remaining properties as JSON
            $event_data['event_properties_json'] = !empty($event_properties) ?
                json_encode($event_properties) ?: null :
                null;

            // Parse the user_id into an int
            if (isset($event_data['user_id'])) {
                $event_data['user_id'] = (int)$event_data['user_id'];
            }

            // Validate the event data
            $event_insert_data = self::validate_event_insert_data($event_data);
            if (is_null($event_insert_data)) {
                return ['error' => 'Invalid event data'];
            }

            // Generate unique_session_id if missing but user data exists
            // if (
            //     empty($event_insert_data['unique_session_id']) &&
            //     !empty($event_insert_data['user_id']) &&
            //     !empty($event_insert_data['user_email'])
            // ) {
            //     $event_insert_data['unique_session_id'] = sprintf(
            //         'user_id-%s-user_email-%s',
            //         $event_insert_data['user_id'],
            //         $event_insert_data['user_email']
            //     );
            // }

            // handle the case of no unique_session_id
            if (!$event_insert_data['unique_session_id'] && (isset($event_insert_data['user_id']) && isset($event_insert_data['user_email']))) {
                // For now, generate one with the user_id + email
                $placeholder_unique_session_id = 'user_id-' . (string)$event_insert_data['user_id'] . '-user_email-' . $event_insert_data['user_email'];

                $event_insert_data['unique_session_id'] = $placeholder_unique_session_id;
            }

            // Insert the event into the database
            return self::insert($event_insert_data);
        } catch (\Throwable $e) {
            error_log("Error tracking event: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate the event data before inserting it into the database.
     * 
     * @param array $data
     * 
     * @return string[]
     */
    public static function get_unique_conversion_types()
    {
        global $wpdb;
        $table_name = self::table_name();
        $results = $wpdb->get_results("SELECT DISTINCT conversion_type FROM $table_name WHERE conversion_type IS NOT NULL", 'ARRAY_A');
        $results = Validators::wpdb_results($results, 'conversion_type');
        $array_of_strings = array_map(function ($result) {
            return $result['conversion_type'];
        }, $results);

        return $array_of_strings;
    }
}
