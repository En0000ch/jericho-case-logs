<?php

namespace BP3D\Base;

class Activate
{

    public function register()
    {
        add_filter('admin_footer_text', [$this, 'bp3d_admin_footer']);
        add_action('admin_head', [$this, 'bp3d_admin_head']);
    }

    public function bp3d_admin_footer($text)
    {
        if ('bp3d-model-viewer' == get_post_type()) {
            $url = 'https://wordpress.org/plugins/3d-viewer/reviews/?filter=5#new-post';
            $text = sprintf('If you like <strong> 3D Viewer </strong> please leave us a <a href="%s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating. Your Review is very important to us as it helps us to grow more. ', $url);
        }

        return $text;
    }

    function bp3d_admin_head()
    {
?>
        <style>
            .menu-icon-bp3d-model-viewer ul li:has(a[href$="3d-viewer-affiliation"]) {
                display: none;
            }
        </style>
<?php
    }
}
