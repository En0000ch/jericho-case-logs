<?php


namespace Breakdance\Subscription;

use Breakdance\Licensing\LicenseKeyManager;

class SubscriptionMode
{
    use \Breakdance\Singleton;

    /** @var "free"|"pro"  */
    public string $subscriptionMode = "free";

    function __construct()
    {
        if (BREAKDANCE_MODE === 'oxygen') {
            $this->subscriptionMode = 'pro';
        } else {
            $this->subscriptionMode = LicenseKeyManager::getInstance()->getSubscriptionModeEligibleForStoredLicenseKey();
        }
    }
}
