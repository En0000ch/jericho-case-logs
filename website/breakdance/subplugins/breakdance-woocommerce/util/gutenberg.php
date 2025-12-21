<?php

namespace Breakdance\WooCommerce\Gutenberg;

add_action('admin_footer-post.php', '\Breakdance\WooCommerce\Gutenberg\maybeAddNotice');
add_action('admin_footer-post-new.php', '\Breakdance\WooCommerce\Gutenberg\maybeAddNotice');
add_action('admin_init', '\Breakdance\WooCommerce\Gutenberg\maybeHandleReplaceRequest');

function isWcPage()
{
    $current_screen = get_current_screen();
    $ids = [wc_get_page_id('cart'), wc_get_page_id('checkout')];

    return $current_screen
        && 'page' === $current_screen->id
        && !empty($_GET['post'])
        && in_array(absint($_GET['post']), $ids, true);
}

function isUsingBlocks()
{
    return \Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_cart_block_default() ||
        \Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_checkout_block_default();
}

function replaceContent()
{
    $cartContent = '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->';
    $checkoutContent = '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->';

    $cartPage = get_post(wc_get_page_id('cart'));
    $checkoutPage = get_post(wc_get_page_id('checkout'));

    if ($cartPage && !str_contains($cartPage->post_content, '[woocommerce_cart]')) {
        wp_update_post([
            'ID' => wc_get_page_id('cart'),
            'post_content' => $cartContent
        ]);
    }

    if ($checkoutPage && !str_contains($checkoutPage->post_content, '[woocommerce_checkout]')) {
        wp_update_post([
            'ID' => wc_get_page_id('checkout'),
            'post_content' => $checkoutContent
        ]);
    }
}

function maybeHandleReplaceRequest()
{
    if (
        isset($_GET['bd_wc_replace_content']) &&
        current_user_can('edit_pages') &&
        check_admin_referer('bd_wc_replace_nonce')
    ) {
        replaceContent();
        $redirectUrl = isset($_GET['redirect_to']) ? esc_url_raw(urldecode($_GET['redirect_to'])) : admin_url();
        wp_redirect(remove_query_arg(['bd_wc_replace_content', '_wpnonce', 'redirect_to'], $redirectUrl));
        exit;
    }
}

function maybeAddNotice()
{
    if (!function_exists('is_woocommerce')) {
        return false;
    }

    if (!isWcPage()) {
        return false;
    }

    if (!isUsingBlocks()) {
        return false;
    }

    $currentUrl = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $url = add_query_arg([
        'bd_wc_replace_content' => '1',
        'redirect_to' => urlencode($currentUrl),
        '_wpnonce' => wp_create_nonce('bd_wc_replace_nonce')
    ], admin_url());
    ?>
  <script type="text/javascript">
    wp.data.dispatch('core/notices').createNotice(
      'warning',
      'The Checkout and Cart pages use Gutenberg blocks. To customize them in Breakdance, replace the content with WooCommerce shortcodes like [woocommerce_cart] and [woocommerce_checkout].',
      {
        isDismissible: true,
        actions: [
          {
            url: '<?php echo $url; ?>',
            label: 'Replace with shortcodes automatically',
          },
        ],
      }
    );
  </script>
    <?php
}