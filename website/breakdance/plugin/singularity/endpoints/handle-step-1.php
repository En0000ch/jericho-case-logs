<?php

// @psalm-ignore-file

namespace Breakdance\Singularity\Endpoints;

use function Breakdance\BreakdanceOxygen\Strings\__bdox;
use function Breakdance\Data\set_meta;


add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_handle_step_1',
        '\Breakdance\Singularity\Endpoints\handleStep1',
        'edit',
        true,
        [
            'args' => [
                'homepageData' => FILTER_UNSAFE_RAW,
                'headerData' => FILTER_UNSAFE_RAW,
                'footerData' => FILTER_UNSAFE_RAW,
                'titlesForAdditionalPagesToCreate' => [
                    'filter' => FILTER_DEFAULT,
                    'flags' => FILTER_REQUIRE_ARRAY,
                ],
                'titlesForPagesToIncludeInMainMenu' => [
                    'filter' => FILTER_DEFAULT,
                    'flags' => FILTER_REQUIRE_ARRAY,
                ],
            ],
        ]
    );
});


/**
 * @param string $homepageData
 * @param string $headerData
 * @param string $footerData
 * @param string[] $titlesForAdditionalPagesToCreate
 * @param string[] $titlesForPagesToIncludeInMainMenu
 * @return array
 */
function handleStep1($homepageData, $headerData, $footerData, $titlesForAdditionalPagesToCreate, $titlesForPagesToIncludeInMainMenu)
{

    deleteAllPagesHeadersAndFooters();

    $parsedHomepageData = json_decode($homepageData, true);
    $parsedHeaderData = json_decode($headerData, true);
    $parsedFooterData = json_decode($footerData, true);

    $homepageId = addPost(
        $parsedHomepageData['title'],
        $parsedHomepageData['postType'],
        $parsedHomepageData['tree'] ?? false,
        $parsedHomepageData['singularityMeta'] ?? false,
        $parsedHomepageData['templateSettings'] ?? false
    );

    setPageToFrontpage($homepageId);

    addPost(
        $parsedHeaderData['title'],
        $parsedHeaderData['postType'],
        $parsedHeaderData['tree'] ?? false,
        $parsedHeaderData['singularityMeta'] ?? false,
        $parsedHeaderData['templateSettings'] ?? false
    );

    addPost(
        $parsedFooterData['title'],
        $parsedFooterData['postType'],
        $parsedFooterData['tree'] ?? false,
        $parsedFooterData['singularityMeta'] ?? false,
        $parsedFooterData['templateSettings'] ?? false,
    );

    $createdPages = createPagesFromTitles($titlesForAdditionalPagesToCreate);

    putPagesInMenu($titlesForPagesToIncludeInMainMenu);

    return [
        'success' => "Created successfully.",
        'homepage' => [
            'id' => $homepageId,
            'title' => $parsedHomepageData['title'],
            'relativeUrl' => getRelativeUrlForPage($homepageId)
        ],
        'otherPages' => $createdPages
    ];
}



/**
 * @param string $title
 * @param string $postType
 * @param string|false $tree
 * @param string|false $singularityMeta
 * @param string|false $templateSettings
 */
function addPost($title, $postType, $tree, $singularityMeta, $templateSettings)
{

    $id = wp_insert_post(
        [
            'post_type' => $postType,
            'post_title' => $title,
            'post_status' => 'publish',
        ],
        true
    );

    if (is_wp_error($id)) {
        return false;
    }

    if ($tree) {
        set_meta(
            $id,
            __bdox('_meta_prefix') . 'data',
            [
                'tree_json_string' => $tree,
            ]
        );
    }

    if ($singularityMeta) {
        set_meta(
            $id,
            __bdox('_meta_prefix') . 'singularity_meta',
            $singularityMeta
        );
    }

    if ($templateSettings) {
        \Breakdance\Data\set_meta(
            $id,
            __bdox('_meta_prefix') . 'template_settings',
            $templateSettings
        );
    }

    return $id;
}



/**
 * @param number $pageId
 */
function setPageToFrontpage($pageId)
{
    update_option('show_on_front', 'page');
    update_option('page_on_front', $pageId);
}


/**
 * @param string[] $titles
 * @return array
 */
function createPagesFromTitles($titles)
{
    $created_pages = [];

    foreach ($titles as $title) {

        $page_id = wp_insert_post([
            'post_title'   => $title,
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);

        if (!is_wp_error($page_id)) {
            $created_pages[] = [
                'id'    => $page_id,
                'title' => $title,
                'relativeUrl' => getRelativeUrlForPage($page_id)
            ];
        }
    }

    return $created_pages;
}


/**
 * @param string[] $titles
 */
function putPagesInMenu($titlesForPagesToIncludeInMainMenu)
{
    $pageIds = [];

    foreach ($titlesForPagesToIncludeInMainMenu as $title) {
        $posts = get_posts([
            'post_type'              => 'page',
            'title'                  => $title,
            'post_status'            => 'publish',
            'numberposts'            => 1,
            'update_post_term_cache' => false, // better performance apparently
            'update_post_meta_cache' => false, // better performance apparently
        ]);

        if (!empty($posts)) {
            $pageIds[] = $posts[0]->ID;
        }
    }

    if (!empty($pageIds)) {
        $menuId = getSingularityDefaultMenuOrCreateIfItDoesNotExist();
        clear_all_menu_items($menuId);
        _syncPagesToDefaultWPMenu($pageIds);
    }
}


/**
 * @param $post_id int
 */
function getRelativeUrlForPage($post_id)
{

    // LLM generated...

    $permalink = get_permalink($post_id);

    if (! $permalink) {
        return '';
    }

    $parsed_url = wp_parse_url($permalink);

    $relative_url = isset($parsed_url['path']) ? $parsed_url['path'] : '/';

    if (!empty($parsed_url['query'])) {
        $relative_url .= '?' . $parsed_url['query'];
    }

    if (!empty($parsed_url['fragment'])) {
        $relative_url .= '#' . $parsed_url['fragment'];
    }

    return $relative_url;
}
