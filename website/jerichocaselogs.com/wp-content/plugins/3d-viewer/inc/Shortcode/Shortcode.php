<?php

namespace BP3D\Shortcode;

use BP3D\Helper\Utils;
use BP3D\Helper\Block;
use BP3D\Woocommerce\Product;

class Shortcode
{

    public function register()
    {
        add_shortcode('3d_viewer', [$this, 'bp3dviewer_cpt_content_func']);
        add_shortcode('3d_viewer_product', [$this, 'product_model_viewer']);
    }

    //Lets register our shortcode
    function bp3dviewer_cpt_content_func($atts)
    {
        extract(shortcode_atts(array(
            'id' => '',
            'src' => '',
            'alt' => '',
            'width' => '100%',
            'height' => 'auto',
            'auto_rotate' => 'auto-rotate',
            'camera_controls' => 'camera-controls',
            'zooming_3d' => '',
            'loading' => '',
            'poster' => ''
        ), $atts));

        if (!$id) {
            return false;
        }


        $post_type = get_post_type($id);
        $isGutenberg = get_post_meta($id, 'isGutenberg', true);

        if (!in_array($post_type, ['bp3d-model-viewer', 'product'])) {
            return false;
        }

        if ($isGutenberg) {
            return Block::instance()->render_block($id);
        }
        ob_start();

        $meta = Utils::getPostMeta($id, '_bp3dimages_');

        if ($meta('bp_3d_src_type') == 'upload') {
            $modelSrc = $meta('bp_3d_src', [], false, 'url');
        } else {
            $modelSrc = $meta('bp_3d_src_link');
        }

        $poster = $meta('bp_3d_poster', [], false, 'url');

        $finalData = wp_parse_args([
            "model" => [
                "modelUrl" => $modelSrc,
                "poster" => $poster
            ],
            "models" => [],
        ], $this->get_common_attributes($meta, $id));

        $finalData =  apply_filters('bp3d_classic_model_attribute', $finalData);
?>

        <div class="modelViewerBlock" data-attributes='<?php echo esc_attr(wp_json_encode($finalData)) ?>'></div>

<?php

        wp_enqueue_script('bp3d-public');
        wp_enqueue_style('bp3d-custom-style');
        wp_enqueue_style('bp3d-frontend');

        if ($meta('currentViewer') === 'O3DViewer') {
            wp_enqueue_script('bp3d-o3dviewer');
        } else {
            wp_enqueue_script('bp3d-model-viewer');
        }

        return ob_get_clean();
    }

    /**
     * shortcode for product model viewer
     */
    public function product_model_viewer($attrs)
    {
        extract(shortcode_atts(array(
            'id' => get_the_ID(),
            'width' => '100%',
            'late_initialize' => false
        ), $attrs));

        $post_type = get_post_type($id);

        if (!in_array($post_type, ['product'])) {
            return false;
        }

        return Product::instance()->get_3d_model_html(true, 'shortcode');
    }

    public function get_common_attributes($meta, $id)
    {
        return  array(
            "align" => $meta('bp_3d_align', "center"),
            "uniqueId" => "model$id",
            "currentViewer" => $meta('currentViewer', "modelViewer"),
            "multiple" => $meta('bp_3d_model_type') !== 'msimple',
            "O3DVSettings" =>  [
                "isFullscreen" =>  $meta('bp_3d_fullscreen', "1", true),
                "isPagination" => $meta("show_thumbs", "0", true),
                "isNavigation" =>  $meta("show_arrows", "0", true),
                "camera" =>  null,
                "mouseControl" =>  $meta("bp_camera_control", '1', true),
            ],
            "environmentImage" => $meta('bp_3d_environment_image'),
            "lazyLoad" =>  $meta('bp_3d_loading', "lazy") === "lazy", // maybe not needed
            "loading" =>  $meta('bp_3d_loading'),
            "autoplay" => $meta('bp_3d_autoplay', "0", true),
            "shadow" =>  $meta('3d_shadow_intensity', "1", false),
            "autoRotate" => $meta('bp_3d_rotate', "0", true),
            "zoomLevel" => $meta('3d_zoom_level'),
            "zoom" => $meta('bp_3d_zooming', "1", true),
            "isPagination" => $meta("show_thumbs", "0", true),
            "isNavigation" =>  $meta("show_arrows", "0", true),
            'hotspotStyle' => $meta('hotspot_style', 'style-1'),
            "preload" => 'auto', //$data['bp_3d_preloader'] == '1' ? 'auto' : $poster ? 'interaction' : 'auto',
            'rotationPerSecond' => $meta('3d_rotate_speed'),
            "mouseControl" =>  $meta('bp_camera_control', '1', true),
            "lockXAxisRotation" =>  $meta('lockXAxisRotation', "0", true),
            "lockYAxisRotation" =>  $meta('lockYAxisRotation', "0", true),
            "fullscreen" =>  $meta('bp_3d_fullscreen', "1", true),
            "zoomInOutBtn" =>  $meta('bp_3d_zoom_in_out_btn', '0', true),
            "cameraBtn" =>  $meta('bp_3d_camera_btn', "0", true),
            "variant" => (bool) false,
            "loadingPercentage" =>  $meta('bp_model_progress_percent', "0", true),
            "progressBar" =>  $meta('bp_3d_progressbar', "0", true),
            "rotate" => $meta('bp_model_angle', "0", true),
            "rotateAlongX" => $meta("angle_property", "0", false, "top"), // $data['angle_property']['top'],
            "rotateAlongY" => $meta('angle_property', "75", false, "right"),
            "exposure" => $meta('3d_exposure'),

            "stylesheet" => null,
            "additional" => [
                "ID" => "",
                "Class" => "",
                "CSS" => $meta('css'),
            ],
            "animation" => (bool) false,
            "woo" => (bool) false,
            "selectedAnimation" => "",
            'placement' => 'shortcode',
            "styles" => [
                "width" => $meta('bp_3d_width', '100', false, 'width') . $meta('bp_3d_width', '%', false, 'unit'),
                "height" => $meta('bp_3d_height', '100', false, 'height') . $meta('bp_3d_height', '%', false, 'unit'),
                "bgColor" => $meta('bp_model_bg'),
                "progressBarColor" => $meta('bp_model_progressbar_color')
            ],
        );
    }
}
