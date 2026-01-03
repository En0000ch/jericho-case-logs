<?php

$id = wp_unique_id('bp3d-viewer-');

if ($attributes['currentViewer'] == 'modelViewer') {
    wp_enqueue_script('bp3d-model-viewer');
} else if ($attributes['currentViewer'] == 'O3DViewer') {
    wp_enqueue_script('bp3d-o3dviewer');
}

$attributes = apply_filters('bp3d_gutenberg_model_attribute', $attributes);

?>

<div
    id="<?php echo esc_attr($id) ?>"
    data-attributes="<?php echo esc_attr(wp_json_encode($attributes)) ?>"
    class="wp-block-b3dviewer-modelviewer">
</div>