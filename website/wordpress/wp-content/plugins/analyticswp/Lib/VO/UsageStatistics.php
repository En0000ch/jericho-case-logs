<?php

namespace AnalyticsWP\Lib\VO;

use AnalyticsWP\Lib\License;
use AnalyticsWP\Lib\Validators;
use AnalyticsWP\Lib\WooSoftwareLicense\WOO_SLT_Licence;


/** 
 * 
 * @psalm-type UsageStatisticsType = array{
 *  domain: string,
 *  license_key: string,
 *  php_version: string,
 *  wp_version: string,
 *  woocommerce_version: string,
 *  analyticswp_version: string,
 *  is_on_keyless_free_trial: string,
 *  keyless_free_trial_ends_at: string,
 *  keyless_id: string,
 * } 
 * 
 */
class UsageStatistics
{
    /** @var UsageStatisticsType $data */
    public $data;

    public string $domain;

    public string $license_key;

    public string $php_version;

    public string $wp_version;

    public string $woocommerce_version;

    public string $analyticswp_version;

    public string $is_on_keyless_free_trial;

    public string $keyless_free_trial_ends_at;

    public string $keyless_id;

    /** @param UsageStatisticsType $data */
    public function __construct($data)
    {
        $this->data = $data;
        $this->domain = $data['domain'];
        $this->license_key = $data['license_key'];
        $this->php_version = $data['php_version'];
        $this->wp_version = $data['wp_version'];
        $this->woocommerce_version = $data['woocommerce_version'];
        // $this->woocommerce_subscriptions_version = $data['woocommerce_subscriptions_version'];
        $this->analyticswp_version = $data['analyticswp_version'];

        // Keyless
        $this->is_on_keyless_free_trial = $data['is_on_keyless_free_trial'];
        $this->keyless_free_trial_ends_at = $data['keyless_free_trial_ends_at'];
        $this->keyless_id = $data['keyless_id'];
    }

    /**
     * Creates an instance of UsageStatistics from the current WordPress environment.
     *
     * @return UsageStatistics
     */
    public static function create_for_this_environment()
    {
        try {
            return self::_create_for_this_environment();
        } catch (\Error $e) {
            return self::empty_usage_statistics();
        } catch (\Exception $e) {
            return self::empty_usage_statistics();
        }
    }

    /**
     * Creates an instance of UsageStatistics from the current WordPress environment.
     *
     * @return UsageStatistics
     */
    private static function _create_for_this_environment()
    {
        $license_data = WOO_SLT_Licence::get_license_data();
        if ($license_data === false) {
            $license_key = '';
        } else {
            $license_key = $license_data['key'];
        }

        // woocommerce_version
        if (class_exists('WooCommerce')) {
            $woocommerce_version = Validators::str(\WC()->version);
        } else {
            $woocommerce_version = '-';
        }

        return new UsageStatistics([
            'domain' => ANALYTICSWP_WOO_SLT_INSTANCE,
            'license_key' => $license_key,
            'php_version' => (string)phpversion(),
            'wp_version' => get_bloginfo('version'),
            'woocommerce_version' => $woocommerce_version,
            'analyticswp_version' => ANALYTICSWP_WOO_SLT_VERSION,

            // Keyless
            'is_on_keyless_free_trial' => (string)License::is_on_keyless_free_trial(),
            'keyless_free_trial_ends_at' => (string)License::get_keyless_free_trial_end_timestamp(),
            'keyless_id' => License::get_keyless_id(),
        ]);
    }

    /**
     * Creates an instance of UsageStatistics with all values empty.
     *
     * @return UsageStatistics
     */
    private static function empty_usage_statistics()
    {
        return new UsageStatistics([
            'domain' => '-',
            'license_key' => '-',
            'php_version' => '-',
            'wp_version' => '-',
            'woocommerce_version' => '-',
            'analyticswp_version' => '-',
            'is_on_keyless_free_trial' => '-',
            'keyless_free_trial_ends_at' => '-',
            'keyless_id' => '-',
        ]);
    }
}
