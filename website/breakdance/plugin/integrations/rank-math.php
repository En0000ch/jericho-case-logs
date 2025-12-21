<?php

namespace Breakdance\Admin\Seo;

use function Breakdance\Admin\get_mode;
use function Breakdance\Data\get_tree_as_html;

add_action('admin_enqueue_scripts', 'Breakdance\Admin\Seo\enqueue_rank_math_compat', 11);

function enqueue_rank_math_compat()
{
    global $pagenow;
    global $post;

    /** @var \WP_Post */
    $post = $post;

    if ($pagenow !== 'post.php') {
        return;
    }

    if (!class_exists('RankMath')) {
        return;
    }

    /** @psalm-suppress MixedPropertyFetch */
    $content = get_mode($post->ID) === 'breakdance' ? get_tree_as_html((int) $post->ID) : (string) $post->post_content;

    wp_enqueue_script(
        'breakdance-rank-math-analysis',
        plugin_dir_url(__FILE__) . 'seo/rank-math-compatibility.js',
        ['wp-hooks', 'rank-math-analyzer'],
        false,
        true
    );

    wp_localize_script(
        'breakdance-rank-math-analysis',
        'breakdance',
        compact('content')
    );
}

add_filter(
    'rank_math/researches/toc_plugins',
    /**
     * @param array<string, string> $toc_plugins
     * @return array<string, string>
     */
    function($toc_plugins ) {
        /**
         * @psalm-suppress UndefinedConstant
         * @var string $pluginFile
         */
        $pluginFile = __BREAKDANCE_PLUGIN_FILE__;

        // Dynamically determine the relative path within the plugins directory
        if (defined('WP_PLUGIN_DIR')) {
            $relativePath = str_replace(WP_PLUGIN_DIR . '/', '', $pluginFile);
        } else {
            // Fallback if WP_PLUGIN_DIR is not defined
            $relativePath = basename(dirname($pluginFile)) . '/' . basename($pluginFile);
        }

        $toc_plugins[$relativePath] = 'Breakdance';
        return $toc_plugins;
    }
);
