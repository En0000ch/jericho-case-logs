<?php

/**
 * Provide a public-facing view for filter shortcode
 *
 * @since      1.0.0
 *
 * @package    WP_3D_Thingviewer_Lite
 * @subpackage WP_3D_Thingviewer_Lite/public/partials
 * @author     Accolore <support@accolore.com>
 */
?>

<div class="wp3dtvl-wrapper" id="<?php echo esc_attr($uid) ?>" data-model="<?php echo esc_url($model_cloaked_url); ?>" data-url="<?php echo esc_url($model_url) ?>" data-rot-x="<?php echo esc_attr($rotation_x); ?>" data-rot-y="<?php echo esc_attr($rotation_y); ?>" data-rot-z="<?php echo esc_attr($rotation_z); ?>" data-preview="<?php echo esc_url($model_preview); ?>">
	<p>WP 3D Thingviewer Lite need Javascript to work.<br/>Please activate and reload the page.</p>
</div>