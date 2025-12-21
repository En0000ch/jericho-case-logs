<?php

namespace AnalyticsWP\Lib\Integrations;

use AnalyticsWP\Lib\Event;
use AnalyticsWP\Lib\Integrations;

/**
 * Responsibilities
 * [ ] TODO untested on real sites
 * [ ] Track SureCart orders when created and paid
 */
class SureCartIntegration implements IntegrationInterface
{
    const SLUG = 'surecart';

    public static function is_available(): bool
    {
        return defined('SURECART_PLUGIN_FILE');
    }

    public static function get_integration_description()
    {
        return [
            'slug' => self::SLUG,
            'isAvailable' => self::is_available(),
            'name' => 'SureCart',
            'description' => 'Track order creation and checkout confirmations in SureCart',
            'category' => 'eCommerce',
        ];
    }

    public static function add_hooks(): void
    {
        if (!self::is_available() || !Integrations::is_integration_enabled(self::SLUG)) {
            return;
        }

        // surecart/checkout_confirmed
        // surecart/order_created
        // surecart/order_paid
        // surecart/purchase_created
        // Track order events
        add_action('surecart/checkout_confirmed', [self::class, 'track_checkout_confirmed'], 10, 2);
        // add_action('surecart/order_created', [self::class, 'track_checkout_confirmed'], 10, 2);
        // add_action('surecart/order_paid', [self::class, 'track_checkout_confirmed'], 10, 2);
        // add_action('surecart/purchase_created', [self::class, 'track_checkout_confirmed'], 10, 2);
    }


    /**
     * Track checkout confirmation events
     * https://developer.surecart.com/docs/order-actions-reference
     * 
     * @param object $checkout The checkout object
     * @param object $request The request object
     * 
     * @return array{error: string}|int Event ID or error message
     */
    public static function track_checkout_confirmed($checkout, $request)
    {
        try {
            return Event::track_server_event('checkout_confirmed', [
                'conversion_type' => 'surecart_checkout',
                'conversion_id' => $checkout->id,
                'unique_event_identifier' => 'surecart_checkout_' . $checkout->id,

                // Checkout amounts
                'amount_due' => $checkout->amount_due,
                'currency' => $checkout->currency,
                'subtotal' => $checkout->subtotal_amount,
                'tax' => $checkout->tax_amount,
                'total' => $checkout->total_amount,
                'discount' => $checkout->discount_amount,

                // Customer information
                'customer_id' => $checkout->customer,
                'email' => $checkout->email,
                'first_name' => $checkout->first_name,
                'last_name' => $checkout->last_name,

                // Additional details
                'status' => $checkout->status,
                'payment_method_required' => $checkout->payment_method_required,
                'shipping_enabled' => $checkout->shipping_enabled,
                'tax_enabled' => $checkout->tax_enabled,
                'tax_status' => $checkout->tax_status,

                // Address information
                'billing_address' => $checkout->billing_address,
                'shipping_address' => $checkout->shipping_address,

                // Timestamps
                'created_at' => $checkout->created_at,
                'updated_at' => $checkout->updated_at,
                'paid_at' => $checkout->paid_at
            ]);
        } catch (\Throwable $e) {
            error_log('AnalyticsWP: Error tracking SureCart checkout confirmation - ' . $e->getMessage());
            return ['error' => 'Failed to track checkout confirmation: ' . $e->getMessage()];
        }
    }
}
