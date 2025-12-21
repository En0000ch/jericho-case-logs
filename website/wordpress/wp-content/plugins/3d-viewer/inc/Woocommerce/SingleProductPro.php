<?php

namespace BP3D\Woocommerce;

use BP3D\Helper\Utils;
use finfo;

class SingleProductPro extends SingleProduct
{

    public $theme_name;

    public function register()
    {
        $this->theme_name = wp_get_theme()->name;
        add_action('wp', [$this, 'woocommerce_loaded']);
        add_action('wp_footer', [$this, 'wp_woocommerce_theme_not_compatible']);
        add_action('wp_footer', [$this, 'wp_woocommerce_3d_popups']);

        add_action('bp3d_product_model_before', [$this, 'model']);
        add_action('bp3d_product_model_after', [$this, 'model']);

        add_action('woocommerce_product_thumbnails', [$this, 'woocommerce_product_thumbnails'], 40); // merge with first image

    }


    public function wp_woocommerfce_3d_popups()
    {
        global $product;
        $woocommerce_enabled = get_option('_bp3d_settings_', ['3d_woo_switcher' => false])['3d_woo_switcher'];

        if (!$woocommerce_enabled || gettype($product) !== 'object' || !is_single()) {
            return;
        }

        if (!method_exists($product, 'get_id')) {
            return;
        }

        $modelData = get_post_meta($product->get_id(), '_bp3d_product_', true);
        $meta = Utils::getPostMeta($product->get_id(), '_bp3d_product_', true);

        // code for modal/popup - Premium only
        $popupModels = isset($modelData['bp3d_popup_models']) && is_array($modelData['bp3d_popup_models']) ? $modelData['bp3d_popup_models'] : [];
        foreach ($popupModels as $index => $model) {
            wp_enqueue_script('bp3d-public');
            wp_enqueue_style('bp3d-custom-style');
            wp_enqueue_style('bp3d-frontend');

            if ($model['popupCurrentViewer'] === 'O3DViewer') {
                wp_enqueue_script('bp3d-o3dviewer');
            } else {
                wp_enqueue_script('bp3d-model-viewer');
            }

            $finalData = Product::getProductAttributes($meta('all'));
            $finalData['loading'] = 'lazy';
            $finalData['placement'] = 'popup';
            $finalData['uniqueId'] = "model" . uniqid();
            $finalData['loadingPercentage'] = true;
            $finalData['currentViewer'] = $model['popupCurrentViewer'] ?? 'modelViewer';

            $finalData['models'] = [[
                'modelUrl' => $model['model_src'],
                'poster' => $model['poster_src'] ?? '',
                'skyboxImage' => $model['skybox_image_src'] ?? '',
                'environmentImage' => $model['environment_image_src'] ?? '',
                'arEnabled' =>  isset($model['enable_ar']) ? $model['enable_ar'] === '1' : false,
                'modelISOSrc' =>  $model['model_iso_src'] ?? '',
            ]];
?>ki kora hosse
<div class="bp3dv-model-main" id="<?php echo esc_attr($model['target']) ?>" data-selector="<?php echo esc_attr($model['selector']) ?>">
    <div class="bp3dv-model-inner">
        <div class="close-btn">&times;</div>
        <div class="bp3dv-model-wrap">
            <div class="pop-up-content-wrap">
                <div class="modelViewerBlock wooCustomSelector wp-block-b3dviewer-modelviewer" data-type="popup" data-attributes='<?php echo esc_attr(wp_json_encode($finalData)); ?>'> </div>
            </div>
        </div>
    </div>
    <div class="bg-overlay"></div>
</div>
<script>

</script>
<?php
        }
    }


    public function wp_woocommerce_3d_popups()
    {
        global $product;
        $woocommerce_enabled = get_option('_bp3d_settings_', ['3d_woo_switcher' => false])['3d_woo_switcher'];

        if (!$woocommerce_enabled || gettype($product) !== 'object' || !is_single()) {
            return;
        }

        if (!method_exists($product, 'get_id')) {
            return;
        }

        $modelData = get_post_meta($product->get_id(), '_bp3d_product_', true);
        $meta = Utils::getPostMeta($product->get_id(), '_bp3d_product_', true);

        // code for modal/popup - Premium only
        $popupModels = isset($modelData['bp3d_popup_models']) && is_array($modelData['bp3d_popup_models']) ? $modelData['bp3d_popup_models'] : [];
        foreach ($popupModels as $index => $model) {
            wp_enqueue_script('bp3d-public');
            wp_enqueue_style('bp3d-custom-style');
            wp_enqueue_style('bp3d-frontend');

            if ($model['popupCurrentViewer'] === 'O3DViewer') {
                wp_enqueue_script('bp3d-o3dviewer');
            } else {
                wp_enqueue_script('bp3d-model-viewer');
            }

            $finalData = Product::getProductAttributes($meta('all'));
            $finalData['loading'] = 'lazy';
            $finalData['placement'] = 'popup';
            $finalData['uniqueId'] = "model" . uniqid();
            $finalData['loadingPercentage'] = true;
            $finalData['currentViewer'] = $model['popupCurrentViewer'] ?? 'modelViewer';
            $finalData['isPagination'] = false;
            $finalData['isNavigation'] = false;
            $finalData['preload'] = 'auto';
            $finalData['mouseControl'] = true;
            $finalData['styles'] = [
                'width' => '100%',
                'height' => '350px',
                'bgColor' => $meta('bp_model_bg', 'transparent'),
            ];
            $finalData['multiple'] = true;
            $finalData['model'] =  [ // this will not work because multiple is enabled by default
                "modelUrl" => '',
                "poster" =>  ''
            ];

            $finalData['models'] = [[
                'modelUrl' => $model['model_src'],
                'poster' => $model['poster_src'] ?? '',
                'skyboxImage' => $model['skybox_image_src'] ?? '',
                'environmentImage' => $model['environment_image_src'] ?? '',
                'arEnabled' =>  isset($model['enable_ar']) ? $model['enable_ar'] === '1' : false,
                'modelISOSrc' =>  $model['model_iso_src'] ?? '',
            ]];

            $finalData["O3DVSettings"] = [
                'isFullscreen' => true,
                "isNavigation" => $meta('show_arrows', false, true),
                'mouseControl' => true,
                // "zoom" =>  self::isset($modelData, 'bp_3d_zooming',  self::isset($options, 'bp_3d_zooming', "1")) === "1", // done
                "zoom" => $meta('bp_3d_zooming', true, true), // done
                "isPagination" => $meta('show_thumbs', false, true)
            ];

?>
    <div class="bp3dv-model-main" id="<?php echo esc_attr($model['target']) ?>" data-selector="<?php echo esc_attr($model['selector']) ?>">
        <div class="bp3dv-model-inner">
            <div class="close-btn">&times;</div>
            <div class="bp3dv-model-wrap">
                <div class="pop-up-content-wrap">
                    <div class="modelViewerBlock wooCustomSelector wp-block-b3dviewer-modelviewer" data-type="popup" data-attributes='<?php echo esc_attr(wp_json_encode($finalData)); ?>'> </div>
                </div>
            </div>
        </div>
        <div class="bg-overlay"></div>
    </div>
    <script>

    </script>
<?php
        }
    }
}
