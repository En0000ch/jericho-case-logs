<?php

namespace Breakdance\BreakdanceOxygen\Selectors;

use Breakdance\Filesystem\Consts;
use function Breakdance\Data\get_global_option;
use function Breakdance\Data\GlobalRevisions\add_new_revision;
use function Breakdance\Data\GlobalRevisions\load_revisions_list;
use function Breakdance\Data\set_global_option;
use function Breakdance\Filesystem\get_file_contents_from_bucket;
use function Breakdance\Filesystem\HelperFunctions\generate_error_msg_from_fs_wp_error;
use function Breakdance\Filesystem\HelperFunctions\is_fs_error;
use function Breakdance\Filesystem\write_file_to_bucket;
use function Breakdance\Render\formatCss;
use function Breakdance\Render\getCss;
use const Breakdance\Data\GlobalRevisions\BREAKDANCE_REVISION_TYPE_OXY_SELECTORS;

if (BREAKDANCE_MODE == 'breakdance') {
    add_filter(
        '__breakdance_oxy_selectors_css_stylesheet_tag',
        function () {
            return '';
        }
    );
}

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_save_oxy_selectors',
        '\Breakdance\BreakdanceOxygen\Selectors\saveSelectors',
        'edit',
        false,
        ['args' => ['selectors' => FILTER_UNSAFE_RAW]]
    );
});

/**
 * @param string $data
 * @return void
 */
function saveSelectors($data)
{
    // Naive way to replace empty objects with nulls, to avoid PHP from converting them to arrays
    $data = str_replace("{}", "null", $data);

    /** @var array{selectors: OxygenSelector[], collections: string[]} $selectorsData */
    $selectorsData = json_decode($data, true);
    $selectors = $selectorsData['selectors'];

    /** @var false|OxygenSelector[] $currentCssSelectors */
    $currentCssSelectors = get_global_option('oxy_selectors_json_string');

    set_global_option('oxy_selectors_collections_json_string', $selectorsData['collections']);

    if ($currentCssSelectors !== $selectors) {
        $currentRevisions = load_revisions_list(BREAKDANCE_REVISION_TYPE_OXY_SELECTORS);
        if (!sizeof($currentRevisions) && $currentCssSelectors) {
            add_new_revision($currentCssSelectors, BREAKDANCE_REVISION_TYPE_OXY_SELECTORS);
        }

        set_global_option('oxy_selectors_json_string', $selectors);

        add_new_revision($selectors, BREAKDANCE_REVISION_TYPE_OXY_SELECTORS);
    }

    if ($currentCssSelectors !== $selectors || !getSelectorsCacheStore()) {
        \Breakdance\Render\generateCacheForGlobalSettings();
    }
}

/**
 * @psalm-suppress MixedInferredReturnType
 * @psalm-suppress MixedReturnStatement
 * @return OxygenSelector[]
 */
function getOxySelectors()
{
    static $selectors = false;

    if ($selectors === false) {
        /**
         * @psalm-suppress MixedAssignment
         * @var OxygenSelector[]|false $selectors
         */
        $selectors = get_global_option('oxy_selectors_json_string');
    }

    return $selectors ?: [];
}

/**
 * @return string[]
 */
function getOxySelectorsCollections()
{
    /**
     * @psalm-suppress MixedAssignment
     * @var string[]|null
     */
    $collections = get_global_option('oxy_selectors_collections_json_string');

    // Auto-fill collections from selectors array if empty
    if (!$collections) {
        $selectors = getOxySelectors();

        if (count($selectors) > 0) {
            /** @var string[] $collections */
            $collections = array_values(array_unique(array_column($selectors, 'collection')));
            return $collections;
        }
    }

    return $collections ?: [];
}

/**
 * @return array{
 *     template: string,
 *     selectors: OxygenSelector[],
 *     collections: string[],
 *     propertyPathsToWhitelistInFlatProps: string[]
 * }
 */
function getOxySelectorsDataForBuilder()
{
    return [
        'template' => cssTemplate(),
        'selectors' => getOxySelectors(),
        'collections' => getOxySelectorsCollections(),
        'propertyPathsToWhitelistInFlatProps' => propertyPathsToWhitelistInFlatProps(),
    ];
}

/**
 * @return string
 */
function cssTemplate()
{
    return (string) file_get_contents(__DIR__ . '/selectors.twig');
}

/**
 * @return string[]
 */
function propertyPathsToWhitelistInFlatProps()
{
    return [
        'selector.id',
        'selector.name',
        'selector.type',
        'selector.children[].id',
        'selector.children[].name'
    ];
}

/**
 * @param array<array-key, mixed> $properties
 * @param OxygenVariable[] $variables
 * @return array
 */
function replaceVariablesInProperties($properties, $variables) {
    /** @var mixed $value */
    foreach ($properties as $key => $value) {
        if (is_array($value)) {
            $properties[$key] = replaceVariablesInProperties($value, $variables);
        } elseif (is_string($value) && str_starts_with($value, '{var-')) {
            $id = str_replace(['{var-', '}'], '', $value);
            $result = array_filter(
                $variables,
                fn($item) => $item['id'] === $id
            );

            $variable = reset($result);

            if ($variable) {
                $properties[$key] = "var(--{$variable['cssVariableName']})";
            } else {
                $properties[$key] = null;
            }
        }
    }

    return $properties;
}

/**
 * @psalm-suppress LessSpecificReturnStatement
 * @psalm-suppress MoreSpecificReturnType
 * @param OxygenSelector|OxygenNestedSelector $selector
 * @param OxygenVariable[] $variables
 * @return OxygenSelector|OxygenNestedSelector
 */
function parseVariablesInSelector($selector, $variables = []) {
    if (isset($selector['properties'])) {
        $selector['properties'] = replaceVariablesInProperties($selector['properties'], $variables);
    }

    if (isset($selector['children'])) {
        $selector['children'] = array_map(
            fn($child) => parseVariablesInSelector($child, $variables),
            $selector['children']
        );
    }

    return $selector;
}

/**
 * Changes made here should be reflected in replaceAmpersandInSelector in the oxygen/classes/lib/utils.ts file
 * @param OxygenSelector $selector
 * @return OxygenSelector
 */
function replaceAmpersandInSelector($selector) {
    $replacement = $selector['type'] === 'class' ? '.' . $selector['name'] : $selector['name'];

    if (!isset($selector['children'])) {
        return $selector;
    }

    $selector['children'] = array_map(function($child) use ($replacement) {
        // Implicitly add an ampersand to the beginning of the name
        $name = str_contains($child['name'], '&') ? $child['name'] : '& ' . $child['name'];

        return array_merge($child, [
            'name' => str_replace('&', $replacement, $name)
        ]);
    }, $selector['children']);

    return $selector;
}

/**
 * @param OxygenSelector[] $selectors
 * @return string
 */
function cssForSelectors($selectors)
{
    $breakpoints = \Breakdance\Config\Breakpoints\get_breakpoints();
    $cssTemplate = cssTemplate();
    $propertiesToWhiteListInFlatProps = propertyPathsToWhitelistInFlatProps();
    $variables = \Breakdance\Variables\getVariables();

    /** @var OxygenSelector[] $selectors */
    $selectors = array_map(
        fn($selector) => parseVariablesInSelector(
            replaceAmpersandInSelector($selector),
            $variables
        ),
        $selectors
    );

    $cacheStore = \Breakdance\BreakdanceOxygen\Selectors\getSelectorsCacheStore();
    $css = '';

    $updatedCache = [];

    foreach ($selectors as $selector) {
        $selectorCss = getCssForSelector(
            $selector,
            $cssTemplate,
            $propertiesToWhiteListInFlatProps,
            $breakpoints,
            $cacheStore
        );

        $css .= $selectorCss;

        $updatedCache[] = [
            'id' => $selector['id'],
            'hash' => getSelectorHash($selector),
            'css' => $selectorCss,
        ];
    }

    saveSelectorsCacheStore($updatedCache);

    return $css;
}

/**
 * @param TreeNode $node
 * @return array
 */
function getElementOxySelectors($node)
{
    /** @var string[] $ids */
    $ids = $node['data']['properties']['meta']['classes'] ?? [];

    if (empty($ids)) return [];

    $availableClasses = getOxySelectors();
    $classMap = array_column($availableClasses, null, 'id');

    return array_filter(
        array_map(
            fn($oxySelectorId) => $classMap[$oxySelectorId] ?? null,
            $ids
        )
    );
}

// Cache Methods

/**
 * @param OxygenSelector $selector
 * @param OxygenSelectorCacheData[] $cacheStore
 * @return bool
 */
function hasSelectorChanged($selector, $cacheStore)
{
    $cachedData = getCachedSelectorData($selector['id'], $cacheStore);

    // If the selector is not cached, it has changed
    if (!$cachedData) return true;

    return $cachedData['hash'] !== getSelectorHash($selector);
}

/**
 * @return OxygenSelectorCacheData[]
 */
function getSelectorsCacheStore()
{
    $contentsOrError = get_file_contents_from_bucket(Consts::BREAKDANCE_FS_BUCKET_CSS, 'oxy-selectors.json');

    if ($contentsOrError && !is_fs_error($contentsOrError)) {
        /** @var OxygenSelectorCacheData[]|null $decodedJson */
        $decodedJson = json_decode($contentsOrError, true);

        if ($decodedJson) {
            return $decodedJson;
        }
    }

    return [];
}

/**
 * @param string $selectorId
 * @param OxygenSelectorCacheData[] $cacheStore
 * @return OxygenSelectorCacheData|null
 */
function getCachedSelectorData($selectorId, $cacheStore)
{
    $cachedData = array_filter(
        $cacheStore,
        fn($item) => $item['id'] === $selectorId
    );

    /** @var OxygenSelectorCacheData|null */
    $output = $cachedData ? reset($cachedData) : null;
    return $output;
}

/**
 * @param OxygenSelectorCacheData[] $cacheStore
 * @return string
 * @throws \Exception
 */
function saveSelectorsCacheStore($cacheStore)
{
    $basename = 'oxy-selectors.json';
    $writeErrorOrFilename = write_file_to_bucket(Consts::BREAKDANCE_FS_BUCKET_CSS, $basename, json_encode($cacheStore));

    if (!is_fs_error($writeErrorOrFilename)) {
        /** @var string */
        return $writeErrorOrFilename;
    } else {
        /** @var \WP_Error $writeErrorOrFilename */
        throw new \Exception(generate_error_msg_from_fs_wp_error($writeErrorOrFilename));
    }
}

/**
 * @param OxygenSelector $selector
 * @return string
 */
function getSelectorHash($selector)
{
    // Re-encode the JSON in a canonicalized form
    $canonicalJson = json_encode(
        $selector,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );

    return hash("sha256", $canonicalJson);
}

/**
 * @param OxygenSelector $selector
 * @param string $cssTemplate
 * @param string[] $propertiesToWhiteListInFlatProps
 * @param Breakpoint[] $breakpoints
 * @param OxygenSelectorCacheData[] $cacheStore
 * @return string
 */
function getCssForSelector(
    $selector,
    $cssTemplate,
    $propertiesToWhiteListInFlatProps,
    $breakpoints,
    $cacheStore
)
{
    $hasChanged = hasSelectorChanged($selector, $cacheStore);

    if (!$hasChanged) {
        $cachedData = getCachedSelectorData($selector['id'], $cacheStore);

        if ($cachedData) {
            return $cachedData['css'];
        }
    }

    return formatCss(
        getCss(
            $cssTemplate,
            ['selector' => $selector],
            $breakpoints,
            [],
            $propertiesToWhiteListInFlatProps,
        )
    );
}
