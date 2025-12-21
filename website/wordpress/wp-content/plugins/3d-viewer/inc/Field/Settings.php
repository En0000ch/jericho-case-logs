<?php

namespace BP3D\Field;

class Settings
{

  protected $prefix = '_bp3d_settings_';
  public function register()
  {
    add_action('init', [$this, 'init'], 0);
  }
  public function init()
  {
    $this->preset();
    $this->woocommerce();
    $this->shortcode();

    $this->woocommerce_selectors();
  }

  public function preset()
  {
    \CSF::createOptions($this->prefix, array(
      'menu_title'  => 'Settings',
      'menu_slug'   => '3dviewer-settings',
      'menu_type'   => 'submenu',
      'menu_parent' => '3d-viewer',
      'theme'       => 'light',
      'framework_title' => __('3D Viewer Settings', 'model-viewer'),
      'menu_position' => 10,
      'footer'      => false,
      'footer_credit'  => '3D Viewer',
      'footer_text' => '',

    ));

    \CSF::createSection($this->prefix, array(
      'title'  => __('Preset', 'model-viewer'),
      'class'    => 'bp3d-readonly',
      'fields' => array(
        array(
          'id'           => 'bpp_3d_width',
          'type'         => 'dimensions',
          'title'        => __('Width', 'model-viewer'),
          'desc'         => __('3D Viewer Width', 'model-viewer'),
          'default'  => array(
            'width'  => '100',
            'unit'   => '%',
          ),
          'height'   => false,
        ),
        array(
          'id'      => 'bpp_3d_height',
          'type'    => 'dimensions',
          'title'   => __('Height', 'model-viewer'),
          'desc'    => __('3D Viewer height', 'model-viewer'),
          'units'   => ['px', 'em', 'pt'],
          'default'  => array(
            'height' => '320',
            'unit'   => 'px',
          ),
          'width'   => false,
        ),
        array(
          'id'           => 'bpp_model_bg',
          'type'         => 'color',
          'title'        => __('Background Color', 'model-viewer'),
          'subtitle'        => __('Set Background Color For 3d Model.If You don\'t need just leave blank. Default : \'transparent color\'', 'model-viewer'),
          'desc'         => __('Choose Your Background Color For Model.', 'model-viewer'),
          'default'      => 'transparent'
        ),
        array(
          'id'       => 'bpp_3d_autoplay',
          'type'     => 'switcher',
          'title'    => __('Autoplay', 'model-viewer'),
          'subtitle' => __('Enable or Disable AutoPlay', 'model-viewer'),
          'desc'     => __('Autoplay Feature is for Autoplay Supported Model.', 'model-viewer'),
          'text_on'  => __('Yes', 'model-viewer'),
          'text_off' => __('No', 'model-viewer'),
          'default'  => false,
        ),
        array(
          'id'       => '3dp_shadow_intensity',
          'type'     => 'spinner',
          'title'    => __('Shadow Intensity', 'model-viewer'),
          'subtitle' => __('Shadow Intensity for Model', 'model-viewer'),
          'desc'     => __('Use Shadow Intensity Limit for Model. "1" for Default.', 'model-viewer'),
          'default' => '1',
          'class' => 'bp3d-readonly'
        ),

        array(
          'id'        => 'bpp_3d_preloader',
          'type'      => 'switcher',
          'title'     => __('Preload', 'model-viewer'),
          'subtitle'  => __('Preload with poster and show model on interaction', 'model-viewer'),
          'desc'      => __('Choose "Yes" if you want to use preload with poster image.', 'model-viewer'),
          'text_on'   => __('Yes', 'model-viewer'),
          'text_off'  => __('NO', 'model-viewer'),
          'text_width'  => 60,
          'default'   => false,
        ),
        array(
          'id'       => 'bpp_camera_control',
          'type'     => 'switcher',
          'title'    => __('Moving Controls', 'model-viewer'),
          'desc'     => __('Use The Moving controls to enable user interaction', 'model-viewer'),
          'text_on'  => __('Yes', 'model-viewer'),
          'text_off' => __('No', 'model-viewer'),
          'default' => true,

        ),
        array(
          'id'        => 'bpp_3d_zooming',
          'type'      => 'switcher',
          'title'     => 'Enable Zoom',
          'subtitle'  => __('Enable or Disable Zoom Behaviour', 'model-viewer'),
          'desc'      => __('If you wish to disable zooming behaviour please choose No.', 'model-viewer'),
          'text_on'   => __('Yes', 'model-viewer'),
          'text_off'  => __('No', 'model-viewer'),
          'text_width'  => 60,
          'default'   => true,
        ),
        array(
          'id'        => 'bpp_3d_progressbar',
          'type'      => 'switcher',
          'title'     => __('Progressbar', 'model-viewer'),
          'subtitle'  => __('Enable or Disable Progressbar', 'model-viewer'),
          'desc'      => __('If you wish to disable Progressbar please choose No.', 'model-viewer'),
          'text_on'   => __('Yes', 'model-viewer'),
          'text_off'  => __('No', 'model-viewer'),
          'text_width'  => 60,
          'default'   => true,
        ),
        array(
          'id'         => 'bpp_3d_loading',
          'type'       => 'radio',
          'title'      => __('Loading Type', 'model-viewer'),
          'subtitle'   => __('Choose Loading type, default:  \'Auto\' ', 'model-viewer'),
          'options'    => array(
            'auto'  => __('Auto', 'model-viewer'),
            'lazy'  => __('Lazy', 'model-viewer'),
            'eager' => __('Eager', 'model-viewer'),
          ),
          'default' => 'auto',
        ),

        array(
          'id'       => 'bpp_3d_rotate',
          'type'     => 'switcher',
          'title'    => __('Auto Rotate', 'model-viewer'),
          'subtitle' => __('Enable or Disable Auto Rotation', 'model-viewer'),
          'desc'     => __('Enables the auto-rotation of the model.', 'model-viewer'),
          'text_on'  => __('Yes', 'model-viewer'),
          'text_off' => __('No', 'model-viewer'),
          'default'  => true,

        ),
        array(
          'id'       => '3dp_rotate_speed',
          'type'     => 'spinner',
          'title'    => __('Auto Rotate Speed', 'model-viewer'),
          'subtitle' => __('Auto Rotation Speed Per Seconds', 'model-viewer'),
          'desc'     => __('Use Negative Number for Reverse Action. "30" for Default Behaviour.', 'model-viewer'),
          'min'         => 0,
          'max'         => 180,
          'default' => 30,
          'dependency' => array('bp_3d_rotate', '==', true),
        ),
        array(
          'id'       => '3dp_rotate_delay',
          'type'     => 'number',
          'title'    => __('Auto Rotation Delay', 'model-viewer'),
          'subtitle' => __('After a period of time auto rotation will start', 'model-viewer'),
          'desc'     => __('Sets the delay before auto-rotation begins. The format of the value is a number in milliseconds.(1000ms = 1s)', 'model-viewer'),
          'default' => 3000,
          'dependency' => array('bp_3d_rotate', '==', true),
        ),
        array(
          'id'       => 'bpp_3d_fullscreen',
          'type'     => 'switcher',
          'title'    => __('Fullscreen', 'model-viewer'),
          'subtitle' => __('Enable or Disable Fullscreen Mode', 'model-viewer'),
          'desc'     => __('Default: "Yes / Enable"', 'model-viewer'),
          'text_on'  => __('Yes', 'model-viewer'),
          'text_off' => __('No', 'model-viewer'),
          'default'  => true,
        ),
      ) // End fields


    ));
  }

  public function woocommerce()
  {
    \CSF::createSection($this->prefix, array(
      'title'  => __('Woocommerce Settings', 'model-viewer'),
      'fields' => array(
        // 3D Model Options
        array(
          'id'       => '3d_woo_switcher',
          'type'      => 'switcher',
          'title'    => __('Woocommerce', 'model-viewer'),
          'subtitle' => __('Enable / Disable Woocommerce Feature for 3D Viewer.', 'model-viewer'),
          'desc'     => __('Enable / Disable. Default is Enable.', 'model-viewer'),
          'default' => true,
        ),
        array(
          'id'       => 'is_not_compatible',
          'type'      => 'switcher',
          'title'    => __('3D Viewer is not Compatible with this Theme', 'model-viewer'),
          'desc'     => __('Enable if 3D Viewer is not compatible with this theme', 'model-viewer'),
          'default' => false,
        ),
        // array(
        //   'id'       => 'product_gallery_selector',
        //   'type'      => 'text',
        //   'title'    => __('Product Gallery Class or ID or Valid CSS Selector', 'model-viewer'),
        //   'desc'     => __('Write here the product gallery class or id or any valid CSS selector', 'model-viewer'),
        //   'default' => '.woocommerce-product-gallery',
        //   'dependency' => array('is_not_compatible', '==', '1'),
        // ),
        array(
          'id'       => '3d_shadow_intensity',
          'type'     => 'spinner',
          'title'    => __('Shadow Intensity', 'model-viewer'),
          'subtitle' => __('Shadow Intensity for Model', 'model-viewer'),
          'desc'     => __('Use Shadow Intensity Limit for Model. "1" for Default.', 'model-viewer'),
          'default' => '1',
          'class'    => 'bp3d-readonly'
        ),
        array(
          'id'       => 'bp_camera_control',
          'type'     => 'switcher',
          'title'    => __('Moving Controls', 'model-viewer'),
          'desc'     => __('Use The Moving controls to enable user interaction', 'model-viewer'),
          'text_on'  => __('Yes', 'model-viewer'),
          'text_off' => __('No', 'model-viewer'),
          'default' => true,
        ),
        array(
          'id'        => 'bp_3d_zooming',
          'type'      => 'switcher',
          'title'     => __('Enable Zoom', 'model-viewer'),
          'subtitle'  => __('Enable or Disable Zoom Behaviour', 'model-viewer'),
          'desc'      => __('If you wish to disable zooming behaviour please choose No.', 'model-viewer'),
          'text_on'   => __('Yes', 'model-viewer'),
          'text_off'  => __('No', 'model-viewer'),
          'text_width'  => 60,
          'default'   => true,
        ),
        array(
          'id'        => 'bp_3d_progressbar',
          'type'      => 'switcher',
          'title'     => __('Progressbar', 'model-viewer'),
          'subtitle'  => __('Enable or Disable Progressbar', 'model-viewer'),
          'desc'      => __('If you wish to disable Progressbar please choose No.', 'model-viewer'),
          'text_on'   => __('Yes', 'model-viewer'),
          'text_off'  => __('No', 'model-viewer'),
          'text_width'  => 60,
          'default'   => true,
          'class'    => 'bp3d-readonly'
        ),
        array(
          'id'         => 'bp_3d_loading',
          'type'       => 'radio',
          'title'      => __('Loading Type', 'model-viewer'),
          'subtitle'   => __('Choose Loading type, default:  \'Auto\' ', 'model-viewer'),
          'options'    => array(
            'auto'  => __('Auto', 'model-viewer'),
            'lazy'  => __('Lazy', 'model-viewer'),
            'eager' => __('Eager', 'model-viewer'),
          ),
          'default'    => 'auto',
        ),
        array(
          'id'       => 'bp_3d_rotate',
          'type'     => 'switcher',
          'title'    => __('Auto Rotate', 'model-viewer'),
          'subtitle' => __('Enable or Disable Auto Rotation', 'model-viewer'),
          'desc'     => __('Enables the auto-rotation of the model.', 'model-viewer'),
          'text_on'  => __('Yes', 'model-viewer'),
          'text_off' => __('No', 'model-viewer'),
          'default'  => true,
          'class'    => 'bp3d-readonly'
        ),
        array(
          'id'       => '3d_rotate_speed',
          'type'     => 'spinner',
          'title'    => __('Auto Rotate Speed', 'model-viewer'),
          'subtitle' => __('Auto Rotation Speed Per Seconds', 'model-viewer'),
          'desc'     => __('Use Negative Number for Reverse Action. "30" for Default Behaviour.', 'model-viewer'),
          'min'         => 0,
          'max'         => 180,
          'default' => 30,
          'dependency' => array('bp_3d_rotate', '==', true),
          'class'    => 'bp3d-readonly'
        ),
        array(
          'id'       => '3d_rotate_delay',
          'type'     => 'number',
          'title'    => __('Auto Rotation Delay', 'model-viewer'),
          'subtitle' => __('After a period of time auto rotation will start', 'model-viewer'),
          'desc'     => __('Sets the delay before auto-rotation begins. The format of the value is a number in milliseconds.(1000ms = 1s)', 'model-viewer'),
          'default' => 3000,
          'dependency' => array('bp_3d_rotate', '==', true),
          'class'    => 'bp3d-readonly'
        ),
        array(
          'id'       => 'bp_3d_autoplay',
          'type'     => 'switcher',
          'title'    => __('Autoplay', 'model-viewer'),
          'subtitle' => __('Enable or Disable AutoPlay', 'model-viewer'),
          'desc'     => __('Autoplay Feature is for Autoplay Supported Model.', 'model-viewer'),
          'text_on'  => __('Yes', 'model-viewer'),
          'text_off' => __('No', 'model-viewer'),
          'default'  => false,
          'class'    => 'bp3d-readonly'
        ),
        array(
          'id'       => 'bp_3d_fullscreen',
          'type'     => 'switcher',
          'title'    => __('Fullscreen', 'model-viewer'),
          'subtitle' => __('Enable or Disable Fullscreen Mode', 'model-viewer'),
          'desc'     => __('Default: "Yes / Enable"', 'model-viewer'),
          'text_on'  => __('Yes', 'model-viewer'),
          'text_off' => __('No', 'model-viewer'),
          'default'  => true,
          'class'    => 'bp3d-readonly'
        ),
      ) // End fields
    ));
  }

  public function woocommerce_selectors()
  {
    \CSF::createSection($this->prefix, array(
      'title'  => __('Woocommerce Selectors', 'model-viewer'),
      'fields' => array(
        // 3D Model Options
        array(
          'id'       => 'gallery',
          'type'      => 'text',
          'title' => __('Gallery Selector', 'model-viewer'),
          'desc' => __('Write here the gallery selector', 'model-viewer'),
          'placeholder' => '.woocommerce-product-gallery',
        ),
        array(
          'id'       => 'gallery_item',
          'type'      => 'text',
          'title' => __('Gallery Item Selector', 'model-viewer'),
          'desc' => __('Write here the gallery item selector', 'model-viewer'),
          'placeholder' => '.woocommerce-product-gallery__image',
        ),
        array(
          'id'       => 'gallery_item_active',
          'type'      => 'text',
          'title' => __('Gallery Item Active Selector', 'model-viewer'),
          'desc' => __('Write here the gallery item active selector', 'model-viewer'),
          'placeholder' => '.woocommerce-product-gallery__image.flex-active-slide',
        ),
        array(
          'id' => 'gallery_thumbnail_item',
          'type' => 'text',
          'title' => __('Gallery Thumbnail Item Selector', 'model-viewer'),
          'desc' => __('Write here the gallery thumbnail item selector', 'model-viewer'),
          'placeholder' => '.flex-control-thumbs li',
        ),
        array(
          'id' => 'gallery_trigger',
          'type' => 'text',
          'title' => __('Gallery Trigger Selector', 'model-viewer'),
          'desc' => __('Write here the gallery trigger selector', 'model-viewer'),
          'placeholder' => '.woocommerce-product-gallery__trigger',
        ),
        array(
          'id' => 'custom_css',
          'type' => 'code_editor',
          'title' => __('Custom CSS', 'model-viewer'),
          'desc' => __('Write here the custom css code', 'model-viewer'),
          'settings' => array(
            'mode' => 'css',
            'theme' => 'monokai',
            'indentWithTabs' => true,
            'smartIndent' => true,
            'lineNumbers' => true
          ),
        )
      )
    ));
  }

  public function shortcode()
  {
    \CSF::createSection($this->prefix, array(
      'title'  => __('Shortcode Generator', 'model-viewer'),
      'fields' => array(
        // 3D Model Options
        array(
          'id'       => 'gutenberg_enabled',
          'type'      => 'switcher',
          'title'    => __('Enable Gutenberg', 'model-viewer'),
          'subtitle' => __('Enable / Disable Gutenberg Shortcode Generator.', 'model-viewer'),
          'default' => false,
        ),
      ) // End fields
    ));
  }
}
