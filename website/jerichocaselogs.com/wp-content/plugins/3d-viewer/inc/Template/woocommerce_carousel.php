<div class="bp3dmodel-carousel" data-fullscreen='<?php echo esc_attr($settings_opt['bp_3d_fullscreen']); ?>'>
    <?php foreach( $models as $carousel_model ): ?>
    <div class="bp3dmodel-item">
    <div class="bp_model_gallery">
        <model-viewer class="model" id="bp_model_id_<?php echo esc_attr($id); ?>" <?php echo esc_attr($model_autoplay); ?> ar shadow-intensity="<?php echo esc_attr($model_Shadow); ?>" src="<?php echo esc_url($carousel_model['model_src']); ?>" alt="<?php echo esc_attr($alt); ?>" <?php echo esc_attr($camera_controls); ?> <?php echo esc_attr($camera_orbit); ?> <?php echo esc_attr($zooming_3d); ?> loading="<?php  echo esc_attr($loading); ?>" <?php echo esc_attr($auto_rotate); ?> <?php echo esc_attr($rotation_speed); ?> <?php echo esc_attr($rotation_delay); ?> 
        <?php 
        if(is_array($attribute)){
            foreach($attribute as $key => $value){ 
                echo "$key='$value'";
            }
        } ?>
        >
        <?php
            if($settings_opt['bp_3d_progressbar'] !== '1') { ?>
                <style>
                    model-viewer<?php echo '#bp_model_id_'.esc_attr($id); ?>::part(default-progress-bar) {
                        display:none;
                    }
                </style>
            <?php 
            } else {

            }
        ?>
        </model-viewer>

    </div>
    </div>
    <?php endforeach; ?>
</div> <!-- End Of Carousel -->