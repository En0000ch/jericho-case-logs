<?php

namespace Breakdance\DynamicData;

class PostFeaturedImage extends ImageField {

    /**
     * @inheritDoc
     */
    public function label()
    {
        return 'Featured Image';
    }

    /**
     * @inheritDoc
     */
    public function category()
    {
        return 'Post';
    }

    /**
     * @inheritDoc
     */
    public function slug()
    {
        return 'post_featured_image';
    }

    /**
     * @inheritDoc
     */
    public function handler($attributes): ImageData
    {
        // In case the current post is an attachment, we need to return the post ID itself.
        if (get_post_type() === 'attachment') {
            $attachment_id = get_the_ID();
        } else {
            $attachment_id = get_post_thumbnail_id(get_the_ID());
        }

        return ImageData::fromAttachmentId($attachment_id);
    }
}


