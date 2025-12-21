<?php 

namespace BP3D\Template;

use BP3D\Helper\Utils;

class ModelViewer{

    public static function html($data){
        self::enqueueFile();
        ob_start();

        ?>
        <!-- <h2>Nothing to hide</h2> -->
        <div id="<?php echo esc_attr($data['uniqueId']) ?>" class="b3dviewer align<?php echo esc_attr(self::i($data, 'align')) ?> <?php echo esc_attr($data['woo'] ? ' woocommerce' : '') ?>" > 
            <div id="<?php echo esc_attr(self::i($data['additional'], 'ID')) ?>" class="bp_model_parent <?php echo esc_attr(self::i($data['additional'], 'Class')) ?> b3dviewer-wrapper <?php echo esc_attr(self::i($data, 'elementor', false) ? 'elementor': '') ?>">
                <style><?php echo esc_html($data['stylesheet']) ?></style>
                <?php 
                    $attribute = "exposure=".$data['exposure'];
                    if($data['mouseControl']){
                        $attribute .= ' camera-controls ';
                    }
                    if($data['autoRotate']){
                        $attribute .= ' auto-rotate ';
                    }
            
                    if($data['lazyLoad']){
                        $attribute .= "loading=lazy ";
                    }
            
                    if($data['shadow']){
                        $attribute .= " shadow-intensity=1 shadow-softness=1 ";
                    }
            
                    if($data['autoplay']){
                        $attribute .= " autoplay ";
                    }
                    if(!$data['multiple'] && $data['selectedAnimation']){
                        $attribute .= " data-animation=".$data['selectedAnimation']." animation-name=".$data['selectedAnimation']." ";
                    }

                    $cameraOrbit = $data['rotateAlongX']."deg ".$data['rotateAlongY']."deg 105% ";

                    if($data['multiple']){
                        $source = $data['models'][0]['modelUrl'];
                        $poster = $data['models'][0]['poster'];
                    }else {
                        $source = self::i($data['model'], 'modelUrl', '');
                        $poster = self::i($data['model'], 'poster', '');
                    }

                    $explode = explode('.', $source);
                    $ext = $explode[count($explode) - 1];

                    if(in_array($ext, ['glb', 'gltf'])){
                        ?>

                        <model-viewer 
                            data-js-focus-visible 
                            data-decoder="<?php echo esc_attr(self::i($data['model'], 'decoder', 'none')) ?>" <?php echo esc_attr($attribute); ?>  poster="<?php echo esc_url($poster); ?>"  src="<?php echo esc_url($source); ?>"  alt="<?php esc_html_e("A 3D model", "model-viewer") ?>"
                            <?php if($data['rotate']){ ?>
                            camera-orbit="<?php echo esc_attr($cameraOrbit) ?>"
                            <?php } ?>
                            class="<?php echo esc_attr($data['progressBar'] ? '' : 'hide_progressbar') ?>"
                            >
                            <?php if($data['fullscreen']){ ?>
                                <?php require(__DIR__.'/../Shortcode/fullscreen_buttons.php'); ?>
                            <?php } ?>

                            <?php if($data['variant']){ ?>
                                <div class="variantWrapper select">
                                    <?php esc_html_e('Variant', 'model-viewer') ?>: <select id="variant"></select>
                                </div>
                            <?php } ?>

                            <?php if($data['animation']){ ?>
                                <div class="animationWrapper select">
                                    <?php esc_html_e('Animations', 'model-viewer') ?>: <select id="animations"></select>
                                </div>
                            <?php } ?>
                            <?php if($data['loadingPercentage']){ ?>
                                <div class="percentageWrapper">
                                    <div class="overlay"></div>
                                    <span class="percentage">0%</span>
                                </div>
                            <?php } ?>

                            <?php if($data['multiple']){ ?>
                            <div class="slider">
                                <div class="slides">
                                    <?php foreach($data['models'] as $key => $model){ ?>
                                        <?php if($model){ ?>
                                            <button class="slide <?php echo esc_attr($key === 0 ? 'selected' : '') ?>" data-source="<?php echo esc_url($model['modelUrl']) ?>" data-poster="<?php echo esc_url(self::i($model, 'poster', '')) ?>"> 
                                            <img src="<?php echo esc_url(self::i($model, 'poster', '')) ?>" /> 
                                            </button>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php } ?>
                        </model-viewer>
                    <?php }else { ?>
                        <div class="online_3d_viewer"
                            style="width: <?php echo esc_attr($data['styles']['width']) ?>; height: <?php echo esc_attr($data['styles']['height']) ?>;"
                            backgroundcolor="<?php echo esc_attr(implode(',', Utils::hexToRGB($data['styles']['bgColor']))) ?>"
                            model="<?php echo esc_url($source) ?>"
                            environmentmap="<?php echo esc_url(BP3D_DIR) ?>public/images/envmaps/fishermans_bastion/negz.jpg,<?php echo esc_url(BP3D_DIR) ?>public/images/envmaps/fishermans_bastion/negx.jpg,<?php echo esc_url(BP3D_DIR) ?>public/images/envmaps/fishermans_bastion/negy.jpg,<?php echo esc_url(BP3D_DIR) ?>public/images/envmaps/fishermans_bastion/posx.jpg,<?php echo esc_url(BP3D_DIR) ?>public/images/envmaps/fishermans_bastion/posy.jpg,<?php echo esc_url(BP3D_DIR) ?>public/images/envmaps/fishermans_bastion/posz.jpg" 
                            >
                        </div>
                        <?php if($data['fullscreen']){ ?>
                            <?php require(__DIR__.'/../Shortcode/fullscreen_buttons.php'); ?>
                        <?php } ?>
                    <?php } ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }

    /**
     * enqueue essential file
     */
    public static function enqueueFile(){
        wp_enqueue_script('bp3d-public');
        wp_enqueue_style('bp3d-public');
    }


    /**
     * return value if it isset
     */
    public static function i($array = [], $index = ''){
        if(isset($array[$index])){
            return $array[$index];
        }
        return false;
    }

}

