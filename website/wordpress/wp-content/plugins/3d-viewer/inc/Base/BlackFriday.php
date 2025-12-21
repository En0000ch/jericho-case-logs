<?php

namespace BP3D\Base;

class BlackFriday
{

    public function register()
    {
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
    }

    function admin_assets()
    {
        wp_enqueue_script('black-friday-3d',  BP3D_DIR . 'dist/black-friday.js', ['wp-element'], BP3D_VERSION, true);
        wp_enqueue_style('black-friday-3d',  BP3D_DIR . 'dist/black-friday.css', [], BP3D_VERSION);

        wp_localize_script('black-friday', 'bp3dPlugin', [
            'isPipe' => bp3dv_fs()->can_use_premium_code()
        ]);
    }
}

if (!bp3dv_fs()->can_use_premium_code() && !isset($_GET['id']) && !isset($_GET['post'])) {
    add_action('admin_bar_menu', function (\WP_Admin_Bar $admin_bar) {
        if (! current_user_can('manage_options')) {
            return;
        }
        $admin_bar->add_menu(array(
            'id'    => 'bp3d_offer_menu',
            'parent' => 'top-secondary',
            'group'  => null,
            'title' => '40% Off Black Friday!', //you can use img tag with image link. it will show the image icon Instead of the title.
            'href'  => admin_url('edit.php?post_type=bp3d-model-viewer&page=3d-viewer-pricing-manual'),
            'meta' => [
                'title' => __('40% Off Black Friday!', 'h5vp'), //This title will show on hover
            ]
        ));
    }, 500);

    add_action('init', function () {
        add_submenu_page(
            '',
            __('Upgrade', 'textdomain'),
            __('Upgrade', 'textdomain'),
            'manage_options',
            '3d-viewer-pricing-manual',
            function () {
?>
            <div id="bp3dvUpgradePage">
                <div id="fsUpgradePrice">
                    <iframe src="https://wp.freemius.com/pricing/?plugin_id=8795&plugin_public_key=pk_5e6ce3f226c86e3b975b59ed84d6a&plugin_version=1.3.20&home_url=https%3A%2F%2Fmissbasin.s2-tastewp.com&post_type=bp3d-model-viewer&page=3d-viewer-pricing&next=https%3A%2F%2Fmissbasin.s2-tastewp.com%2Fwp-admin%2Fedit.php%3Fpost_type%3Dbp3d-model-viewer%26fs_action%3D3d-viewer_sync_license%26page%3D3d-viewer-account&billing_cycle=annual&is_network_admin=false&currency=usd&discounts_model=absolute#https%3A%2F%2Fmissbasin.s2-tastewp.com%2Fwp-admin%2Fedit.php%3Fpost_type%3Dbp3d-model-viewer%26page%3D3d-viewer-pricing" width="100%" height="800px" scrolling="no" frameborder="0" style="background: transparent; width: 1px; min-width: 100%; height: 1100px;"></iframe>
                </div>
            </div>
<?php
            },
            13
        );
    }, 500);
}
