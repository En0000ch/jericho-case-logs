<?php

namespace Breakdance\Admin;

use function Breakdance\BreakdanceOxygen\Strings\__bdox;

add_filter('display_post_states',
    /**
     * @param array $states
     * @param \WP_Post $post
     */
    function ($states, $post) {
        $hasPermissions = \Breakdance\Permissions\hasMinimumPermission('edit');

        if ($hasPermissions && \Breakdance\Data\get_tree($post->ID) !== false) {
            $states['breakdance'] = __bdox('plugin_name');
        }

        return $states;
    },
    10,
    2
);
