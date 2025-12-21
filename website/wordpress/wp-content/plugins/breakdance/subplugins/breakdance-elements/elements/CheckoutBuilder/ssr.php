<?php

if (!is_checkout()) {
    \Breakdance\WooCommerce\getErrorMessageForWooElementPageInWrongPage(
        'checkout',
        \EssentialElements\Checkoutbuilder::name(),
        "Checkout"
    );
} elseif (is_order_received_page()) {
    \Breakdance\WooCommerce\CheckoutBuilder\thankYou();
} elseif (is_checkout_pay_page()) {
    \Breakdance\WooCommerce\CheckoutBuilder\orderPay();
} else {
    \Breakdance\WooCommerce\CheckoutBuilder\checkout();
}
