<?php

namespace AnalyticsWP\Lib;

/**
 * @psalm-import-type SavedFilter from APIServer
 */
class SavedFilters
{
    private const OPTION_NAME = 'analyticswp_saved_filters';

    /**
     * @return SavedFilter[]
     */
    public static function get_saved_filters()
    {
        $saved_filters = get_option(self::OPTION_NAME, []);

        // merge with mock data - TODO maybe later this can be like a pre-set package
        // of filters that we ship with the plugin, to give users a starting point.
        // $saved_filters = array_merge($saved_filters, self::get_mock_saved_filters());

        if (empty($saved_filters)) {
            return [];
        }

        return $saved_filters;
    }

    /**
     * @return SavedFilter[]
     */
    public static function get_mock_saved_filters()
    {
        $mock_saved_filter = [
            'id' => '1',
            'name' => 'All Time - Desktop Devices!!',
            'description' => 'All the traffic from only desktops',
            'created_at' => '2021-01-08',
            'created_by_user_id' => 1,
            'filters' => [
                'date_range' => 'All time',
                'custom_date_range' => [
                    'start_date' => '2021-01-01',
                    'end_date' => '2021-01-07',
                ],
                'custom_sql' => "(device_type = 'desktop')",
            ],
        ];

        return [
            $mock_saved_filter
        ];
    }

    /**
     * Save a new filter
     * 
     * @param array{name: string, description: string, filters: array} $filter_data
     * @return array{success: bool, message: string, filter?: array, saved_filters: array}
     */
    public static function save_filter($filter_data)
    {
        if (empty($filter_data['name'])) {
            return [
                'success' => false,
                'message' => 'Filter name is required',
                'saved_filters' => self::get_saved_filters()
            ];
        }

        $current_user_id = get_current_user_id();

        $new_filter = [
            'id' => uniqid(), // Generate a unique ID
            'name' => sanitize_text_field($filter_data['name']),
            'description' => sanitize_text_field($filter_data['description']),
            'filters' => $filter_data['filters'],
            'created_by_user_id' => $current_user_id,
            'created_at' => current_time('mysql', true)
        ];
        // TODO validate filter data ($new_filter)

        // Get existing filters
        $saved_filters = self::get_saved_filters();

        // Add new filter
        $saved_filters[] = $new_filter;

        // Save updated filters array
        $update_success = update_option(self::OPTION_NAME, $saved_filters);

        if (!$update_success) {
            return [
                'success' => false,
                'message' => 'Failed to save filter',
                'saved_filters' => self::get_saved_filters()
            ];
        }

        return [
            'success' => true,
            'message' => 'Filter saved successfully',
            'filter' => $new_filter,
            'saved_filters' => self::get_saved_filters()
        ];
    }

    /**
     * Delete a saved filter
     * 
     * @param string $filter_id
     * @return array{success: bool, message: string, saved_filters: array}
     */
    public static function delete_filter($filter_id)
    {
        $saved_filters = self::get_saved_filters();

        // Find and remove the filter with matching ID
        $saved_filters = array_filter($saved_filters, function ($filter) use ($filter_id) {
            return $filter['id'] !== $filter_id;
        });

        // Re-index array to ensure sequential keys
        $saved_filters = array_values($saved_filters);

        // Save updated filters array
        $update_success = update_option(self::OPTION_NAME, $saved_filters);

        if (!$update_success) {
            return [
                'success' => false,
                'message' => 'Failed to delete filter',
                'saved_filters' => self::get_saved_filters()
            ];
        }

        return [
            'success' => true,
            'message' => 'Filter deleted successfully',
            'saved_filters' => self::get_saved_filters()
        ];
    }



    public static function handle_ajax_save_filter()
    {
        // Set CORS headers
        header("Access-Control-Allow-Origin: *"); // Allows all domains. For specific domains, replace '*' with the domain.
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
        header("Content-Type: application/json");

        // check that the user is a logged in administrator (has the manage_options capability)
        // TODO figure this out. It doesn't work in development mode for some reason...but maybe works in production mode (?)
        // if (!current_user_can('manage_options')) {
        //     wp_send_json_error(array('message' => 'You do not have permission to access this resource.'));
        // }

        // Handle pre-flight requests
        if (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')) {
            exit(0);
        }

        // check_ajax_referer('analyticswp_nonce', 'nonce');

        $filter_data = json_decode(stripslashes($_POST['filter_data']), true);

        if (!$filter_data) {
            wp_send_json([
                'success' => false,
                'message' => 'Invalid filter data'
            ]);
        }

        $result = SavedFilters::save_filter($filter_data);
        wp_send_json($result);
    }

    public static function handle_ajax_delete_filter()
    {
        // Set CORS headers
        header("Access-Control-Allow-Origin: *"); // Allows all domains. For specific domains, replace '*' with the domain.
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
        header("Content-Type: application/json");

        // check that the user is a logged in administrator (has the manage_options capability)
        // TODO figure this out. It doesn't work in development mode for some reason...but maybe works in production mode (?)
        // if (!current_user_can('manage_options')) {
        //     wp_send_json_error(array('message' => 'You do not have permission to access this resource.'));
        // }

        // Handle pre-flight requests
        if (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')) {
            exit(0);
        }

        // if (!current_user_can('manage_options')) {
        //     wp_send_json([
        //         'success' => false,
        //         'message' => 'Unauthorized'
        //     ]);
        // }

        // check_ajax_referer('analyticswp_nonce', 'nonce');

        $filter_id = $_POST['filter_id'];

        if (!$filter_id) {
            wp_send_json([
                'success' => false,
                'message' => 'Invalid filter ID'
            ]);
        }

        $result = SavedFilters::delete_filter($filter_id);
        wp_send_json($result);
    }
}
