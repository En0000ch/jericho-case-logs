<?php

namespace BP3D\Base;

class EnqueueAssets
{

    public function register()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueBackendFiles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontEndFiles']);
        add_filter('script_loader_tag', [$this, 'b3dviewer_script_type_load'], 10, 3);
        add_action('wp_footer', [$this, 'custom_css']);
    }


    public function b3dviewer_script_type_load($tag, $handle, $src)
    {
        // if not your script, do nothing and return original $tag
        if ('bp3d-model-viewer' !== $handle) {
            return $tag;
        }
        // change the script tag by adding type="module" and return it.
        $tag = '<script type="module" id="' . $handle . '-js" src="' . esc_url($src) . '"></script>';
        return $tag;
    }

    public function enqueueFrontEndFiles()
    {

        wp_localize_script('bp3d-public', 'assetsUrl', [
            'siteUrl'   => site_url(),
            'assetsUrl' => BP3D_DIR . '/public',
        ]);
    }

    public function enqueueBackendFiles($hook_suffix)
    {
        global $post;
        $post_type = isset($post->post_type) ? $post->post_type : (isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : null);
        $woo_enabled = get_option('b3dviewer_enable_woocommerce', true);

        //script
        wp_register_script('bp3d-admin-script', BP3D_DIR . 'build/admin.js', ['jquery'], BP3D_VERSION, true);
        // style
        wp_register_style('bp3d-admin-style', BP3D_DIR . 'public/css/admin-style.css', [], BP3D_VERSION);
        wp_register_style('bp3d-readonly-style', BP3D_DIR . 'public/css/readonly.css', [], BP3D_VERSION);

        if ($post_type === 'bp3d-model-viewer' || $post_type === 'product') {
            wp_enqueue_style('bp3d-admin-style');
            wp_enqueue_style('bp3d-readonly-style');
            wp_enqueue_script('bp3d-admin-script');
        }

        wp_register_script('bp3d-model-viewer', BP3D_DIR . 'public/js/model-viewer.latest.min.js', [], BP3D_VERSION, true);
        wp_register_script('bp3d-o3dviewer', BP3D_DIR . 'public/js/o3dv.min.js', [], BP3D_VERSION, true);

        wp_register_script('bp3d-visual-editor', BP3D_DIR . 'build/visual-editor/index.js', [
            'b3dviewer-modelviewer-editor-script',
            'wp-block-library',
            'wp-editor',
            'wp-i18n',
            'wp-api',
            'wp-util',
            'lodash',
            'wp-data',
            'wp-core-data',
            'wp-api-request',
            'wp-tinymce',
            "bp3d-model-viewer",
            "bp3d-o3dviewer",
            "wp-components",
            "wp-i18n"
        ], BP3D_VERSION, true);

        wp_register_style('bp3d-visual-editor', BP3D_DIR . 'build/visual-editor/index.css', ['b3dviewer-modelviewer-editor-style', 'wp-components', 'wp-block-library', 'wp-block-editor', 'wp-edit-blocks', 'wp-format-library'], BP3D_VERSION, 'all');
    }

    public function custom_css()
    {
        $settings = \BP3D\Helper\Utils::getSettings('_bp3d_settings_', []);
?>
        <style>
            <?php echo esc_html($settings('custom_css')); ?>
        </style>

<?php
    }
}
