<?php

if (!function_exists('oxygen_zero_theme_setup')) {
    function oxygen_zero_theme_setup()
    {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
    }
}

add_action('after_setup_theme', 'oxygen_zero_theme_setup');


if (!function_exists('warn_if_oxygen_is_disabled')) {
    add_action('admin_notices', 'warn_if_oxygen_is_disabled');

    function warn_if_oxygen_is_disabled()
    {
        if (defined('__BREAKDANCE_DIR__')) {
            return;
        }

        ?>
        <div class="notice notice-error is-dismissible">
            <p>You're using Oxygen's Zero Theme but Oxygen is not enabled. This isn't supported.</p>
        </div>
        <?php
    }
}