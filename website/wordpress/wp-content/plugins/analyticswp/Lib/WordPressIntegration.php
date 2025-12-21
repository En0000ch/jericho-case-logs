<?php

namespace AnalyticsWP\Lib;

/**
 * @psalm-import-type SourceData from Event
 */
class WordPressIntegration
{


    public static function add_hooks(): void
    {

        add_filter('manage_users_columns', [self::class, 'add_journey_column']);
        add_action('manage_users_custom_column', [self::class, 'populate_journey_column'], 10, 3);

        add_filter('manage_users_columns', [self::class, 'add_source_column']);
        add_action('manage_users_custom_column', [self::class, 'populate_source_column'], 10, 3);

        add_action('admin_bar_menu', [self::class, 'add_analyticswp_link_to_admin_bar'], 100);

        /**
         * Meta Box on Admin Order page
         */
        // add_action('add_meta_boxes', [self::class, 'add_meta_boxes']);
    }

    /**
     * Adds a new 'Journey' column to the Users table.
     *
     * @param string[] $columns Current columns on the list screen.
     *
     * @return string[] Updated columns on the list screen.
     */
    public static function add_journey_column($columns)
    {
        $columns['user_journey'] = 'AnalyticsWP Journey';  // 'Journey' is the title of the column
        return $columns;
    }


    /**
     * Populates the 'Journey' column with a link to view the journey.
     *
     * @param string $value The value to be displayed in the column.
     * @param string $column_name The name/ID of the column.
     * @param int $user_id The user ID.
     * 
     * @return string The modified (or original) content to be displayed in the column.
     */
    public static function populate_journey_column($value, $column_name, $user_id)
    {
        if ('user_journey' == $column_name) {
            return '<a class="button button-secondary" href="' . esc_url(self::url_user_journey_link($user_id)) . '">View Journey</a>';
        } else {
            return $value;
        }
    }

    /**
     * @param int $user_id
     * @return string
     */
    public static function url_user_journey_link($user_id)
    {
        $journey_link = add_query_arg(array(
            'page' => 'user_journeys',
            'user_id' => $user_id
        ), admin_url('admin.php'));

        return $journey_link;
    }

    /**
     * Adds a new 'Source' column to the Users table.
     *
     * @param string[] $columns Current columns on the list screen.
     *
     * @return string[] Updated columns on the list screen.
     */
    public static function add_source_column($columns)
    {
        $columns['user_analyticswp_source'] = 'AnalyticsWP Source';
        return $columns;
    }


    /**
     * Populates the 'Source' column.
     *
     * @param string $value The value to be displayed in the column.
     * @param string $column_name The name/ID of the column.
     * @param int $user_id The user ID.
     * 
     * @return string The modified (or original) content to be displayed in the column.
     */
    public static function populate_source_column($value, $column_name, $user_id)
    {
        if ('user_analyticswp_source' == $column_name) {
            return self::render_source_component_for_user_id($user_id);
        } else {
            return $value;
        }
    }

    /**
     * @param int $user_id
     * @return string
     */
    public static function render_source_component_for_user_id($user_id)
    {
        $source = Event::source_for_user_id($user_id);
        if (!$source) {
            return '<strong>Direct / None</strong>';
        } else {
            return Views::render_source_data_html($source);
        }
    }

    public static function add_analyticswp_link_to_admin_bar(\WP_Admin_Bar $wp_admin_bar): void
    {
        if (!current_user_can('edit_posts')) {
            return;
        }

        $wp_admin_bar->add_node(array(
            'id' => 'analyticswp',
            'title' => 'AnalyticsWP',
            'href' => admin_url('admin.php?page=analyticswp'),
        ));
    }
}
