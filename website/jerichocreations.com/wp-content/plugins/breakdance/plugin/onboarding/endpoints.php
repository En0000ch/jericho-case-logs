<?php

namespace Breakdance\Onboarding;

use function Breakdance\Data\get_global_option;
use function Breakdance\Data\set_global_option;
use function Breakdance\Data\set_meta;
use function Breakdance\BreakdanceOxygen\Strings\__bdox;


\Breakdance\AJAX\register_handler(
    'breakdance_get_onboarding_settings',
    'Breakdance\Onboarding\getSettings',
    'edit',
    true
);

\Breakdance\AJAX\register_handler(
    'breakdance_save_onboarding_settings',
    'Breakdance\Onboarding\saveSettings',
    'edit',
    false,
    [
        'args' => [
            'settings' => FILTER_UNSAFE_RAW
        ]
    ]
);

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_onboarding_talk_to_unsplash',
        'Breakdance\Onboarding\talkToUnsplash',
        'edit',
        false,
        [
            'args' => [
                'prompt' => FILTER_UNSAFE_RAW,
                'limit' => FILTER_UNSAFE_RAW,

            ]
        ]
    );
});


\Breakdance\AJAX\register_handler(
    'breakdance_save_document_ai_settings',
    '\Breakdance\Onboarding\saveDocumentAISettings',
    'edit',
    true,
    [
        'args' => [
            'postId' => FILTER_VALIDATE_INT,
            'settings' => FILTER_UNSAFE_RAW,
        ]
    ]
);


/**
 * Save the onboarding settings.
 *
 * @param string $settings JSON encoded settings.
 * @return array The merged settings with defaults.
 */
function saveSettings($settings)
{
    /** @var array $settings */
    $settings = json_decode((string)$settings, true);
    set_global_option('onboarding_settings_json_string', $settings);
    return getSettings();
}


/**
 * @param int $postId
 * @param string $aiSettings
 * @return void
 */
function saveDocumentAISettings($postId, $aiSettings)
{
    /** @var string $aiSettings */
    $aiSettings = json_decode($aiSettings, true);

    if ($aiSettings) {
        set_meta($postId, __bdox('_meta_prefix') . 'ai_settings', $aiSettings);
    }
}


/**
 * @return array
 */
function getSettings()
{
    /** @var array $settings */
    $settings = get_global_option('onboarding_settings_json_string');

    if (empty($settings)) {
        $settings = [];
    }

    $defaults = [
        'websiteDetails' => [
            'websiteName' => get_bloginfo('name'),
            'websiteDescription' => get_bloginfo('description'),
            'websiteCategory' => [],
            'websiteGoals' => [],
            'websiteWritingStyle' => [],
        ],
        'type' => 'barebones',
        'websiteUI' => [
            'colorPalettes' => [
                'neutral' => null,
                'primary' => null,
                'secondary' => []
            ],
            'typography' => [
                'headings' => null,
                'body' => null
            ],
            'buttons' => [
                'radius' => 'md'
            ],
            'images' => [
                'radius' => 'md'
            ]
        ],
        'generatedPages' => [
            'homepage' => null,
            'pages' => [],
            'toRemove' => [
                'homepageSectionIds' => null,
                'pageIds' => null
            ]
        ],
        'previewBreakpointId' => BASE_BREAKPOINT_ID,
        'designLibrary' => [
            'selectedProvider' => null,
            'data' => [
                'designProviders' => [],
                'globalSettingsLastImportedFromUrl' => null,
                'designPresetsLastImportedFromUrl' => null,
                'fullSiteLastImportedFromUrl' => null
            ]
        ],
        'sitemap' => null,
        'currentTree' => null,
        'activeElement' => null,
    ];

    return array_merge($defaults, $settings);
}



/**
 * @param string|null $prompt
 * @param string|null $limit
 * @return array
 */
function talkToUnsplash($prompt, $limit)
{
    $url = 'https://breakdance.com/wp-json/breakdance/v1/onboarding/unsplash';

    $params = [
        'per_page' => $limit ?? 10,
        'query' => $prompt ?? 'People',
    ];

    $request = wp_remote_get($url . '?' . http_build_query($params));
    $body = wp_remote_retrieve_body($request);
    /**
     * @psalm-suppress MixedAssignment
     * @var array{data: array} $response
     */
    $response = json_decode($body, true);

    if (is_wp_error($body)) {
        return ['type' => 'error', 'message' => 'Could not fetch images from Unsplash.'];
    }

    return [
        /**
         * @psalm-suppress MixedArrayAccess
         * @psalm-suppress PossiblyUndefinedStringArrayOffset
         * @psalm-suppress MixedArrayOffset
         * @psalm-suppress MixedPropertyFetch
         * @psalm-suppress MixedArgument
         *
         */
        'data' => $response['data']
    ];
}
