<?php

namespace AnalyticsWP\Lib;

class Validators
{



    /**
     * This takes a mixed value and returns an array of arrays 
     * with all the $keys as keys and all the values as strings
     * 
     * And it will also provide PSALM type safety
     * 
     * Gotchas:
     *   - currently it only supports 4 keys
     *   - it also 'adds' a few throw away keys to the array ('foo', 'bar', 'baz') if those params aren't supplied
     *     but this doesn't really matter in practice
     * 
     * For example:
     *   wpdb_results([['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Jane']], ['id', 'name'])
     * 
     * Would return:
     *   [['id' => '1', 'name' => 'John'], ['id' => '2', 'name' => 'Jane']]
     * 
     * @template K1 of string
     * @template K2 of string
     * @template K3 of string
     * @template K4 of string
     * @template K5 of string
     * @template K6 of string
     * @template K7 of string
     * @template K8 of string
     * 
     * @param mixed $val
     * @param K1 $key1
     * @param K2 $key2
     * @param K3 $key3
     * @param K4 $key4
     * @param K5 $key5
     * @param K6 $key6
     * @param K7 $key7
     * @param K8 $key8
     * 
     * 
     * @psalm-return array<array<K1|K2|K3|K4|K5|K6|K7|K8, string>>
     * 
     */
    public static function wpdb_results($val, $key1, $key2 = 'foo', $key3 = 'bar', $key4 = 'baz', $key5 = 'qux', $key6 = 'quux', $key7 = 'quuz', $key8 = 'xyzzy')
    {
        $array_val = self::arr($val);
        $keys = [$key1, $key2, $key3, $key4, $key5, $key6, $key7, $key8];

        $array_with_all_string_keys = array_map(
            function ($row) use ($keys) {
                $array_row = self::arr($row);
                $new_row = [];
                foreach ($keys as $key) {
                    $new_row[$key] = isset($row[$key]) ? (string)$array_row[$key] : '';
                }
                return $new_row;
            },
            $array_val
        );

        return $array_with_all_string_keys;
    }




    /**
     * Validates that the $val is within the $array_of_vals otherwise returns the $default
     * 
     * @template Ta
     * @template Tb
     *
     * @param array<Ta> $array_of_vals
     * @param Tb $default
     * @param mixed $val
     * @return Ta|Tb
     */
    public static function one_of($array_of_vals, $default, $val)
    {
        if (in_array($val, $array_of_vals)) {
            /** @var Ta */
            return $val;
        } else {
            return $default;
        }
    }


    /**
     * @template T
     *
     * @psalm-param array<T|null> $array_of_maybe_null
     * @param array $array_of_maybe_null
     * 
     * @return array<T>
     */
    public static function non_null_array($array_of_maybe_null)
    {
        return array_filter(
            $array_of_maybe_null,
            /** @param mixed $val */
            function ($val) {
                return !is_null($val);
            }
        );
    }

    /**
     * @param mixed $val
     * 
     * @return array
     */
    public static function arr($val)
    {
        if (is_array($val)) {
            return $val;
        } else {
            return (array)$val;
        }
    }

    /**
     * @param mixed $val
     * 
     * @return array<int>
     */
    public static function array_of_int($val)
    {
        $val = Validators::arr($val);

        $val = array_filter(array_map('intval', $val));

        return $val;
    }

    /**
     * @param mixed $val
     * @param string|null $default
     * 
     * @return string
     */
    public static function str($val, $default = null)
    {
        if (is_string($val)) {
            return $val;
        } elseif (isset($default)) {
            return $default;
        } elseif (is_null($val) || is_object($val) || is_scalar($val)) {
            return strval($val);
        } else {
            return '';
        }
    }

    /**
     * @param mixed $val
     * 
     * @return int
     */
    public static function int($val)
    {
        if (is_numeric($val)) {
            return intval($val);
        } else {
            return 0;
        }
    }

    /**
     * @param array $val
     * @param string $key
     *
     * @return string
     */
    public static function str_from_array($val, $key)
    {
        if (isset($val[$key])) {
            return self::str($val[$key]);
        } else {
            return '';
        }
    }

    /**
     * @param array $val
     * @param string $key
     *
     * @return int
     */
    public static function int_from_array($val, $key)
    {
        if (isset($val[$key])) {
            return intval($val[$key]);
        } else {
            return 0;
        }
    }

    /**
     * @param mixed $val
     * @param non-empty-string $default
     *
     * @return non-empty-string
     */
    public static function non_empty_str($val, $default)
    {
        $str = self::str($val);

        if (empty($str)) {
            return $default;
        } else {
            return $str;
        }
    }

    /**
     * @param mixed $val
     * @param numeric-string|null $default
     *
     * @return numeric-string|''
     */
    public static function numeric_str($val, $default = null)
    {
        $str = self::str($val);

        if (is_numeric($str)) {
            return $str;
        } elseif (isset($default)) {
            return $default;
        } else {
            return '';
        }
    }

    /**
     * @param mixed $val
     * 
     * @return array<array-key, string>
     */
    public static function array_of_string($val)
    {
        $val = Validators::arr($val);

        $val = array_filter($val, 'is_string');

        return $val;
    }

    /**
     * @param mixed $val
     * 
     * @return array<string, string>
     */
    public static function array_of_string_keys_and_values($val)
    {
        // Validate that $val is an array
        $val = Validators::array_of_string($val);

        // Filter array to ensure all keys and values are strings
        $filteredArray = [];
        foreach ($val as $key => $value) {
            if (is_string($key)) {
                $filteredArray[$key] = $value;
            }
        }

        return $filteredArray;
    }


    /**
     * @param array $val
     * @param string $key
     *
     * @return boolean
     */
    public static function bool_from_array($val, $key)
    # TODO:1: Anything else we should be casting to true or false?
    {
        if (isset($val[$key])) {
            /** @var mixed $value */
            $value = $val[$key];

            if ($value === 'false') {
                return false;
            } else {
                return boolval($value);
            }
        } else {
            return false;
        }
    }

    /**
     * @param mixed $val
     *
     * @return array<string>
     */
    public static function array_of_coerced_string($val)
    {
        $val = Validators::arr($val);

        $val = array_map([self::class, 'str'], $val);

        return $val;
    }




    /**
     * @param mixed $val
     * 
     * @return int
     * @psalm-return positive-int
     */
    public static function positive_int($val)
    {
        $int = intval($val);
        return ($int > 0) ? $int : 1;
    }

    /**
     * @param float $val
     * 
     * @return float
     */
    public static function currency_amount_float($val)
    {
        return round($val, 2);
    }

    /**
     * Will return a proper string for a currency value. PayPal Payouts requires this.
     * examples
     *   Validators::currency_amount_string(1.0) => '1.00' # notice the 2 decimals in the string
     *   Validators::currency_amount_string(2.3123) => '2.30'
     *   Validators::currency_amount_string(2.3) => '2.30'
     * 
     * @param float $val
     * 
     * @return string
     */
    public static function currency_amount_string($val)
    {
        $rounded_float = round($val, 2);
        return number_format($rounded_float, 2);
    }

    /**
     * Validates into a proper string value for a WordPress Post title
     * @param mixed $val
     * 
     * @return string
     */
    public static function post_name($val)
    {
        $val = sanitize_title_with_dashes(trim(self::str($val)));

        return $val;
    }


    /**
     * @param mixed $val
     * 
     * @return \WP_Post[]
     */
    public static function arr_of_wp_post($val)
    {
        $val = self::arr($val);

        return array_filter($val, function ($val) {
            return $val instanceof \WP_Post;
        });
    }

    /**
     * @param mixed $val
     * 
     * @return \WP_User[]
     */
    public static function arr_of_wp_user($val)
    {
        $val = self::arr($val);

        return array_filter($val, function ($val) {
            return $val instanceof \WP_User;
        });
    }
}
