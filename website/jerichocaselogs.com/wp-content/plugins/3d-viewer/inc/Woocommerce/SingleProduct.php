<?php

namespace BP3D\Woocommerce;

use BP3D\Helper\Utils;

class SingleProduct
{

    public $theme_name;

    public function register()
    {
        $this->theme_name = wp_get_theme()->name;
        add_action('wp', [$this, 'woocommerce_loaded']); // 
        add_action('wp_footer', [$this, 'wp_woocommerce_theme_not_compatible']); // handle woocommerce incompatible with 3d-viewer models

        add_action('bp3d_product_model_before', [$this, 'model']);
        add_action('bp3d_product_model_after', [$this, 'model']);

        add_action('woocommerce_product_thumbnails', [$this, 'woocommerce_product_thumbnails'], 40); // merge with first image
    }

    public function hide_product_gallery()
    {
        $settings = Utils::getSettings('_bp3d_settings_');
        $meta = Utils::getPostMeta(get_the_ID(), '_bp3d_product_');
        $is_compatible = Utils::isCompatibleTheme();
        if ($is_compatible) {
            // return;
        }
        if ($meta('viewer_position') === 'replace') {
            $selector = $settings['gallery'];
            echo "selector is {$selector}";
        }
    }


    public function woocommerce_loaded()
    {
        $meta = Utils::getPostMeta(get_the_ID(), '_bp3d_product_');
        $settings = Utils::getSettings('_bp3d_settings_');

        $is_compatible = Utils::isCompatibleTheme();

        if ($meta('force_to_change_position', false, true) || $meta('is_custom_selector', false, true) || in_array($meta('viewer_position'), ['none', 'merge_with_first_image', 'custom_selector']) || !$is_compatible) {
            return;
        }

        if (is_array($meta('bp3d_models', [])) && count($meta('bp3d_models', [])) < 1) {
            return;
        }

        if ($settings('3d_woo_switcher') !== '0' && $settings('is_not_compatible') !== '1') {
            remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 30);
            remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 10);
            remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
            add_action('woocommerce_before_single_product_summary', [$this, 'bp3d_product_models'], 20);
        }
    }

    public function wp_woocommerce_theme_not_compatible()
    {
        global $product;
        $woocommerce_enabled = get_option('_bp3d_settings_', ['3d_woo_switcher' => false])['3d_woo_switcher'];

        if (!$woocommerce_enabled || gettype($product) !== 'object' || !is_single()) {
            return;
        }

        if (!method_exists($product, 'get_id')) {
            return;
        }

        if (Utils::isCompatibleTheme()) {
            return;
        }

        Product::instance()->get_3d_model_html(false, 'product-gallery');
    }





    /**
     * Adds 3D model thumbnail to WooCommerce product gallery
     * 
     * This method is hooked into 'woocommerce_product_thumbnails' action
     * and displays the 3D model viewer when position is set to merge with first image
     * 
     * @since 1.0.0
     * @return void
     */
    public function woocommerce_product_thumbnails()
    {
        $meta = Utils::getPostMeta(get_the_ID(), '_bp3d_product_');
        if ($meta('viewer_position') === 'merge_with_first_image') {
            Product::instance()->get_3d_model_html(false, 'product-gallery-inline');
        }
    }

    public function bp3d_product_models()
    {
        if (! function_exists('wc_get_gallery_image_html')) {
            return;
        }


        // Meta data of 3D Viewer
        $modeview_3d = get_post_meta(get_the_ID(), '_bp3d_product_', true);
        $viewer_position = isset($modeview_3d['viewer_position']) ? $modeview_3d['viewer_position'] : 'none';
        $class = Utils::getThemeClass($this->theme_name);

        if (in_array($this->theme_name, Utils::getNotCompatibleThemes())) {
            if ($viewer_position === 'replace') {
                $custom_selector = Utils::getCustomSelector($this->theme_name);
?>
                <style>
                    <?php echo esc_html($custom_selector) ?>>*:not(.modelViewerBlock) {
                        display: none;
                    }
                </style>
        <?php
            }
            return;
        }


        global $product;
        wp_enqueue_style('bp3d-custom-style');
        wp_enqueue_script('bp3d-public');


        $columns           = apply_filters('woocommerce_product_thumbnails_columns', 4);
        $post_thumbnail_id = $product->get_image_id();
        $wrapper_classes   = apply_filters(
            'woocommerce_single_product_image_gallery_classes',
            array(
                'woocommerce-product-gallery',
                'woocommerce-product-gallery--' . ($post_thumbnail_id ? 'with-images' : 'without-images'),
                'woocommerce-product-gallery--columns-' . absint($columns),
                'images',
            )
        );

        ?>

        <div class="product-modal-wrap <?php echo esc_attr($class) ?> position_<?php echo esc_attr($viewer_position) ?>">
            <div class="<?php echo esc_attr(implode(' ', array_map('sanitize_html_class', $wrapper_classes))); ?>" data-columns="<?php echo esc_attr($columns); ?>">
                <!-- Custom hook for 3d-viewer -->
                <?php
                if ($viewer_position === 'top') {
                    do_action('bp3d_product_model_before'); ?>
                <?php
                }

                if ($viewer_position === 'replace') {
                    add_filter('woocommerce_single_product_image_thumbnail_html', function ($content) {
                        return '';
                    }, 10, 2);
                    do_action('bp3d_product_model_before');
                }
                ?>

                <!-- this is required to work existing gallery -->
                <figure class="woocommerce-product-gallery__wrapper">
                    <?php

                    if ($post_thumbnail_id) {
                        $html = \wc_get_gallery_image_html($post_thumbnail_id, true);
                    } else {
                        $html  = '<div class="woocommerce-product-gallery__image--placeholder">';
                        $html .= sprintf('<img src="%s" alt="%s" class="wp-post-image" />', esc_url(\wc_placeholder_img_src('woocommerce_single')), esc_html__('Awaiting product image', 'model-viewer'));
                        $html .= '</div>';
                    }

                    echo apply_filters('woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
                    do_action('woocommerce_product_thumbnails');
                    ?>
                </figure>
            </div>
            <?php
            if ($viewer_position === 'bottom') {
                do_action('bp3d_product_model_after');
            }
            ?>

        </div> <!-- End of Product modal wrap -->
<?php
    }

    /**
     * Model
     */

    public function model()
    {
        Product::instance()->get_3d_model_html(false, 'product-gallery');
    }
}



// function remove_default_product_images() {}
