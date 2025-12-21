<?php

namespace Breakdance\Extensions;

use Breakdance\Licensing\EDD_SL_Plugin_Updater;
use Breakdance\Licensing\EddApi;
use function Breakdance\Data\get_global_option;
use function Breakdance\Data\set_global_option;
use function Breakdance\Licensing\get_option_receive_beta_updates;

class ExtensionsController {
    public string $name;
    public string $slug;
    public string $path;
    /**
     * @var array{Name: String, Version: String} $pluginData
     */
    public $pluginData;
    public int $eddItemId;

    /**
     * @var ExtensionsController[]
     */
    static $extensions = [];

    public function __construct(
        string $slug,
        string $path,
        int $eddItemId
    ) {
        $this->slug = $slug;
        $this->path = $path;

        // TODO: Calling get_plugin_data() may be expensive. Consider caching this data.
        /** @var array{Name: String, Version: String} $pluginData */
        $pluginData = get_plugin_data($this->path);
        $this->pluginData = $pluginData;

        $this->name = $this->pluginData['Name'];
        $this->eddItemId = $eddItemId;

        self::$extensions[] = $this;
    }

    /**
     * @return ExtensionsController[]
     */
    public static function getExtensions()
    {
        return self::$extensions;
    }

    /**
     * @param string $license_key
     */
    function setLicenseKey($license_key)
    {
        /** @var array<string, string> $allKeys */
        $allKeys = get_global_option('extensions_license_keys');

        if (!$allKeys) $allKeys = [];

        $allKeys[$this->slug] = $license_key;
        set_global_option('extensions_license_keys', $allKeys);

        $this->setLicenseKeyStatus(null);
        $this->activateLicenseKey($license_key);
    }

    /**
     * @return string
     */
    function getLicenseKey()
    {
        /** @var array<string, string> $allKeys */
        $allKeys = get_global_option('extensions_license_keys');
        return $allKeys ? $allKeys[$this->slug] ?? '' : '';
    }

    /**
     * @return string
     */
    function getMaskedLicenseKey()
    {
        $key = $this->getLicenseKey();
        return str_repeat('*', strlen($key));
    }

    /**
     * @return string
     */
    function getLicenseStatus()
    {
        /** @var array<string, string> $allKeys */
        $allKeys = get_global_option('extensions_license_statuses');
        return $allKeys ? $allKeys[$this->slug] ?? '' : '';
    }

    /**
     * @param string|null $license_key_status
     */
    function setLicenseKeyStatus($license_key_status)
    {
        /** @var array<string, string> $allStatuses */
        $allStatuses = get_global_option('extensions_license_statuses');

        if (!$allStatuses) {
            $allStatuses = [];
        }

        if ($license_key_status) {
            $allStatuses[$this->slug] = $license_key_status;
        } else {
            unset($allStatuses[$this->slug]);
        }

        set_global_option('extensions_license_statuses', $allStatuses);
    }

    function fetchLicenseKeyStatus()
    {
        $eddApi = new \Breakdance\Licensing\EddApi(get_site_url());
        $licenseKey = $this->getLicenseKey();

        if (!$licenseKey) {
            $this->setLicenseKeyStatus(null);
            return;
        }

        $licenseData = $eddApi->fetchLicenseInfo(
            $licenseKey,
            $this->eddItemId
        );

        if (!$licenseData) {
            $this->setLicenseKeyStatus('invalid');
        } else {
            $this->setLicenseKeyStatus($licenseData['license']);
        }
    }

    /**
     * @param string $license_key
     */
    function activateLicenseKey($license_key)
    {
        $licenseKeyStatus = $this->getLicenseStatus();

        if ($licenseKeyStatus === 'valid') return;

        $eddApi = new \Breakdance\Licensing\EddApi(get_site_url());

        $response = $eddApi->activateLicense(
            $license_key,
            $this->eddItemId
        );

        if (isset($response) && isset($response['success']) && $response['success']) {
            $this->afterActivation();
        }

        $this->fetchLicenseKeyStatus();
    }

    function afterActivation()
    {
        // TODO: Implement tracking code
        // $domain = str_replace('www.', '', parse_url(get_site_url(), PHP_URL_HOST));

        //\Breakdance\Licensing\Events\log(
        //    BREAKDANCE_AI_OWAUID,
        //    $this->name . ' Activated',
        //    [
        //        'domain' => $domain
        //    ]
        //);
    }

    public function listenForPluginUpdates()
    {
        if (!$this->getLicenseKey()) {
            return;
        }

        /** @psalm-suppress UndefinedClass */
        (new EDD_SL_Plugin_Updater(
            EddApi::get_edd_store_url(),
            $this->path,
            [
                'version' => $this->pluginData['Version'],
                'license' => $this->getLicenseKey(),
                'item_id' => $this->eddItemId,
                'author' => 'Soflyy',
                'beta' => get_option_receive_beta_updates(),
            ]
        ));
    }
}

/**
 * Add an  to the admin dashboard
 * @param string $slug
 * @param string $path
 * @param int $eddItemId
 * @return ExtensionsController
 */
function registerExtension(string $slug, string $path, int $eddItemId)
{
    return new ExtensionsController($slug, $path, $eddItemId);
}
