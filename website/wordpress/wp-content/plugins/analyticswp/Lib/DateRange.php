<?php

namespace AnalyticsWP\Lib;

/**
 * @psalm-type DateRangeValue = 'Last 30 days'|'Last 7 days'|'All time'|'Realtime'|'Custom'
 */
class DateRange
{
    const DATE_RANGE_VALUES = [
        'Last 30 days',
        'Last 7 days',
        'All time',
        'Realtime',
        'Custom'
    ];

    /**
     * @var DateRangeValue
     */
    public $range;

    /**
     * @var string|null
     */
    public $start_date;

    /**
     * @var string|null
     */
    public $end_date;

    /**
     * @param DateRangeValue $range
     * @param string|null $start_date
     * @param string|null $end_date
     */
    public function __construct($range, $start_date = null, $end_date = null)
    {
        $this->range = self::validate($range);
        if ($this->range === 'Custom') {
            if ($start_date === null || $end_date === null) {
                throw new \InvalidArgumentException("Custom range requires both start_date and end_date");
            }
            $this->start_date = $start_date;
            $this->end_date = $end_date;
        }
    }

    /**
     * @param mixed $val
     * @return DateRangeValue
     */
    public static function validate($val)
    {
        return Validators::one_of(self::DATE_RANGE_VALUES, 'Last 30 days', $val);
    }

    // /**
    //  * @return string
    //  */
    // public function toSql()
    // {
    //     switch ($this->range) {
    //         case 'Last 30 days':
    //             return 'AND timestamp >= UTC_TIMESTAMP() - INTERVAL 30 DAY';
    //         case 'Last 7 days':
    //             return 'AND timestamp >= UTC_TIMESTAMP() - INTERVAL 7 DAY';
    //         case 'All time':
    //             return '';
    //         case 'Realtime':
    //             return 'AND timestamp >= UTC_TIMESTAMP() - INTERVAL 120 SECOND';
    //         case 'Custom':
    //             return "AND timestamp BETWEEN '{$this->start_date}' AND '{$this->end_date}'";
    //     }
    // }

    /**
     * @return string
     */
    public function toSql()
    {
        switch ($this->range) {
            case 'Last 30 days':
                return 'AND timestamp >= UTC_TIMESTAMP() - INTERVAL 30 DAY';
            case 'Last 7 days':
                return 'AND timestamp >= UTC_TIMESTAMP() - INTERVAL 7 DAY';
            case 'All time':
                return '';
            case 'Realtime':
                return 'AND timestamp >= UTC_TIMESTAMP() - INTERVAL 120 SECOND';
            case 'Custom':
                if ($this->start_date === null || $this->end_date === null) {
                    throw new \InvalidArgumentException("Custom range requires both start_date and end_date");
                }
                $start_utc = $this->dateToUtc($this->start_date);
                $end_utc = $this->dateToUtc($this->end_date, true);
                return "AND timestamp >= '{$start_utc}' AND timestamp < '{$end_utc}'";
        }
    }

    /**
     * Convert a date string to UTC timestamp
     * 
     * @param string $date Date in 'Y-m-d' format
     * @param bool $isEndDate Whether this is the end date of the range
     * @return string UTC timestamp in 'Y-m-d H:i:s' format
     */
    private function dateToUtc($date, $isEndDate = false)
    {
        // Create a DateTime object with the given date in UTC
        $dateTime = new \DateTime($date, new \DateTimeZone('UTC'));

        // If it's the end date, set it to the end of the day
        if ($isEndDate) {
            $dateTime->modify('+1 day');
        }

        // Return the formatted UTC timestamp
        return $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public function toSqlForComparison()
    {
        switch ($this->range) {
            case 'Last 30 days':
                return 'AND timestamp >= UTC_TIMESTAMP() - INTERVAL 60 DAY AND timestamp < UTC_TIMESTAMP() - INTERVAL 30 DAY';
            case 'Last 7 days':
                return 'AND timestamp >= UTC_TIMESTAMP() - INTERVAL 14 DAY AND timestamp < UTC_TIMESTAMP() - INTERVAL 7 DAY';
            case 'All time':
                return '';
            case 'Realtime':
                return 'AND timestamp >= UTC_TIMESTAMP() - INTERVAL 240 SECOND AND timestamp < UTC_TIMESTAMP() - INTERVAL 120 SECOND';
            case 'Custom':
                if ($this->start_date === null || $this->end_date === null) {
                    throw new \LogicException("Custom range start_date and end_date should not be null");
                }
                $start = date('Y-m-d', strtotime($this->start_date . ' -' . $this->getDaysDifference() . ' days'));
                $end = date('Y-m-d', strtotime($this->end_date . ' -' . $this->getDaysDifference() . ' days'));
                return "AND timestamp BETWEEN '{$start}' AND '{$end}'";
        }
    }

    /**
     * @return string[]
     */
    public function toArrayOfDateString()
    {
        if ($this->range === 'Custom') {
            if ($this->start_date === null || $this->end_date === null) {
                throw new \LogicException("Custom range start_date and end_date should not be null");
            }
            $start = new \DateTime($this->start_date);
            $end = new \DateTime($this->end_date);
        } else {
            $start = new \DateTime();
            $end = clone $start;
        }

        switch ($this->range) {
            case 'Last 30 days':
                $start->modify('-29 days');
                break;
            case 'Last 7 days':
                $start->modify('-6 days');
                break;
            case 'All time':
                $start->modify('-364 days');
                break;
            case 'Realtime':
                // No modification needed
                break;
        }

        $output = [];
        $current = clone $start;
        while ($current <= $end) {
            $output[] = $current->format('Y-m-d');
            $current->modify('+1 day');
        }

        return $output;
    }
    /**
     * @return int
     */
    private function getDaysDifference()
    {
        if ($this->range !== 'Custom' || $this->start_date === null || $this->end_date === null) {
            throw new \LogicException("getDaysDifference() should only be called for Custom range with valid dates");
        }
        $start = new \DateTime($this->start_date);
        $end = new \DateTime($this->end_date);
        return $end->diff($start)->days;
    }


    /**
     * Creates a DateRange object from $_POST data
     * 
     * @param array $post_data The $_POST array
     * @return self|string Returns DateRange object on success, error message string on failure
     */
    public static function fromPOST(array $post_data)
    {
        if (!isset($post_data['date_range']) || !is_string($post_data['date_range']) || empty($post_data['date_range'])) {
            return 'Invalid or missing date_range in POST data';
        }

        $date_range = self::validate($post_data['date_range']);

        if ($date_range === 'Custom') {
            if (
                !isset($post_data['start_date']) || !is_string($post_data['start_date']) || empty($post_data['start_date']) ||
                !isset($post_data['end_date']) || !is_string($post_data['end_date']) || empty($post_data['end_date'])
            ) {
                return 'Invalid or missing start_date or end_date for Custom range';
            }

            // You might want to add additional validation for date format here
            // For example:
            if (!self::isValidDate($post_data['start_date']) || !self::isValidDate($post_data['end_date'])) {
                return 'Invalid date format for start_date or end_date. Use YYYY-MM-DD format.';
            }

            return new self($date_range, $post_data['start_date'], $post_data['end_date']);
        }

        return new self($date_range);
    }

    /**
     * Validates if a string is a valid date in YYYY-MM-DD format
     * 
     * @param string $date
     * @return bool
     */
    private static function isValidDate($date)
    {
        $format = 'Y-m-d';
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Returns a human-readable date range string in m/d/Y format
     * 
     * Examples:
     * - "12/24/2024 - 1/16/2025" for Custom range
     * - "12/13/2024 - 1/12/2025" for Last 30 days
     * - "1/5/2025 - 1/12/2025" for Last 7 days
     * - "1/12/2024 - 1/12/2025" for All time
     * - "1/12/2025" for Realtime (just shows current date)
     * 
     * @param 'us'|'eu'|null $format
     * @return string
     */
    public function human_readable_range($format = 'us')
    {
        // Set date format once based on region parameter
        $dateFormat = $format === 'us' ? 'n/j/Y' : 'j/n/Y';

        if ($this->range === 'Custom') {
            if ($this->start_date === null || $this->end_date === null) {
                throw new \LogicException("Custom range start_date and end_date should not be null");
            }
            $start = new \DateTime($this->start_date);
            $end = new \DateTime($this->end_date);
        } else {
            $end = new \DateTime();
            $start = clone $end;
            switch ($this->range) {
                case 'Last 30 days':
                    $start->modify('-29 days'); // 30 days including today
                    break;
                case 'Last 7 days':
                    $start->modify('-6 days'); // 7 days including today
                    break;
                case 'All time':
                    $start->modify('-364 days'); // Last 365 days
                    break;
                case 'Realtime':
                    // Just return today's date with appropriate format
                    return $end->format($dateFormat);
            }
        }

        return sprintf(
            '%s - %s',
            $start->format($dateFormat),
            $end->format($dateFormat)
        );
    }
}
