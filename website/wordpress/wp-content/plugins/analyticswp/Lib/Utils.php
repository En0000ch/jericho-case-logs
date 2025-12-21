<?php

namespace AnalyticsWP\Lib;

class Utils
{

    /**
     * Caches the result of a callback function and returns cached result when available.
     * 
     * Example usage:
     * 
     *   $result = Utils::cache_function_result([__CLASS__, 'some_function'], [$arg1, $arg2]);
     *
     * @template T
     *
     * @param callable $callback      The callback function whose result is to be cached.
     * @param array    $args          The arguments to pass to the callback function.
     * @param int      $expiration    Time in seconds to keep the result cached.
     * @param string   $key_prefix    A prefix for the transient key to avoid key collisions.
     *
     * @psalm-param callable(mixed ...$args): T $callback
     * @psalm-return T           The result of the callback function. Null if the callback does not return a value.
     */
    public static function cache_function_result($callback, $args = array(), $expiration = 3600, $key_prefix = 'analyticswp_cache_')
    {
        // Generate a unique key based on the callback name and arguments
        $transient_key = $key_prefix . md5(serialize($callback) . serialize($args));

        // Try to get cached result
        /** @var T|false */
        $cached_result = get_transient($transient_key);
        if ($cached_result !== false) {
            return $cached_result; // Return cached data
        }

        // Call the function with the arguments and store the result
        $result = call_user_func_array($callback, $args);

        // Cache the result
        set_transient($transient_key, $result, $expiration);

        return $result;
    }



    public static function is_current_user_excluded_from_tracking(): bool
    {
        // Get the current user
        $current_user = wp_get_current_user();
        // if it is not a user, return false
        if ($current_user->ID == 0) {
            return false;
        }

        // Get the excluded user roles
        $excluded_user_roles = Validators::array_of_string_keys_and_values(SuperSimpleWP::get_setting('analyticswp', 'disabled-tracking-user-roles'));

        // Check if the current user has one of the excluded roles
        foreach ($current_user->roles as $role) {
            if (isset($excluded_user_roles[$role])) {
                return true;
            }
        }

        return false;
    }
}
