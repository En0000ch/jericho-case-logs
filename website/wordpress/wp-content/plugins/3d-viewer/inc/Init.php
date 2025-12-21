<?php

namespace BP3D;

if (!defined('ABSPATH')) {
    exit;
} //Exit if accessed directly

class Init
{
    private static $instance = null;
    private function __construct()
    {
        add_action('init', [$this, 'i18n']);
        add_action('woocommerce_after_register_post_type', [$this, 'load_woocommerce_files']);
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function i18n()
    {
        load_plugin_textdomain('model-viewer', false, dirname(plugin_basename(BP3D__FILE__)) . '/languages/');
    }

    public static function get_services()
    {
        return [
            Base\Activate::class,
            Base\EnqueueAssets::class,
            Base\MenuOrder::class,
            Base\Import::class,
            Base\ChoosePreferredEditor::class,
            Shortcode\Shortcode::class,
            Base\ExtendMimeType::class,
            Field\Viewer::class,
            Field\Settings::class,
            Woocommerce\SingleProduct::class,
            Woocommerce\ProductsPro::class,
            Helper\Utils::class,
            Helper\Block::class,
            Addons\Controls\Controls::class,
            Addons\AddonsPro::class,
            Addons\Blocks::class,
            Template\ModelViewer::class,
        ];
    }

    public static function get_woocommerce_services()
    {
        return [
            Woocommerce\ProductMeta::class,
        ];
    }

    public static function register_post_type()
    {
        self::instantiate('BP3D\Base\PostTypeModelViewer')->register();
        if (\bp3dv_fs()->can_use_premium_code()) {
            self::instantiate('BP3D\Base\PostTypePreset')->register();
        }
    }

    public static function init()
    {
        foreach (self::get_services() as $class) {
            if ($class = self::require_file($class)) {
                $services = self::instantiate($class);
                if (method_exists($services, 'register')) {
                    $services->register();
                }
            }
        }
    }

    public function load_woocommerce_files()
    {
        foreach (self::get_woocommerce_services() as $class) {
            if ($class = self::require_file($class)) {
                $services = self::instantiate($class);
                if (method_exists($services, 'register')) {
                    $services->register();
                }
            }
        }
    }

    public static function require_file($class)
    {
        $file = str_replace('\\', '/', $class);

        if (file_exists(BP3D_PATH . str_replace('BP3D', 'inc', $file . "Pro") . '.php') && \bp3dv_fs()->is__premium_only() && \bp3dv_fs()->can_use_premium_code()) {
            $file = BP3D_PATH . str_replace('BP3D', 'inc', $file . "Pro") . '.php';;
            $class = $class . "Pro";
        } else {
            $file = BP3D_PATH . str_replace('BP3D', 'inc', $file) . '.php';
        }

        if (file_exists($file)) {
            require_once($file);
            return $class;
        }
        return false;
    }



    private static function instantiate($class)
    {
        if (class_exists($class)) {
            return new $class();
        }

        return new \stdClass();
    }
}
