<?php

namespace Breakdance\DesignLibrary;

function registerPostType() {
    if (!isDesignLibraryEnabled()) return;
    $labels = array(
        'name'                  => _x( 'Parts', 'Post type general name', 'breakdance' ),
        'singular_name'         => _x( 'Part', 'Post type singular name', 'breakdance' ),
        'menu_name'             => _x( 'Parts', 'Admin Menu text', 'breakdance' ),
        'name_admin_bar'        => _x( 'Part', 'Add New on Toolbar', 'breakdance' ),
        'add_new'               => __( 'Add New', 'breakdance' ),
        'add_new_item'          => __( 'Add New Part', 'breakdance' ),
        'new_item'              => __( 'New Part', 'breakdance' ),
        'edit_item'             => __( 'Edit Part', 'breakdance' ),
        'view_item'             => __( 'View Part', 'breakdance' ),
        'all_items'             => __( 'All Part', 'breakdance' ),
        'search_items'          => __( 'Search Part', 'breakdance' ),
        'parent_item_colon'     => __( 'Parent Part:', 'breakdance' ),
        'not_found'             => __( 'No parts found.', 'breakdance' ),
        'not_found_in_trash'    => __( 'No parts found in Trash.', 'breakdance' ),
        'featured_image'        => _x( 'Part Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'breakdance' ),
        'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'breakdance' ),
        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'breakdance' ),
        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'breakdance' ),
        'archives'              => _x( 'Part archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'breakdance' ),
        'insert_into_item'      => _x( 'Insert into part', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'breakdance' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this part', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'breakdance' ),
        'filter_items_list'     => _x( 'Filter parts list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'breakdance' ),
        'items_list_navigation' => _x( 'Parts list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'breakdance' ),
        'items_list'            => _x( 'Parts list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'breakdance' ),
    );

    $args = [
        'labels'      => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => true,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'supports'           => ['title', 'editor', 'author', 'thumbnail', 'custom-fields'],
        'taxonomies'         => [],
        'show_in_rest'       => true
    ];

    register_post_type(BREAKDANCE_PART_POST_TYPE, $args);
}
add_action( 'init', '\Breakdance\DesignLibrary\registerPostType' );

add_action('breakdance_admin_menu', function () {
    if (!isDesignLibraryEnabled()) return;
    add_submenu_page('breakdance', 'Design Library Parts', 'Design Library Parts', 'manage_options', 'edit.php?post_type=' . BREAKDANCE_PART_POST_TYPE);
}, 9);
