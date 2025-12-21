<?php

namespace Breakdance\BreakdanceOxygen\Strings;

/**
 * public API
 * @param string $key
 * @return string
 */
function __bdox($key)
{

    if (BREAKDANCE_MODE === 'breakdance') {
        return StringsHolder::getInstance()->breakdance_translations[$key];
    }

    if (BREAKDANCE_MODE === 'oxygen') {
        return StringsHolder::getInstance()->oxygen_translations[$key];
    }

    return '';
}

/**
 * @return array{oxygen:array<string,string>,breakdance:array<string,string>}
 */
function getBdoxTranslationsForBuilder()
{
    return [
        'oxygen' => StringsHolder::getInstance()->oxygen_translations,
        'breakdance' => StringsHolder::getInstance()->breakdance_translations,
    ];
}

/**
 * private
 * @return array<string,string>
 */
function get_breakdance_translations()
{
    return [
        'global_blocks' => 'Global Blocks',
        'global_block' => 'Global Block',

        'plugin_name' => 'Breakdance',
        'admin_page_settings_slug' => 'breakdance_settings',

        'docs_link_security' => 'https://breakdance.com/documentation/other/security/',
        'docs_link_element_studio' => 'https://breakdance.com/documentation/developers/element-studio/',
        'docs_link_troubleshooting_500' => 'https://breakdance.com/documentation/troubleshooting/troubleshooting-500-50x-errors/',
        'docs_link_troubleshooting_403' => 'https://breakdance.com/documentation/troubleshooting/403-errors/',

        'website_link_upgrade_to_pro_iframe' => 'https://breakdance.com/upgrade-iframe/',

        'partner_discounts_link' => 'https://breakdance.com/portal/partner-discounts',

        'meta_prefix'  => 'breakdance_',
        '_meta_prefix' => '_breakdance_',
        'table_prefix' => 'breakdance_'
    ];
}

/**
 * private
 * @return array<string,string>
 */
function get_oxygen_translations()
{
    return [
        'global_blocks' => 'Components',
        'global_block' => 'Component',

        'plugin_name' => 'Oxygen',
        'admin_page_settings_slug' => 'oxygen_settings',

        'docs_link_security' => 'https://oxygenbuilder.com/documentation/other/security/',
        'docs_link_element_studio' => 'https://oxygenbuilder.com/documentation/developers/element-studio/',
        'docs_link_troubleshooting_500' => 'https://oxygenbuilder.com/documentation/troubleshooting/troubleshooting-500-50x-errors/',
        'docs_link_troubleshooting_403' => 'https://oxygenbuilder.com/documentation/troubleshooting/403-errors/',

        'website_link_upgrade_to_pro_iframe' => 'https://breakdance.com/upgrade-iframe/',

        'partner_discounts_link' => 'https://oxygenbuilder.com/portal/partner-discounts',

        'meta_prefix'  => 'oxygen_',
        '_meta_prefix' => '_oxygen_',
        'table_prefix' => 'oxygen_'
    ];
}

/**
 * private
 */
class StringsHolder
{

    use \Breakdance\Singleton;

    /**
     * @var array<string,string>
     */
    public $oxygen_translations = [];

    /**
     * @var array<string,string>
     */
    public $breakdance_translations = [];

    function setupStrings()
    {
        $this->oxygen_translations = get_oxygen_translations();
        $this->breakdance_translations = get_breakdance_translations();
    }
}

StringsHolder::getInstance()->setupStrings();
