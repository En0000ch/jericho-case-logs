<?php

namespace Breakdance\Themeless;

// post type slugs can have max 20 characters. if longer than 20 characters, fails silently. lmfao.

$isDesignLibraryEnabled = \Breakdance\Data\get_global_option('is_copy_from_frontend_enabled') === 'yes';

define('BREAKDANCE_ACF_BLOCK_POST_TYPE', 'breakdance_acf_block');
define('BREAKDANCE_POPUP_POST_TYPE', 'breakdance_popup');

if (BREAKDANCE_MODE === 'breakdance') {
    define('BREAKDANCE_TEMPLATE_POST_TYPE', 'breakdance_template');
    define('BREAKDANCE_HEADER_POST_TYPE', 'breakdance_header');
    define('BREAKDANCE_FOOTER_POST_TYPE', 'breakdance_footer');
    define('BREAKDANCE_BLOCK_POST_TYPE', 'breakdance_block');
    define('BREAKDANCE_PART_POST_TYPE', 'breakdance_part');

    $templatePostTypes = [
        BREAKDANCE_TEMPLATE_POST_TYPE,
        BREAKDANCE_HEADER_POST_TYPE,
        BREAKDANCE_FOOTER_POST_TYPE,
        BREAKDANCE_POPUP_POST_TYPE,
    ];

    if ($isDesignLibraryEnabled) {
        $templatePostTypes[] = BREAKDANCE_PART_POST_TYPE;
    }

    define('BREAKDANCE_ALL_TEMPLATE_POST_TYPES', $templatePostTypes);

    // Post types that have previewable items only for previewing dynamic data values (not content)
    $dynamicDataPreviewPostTypes = [
        BREAKDANCE_HEADER_POST_TYPE,
        BREAKDANCE_BLOCK_POST_TYPE,
        BREAKDANCE_FOOTER_POST_TYPE,
        BREAKDANCE_POPUP_POST_TYPE,
        BREAKDANCE_ACF_BLOCK_POST_TYPE,
    ];

    define('BREAKDANCE_DYNAMIC_DATA_PREVIEW_POST_TYPES', $dynamicDataPreviewPostTypes);

    // "Editable" because breakdance_form_res is also a post type but it's "read-only"
    $editablePostTypes = [
        BREAKDANCE_TEMPLATE_POST_TYPE,
        BREAKDANCE_HEADER_POST_TYPE,
        BREAKDANCE_FOOTER_POST_TYPE,
        BREAKDANCE_BLOCK_POST_TYPE,
        BREAKDANCE_POPUP_POST_TYPE,
        BREAKDANCE_ACF_BLOCK_POST_TYPE,
    ];

    if ($isDesignLibraryEnabled) {
        $editablePostTypes[] = BREAKDANCE_PART_POST_TYPE;
    }

    define('BREAKDANCE_ALL_EDITABLE_POST_TYPES', $editablePostTypes);
} else if (BREAKDANCE_MODE === 'oxygen') {
    define('BREAKDANCE_TEMPLATE_POST_TYPE', 'oxygen_template');
    define('BREAKDANCE_HEADER_POST_TYPE', 'oxygen_header');
    define('BREAKDANCE_FOOTER_POST_TYPE', 'oxygen_footer');
    define('BREAKDANCE_BLOCK_POST_TYPE', 'oxygen_block');
    define('BREAKDANCE_PART_POST_TYPE', 'oxygen_part');

    $templatePostTypes = [
        BREAKDANCE_TEMPLATE_POST_TYPE,
        BREAKDANCE_HEADER_POST_TYPE,
        BREAKDANCE_FOOTER_POST_TYPE,
    ];

    if ($isDesignLibraryEnabled) {
        $templatePostTypes[] = BREAKDANCE_PART_POST_TYPE;
    }

    define('BREAKDANCE_ALL_TEMPLATE_POST_TYPES', $templatePostTypes);

    // Post types that have previewable items only for previewing dynamic data values (not content)
    define('BREAKDANCE_DYNAMIC_DATA_PREVIEW_POST_TYPES', [
        BREAKDANCE_HEADER_POST_TYPE,
        BREAKDANCE_BLOCK_POST_TYPE,
        BREAKDANCE_FOOTER_POST_TYPE,
        BREAKDANCE_ACF_BLOCK_POST_TYPE,
    ]);

    // "Editable" because breakdance_form_res is also a post type but it's "read-only"
    $editablePostTypes = [
        BREAKDANCE_TEMPLATE_POST_TYPE,
        BREAKDANCE_HEADER_POST_TYPE,
        BREAKDANCE_FOOTER_POST_TYPE,
        BREAKDANCE_BLOCK_POST_TYPE,
        BREAKDANCE_ACF_BLOCK_POST_TYPE,
    ];

    if ($isDesignLibraryEnabled) {
        $editablePostTypes[] = BREAKDANCE_PART_POST_TYPE;
    }

    define('BREAKDANCE_ALL_EDITABLE_POST_TYPES', $editablePostTypes);
}

define('BREAKDANCE_BANNED_POST_TYPES', [
    'product',
    'attachment',
]);
