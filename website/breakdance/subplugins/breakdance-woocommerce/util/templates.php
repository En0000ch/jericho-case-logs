<?php

namespace Breakdance\WooCommerce;

/**
 * @param string $template
 * @param string $templateName
 * @param string $templatePath
 */
function debugTemplate($template, $templateName, $templatePath)
{
    echo "<b>Template (woocommerce_locate_template)</b>";

    \Breakdance\Debug\pre_print_r(
        [
            'template' => $template,
            'templateName' => $templateName,
            'templatePath' => $templatePath
        ]
    );

    echo "<br /><br />";
}

add_filter('woocommerce_template_overrides_scan_paths', function($paths) {
    $paths[] = ['breakdance' => BREAKDANCE_WOO_TEMPLATES_DIR];
    return $paths;
});

/*
 * wc_get_template_part handles caching
 * i don't think this code does though. it looks like it just calls load_template on the returned path
 * does that cache? i don't think so.
 * so we are calling load template for the same file 27 times in a lot of cases
 (shop loop, for example)
-*/

add_filter(
    "wc_get_template_part",
    /**
     * @param string $template
     * @param string $slug
     * @param string|null $name
     * @return string
     */
    function($template, $slug, $name) {
        global $wp_stylesheet_path, $wp_template_path;
        static $template_cache = [];
        $cache_key = $slug . ($name ? "-$name" : '');

        if (isset($template_cache[$cache_key])) {
            return $template_cache[$cache_key];
        }

        $is_child_theme = is_child_theme();

        if (str_starts_with($template, $wp_stylesheet_path)) {
            return $template;
        } elseif ($is_child_theme && str_starts_with($template, $wp_template_path)) {
            return $template;
        }

        $file = $name ? "{$slug}-{$name}.php" : "{$slug}.php";
        $path = BREAKDANCE_WOO_TEMPLATES_DIR . $file;

        if (file_exists($path)) {
            $template_cache[$cache_key] = $path;
        } else {
            $template_cache[$cache_key] = $template;
        }

        return $template_cache[$cache_key];
    },
    10, 3
);

// Get path for all other templates.
add_filter('wc_get_template', function($template, $templateName, $templatePath) {
    global $wp_stylesheet_path, $wp_template_path;
    static $template_cache = [];
    $cache_key = $templateName;

    if (isset($template_cache[$cache_key])) {
        return $template_cache[$cache_key];
    }

    $is_child_theme = is_child_theme();

    if (str_starts_with($template, $wp_stylesheet_path)) {
        return $template;
    } elseif ($is_child_theme && str_starts_with($template, $wp_template_path)) {
        return $template;
    }

    $path = BREAKDANCE_WOO_TEMPLATES_DIR . $templateName;

    if (file_exists($path)) {
        $template_cache[$cache_key] = $path;
    } else {
        $template_cache[$cache_key] = $template;
    }

    return $template_cache[$cache_key];
}, 10, 3);
