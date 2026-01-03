<?php

namespace WPV_AE;

class PostType{

    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct(){

        add_action( 'init', [$this, 'register_post_type'], 0 );

        add_action('elementor/init', [ $this, 'add_elementor_support']);
    }

    public function register_post_type(){

        $labels = array(
            'name'                  => _x( 'Dynific Global Templates', 'Post Type General Name', 'wts_ae' ),
            'singular_name'         => _x( 'Dynific Template', 'Post Type Singular Name', 'wts_ae' ),
            'menu_name'             => __( 'Dynific Templates', 'wts_ae' ),
            'name_admin_bar'        => __( 'Dynific Templates', 'wts_ae' ),
            'archives'              => __( 'List Archives', 'wts_ae' ),
            'parent_item_colon'     => __( 'Parent List:', 'wts_ae' ),
            'all_items'             => __( 'All Dynific Templates', 'wts_ae' ),
            'add_new_item'          => __( 'Add New Dynific Template', 'wts_ae' ),
            'add_new'               => __( 'Add New', 'wts_ae' ),
            'new_item'              => __( 'New Dynific Template', 'wts_ae' ),
            'edit_item'             => __( 'Edit Dynific Template', 'wts_ae' ),
            'update_item'           => __( 'Update Dynific Template', 'wts_ae' ),
            'view_item'             => __( 'View Dynific Template', 'wts_ae' ),
            'search_items'          => __( 'Search Dynific Template', 'wts_ae' ),
            'not_found'             => __( 'Not found', 'wts_ae' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'wts_ae' )
        );
        $args = array(
            'label'                 => __( 'Post List', 'wts_ae' ),
            'labels'                => $labels,
            'supports'              => array( 'title','editor' ),
            'public'                => true,
            'rewrite'               => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => false,
            'exclude_from_search'   => true,
            'capability_type'       => 'post',
            'hierarchical'          => false,
            'menu_icon'           => 'data:image/svg+xml;base64,' . base64_encode('<svg width="1249" height="1512" viewBox="0 0 1249 1512" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M446.137 495.992C482.213 495.992 515.513 501.419 545.761 512.64C576.172 523.921 602.392 540.876 624.07 563.551H624.069C631.518 571.266 638.337 579.586 644.537 588.489L596.23 707.79L596.644 707.957L580.974 745.224C580.13 720.106 576.778 698.756 571.225 680.918L571.216 680.891L571.208 680.862C564.673 659.672 555.474 643.223 544.108 630.775C532.693 618.273 518.614 608.934 501.463 602.782L501.363 602.747L501.265 602.71C483.762 596.242 463.281 592.785 439.496 592.785H394.758V928.297H426.547C480.967 928.297 518.25 913.352 542.487 886.944C549.121 879.717 554.994 871.401 560.033 861.922C561.111 860.39 564.246 854.808 573 834.499C584.619 807.544 599.693 766.765 601.761 761.148L661.894 618.146C666.174 626.916 669.998 636.108 673.371 645.71L673.913 647.238C685.196 679.394 690.598 715.787 690.598 756.059C690.598 798.856 685.052 837.326 673.471 871.104L673.463 871.127L673.455 871.149C661.911 904.578 644.957 933.245 622.314 956.634L622.273 956.677L622.232 956.719C599.555 979.894 571.83 997.113 539.512 1008.54C507.506 1019.94 471.739 1025.42 432.523 1025.42H289V495.992H446.137Z" fill="white"/>
								<path d="M616.871 1008.83L611.653 1023.42H559.899C560.889 1022.84 561.878 1022.25 562.869 1021.66C572.706 1016.98 584.61 1009.66 596.743 1000.51C612.572 988.558 625.238 976.164 631.921 966.742L616.871 1008.83ZM789.137 506.43L967.77 993.852L978.606 1023.42H868.466L863.283 1008.75L815.341 873.012H665.442L660.936 885.612C645.359 865.615 597.716 873.225 546.771 901.743L691.374 506.442L696.657 492H783.849L789.137 506.43ZM740.108 658.854C739.653 660.193 739.217 661.462 738.796 662.657L700.642 774.227H780.332L741.401 662.695L741.35 662.549L741.301 662.402C740.931 661.294 740.533 660.112 740.108 658.854Z" fill="white"/>
								<path d="M1097.85 479.113V1037.79C1097.85 1127.72 1024.62 1200.53 934.597 1200.53H316.935H45.6949H0L317.13 1511.6V1351H934.597C1107.81 1351 1248.8 1210.65 1248.8 1037.79V479.113V403H1097.07L1097.85 479.113Z" fill="white"/>
								<path d="M936.745 165.657C935.964 165.657 935.378 165.657 934.597 165.657H314.201C140.99 165.657 0 306.007 0 478.867V1037.54V1113.66H151.926L150.949 1037.54V478.867C150.949 388.933 224.178 316.13 314.201 316.13H1203.1H1248.8L936.745 0V165.657Z" fill="white"/>
								</svg>'),
        );
        register_post_type( 'ae_global_templates', $args );

    }

    public function add_elementor_support(){

        add_post_type_support( 'ae_global_templates', 'elementor' );
    }

    

}

PostType::instance();