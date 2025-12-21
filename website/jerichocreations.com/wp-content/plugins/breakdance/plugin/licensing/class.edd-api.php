<?php

namespace Breakdance\Licensing;

use function Breakdance\Util\Http\http_get_json;

class EddApi
{
    protected string $site_url;

    public function __construct(string $site_url)
    {
        $this->site_url = $site_url;
    }

    /**
     * @return string
     */
    public static function get_edd_store_url() {
        if (BREAKDANCE_MODE === 'breakdance') {
            return 'https://breakdance.com';
        }
        if (BREAKDANCE_MODE === 'oxygen') {
            return 'https://oxygenbuilder.com';
        }
        return '';
    }

    /**
     * @param string $license_key
     * @param int $edd_item_id
     * @return null|EddLicenseInfo
     */
    public function fetchLicenseInfo(string $license_key, int $edd_item_id): ?array
    {
        /** @var EddLicenseInfo|false $response */
        $response = http_get_json(
            sprintf(
                '%s?edd_action=check_license&item_id=%s&license=%s&url=%s&avoid_cache=%s',
                self::get_edd_store_url(),
                $edd_item_id,
                $license_key,
                $this->site_url,
                uniqid()
            )
        );

        return is_array($response) ? $response : null;
    }

    public function activateLicense(string $license_key, int $edd_item_id): ?array
    {
        /** @var mixed|false $response */
        $response = http_get_json(
            sprintf(
                '%s?edd_action=activate_license&item_id=%s&license=%s&url=%s&avoid_cache=%s',
                self::get_edd_store_url(),
                $edd_item_id,
                $license_key,
                $this->site_url,
                uniqid()
            )
        );

        return is_array($response) ? $response : null;
    }

    public function deactivateLicense(string $license_key, int $edd_item_id): ?array
    {
        /** @var mixed|false $response */
        $response = http_get_json(sprintf(
            '%s?edd_action=deactivate_license&item_id=%s&license=%s&url=%s&avoid_cache=%s',
            self::get_edd_store_url(),
            $edd_item_id,
            $license_key,
            $this->site_url,
            uniqid()
        ));

        return is_array($response) ? $response : null;
    }
}
