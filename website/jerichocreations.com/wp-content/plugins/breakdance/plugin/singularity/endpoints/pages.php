<?php

// @psalm-ignore-file

namespace Breakdance\Singularity\Endpoints;

use function Breakdance\Util\WP\performant_get_posts;
use function Breakdance\BreakdanceOxygen\Strings\__bdox;
use function Breakdance\Data\set_meta;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_get_pages',
        '\Breakdance\Singularity\Endpoints\getPages',
        'edit',
        true,
        [
            'args' => [
                'postType' => FILTER_UNSAFE_RAW
            ],
        ]
    );
});

/**
 * @param string $postType
 * @return array{data: mixed[]}
 */
function getPages($postType)
{
    $pages = performant_get_posts([
        'post_type' => $postType,
        'post_status' => 'any',
        'posts_per_page' => -1,
    ]);

    $formattedPages = array_map(
        '\Breakdance\Singularity\Endpoints\formatPage',
        $pages
    );

    return ['data' => $formattedPages];
}

/**
 * @param \WP_Post $post
 * @return mixed
 */
function formatPage($post)
{

    /**
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArgument
     * @psalm-suppress PossiblyFalseArgument
     */
    $singularityMeta = json_decode(\Breakdance\Data\get_meta($post->ID, __bdox('_meta_prefix') . 'singularity_meta'));

    return [
        'id' => $post->ID,
        'title' => $post->post_title,
        'postType' => $post->post_type,
        'relativeUrl' => getRelativeUrlForPage($post->ID),
        'singularityMeta' => $singularityMeta ? $singularityMeta : false,
    ];
}


add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_delete_all_pages_headers_and_footers',
        '\Breakdance\Singularity\Endpoints\deleteAllPagesHeadersAndFooters',
        'edit',
        true,
        [
            'args' => [],
        ]
    );
});

/**
 * @return array
 */
function deleteAllPagesHeadersAndFooters()
{

    $failedToDeleteSomething = false;

    $pages = get_posts([
        'post_type' => ['page', BREAKDANCE_HEADER_POST_TYPE, BREAKDANCE_FOOTER_POST_TYPE],
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]);

    foreach ($pages as $pageId) {
        $trashed = wp_trash_post($pageId);

        if (!$trashed) {
            $failedToDeleteSomething = true;
        }
    }

    $pages = get_posts([
        'post_type' => 'page',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ]);

    if ($failedToDeleteSomething) {
        return ['error' => "Failed to delete all."];
    }

    return ['success' => "Deleted all successfully."];
}





add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_delete_page',
        '\Breakdance\Singularity\Endpoints\deletePage',
        'edit',
        true,
        [
            'args' => [
                'id' => FILTER_UNSAFE_RAW,
            ],
        ]
    );
});

/**
 * @param int $pageId
 * @return array
 */
function deletePage($pageId)
{
    $trashed = wp_trash_post($pageId);
    if (!$trashed) {
        return ['error' => "Failed to delete " . $pageId . "."];
    }
    return ['success' => "Deleted " . $pageId . " successfully."];
}


add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_add_page',
        '\Breakdance\Singularity\Endpoints\addPage',
        'edit',
        true,
        [
            'args' => [
                'title' => FILTER_UNSAFE_RAW,
                'singularityMeta' => FILTER_UNSAFE_RAW,
                'post_type' => FILTER_UNSAFE_RAW,
            ],
        ]
    );
});

/**
 * @param string $title
 * @param string $singularityMeta
 * @param string $post_type
 * @return array
 */
function addPage($title, $singularityMeta, $post_type)
{
    $pageId = wp_insert_post(
        [
            'post_type' => $post_type,
            'post_title' => $title,
            'post_status' => 'publish',
        ],
        true
    );

    if (is_wp_error($pageId)) {
        return ['error' => "Failed to create page."];
    }

    set_meta($pageId, __bdox('_meta_prefix') . 'singularity_meta', $singularityMeta);

    return ['success' => "Created " . $post_type . " successfully.", 'id' => $pageId, 'relativeUrl' => getRelativeUrlForPage($pageId)];
}


add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_add_or_update_page',
        '\Breakdance\Singularity\Endpoints\addOrUpdatePage',
        'edit',
        true,
        [
            'args' => [
                'title' => FILTER_UNSAFE_RAW,
                'postType' => FILTER_UNSAFE_RAW,
                'tree' => FILTER_UNSAFE_RAW,
                'singularityMeta' => FILTER_UNSAFE_RAW,
                'templateSettings' => FILTER_UNSAFE_RAW,
                'id' => FILTER_SANITIZE_NUMBER_INT,
            ],
            'optional_args' => ['tree', 'singularityMeta', 'templateSettings', 'id'],
        ]
    );
});



/**
 * @param string $title
 * @param string $postType
 * @param string $tree
 * @param string $singularityMeta
 * @param string $templateSettings
 * @param int $id
 * @return array
 */
function addOrUpdatePage($title, $postType, $tree, $singularityMeta, $templateSettings, $id)
{

    $post_data = [
        'post_type' => $postType,
        'post_title' => $title,
        'post_status' => 'publish',
    ];

    if ($id && get_post($id)) {
        $post_data['ID'] = $id;
    }

    $id = wp_insert_post($post_data, true);

    if (is_wp_error($id)) {
        return ['error' => "Failed to " . ($post_data['ID'] ?? false ? 'update' : 'create') . " " . $postType];
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

    return ['success' => "Created " . $postType . " successfully.", 'id' => $id, 'relativeUrl' => getRelativeUrlForPage($id)];
}





// update page
add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_update_page',
        '\Breakdance\Singularity\Endpoints\updatePage',
        'edit',
        true,
        [
            'args' => [
                'id' => FILTER_UNSAFE_RAW,
                'title' => FILTER_UNSAFE_RAW,
            ],
        ]
    );
});
/**
 * @param int $id
 * @param string $title
 * @return array
 */
function updatePage($id, $title)
{
    $updatedPageId = wp_update_post(
        [
            'ID' => $id,
            'post_title' => $title,
        ],
        true
    );

    if (is_wp_error($updatedPageId)) {
        return ['error' => "Failed to update page."];
    }

    return ['success' => "Updated " . $id . " successfully."];
}






add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_update_singularity_meta',
        '\Breakdance\Singularity\Endpoints\updateSingularityMeta',
        'edit',
        true,
        [
            'args' => [
                'id' => FILTER_UNSAFE_RAW,
                'singularityMeta' => FILTER_UNSAFE_RAW,
            ],
        ]
    );
});

/**
 * @param int $id
 * @param string $singularityMeta
 * @return array
 */
function updateSingularityMeta($id, $singularityMeta)
{

    set_meta($id, __bdox('_meta_prefix') . 'singularity_meta', $singularityMeta);

    return ['success' => "Updated " . $id . " successfully."];
}
