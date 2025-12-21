<?php

// @psalm-ignore-file

namespace Breakdance\Singularity\Endpoints;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_singularity_sync_pages_to_default_wp_menu',
        '\Breakdance\Singularity\Endpoints\syncPagesToDefaultWpMenu',
        'edit',
        true,
        [
            'args' => [
                'pageIds' => FILTER_UNSAFE_RAW
            ],
        ]
    );
});


//
/**
 * @param string $pageIds
 * @return array
 */
function syncPagesToDefaultWpMenu($pageIds)
{

    $pageIds = json_decode($pageIds);
    return _syncPagesToDefaultWpMenu($pageIds);
}

/**
 * @param int[] $pageIds 
 */
function _syncPagesToDefaultWPMenu($pageIds)
{
    $menuId = getSingularityDefaultMenuOrCreateIfItDoesNotExist();
    if (!$menuId) {
        return ['error' => "Failed to create or retrieve the default menu."];
    }

    clear_all_menu_items($menuId);

    foreach ($pageIds as $page_id) {
        $page = get_post($page_id);

        if ($page && $page->post_type === 'page') {
            wp_update_nav_menu_item($menuId, 0, [
                'menu-item-title'     => $page->post_title,
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $page_id,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }
    }

    return ['success' => "Synced pages to default menu successfully."];
}


function getSingularityDefaultMenuOrCreateIfItDoesNotExist()
{
    $menu_name = 'singularity_default_menu';
    $menu = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu ? $menu->term_id : null;

    if ($menu_id) {
        return $menu_id;
    }

    $menu_id = wp_create_nav_menu($menu_name);

    if (is_wp_error($menu_id)) {
        return null;
    }

    return $menu_id;
}

function clear_all_menu_items($menu_id)
{
    if (! is_nav_menu($menu_id)) {
        return new \WP_Error('invalid_menu', 'Invalid menu ID provided.');
    }

    $menu_items = wp_get_nav_menu_items($menu_id);

    if (empty($menu_items)) {
        return true;
    }

    foreach ($menu_items as $item) {
        wp_delete_post($item->ID, true);
    }

    return true;
}
