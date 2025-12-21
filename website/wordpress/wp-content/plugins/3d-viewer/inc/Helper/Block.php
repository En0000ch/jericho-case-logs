<?php

namespace BP3D\Helper;

class Block
{

    public static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function get($id)
    {
        $content_post = get_post($id);
        $content = $content_post->post_content;
        return $content;
    }

    public static function getBlock($id)
    {
        $blocks = parse_blocks(self::get($id));
        return $blocks[0];
    }

    public function render_block($id)
    {
        $block = self::getBlock($id);
        return render_block($block);
    }
}
