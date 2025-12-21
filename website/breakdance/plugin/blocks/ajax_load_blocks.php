<?php

namespace Breakdance\Blocks;

use function Breakdance\Admin\get_builder_loader_url;
use function Breakdance\Data\get_meta;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_load_block',
        '\Breakdance\Blocks\loadSingle',
        'edit',
        true,
        [
            'args' => [
                'blockId' => FILTER_VALIDATE_INT
            ]
        ]
    );
    \Breakdance\AJAX\register_handler('breakdance_load_blocks', '\Breakdance\Blocks\load', 'edit', true);
});

/**
 * @param \WP_Post $post
 * @return GlobalBlock
 */
function formatBlock($post)
{
    $tree = \Breakdance\Data\get_tree($post->ID);
    $settings = getBlockSettings($post->ID);
    /** @var TemplatePreviewableItem|false $lastPreviewedItem */
    $lastPreviewedItem = get_meta($post->ID, '_breakdance_template_last_previewed_item') ?: false;

    return [
        'label' => $post->post_title,
        'id' => intval($post->ID),
        'tree' => $tree,
        'settings' => $settings,
        'lastPreviewedItem' => $lastPreviewedItem,
        'editInBreakdanceLink' => get_builder_loader_url($post->ID)
    ];
}

/**
 * @return array{blocks: GlobalBlock[]}
 */
function load()
{
    /**
     * @var \WP_Post[]
     */
    $posts = get_posts([
        'post_type' => BREAKDANCE_BLOCK_POST_TYPE,
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    $blocks = array_map(
        fn($post) => formatBlock($post),
        $posts
    );

    return [
        'blocks' => $blocks,
    ];
}

/**
 * @param int $blockId
 * @return array{block: GlobalBlock}
 */
function loadSingle($blockId)
{
    /**
     * @var \WP_Post $post
     */
    $post = get_post($blockId);

    return [
        'block' => formatBlock($post),
    ];
}

/**
 * @param int $id
 * @return GlobalBlockSettings
 */
function getBlockSettings($id)
{
    /** @var GlobalBlockSettings $settings */
    $settings = get_meta($id, '_breakdance_block_settings');

    return $settings;
}
