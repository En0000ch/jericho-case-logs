<?php

namespace BP3D\Woocommerce;

use BP3D\Helper\Utils;

class Product
{

    public static $_instance = null;
    public static function isset($array, $key, $default)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        return $default;
    }

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function getProductAttributes($modelData = [])
    {
        if (!is_array($modelData) || !is_array($modelData['bp3d_models'])) {
            return [];
        }
        global $product;
        $meta = Utils::getPostMeta($product && method_exists($product, 'get_id') ? $product->get_id() : '', '_bp3d_product_');

        // return empty array if not data found
        if (!$meta('all')) {
            return [];
        }

        $get_option = Utils::getSettings('_bp3d_settings_');

        $models = [];
        $variant_keys = [];
        if ($product && method_exists($product, 'get_available_variations')) {
            $variations =  $product->get_available_variations();
            if ($variations) {
                $list = wp_list_pluck($variations, 'attributes');
                if ($list) {
                    $variant_keys = array_keys(is_array($list[0]) ? $list[0] : []);
                }
            }
        }

        if (is_array($modelData['bp3d_models']) && count($modelData['bp3d_models']) > 0) {
            foreach ($modelData['bp3d_models'] as $value) {
                $model = [
                    'modelUrl' => $value['model_src'],
                    "useDecoder" => "none",
                    'poster' => $value['poster_src'] ?? '',
                    'product_variant' => $value['product_variant'] ?? '',
                    'skyboxImage' => $value['skybox_image_src'] ?? '',
                    'environmentImage' => $value['environment_image_src'] ?? '',
                    'arEnabled' => isset($value['enable_ar']) ? $value['enable_ar'] === '1' : false,
                    'modelISOSrc' => $value['model_iso_src'] ?? '',
                    'exposure' =>  isset($value['exposure']) ? $value['exposure'] : '1.0',
                    'arPlacement' => isset($value['ar_placement']) ? $value['ar_placement'] : 'floor',
                    'arMode' => isset($value['ar_mode']) ? $value['ar_mode'] : 'webxr',
                    'hotspots' => isset($value['hotspots']) ? $value['hotspots'] : '{}',
                    'variations' => [],
                    'initialView' => isset($value['initial_view']) ? json_decode($value['initial_view'], true) : '[]'
                ];

                // add variants data to the model
                foreach ($variant_keys as $key) {
                    if (isset($value[$key])) {
                        $model['variations'][$key] = $value[$key];
                        // $model[$key] = $value[$key];
                    }
                }
                // push the model to the models array
                $models[] = $model;
            }
        }

        $finalData = [
            "align" => 'center',
            "uniqueId" => "model" . get_the_ID(),
            "O3DVSettings" => [
                'isFullscreen' => true,
                "isNavigation" => $meta('show_arrows', false, true),
                'mouseControl' => true,
                // "zoom" =>  self::isset($modelData, 'bp_3d_zooming',  self::isset($options, 'bp_3d_zooming', "1")) === "1", // done
                "zoom" => $meta('bp_3d_zooming', $get_option('bp_3d_zooming', "1"), true), // done
                "isPagination" => $meta('show_thumbs', false, true)
            ],
            "currentViewer" => $meta('currentViewer', 'modelViewer'), //isset($modelData['currentViewer']) ? $modelData['currentViewer'] : 'modelViewer',
            "multiple" => true,
            "model" => [ // this will not work because multiple is enabled by default
                "modelUrl" => '',
                "poster" =>  ''
            ],
            "models" => $models,
            'show_model_instead_thumbnail' => $meta('show_model_instead_thumbnail', false, true),
            "zoom" => $meta('bp_3d_zooming', $get_option('bp_3d_zooming', "1"), true), // done
            "lazyLoad" => $get_option('bp_3d_loading', 'lazy') === 'lazy', // done
            "autoplay" => $get_option('bp_3d_autoplay', false, true), // done
            "shadow" =>  $meta('3d_shadow_intensity', '1', true), //done
            "autoRotate" => $get_option('bp_3d_rotate', false, true), // done
            "rotateDelay"  => (int) $get_option('3d_rotate_delay', 200), // done - 3d_rotate_delay
            "isPagination" => $meta('show_thumbs', false, true),
            "isNavigation" => $meta('show_arrows', false, true),
            'hotspotStyle' => $meta('hotspot_style', 'style-1'),
            "preload" => 'auto', //$get_option('bp_3d_preloader'] == '1' ? 'auto' : 'interaction',
            'rotationPerSecond' => $get_option('3d_rotate_speed', 20), // done
            "mouseControl" =>  $get_option('bp_camera_control', '1', true),
            "fullscreen" =>  $get_option('bp_3d_fullscreen', '1', true), // done
            "variant" => (bool) false,
            "loadingPercentage" =>  false, //$get_option('bp_model_progress_percent'] == '1',
            "progressBar" =>  $get_option('bp_3d_progressbar', '1', true), // done,
            "rotate" =>  $meta('bp_model_angle', false, true), //$get_option('bp_model_angle'] === '1',
            "rotateAlongX" => $meta('angle_property', 0, false, 'top'), //$get_option('angle_property']['top'],
            "rotateAlongY" => $meta('angle_property', 75, false, 'right'), //$get_option('angle_property']['right'],
            "exposure" => 1, //$get_option('3d_exposure'],
            "styles" => [
                "width" => '100%', //$get_option('bp_3d_width']['width'].$get_option('bp_3d_width']['unit'],
                'height' => $meta('bp_3d_height', '350', false, 'height') . $meta('bp_3d_height', 'px', false, 'unit'),
                "bgColor" => $meta('bp_model_bg', 'transparent'), //$modelData['bp_model_bg'] ?? '', // done
                "progressBarColor" => '#666', //$get_option('bp_model_progressbar_color'] ?? ''
            ],
            "stylesheet" => null,
            "additional" => [
                "ID" => "",
                "Class" => "",
                "CSS" => '', //$get_option('css'] ?? '',
            ],
            "animation" => false,
            "woo" =>  true,
            "selectedAnimation" => "",
            "placement" => "shortcode"
        ];


        return $finalData;
    }

    public function get_3d_model_html($return = true, $placement = 'block', $popup = false)
    {
        global $product;

        if (!$product || !$product->get_id()) {
            return '';
        }

        $meta = Utils::getPostMeta($product->get_id(), '_bp3d_product_');
        $modelData = $meta('all');
        $finalData = Product::getProductAttributes($modelData);
        $preset =  \BP3D\Base\Presets::getPresetById($meta('bp_model_template'));
        // $settings = Utils::getSettings('_bp3d_settings_', []);
        unset($preset['currentViewer']);
        $finalData = wp_parse_args($preset, $finalData);
        $class = Utils::getThemeClass();


        if ($placement == 'shop-loop-item') {
            $class .= ' productListItem';
        }

        if ($placement == 'product-gallery-inline') {
            $class .= ' position_' . $meta('viewer_position');
        }

        $finalData['position'] = $meta('viewer_position');
        $finalData['is_not_compatible'] = !Utils::isCompatibleTheme();
        $finalData['placement'] = $placement;

        $finalData['arLink'] = get_the_permalink();

        $show_model_instead_thumbnail = $meta('show_model_instead_thumbnail', false, true);

        $finalData =  apply_filters('bp3d_woocommerce_model_attribute', $finalData);

        ob_start();

?>
        <div class="modelViewerBlock wooCustomSelector product_<?php echo esc_attr($class) ?> <?php echo esc_attr($meta('show_model_instead_thumbnail', false, true) ? 'active' : ''); ?>" data-attributes='<?php echo esc_attr(wp_json_encode($finalData)); ?>'></div>

<?php

        wp_enqueue_script('bp3d-public');
        wp_enqueue_style('bp3d-custom-style');
        wp_enqueue_style('bp3d-frontend');

        if ($meta('currentViewer') === 'O3DViewer') {
            wp_enqueue_script('bp3d-o3dviewer');
        } else {
            wp_enqueue_script('bp3d-model-viewer');
        }

        $content = ob_get_contents();

        ob_end_clean();

        if ($return) {
            return $content;
        }
        echo wp_kses_post($content);
    }
}
