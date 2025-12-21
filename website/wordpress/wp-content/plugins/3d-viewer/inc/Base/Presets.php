<?php

namespace BP3D\Base;

class Presets
{


    public static function getPresetById($id)
    {
        if (!$id) return [];

        $content = get_post_field('post_content', $id);
        $content = str_replace(']]>', ']]&gt;', $content);

        $blocks = parse_blocks($content);
        if (count($blocks) === 0) return [];
        // $block = $blocks[0];

        return isset($blocks[0]['attrs']['preset']) ? $blocks[0]['attrs']['preset'] : [];
    }
}
