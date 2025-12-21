<?php
namespace BP3D\Base;

class MenuOrder {

    public function register(){
        add_filter( 'custom_menu_order', [$this, 'order3dviewerSubMenu'] );
    }

    // Re-ordering 3D Order menu
    public function order3dviewerSubMenu( $menu_ord ) {
        global  $submenu ;
        $arr = array();
        if ( isset( $submenu['edit.php?post_type=bp3d-model-viewer'][5] ) ) {
            $arr[] = $submenu['edit.php?post_type=bp3d-model-viewer'][5];
        }
        // All 3D Viewers
        if ( isset( $submenu['edit.php?post_type=bp3d-model-viewer'][10] ) ) {
            $arr[] = $submenu['edit.php?post_type=bp3d-model-viewer'][10];
        }
        // Add New
        if ( isset( $submenu['edit.php?post_type=bp3d-model-viewer'][12] ) ) {
            $arr[] = $submenu['edit.php?post_type=bp3d-model-viewer'][12];
        }
        // 3D Viewer Settings
        if ( isset( $submenu['edit.php?post_type=bp3d-model-viewer'][11] ) ) {
            $arr[] = $submenu['edit.php?post_type=bp3d-model-viewer'][11];
        }
        // Help
        if ( isset( $submenu['edit.php?post_type=bp3d-model-viewer'][13] ) ) {
            // Account
            $arr[] = $submenu['edit.php?post_type=bp3d-model-viewer'][13];
        }
        //
        if ( isset( $submenu['edit.php?post_type=bp3d-model-viewer'][14] ) ) {
            // Contact Us
            $arr[] = $submenu['edit.php?post_type=bp3d-model-viewer'][14];
        }
        if ( isset( $submenu['edit.php?post_type=bp3d-model-viewer'][15] ) ) {
            // Support Forum
            $arr[] = $submenu['edit.php?post_type=bp3d-model-viewer'][15];
        }
        if ( isset( $submenu['edit.php?post_type=bp3d-model-viewer'][16] ) ) {
            // Upgrade
            $arr[] = $submenu['edit.php?post_type=bp3d-model-viewer'][16];
        }
        $submenu['edit.php?post_type=bp3d-model-viewer'] = $arr;
        return $menu_ord;
    }
}