<?php

namespace Breakdance\AjaxEndpoints;

use function Breakdance\Data\set_meta;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_insert_onboarding_page',
        'Breakdance\AjaxEndpoints\insertOnboardingPage',
        'full',
        true,
        [
            'args' => [
                'postTitle' => FILTER_SANITIZE_SPECIAL_CHARS,
            ]
        ]
    );
});


add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_trash_onboarding_page',
        'Breakdance\AjaxEndpoints\trashOnboardingPage',
        'full',
        true,
        [
            'args' => [
                'postId' => FILTER_SANITIZE_NUMBER_INT,
            ]
        ]
    );
});


/**
 * Inserts a new onboarding page with the given title.
 *
 * @param string $postTitle The title of the post.
 * @param bool $setAsHomepage Whether to set the post as the homepage.
 * @return array{postId: int} The ID of the newly created post.
 * @throws \Exception If there is an error inserting the post.
 */
function insertOnboardingPage($postTitle, $setAsHomepage = false)
{
    $post = [
        'post_title' => $postTitle,
        'post_status' => 'draft',
        'post_type' => 'page',
    ];

    $postId = wp_insert_post($post);

    if (is_wp_error($postId)) {
        /** @var \WP_Error $postId */
        $error = $postId;
        $error_code = $error->get_error_message();
        throw new \Exception($error_code);
    }

    /** @var int $postId */
    $postId = $postId;

    // Blank Tree
    set_meta(
        $postId,
        '_breakdance_data',
        [
            'tree_json_string' => "",
        ]
    );

    // If the post type is 'page', set it as the homepage
    if ($setAsHomepage === true) {
        update_option('show_on_front', 'page'); // Set the homepage display to a static page
        update_option('page_on_front', $postId); // Set the newly created page as the homepage
    }

    return ['postId' => $postId];
}


/**
 * Moves the specified onboarding page to the trash.
 *
 * @param int|string $postId The ID of the post to be trashed.
 * @return array{success: bool} An array indicating success.
 * @throws \Exception If the post ID is invalid or the post is not found.
 */
function trashOnboardingPage($postId)
{
    if (is_numeric($postId)) {
        $postId = (int)$postId;
    } else {
        throw new \Exception('Invalid post ID');
    }

    $post = get_post($postId);

    if ($post) {
        wp_trash_post($postId);
        return ['success' => true];
    } else {
        throw new \Exception('Post not found');
    }
}
