<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;
use AnalyticsWP\Lib\Validators;
use AnalyticsWP\Lib\Views;

/**
 * Responsibilities
 * [ ] Fire a conversion event when an EDD order is placed
 * [ ] Add journey and source information to EDD order screens
 * [ ] Track product views and cart interactions
 */
class EDDIntegration implements IntegrationInterface
{
    const SLUG = 'edd';
    const CONVERSION_TYPE = 'edd_order';

    public static function is_available(): bool
    {
        return class_exists('Easy_Digital_Downloads');
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'Easy Digital Downloads',
            'description' => 'Track product views, cart interactions, and conversions in Easy Digital Downloads',
            'category' => 'eCommerce',
        ];
    }

    public static function add_hooks(): void
    {
        // Check if EDD is active and enabled on this site
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        // Order tracking hooks
        add_action('edd_complete_purchase', [self::class, 'track_order'], 10);
        add_action('edd_payment_receipt_before_table', [self::class, 'track_order'], 10);

        // Product view tracking
        add_action('template_redirect', [self::class, 'track_product_view']);

        // Cart interaction tracking
        add_action('edd_after_cart_contents', [self::class, 'track_cart_view']);

        // Checkout process tracking
        add_action('edd_before_purchase_form', [self::class, 'track_begin_checkout']);

        // Admin columns for orders
        add_filter('edd_payments_table_columns', [self::class, 'add_admin_columns']);
        add_filter('edd_payments_table_column', [self::class, 'populate_admin_columns'], 10, 3);

        // Meta box for order details
        add_action('add_meta_boxes', [self::class, 'add_meta_boxes']);

        // Add journey section to order details
        add_action('edd_view_order_details_sidebar_after', [self::class, 'add_journey_section']);

        // Support for EDD Software Licensing
        if (class_exists('EDD_Software_Licensing')) {
            add_action('edd_sl_license_generated', [self::class, 'track_license_generated'], 10, 4);
            add_action('edd_sl_license_renewed', [self::class, 'track_license_renewed'], 10, 2);
        }

        // Support for EDD Recurring
        if (class_exists('EDD_Recurring')) {
            add_action('edd_recurring_add_subscription_payment', [self::class, 'track_subscription_renewal'], 10, 2);
            add_action('edd_subscription_cancelled', [self::class, 'track_subscription_cancelled'], 10);
        }
    }

    /**
     * Track when a product page is viewed
     */
    public static function track_product_view(): void
    {
        try {

            if (!is_singular('download')) {
                return;
            }

            $download_id = get_the_ID();
            $download = edd_get_download($download_id);

            if (!$download) {
                return;
            }

            // Get product data
            $product_data = [
                'product_id' => $download_id,
                'name' => $download->get_name(),
                'price' => edd_get_download_price($download_id),
                'currency' => edd_get_currency(),
            ];

            // Add category data if available
            $categories = get_the_terms($download_id, 'download_category');
            if ($categories && !is_wp_error($categories)) {
                $category_names = array_map(function ($cat) {
                    return $cat->name;
                }, $categories);
                $product_data['categories'] = $category_names;
            }

            // Add variable price data if available
            if (edd_has_variable_prices($download_id)) {
                $prices = edd_get_variable_prices($download_id);
                $product_data['price_options'] = array_map(function ($price) {
                    return [
                        'id' => $price['index'],
                        'name' => $price['name'],
                        'amount' => $price['amount']
                    ];
                }, $prices);
            }

            Event::track_server_event('view_product', [
                'item_id' => $download_id,
                'item_data' => $product_data
            ]);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Track cart view events
     */
    public static function track_cart_view(): void
    {
        try {
            $cart_items = edd_get_cart_contents();
            if (empty($cart_items)) {
                return;
            }

            $items_data = self::get_cart_items_data($cart_items);

            Event::track_server_event('view_cart', [
                'items' => $items_data,
                'total' => edd_get_cart_total(),
                'currency' => edd_get_currency()
            ]);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }


    /**
     * Track when user begins checkout
     */
    public static function track_begin_checkout(): void
    {
        try {

            $cart_items = edd_get_cart_contents();
            if (empty($cart_items)) {
                return;
            }

            $items_data = self::get_cart_items_data($cart_items);

            Event::track_server_event('begin_checkout', [
                'items' => $items_data,
                'total' => edd_get_cart_total(),
                'currency' => edd_get_currency()
            ]);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Track an EDD order as a conversion
     * 
     * @param int $payment_id The EDD payment ID
     * @return array{error: string}|int|null
     */
    public static function track_order($payment_id)
    {
        try {

            if (!$payment_id) {
                return null;
            }

            // Get the payment object
            if ($payment_id instanceof \EDD_Payment) {
                $payment = $payment_id;
                $payment_id = $payment->ID;
            } else {
                $payment = edd_get_payment($payment_id);
            }

            // Check if we've already tracked this order
            if (count(Event::where([
                'conversion_id' => (string)$payment_id,
                'conversion_type' => SELF::CONVERSION_TYPE
            ])) > 0) {
                return null;
            }

            // Get customer details
            $user_id = $payment->user_id;
            $email = $payment->email;

            // Get cart items
            $cart_items = $payment->cart_details;
            $items_data = [];

            foreach ($cart_items as $item) {
                $item_data = [
                    'product_id' => $item['id'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'discount' => $item['discount']
                ];

                // Add price option information if present
                if (isset($item['item_number']['options']['price_id'])) {
                    $price_id = $item['item_number']['options']['price_id'];
                    $prices = edd_get_variable_prices($item['id']);
                    if (isset($prices[$price_id])) {
                        $item_data['variant'] = $prices[$price_id]['name'];
                    }
                }

                $items_data[] = $item_data;
            }

            // Track main order conversion
            $result = Event::track_server_event('conversion', [
                'conversion_id' => $payment_id,
                'conversion_type' => SELF::CONVERSION_TYPE,
                'user_id' => $user_id,
                'user_email' => $email,
                'amount' => $payment->total,
                'currency' => edd_get_currency(),
                'items' => $items_data,
                'discount_codes' => $payment->discounts,
                'payment_method' => $payment->gateway
            ]);

            return $result;
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Track when a software license is generated
     * 
     * @param int $license_id
     * @param int $download_id
     * @param int $payment_id
     * @param string $type
     */
    public static function track_license_generated($license_id, $download_id, $payment_id, $type): void
    {
        try {
            Event::track_server_event('license_generated', [
                'license_id' => $license_id,
                'product_id' => $download_id,
                'order_id' => $payment_id,
                'license_type' => $type
            ]);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Track when a software license is renewed
     * 
     * @param int $license_id
     * @param int $payment_id
     */
    public static function track_license_renewed($license_id, $payment_id): void
    {
        try {
            Event::track_server_event('license_renewed', [
                'license_id' => $license_id,
                'order_id' => $payment_id
            ]);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Track subscription renewal payments
     * 
     * @param \EDD_Subscription $subscription
     * @param \EDD_Payment $payment
     */
    public static function track_subscription_renewal($subscription, $payment): void
    {
        try {
            Event::track_server_event('subscription_renewal', [
                'subscription_id' => $subscription->id,
                'order_id' => $payment->ID,
                'amount' => $payment->total,
                'currency' => $payment->currency,
                'product_id' => $subscription->product_id
            ]);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Track when a subscription is cancelled
     * 
     * @param \EDD_Subscription $subscription
     */
    public static function track_subscription_cancelled($subscription): void
    {
        try {
            Event::track_server_event('subscription_cancelled', [
                'subscription_id' => $subscription->id,
                'product_id' => $subscription->product_id
            ]);
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Helper function to get cart items data
     * 
     * @param array $cart_items
     * @return array
     */
    private static function get_cart_items_data($cart_items): array
    {
        try {
            $items_data = [];
            foreach ($cart_items as $key => $item) {
                $download = edd_get_download($item['id']);
                if (!$download) {
                    continue;
                }

                $item_data = [
                    'product_id' => $item['id'],
                    'name' => $download->get_name(),
                    'quantity' => $item['quantity'],
                    'price' => edd_get_cart_item_price($item['id'], $item['options'])
                ];

                // Add price option information if present
                if (isset($item['options']['price_id'])) {
                    $prices = edd_get_variable_prices($item['id']);
                    if (isset($prices[$item['options']['price_id']])) {
                        $item_data['variant'] = $prices[$item['options']['price_id']]['name'];
                    }
                }

                $items_data[] = $item_data;
            }
            return $items_data;
        } catch (\Throwable $th) {
            error_log($th);
        }
    }



    /**
     * Add AnalyticsWP columns to EDD payments table
     * 
     * @param array<string, string> $columns
     * @return array<string, string>
     */
    public static function add_admin_columns($columns): array
    {
        $columns['analyticswp_journey'] = 'AnalyticsWP Journey';
        $columns['analyticswp_source'] = 'AnalyticsWP Source';
        return $columns;
    }

    /**
     * Populate AnalyticsWP columns in EDD payments table
     * 
     * @param string $value The column content
     * @param int $payment_id The payment ID
     * @param string $column_name The name of the column
     * @return string
     */
    public static function populate_admin_columns($value, $payment_id, $column_name): string
    {
        switch ($column_name) {
            case 'analyticswp_journey':
                $journey_link = self::url_conversion_journey_link($payment_id);
                return '<a class="button button-secondary" href="' . esc_url($journey_link) . '">View Journey</a>';

            case 'analyticswp_source':
                return self::render_source_component_for_order_id($payment_id);

            default:
                return $value;
        }
    }

    /**
     * Generate journey link for an order
     * 
     * @param int $conversion_id
     * @return string
     */
    public static function url_conversion_journey_link($conversion_id): string
    {
        return add_query_arg([
            'page' => 'conversion_journeys',
            'conversion_id' => $conversion_id,
            'conversion_type' => SELF::CONVERSION_TYPE // TODO-INTEGRATION use a constant
        ], admin_url('admin.php'));
    }

    /**
     * Add AnalyticsWP meta box to EDD payment details screen
     */
    public static function add_meta_boxes(): void
    {
        $callback = function ($post) {
            $payment_id = $post->ID;
            echo "<h4>AnalyticsWP - Order #{$payment_id}</h4>";

            $journey_link = self::url_conversion_journey_link($payment_id);
            echo '<a class="button button-secondary" style="margin-bottom:10px; text-align:center; display:block" href="' . esc_url($journey_link) . '">View Journey</a>';

            echo 'Source:';
            echo '<div style="overflow: scroll;">' . self::render_source_component_for_order_id($payment_id) . '</div>';
        };

        $icon = "<img class='analytics-wp_meta-box_icon' src='https://solidaffiliate.com/brand/analyticswp/Icon-black.svg' width='30' height='30' alt=''>";

        add_meta_box(
            'analytics-wp_meta-box_edd-payment',
            "<span class='analytics-wp_meta-box-title' style='display:flex; flex-direction:row; align-items:center; gap:5px;'>" . $icon . "AnalyticsWP</span>",
            $callback,
            'download_page_edd-payment-history',
            'side',
            'high'
        );
    }

    /**
     * Get source data for an EDD order
     * 
     * @param int $order_id
     * @return array{0: array<string, mixed>, 1: array{multiple_referrers: null|array<array{referrer: string, timestamp: string}>}}|null
     */
    public static function source_for_order_id($order_id)
    {
        try {
            global $wpdb;

            $table_name = Event::table_name();

            // Get grouping identifier for the conversion
            $sql_identify_grouping = "
            SELECT user_id, unique_session_id 
            FROM $table_name 
            WHERE conversion_id = %d 
            AND conversion_type = %s
            LIMIT 1
        ";

            $sql_identify_grouping = (string)$wpdb->prepare($sql_identify_grouping, $order_id, SELF::CONVERSION_TYPE);
            $grouping_info = $wpdb->get_row($sql_identify_grouping, 'ARRAY_A');

            if (is_null($grouping_info)) {
                return null;
            }

            $groupBy = $grouping_info['user_id'] ? 'user_id' : 'unique_session_id';
            $groupingValue = $grouping_info['user_id'] ? (string)$grouping_info['user_id'] : (string)$grouping_info['unique_session_id'];

            // Get first event in journey
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

            // Get referrer data
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

            $multiple_referrers = array_map(function ($row) {
                return [
                    'referrer' => $row['referrer'],
                    'timestamp' => $row['timestamp']
                ];
            }, $results);

            return [
                $source_data,
                ['multiple_referrers' => $multiple_referrers]
            ];
        } catch (\Throwable $th) {
            error_log($th);
        }
    }

    /**
     * Render source component for an order
     * 
     * @param int $order_id
     * @return string
     */
    public static function render_source_component_for_order_id($order_id): string
    {
        $source = self::source_for_order_id($order_id);
        if (!$source) {
            return '<strong>Direct / None</strong>';
        }

        $html = Views::render_source_data_html($source[0]);

        // Render multiple referrers if they exist
        if (!empty($source[1]['multiple_referrers'])) {
            $html .= '<div style="font-weight:600; margin-top:10px; margin-bottom:5px;">Multiple referrers</div>';
            $html .= '<div style="position:relative; background:#fff; padding:10px; border:1px solid #c3c4c7; border-radius:6px; max-height: 200px; overflow-y: auto;">';
            $html .= '<ul style="margin:0">';

            foreach ($source[1]['multiple_referrers'] as $referrer) {
                $html .= '<li>';
                $html .= '<div><strong>Referrer:</strong> ' . htmlspecialchars($referrer['referrer'], ENT_QUOTES, 'UTF-8') . '</div>';
                $html .= '<div style="font-size:12px; opacity:.8">' . htmlspecialchars($referrer['timestamp'], ENT_QUOTES, 'UTF-8') . '</div>';
                $html .= '</li>';
            }

            $html .= '</ul>';
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Add journey section to EDD payment details screen
     * 
     * @param int $payment_id
     * @return void
     */
    public static function add_journey_section($payment_id): void
    {
        $source = self::source_for_order_id($payment_id);
        if (!$source) {
            return;
        }

        echo '<div class="postbox">';
        echo '<h3 class="hndle">AnalyticsWP Journey</h3>';
        echo '<div class="inside">';
        echo '<a class="button button-secondary" href="' . esc_url(self::url_conversion_journey_link($payment_id)) . '">View Full Journey</a>';
        echo '</div>';
        echo '</div>';
    }
}
