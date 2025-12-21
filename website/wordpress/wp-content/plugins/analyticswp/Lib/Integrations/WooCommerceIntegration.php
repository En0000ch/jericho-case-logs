<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\SuperSimpleWP;
use AnalyticsWP\Lib\Utils;
use AnalyticsWP\Lib\Validators;
use AnalyticsWP\Lib\Views;

/**
 * Responsibilities
 * [ ] Fire a conversion event when a WooCommerce order is placed
 * 
 * v2
 * [ ] Order Refunds
 *
 * @psalm-import-type SourceData from Event
 * 
 * @psalm-type WCOrderSourceData = array{
 *   multiple_referrers: null|array<array{referrer: string, timestamp: string}>,
 *   where_did_you_hear_about_us: string|null
 * }
 * 
 * @psalm-type SourceDataAndWCOrderSourceData = array{0: SourceData, 1: WCOrderSourceData}
 * 
 */
class WooCommerceIntegration implements IntegrationInterface
{
    const SLUG = 'woocommerce';

    public static function is_available(): bool
    {
        return function_exists('WC');
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'WooCommerce',
            'description' => 'Full-fledged eCommerce integration including journey tracking of customers, email notifications, etc.',
            'category' => 'eCommerce',
        ];
    }

    public static function add_hooks(): void
    {
        // Check if woocommerce is active on this site
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        add_action('woocommerce_thankyou', [self::class, 'track_order'], 10);
        add_action('woocommerce_new_order', [self::class, 'track_order'], 10);
        add_action('woocommerce_order_status_completed', [self::class, 'track_order'], 10);

        add_action('woocommerce_subscription_renewal_payment_complete', [self::class, 'track_subscription_renewal'], 10, 2);

        add_filter('manage_edit-shop_order_columns', [self::class, 'add_journey_column']);
        add_action('manage_shop_order_posts_custom_column', [self::class, 'populate_journey_column'], 1000, 2);
        add_filter('woocommerce_shop_order_list_table_columns', [self::class, 'add_journey_column']);
        add_action('woocommerce_shop_order_list_table_custom_column', [self::class, 'populate_journey_column'], 1000, 2);

        add_filter('manage_edit-shop_order_columns', [self::class, 'add_source_column']);
        add_action('manage_shop_order_posts_custom_column', [self::class, 'populate_source_column'], 1000, 2);
        add_filter('woocommerce_shop_order_list_table_columns', [self::class, 'add_source_column']);
        add_action('woocommerce_shop_order_list_table_custom_column', [self::class, 'populate_source_column'], 1000, 2);


        add_filter('woocommerce_email_additional_content_new_order', [self::class, 'custom_additional_content_new_order_email'], 99, 3);

        /**
         * Meta Box on Admin Order page
         */
        add_action('add_meta_boxes', [self::class, 'add_meta_boxes']);

        /**
         * WooCommerce Checkout - Where did you hear about us?
         */
        $how_did_you_hear_field_position = (string)SuperSimpleWP::get_setting('analyticswp', 'how_did_you_hear');
        if (!empty($how_did_you_hear_field_position) && $how_did_you_hear_field_position !== 'disable') {
            // deal with saving the data
            add_action($how_did_you_hear_field_position, [self::class, 'add_custom_checkout_field']);
            add_action('woocommerce_checkout_update_order_meta', [self::class, 'save_custom_checkout_field']);
        }
    }

    /**
     * @param string $message
     * @param \WC_Order $order
     * 
     * @return string
     */
    public static function custom_additional_content_new_order_email($message, $order)
    {
        $original_message = $message;
        try {
            $is_enabled = (bool)SuperSimpleWP::get_setting('analyticswp', 'enable_woocommerce_order_notification_source_section');
            if (!$is_enabled) {
                return $original_message;
            }

            $order_id = $order->get_id();
            $journey_link = self::url_conversion_journey_link($order_id);
            $journey_link_html = '<a href="' . esc_url($journey_link) . '">📈 View Journey</a>';
            $message .= '<br><hr><br>';
            $message .= '<h2>AnalyticsWP</h2>';
            $message .= 'Source:';
            $message .= self::render_source_component_for_order_id($order_id);
            $message .= '<br>Order Journey: ' . $journey_link_html;

            return $message;
        } catch (\Throwable $e) {
            return $original_message;
        }
    }


    /**
     * @param int $order_id
     * 
     * @return array{error: string}|int|null
     */
    public static function track_order($order_id)
    {
        if (!$order_id) {
            return;
        }

        // get user id and email from the order
        $order = \wc_get_order($order_id);

        // check if the order is of type \WC_Order
        if (!($order instanceof \WC_Order)) {
            return;
        }

        ///////////////////////////////////////////////////////////////////////////
        // For now, check if a conversion event was already tracked for this order
        // If it was, don't track it again
        if (count(Event::where([
            'conversion_id' => (string)$order_id,
            'conversion_type' => 'woocommerce_order'
        ])) > 0) {
            return;
        }
        ///////////////////////////////////////////////////////////////////////////


        $user_id = $order->get_user_id();
        $email = $order->get_billing_email();

        // Check if the order was made by a guest
        if ($order->get_customer_id() == 0 || !$order->get_user()) {
            // This was a guest order, we should still identify the guest by their email.
            $r = Event::track_server_event('conversion', [
                'conversion_id' => $order_id,
                'conversion_type' => 'woocommerce_order',
                'user_id' => $user_id,
                'user_email' => $email,
            ]);
        } else {
            // This was not a guest order, there is a registered user
            $r = Event::track_server_event('conversion', [
                'conversion_id' => $order_id,
                'conversion_type' => 'woocommerce_order',
                'user_id' => $user_id,
                'user_email' => $email,
            ]);
        }


        return $r;
    }

    /**
     * Undocumented function
     *
     * @param mixed $_subscription TODO this is a \WC_Subscription, but we don't have the stubs loaded
     * @param \WC_Order $renewal_order
     * @return array{error: string}|int|null
     */
    public static function track_subscription_renewal($_subscription, $renewal_order)
    {
        // get user id and email from the order
        $order = wc_get_order($renewal_order);

        if (!($order instanceof \WC_Order)) {
            return;
        }

        $order_id = $order->get_id();
        $user_id = $order->get_user_id();
        $email = $order->get_billing_email();

        $r = Event::track_server_event('conversion', [
            'conversion_id' => $order_id,
            'conversion_type' => 'woocommerce_order-subscription-renewal',
            'user_id' => $user_id,
            'user_email' => $email,
        ]);

        return $r;
    }

    /**
     * Adds a new 'Journey' column to the WooCommerce Orders table.
     *
     * @param string[] $columns Current columns on the list screen.
     *
     * @return string[] Updated columns on the list screen.
     */
    public static function add_journey_column($columns)
    {
        $columns['order_journey'] = 'AnalyticsWP Journey';  // 'Journey' is the title of the column
        return $columns;
    }


    /**
     * Populates the 'Journey' column with a link to view the journey.
     * 
     * @param string $column Name of the current column.
     * @param int|\WC_Order $post_id_or_order_object
     *
     * @return void
     */
    public static function populate_journey_column($column, $post_id_or_order_object)
    {
        if ('order_journey' === $column) {
            $order_id = ($post_id_or_order_object instanceof \WC_Order) ? $post_id_or_order_object->get_id() : $post_id_or_order_object;
            $journey_link = self::url_conversion_journey_link($order_id);
            echo '<a class="button button-secondary" href="' . esc_url($journey_link) . '">View Journey</a>';
        }
    }

    /**
     * @param int $conversion_id
     * @return string
     */
    public static function url_conversion_journey_link($conversion_id)
    {
        $journey_link = add_query_arg(array(
            'page' => 'conversion_journeys',
            'conversion_id' => $conversion_id,
            'conversion_type' => 'woocommerce_order' // TODO-INTEGRATION use a constant
        ), admin_url('admin.php'));

        return $journey_link;
    }

    /**
     * @param int $wc_order_id
     * 
     * @global \wpdb $wpdb The WordPress database class instance.
     * 
     * @return null|SourceDataAndWCOrderSourceData
     * 
     */
    public static function source_for_wc_order_id($wc_order_id)
    {
        // Find the source for the journey which includes this conversion_id.
        // We need to find the very first event in the journey.

        // If the conversion_id is 0, return null
        if ($wc_order_id == 0) {
            return null;
        }

        $conversion_id = $wc_order_id;

        global $wpdb;

        $table_name = Event::table_name();

        // First, identify the grouping identifier (user_id or unique_session_id) for the given conversion_id
        $sql_identify_grouping = "
    SELECT user_id, unique_session_id 
    FROM $table_name 
    WHERE conversion_id = %d 
    LIMIT 1
    ";

        $sql_identify_grouping = (string)$wpdb->prepare($sql_identify_grouping, $conversion_id);
        $grouping_info = $wpdb->get_row($sql_identify_grouping, 'ARRAY_A');

        if (is_null($grouping_info)) {
            return null;
        }

        $groupBy = $grouping_info['user_id'] ? 'user_id' : 'unique_session_id';
        $groupingValue = $grouping_info['user_id'] ? (string)$grouping_info['user_id'] : (string)$grouping_info['unique_session_id'];

        // Next, get the first event in the journey based on the identified grouping identifier
        $sql = "
    SELECT *
    FROM $table_name 
    WHERE $groupBy = %s
    ORDER BY timestamp ASC
    LIMIT 1
    ";

        $sql = (string)$wpdb->prepare($sql, $groupingValue);

        $first_event = Event::db_get_row($sql);
        if (is_null($first_event)) {
            return null;
        }

        $source_data = Event::source_data_from_event_data($first_event);

        $wc_order_specific_source_data = [];
        $wc_order_specific_source_data['where_did_you_hear_about_us'] = self::get_analyticswp_where_heard_about_us_for_order_id($wc_order_id);

        // I need the current site so we can filter out internal navigation
        $current_site = preg_replace('/^https?:\/\/(www\.)?/', '', get_site_url());

        $sql = "
    SELECT *
    FROM $table_name 
    WHERE $groupBy = %s
    AND referrer NOT LIKE %s
    ORDER BY timestamp ASC
    ";

        $sql = (string)$wpdb->prepare($sql, $groupingValue, '%' . $current_site . '%');
        $results = Validators::wpdb_results($wpdb->get_results($sql, 'ARRAY_A'), 'referrer', 'timestamp');

        $results_mapped = array_map(function ($row) {
            return [
                'referrer' => $row['referrer'],
                'timestamp' => $row['timestamp']
            ];
        }, $results);

        $wc_order_specific_source_data['multiple_referrers'] = $results_mapped;

        return [$source_data, $wc_order_specific_source_data];
    }

    /**
     * Adds a new 'Source' column to the WooCommerce Orders table.
     *
     * @param string[] $columns Current columns on the list screen.
     *
     * @return string[] Updated columns on the list screen.
     */
    public static function add_source_column($columns)
    {
        $columns['order_analyticswp_source'] = 'AnalyticsWP Source';
        return $columns;
    }


    /**
     * Populates the 'Source' column.
     *
     * @param string $column Name of the current column.
     * @param int|\WC_Order $post_id_or_order_object
     *
     * @return void
     */
    public static function populate_source_column($column, $post_id_or_order_object)
    {
        if ('order_analyticswp_source' === $column) {
            $order_id = ($post_id_or_order_object instanceof \WC_Order) ? $post_id_or_order_object->get_id() : $post_id_or_order_object;
            echo self::render_source_component_for_order_id($order_id);
        }
    }


    /**
     * @param int $order_id
     * @return string
     */
    public static function render_source_component_for_order_id($order_id)
    {
        $cache_time_in_seconds = 60 * 60 * 24 * 30; // 1 month
        return Utils::cache_function_result([self::class, 'cached_render_source_component_for_order_id'], [$order_id], $cache_time_in_seconds);
    }

    /**
     * @param int $order_id
     * @return string
     */
    public static function cached_render_source_component_for_order_id($order_id)
    {
        $source = self::source_for_wc_order_id($order_id);
        if (!$source) {
            return '<strong>Direct / None</strong>';
        } else {
            // Render the source data by using AnalyticsWP's Views class
            $html = Views::render_source_data_html($source[0]);
            // Now, render the WooCommerce specific data
            $html .= self::render_woocommerce_specific_source_data_html($source[1]);
            return $html;
        }
    }

    /**
     * @param WCOrderSourceData $data
     * 
     * @return string
     */
    public static function render_woocommerce_specific_source_data_html($data)
    {
        $output = '';
        /////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////
        // THIS IS FOR WOOCOMMERCE ORDER SPECIFIC DATA.
        // It would use the type SourceDataAndWCOrderSourceData
        // where_did_you_hear_about_us if it exists and not empty
        if (!is_null($data['where_did_you_hear_about_us']) && !empty($data['where_did_you_hear_about_us'])) {
            $value = htmlspecialchars($data['where_did_you_hear_about_us'], ENT_QUOTES, 'UTF-8');
            $truncated_value = mb_strimwidth($value, 0, 80, '...');
            $output .= "<div><strong>Where did you hear about us?:</strong> <span class=\"analyticswp-truncate\" title=\"$value\">$truncated_value</span></div>";
        }

        // * multiple_referrers: null|array<array{referrer: string, timestamp: string}>,

        // render the multiple referrers if they exist
        if (!is_null($data['multiple_referrers']) && !empty($data['multiple_referrers'])) {
            $output .= '<div style="font-weight:600; margin-top:10px; margin-bottom:5px;">Multiple referrers</div>';
            $output .= '<div style="position:relative; background:#fff; padding:10px; border:1px solid #c3c4c7; border-radius:6px; max-height: 200px; overflow-y: auto;" class="analyticswp-multiple-referrers-source-container">';
            $output .= '<ul style="margin:0">';

            foreach ($data['multiple_referrers'] as $referrer) {
                $output .= '<li>';
                $output .= '<div><strong>Referrer:</strong> ' . htmlspecialchars($referrer['referrer'], ENT_QUOTES, 'UTF-8') . '</div>';
                $output .= '<div style="font-size:12px; opacity:.8">' . htmlspecialchars($referrer['timestamp'], ENT_QUOTES, 'UTF-8') . '</div>';
                $output .= '</li>';
            }

            $output .= '</ul>';
            $output .= '</div>';
        }
        /////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////
        return $output;
    }

    /**
     * @return void
     */
    public static function add_meta_boxes()
    {
        // WooCommerce Admin > Order > Edit Order
        $callback =
            /**
             * @param \WP_Post|\WC_Order $post
             * @return void
             */
            function ($post) {
                if ($post instanceof \WC_Order) {
                    $order_id = $post->get_id();
                } else {
                    $order_id = $post->ID;
                }

                echo "<h4>AnalyticsWP - Order #{$order_id}</h4>";
                $journey_link = self::url_conversion_journey_link($order_id);
                echo '<a class="button button-secondary" style="margin-bottom:10px; text-align:center; display:block" href="' . esc_url($journey_link) . '">View Journey</a>';
                echo 'Source:';

                echo '<div style="overflow: scroll;">' . self::render_source_component_for_order_id($order_id) . '</div>';
            };
        $icon = "<img class='analytics-wp_meta-box_icon' src='https://solidaffiliate.com/brand/analyticswp/Icon-black.svg' width='30' height='30' alt=''>";


        $screen = self::get_shop_order_screen();
        add_meta_box('analytics-wp_meta-box_shop-order', "<span class='analytics-wp_meta-box-title' style='display:flex; flex-direction:row; align-items:center; gap:5px;' >" . $icon . "AnalyticsWP</span>", $callback, $screen, 'side', 'high');
    }

    public static function get_shop_order_screen(): string
    {
        $screen = 'shop_order';

        try {
            /** @psalm-suppress MixedMethodCall - This one is ok. */
            $screen = class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController') && wc_get_container()->get('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')->custom_orders_table_usage_is_enabled()
                ? wc_get_page_screen_id('shop-order')
                : 'shop_order';
        } catch (\Exception $e) {
            // Log the exception or handle it as needed
            error_log('Error in getting shop order screen: ' . $e->getMessage());
        }

        return $screen;
    }

    /**
     * @return array<string, string>
     */
    public static function get_wc_field_positions()
    {
        $field_positions = Validators::array_of_string_keys_and_values(apply_filters('wc_customer_source_checkout_field_actions', array(
            'woocommerce_before_order_notes'            =>  __('Before order notes', 'wc-customer-source'),
            'woocommerce_after_order_notes'             =>  __('After order notes', 'wc-customer-source'),
            'woocommerce_before_checkout_billing_form'  =>  __('Before checkout billing form', 'wc-customer-source'),
            'woocommerce_after_checkout_billing_form'   =>  __('After checkout billing form', 'wc-customer-source'),
            'woocommerce_before_checkout_shipping_form' =>  __('Before checkout shipping form', 'wc-customer-source'),
            'woocommerce_after_checkout_shipping_form'  =>  __('After checkout shipping form', 'wc-customer-source'),
        )));

        return $field_positions;
    }

    /**
     * Adds a custom checkout field to the WooCommerce checkout page.
     *
     * This function hooks into the WooCommerce checkout process and adds a text field
     * where customers can specify how they heard about the product.
     *
     * @param \WC_Checkout $checkout The WooCommerce checkout object.
     * 
     * @return void
     */
    public static function add_custom_checkout_field($checkout)
    {
        woocommerce_form_field('analyticswp_where_heard_about_us', array(
            'type' => 'text',
            'class' => array(
                'analyticswp-checkout-field form-row-wide'
            ),
            'label' => __('Where did you hear about us?'),
            'placeholder' => __('Enter where you heard about us'),
        ), $checkout->get_value('analyticswp_where_heard_about_us'));
    }

    /**
     * Saves the custom checkout field data to the order meta.
     *
     * This function checks if the 'Where did you hear about us?' field is set in the POST data
     * and saves its value to the order meta if present.
     *
     * @param int $order_id The ID of the WooCommerce order.
     * 
     * @return void
     */
    public static function save_custom_checkout_field($order_id)
    {
        $val = $_POST['analyticswp_where_heard_about_us'];

        if (is_string($val) && $val !== '') {
            update_post_meta($order_id, 'analyticswp_where_heard_about_us', sanitize_text_field($val));
        }
    }

    /**
     * @param int $order_id
     * @return string
     */
    public static function get_analyticswp_where_heard_about_us_for_order_id($order_id): string
    {
        return (string)get_post_meta($order_id, 'analyticswp_where_heard_about_us', true);
    }
}
