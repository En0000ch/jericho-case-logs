<?php

// @psalm-ignore-file

namespace Breakdance\Singularity\Endpoints;

add_action('breakdance_loaded', function () {
    \Breakdance\AJAX\register_handler(
        'breakdance_download_external_image',
        '\Breakdance\Singularity\Endpoints\downloadExternalImage',
        'edit',
        true,
        [
            'args' => [
                'image_url' => FILTER_UNSAFE_RAW,
                'alt_text' => FILTER_UNSAFE_RAW,
                'caption' => FILTER_UNSAFE_RAW,
                'filename' => FILTER_UNSAFE_RAW
            ],
        ]
    );
});

/**
 * Download external image to WordPress media library
 * 
 * @param string $imageUrl
 * @param string $altText
 * @param string $caption
 * @param string $filename
 * @return array
 */
function downloadExternalImage($imageUrl, $altText, $caption, $filename)
{
    if (empty($imageUrl)) {
        return ['error' => 'Image URL is required'];
    }

    // Download the image
    $temp_file = download_url($imageUrl);
    
    if (is_wp_error($temp_file)) {
        return ['error' => 'Failed to download image: ' . $temp_file->get_error_message()];
    }

    // Get the file extension from the original URL or use jpg as default
    $file_extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (empty($file_extension)) {
        $file_extension = 'jpg';
    }

    // Ensure filename has proper extension
    if (!empty($filename)) {
        $filename = sanitize_file_name($filename);
        if (!preg_match('/\.' . preg_quote($file_extension) . '$/', $filename)) {
            $filename = pathinfo($filename, PATHINFO_FILENAME) . '.' . $file_extension;
        }
    } else {
        $filename = 'stock-image-' . time() . '.' . $file_extension;
    }

    // Prepare file array for wp_handle_sideload
    $file_array = [
        'name' => $filename,
        'tmp_name' => $temp_file,
    ];

    // Import the image into media library
    $attachment_id = media_handle_sideload($file_array, 0);

    // Clean up temp file
    @unlink($temp_file);

    if (is_wp_error($attachment_id)) {
        return ['error' => 'Failed to import image to media library: ' . $attachment_id->get_error_message()];
    }

    // Update alt text and caption if provided
    if (!empty($altText)) {
        update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($altText));
    }

    if (!empty($caption)) {
        wp_update_post([
            'ID' => $attachment_id,
            'post_excerpt' => sanitize_text_field($caption)
        ]);
    }

    // Get the attachment URL
    $attachment_url = wp_get_attachment_url($attachment_id);

    return [
        'attachment_id' => $attachment_id,
        'url' => $attachment_url,
        'alt' => $altText,
        'caption' => $caption
    ];
}