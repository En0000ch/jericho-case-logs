<?php

namespace AnalyticsWP\Lib;

/**
 * @psalm-import-type JourneyEvent from Event
 * 
 * @psalm-import-type IdentifiedJourney from Event
 * @psalm-import-type AnonymousJourney from Event
 * 
 * @psalm-import-type SourceData from Event
 */
class Core
{

    /**
     * @return void
     */
    public static function init()
    {
        Event::create_table_if_necessary();

        // enqueue scripts analyticswp.js
        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_script('analyticswp', plugin_dir_url(__FILE__) . '../Lib/analyticswp.min.js', [], ANALYTICSWP_VERSION, true);
            // Localize the script with the ajaxurl variable and the nonce
            wp_localize_script('analyticswp', 'analyticswp_vars', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('analyticswp_nonce')
            ]);
        });

        // Register the AJAX action
        add_action('wp_ajax_analyticswp_send_analytics', [self::class, 'analyticswp_handle_analytics']);
        add_action('wp_ajax_nopriv_analyticswp_send_analytics', [self::class, 'analyticswp_handle_analytics']);
    }

    /**
     * Handle analytics data sent from client-side
     * 
     * @return void
     */
    public static function analyticswp_handle_analytics(): void
    {
        try {
            // Early returns for invalid requests
            if (Utils::is_current_user_excluded_from_tracking()) {
                wp_send_json_error(['message' => 'User excluded from tracking']);
                return;
            }

            if (BotDetection::should_block_current_request($_REQUEST)) {
                wp_send_json_error(['message' => 'Bot detected']);
                return;
            }

            // Validate required parameters
            $event_type = self::validate_event_type($_POST['event_type'] ?? null);
            if (!$event_type) {
                wp_send_json_error(['message' => 'Invalid event type']);
                return;
            }

            // Parse analytics data and event properties
            $client_data = self::parse_client_data($_POST['analyticsData'] ?? null);
            if (!$client_data) {
                wp_send_json_error(['message' => 'Invalid analytics data']);
                return;
            }

            $event_properties = self::parse_event_properties($_POST['eventProperties'] ?? null);

            // Build final event properties array with all data
            $final_event_properties = array_merge(
                $client_data,
                $event_properties,
                [
                    'user_id' => is_user_logged_in() ? get_current_user_id() : null,
                    'user_email' => is_user_logged_in() ? wp_get_current_user()->user_email : null,
                ]
            );

            // Use the same track_server_event function
            $result = Event::track_server_event($event_type, $final_event_properties);

            if (isset($result['error'])) {
                wp_send_json_error(['message' => $result['error']]);
                return;
            }

            wp_send_json_success(['id' => $result]);
        } catch (\Throwable $e) {
            error_log("Error handling analytics event: " . $e->getMessage());
            wp_send_json_error(['message' => 'Internal server error']);
        }
    }

    /**
     * Validate and sanitize event type
     * 
     * @param mixed $event_type
     * @return string|null
     */
    private static function validate_event_type($event_type): ?string
    {
        if (!is_string($event_type) || empty(trim($event_type))) {
            return null;
        }
        return sanitize_text_field($event_type);
    }

    /**
     * Parse and validate client-side analytics data
     * 
     * @param mixed $data
     * @return array|null
     */
    private static function parse_client_data($data): ?array
    {
        if (!is_string($data)) {
            return null;
        }

        $decoded = json_decode(stripslashes(Validators::str($data)), true);
        if (!is_array($decoded)) {
            return null;
        }

        return [
            'page_url' => isset($decoded['pageURL']) ? esc_url_raw($decoded['pageURL']) : null,
            'referrer' => isset($decoded['referrer']) ? esc_url_raw($decoded['referrer']) : null,
            'device_type' => isset($decoded['deviceType']) ? sanitize_text_field($decoded['deviceType']) : null,
            'user_agent' => isset($decoded['userAgent']) ? sanitize_text_field($decoded['userAgent']) : null,
            'utm_source' => isset($decoded['utmSource']) ? sanitize_text_field($decoded['utmSource']) : null,
            'utm_medium' => isset($decoded['utmMedium']) ? sanitize_text_field($decoded['utmMedium']) : null,
            'utm_campaign' => isset($decoded['utmCampaign']) ? sanitize_text_field($decoded['utmCampaign']) : null,
            'utm_term' => isset($decoded['utmTerm']) ? sanitize_text_field($decoded['utmTerm']) : null,
            'utm_content' => isset($decoded['utmContent']) ? sanitize_text_field($decoded['utmContent']) : null,
            'unique_session_id' => isset($decoded['unique_session_id']) ? sanitize_text_field($decoded['unique_session_id']) : null,
            'timestamp' => isset($decoded['timestamp']) ? sanitize_text_field($decoded['timestamp']) : null
        ];
    }

    /**
     * Parse and validate event properties
     * 
     * @param mixed $properties
     * @return array
     */
    private static function parse_event_properties($properties): array
    {
        if (!is_string($properties)) {
            return [];
        }

        $decoded = json_decode(stripslashes(Validators::str($properties)), true);
        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }
}
