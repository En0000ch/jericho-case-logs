<?php

namespace AnalyticsWP\Lib;

class Timezone
{
    public static function t(string $gmt_timestamp): string
    {
        return get_date_from_gmt($gmt_timestamp, 'Y-m-d H:i:s');
    }

    /**
     * Calculates the timezone offset relative to UTC based on the WordPress site settings.
     * 
     * This function returns the timezone offset in a format suitable for SQL queries (e.g., '+02:00', '-07:00').
     * It accounts for both a timezone string or a manual GMT offset set in the WordPress settings.
     * If the timezone string is available, it uses that to determine the offset, including daylight saving time if applicable.
     * If only a manual GMT offset is set, it attempts to find a suitable timezone with that offset.
     * 
     * It can be used in SQL queries like this:
     *   $tz_offset = Timezone::get_wp_timezone_offset();
     *           
     *   sql: 
     *     DATE(DATE_ADD(timestamp, INTERVAL $tz_offset HOUR_MINUTE))
     * 
     * @return string Timezone offset formatted for SQL usage (e.g., '+02:00', '-07:00').
     */
    public static function get_wp_timezone_offset(): string
    {
        $timezone_string = Validators::str(get_option('timezone_string'));
        $gmt_offset = Validators::str(get_option('gmt_offset'));

        if (!empty($timezone_string)) {
            $timezone = new \DateTimeZone($timezone_string);
        } else {
            // Attempt to find a timezone with the specified GMT offset that observes daylight saving time.
            $timezone = timezone_name_from_abbr('', (int)$gmt_offset * 3600, 1);
            if ($timezone === false) { // Fallback to any timezone with the specified GMT offset when DST is not found.
                $timezone = timezone_name_from_abbr('', (int)$gmt_offset * 3600, 0);
            }
            if ($timezone === false || empty($timezone)) { // Fallback to UTC when no timezone is found.
                $timezone = 'UTC';
            }
            $timezone = new \DateTimeZone($timezone);
        }

        // Create a DateTime object to find the offset for the current date/time in the specified timezone.
        $datetime = new \DateTime('now', $timezone);

        // Calculate the offset in seconds and convert to hours and minutes.
        $offset = $timezone->getOffset($datetime);
        $hours = intdiv($offset, 3600);
        $minutes = (abs($offset) / 60) % 60;

        // Format the offset for SQL use, ensuring it includes leading zeros and a sign.
        $formatted_offset = sprintf('%+03d:%02d', $hours, $minutes);

        return $formatted_offset;
    }
}
