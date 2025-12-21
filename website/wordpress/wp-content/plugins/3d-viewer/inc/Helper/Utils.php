<?php

namespace BP3D\Helper;

class Utils
{

    public static $theme_name = null;

    function __construct()
    {
        self::$theme_name = wp_get_theme()->name;
    }

    public static function isset($array, $key, $default = false)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        return $default;
    }

    public static function isset2($array, $key1, $key2, $default = false)
    {
        if (isset($array[$key1][$key2])) {
            return $array[$key1][$key2];
        }
        return $default;
    }

    /**
     * convert hex to rgb color
     */
    public static function hexToRGB($hex, $alpha = false)
    {
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
        if ($alpha) {
            $rgb['a'] = $alpha;
        }
        return $rgb;
    }

    /**
     * @param string $theme
     * @return string css selector
     */
    public static function getCustomSelector($theme)
    {

        $theme = str_replace(' Child', '', $theme);

        // $common_themes = ['Twenty Twenty-Four', 'Astra', 'Storely', 'OceanWP', 'Woodmart', 'Rafdt'];

        // if(in_array($theme, $common_themes)){
        //     return '.woocommerce-product-gallery';
        // }

        $selectors = [
            'Woostify' => '.product-gallery'
        ];

        return $selectors[$theme] ?? '.woocommerce-product-gallery';
    }


    /**
     * @param string $string
     * @return string css class
     */
    static function getThemeClass($string = null)
    {
        if (!$string) {
            $string = wp_get_theme()->name;
        }
        // Replace spaces with underscores
        $string = str_replace(' ', '_', $string);
        // Convert the string to lowercase
        $string = strtolower($string);
        return $string;
    }

    static function getNotCompatibleThemes()
    {

        $settings = get_option('_bp3d_settings_');

        $is_not_compatible = $settings['is_not_compatible'] ?? false;
        $themes = ['Twenty Twenty-Four', 'Twenty Twenty Three', 'Woostify', 'Raft', 'eStore', 'Customify', 'B Technologies'];

        if ($is_not_compatible) {
            return wp_parse_args([wp_get_theme()->name], $themes);
        }

        return $themes;
    }

    static function isCompatibleTheme()
    {
        if (in_array(wp_get_theme()->name, self::getNotCompatibleThemes()) || (function_exists('wp_is_block_theme') && wp_is_block_theme())) {
            return false;
        }
        return true;
    }

    static function getPostMeta($id, $key)
    {
        $meta = get_post_meta($id, $key, true);
        return function ($key, $default = null, $is_boolean = false, $key2 = null) use ($meta) {
            if ($key === 'all') {
                return $meta;
            }
            if ($key2) {
                $value = isset($meta[$key][$key2]) ? $meta[$key][$key2] : $default;
            } else {
                $value = isset($meta[$key]) ? $meta[$key] : $default;
            }
            if ($is_boolean) {
                return $value == '1';
            }
            return $value;
        };
    }

    public static function return_function($meta)
    {
        return function ($key, $default = null, $is_boolean = false, $key2 = null) use ($meta) {
            if ($key === 'all') {
                return $meta;
            }
            if ($key2) {
                $value = isset($meta[$key][$key2]) ? $meta[$key][$key2] : $default;
            } else {
                $value = isset($meta[$key]) ? $meta[$key] : $default;
            }

            // if (!$value) {
            //     $value = $default;
            // }
            if ($is_boolean) {
                return $value == '1';
            }
            return $value;
        };
    }

    static function getSettings($key, $default = null)
    {
        $settings = get_option($key, $default);
        return self::return_function($settings);
    }
}
