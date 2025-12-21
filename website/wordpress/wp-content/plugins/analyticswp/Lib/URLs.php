<?php

namespace AnalyticsWP\Lib;

class URLs
{

    /**
     * Builds URLs for any Admin Page thats just a function of a page_key.
     * 
     * @param string $page_key
     * @param bool $return_just_path If true, will just return the path. Otherwise the full URL.
     * @param array<string, mixed> $query_args
     * 
     * @return string
     */
    public static function admin_path($page_key, $return_just_path = false, $query_args = [])
    {
        $path = "admin.php?page={$page_key}";
        if (!empty($query_args)) {
            $path = add_query_arg($query_args, $path);
        }
        return $return_just_path ? $path : get_admin_url(null, $path);
    }


    /** 
     * @param array<string,string> $conditions
     * 
     * @return string|null
     */
    public static function admin_journey_path_for_event_where_condition($conditions)
    {
        $event = Event::find_where($conditions);
        if ($event == null) {
            return null;
        }

        if ($event['id']) {
            return self::admin_path('event_id_journeys', false, ['event_id' => $event['id']]);
        } else {
            return null;
        }
    }
}
