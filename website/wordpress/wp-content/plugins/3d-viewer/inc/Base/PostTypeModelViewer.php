<?php

namespace BP3D\Base;

class PostTypeModelViewer
{

    protected $post_type = 'bp3d-model-viewer';
    protected $import_ver = '1.0.0';

    public function register()
    {
        add_action('init', [$this, 'registerPostType'], 0);

        if (is_admin()) {
            add_action('manage_' . $this->post_type . '_posts_custom_column', [$this, 'addShortcodeColumn'], 10, 2);
            add_action('manage_' . $this->post_type . '_posts_columns', [$this, 'addShortcodeColumnContent'], 10, 2);
            add_filter('post_updated_messages', [$this, 'changeUpdateMessage']);
            add_action('admin_head-post.php', [$this, 'bp3d_hide_publishing_actions']);
            add_action('admin_head-post-new.php', [$this, 'bp3d_hide_publishing_actions']);
            add_filter('gettext', [$this, 'bp3d_change_publish_button'], 10, 2);
            add_action('edit_form_after_title', [$this, 'bp3d_shortcode_area']);
            add_filter('post_row_actions', [$this, 'bp3d_remove_row_actions'], 10, 2);
            add_action('admin_init', [$this, 'set_meta_data']);


            // force gutenberg here
            add_action('use_block_editor_for_post', [$this, 'use_block_editor_for_post'], 999, 2);
            add_filter('filter_block_editor_meta_boxes', [$this, 'remove_metabox']);
        }
    }

    // set isGutenberg = false for old previously created shortcode
    function set_meta_data()
    {
        if (get_option('model_viewer_import_ver', '0') < $this->import_ver) {
            $modelViewer = new \WP_Query(array(
                'post_type' => $this->post_type,
                'post_status' => 'any',
                'posts_per_page' => -1
            ));

            while ($modelViewer->have_posts()) {
                $modelViewer->the_post();
                $id = get_the_ID();
                if (!get_post_meta($id, 'isGutenberg', true)) {
                    update_post_meta($id, 'isGutenberg', false);
                }
            };
            update_option('model_viewer_import_ver', $this->import_ver);
        }
    }

    public function use_block_editor_for_post($use, $post)
    {
        $option =  get_option('_bp3d_settings_', []);
        $gutenberg = $option['gutenberg_enabled'] ?? false;
        $isGutenberg = (bool) get_post_meta($post->ID, 'isGutenberg', true);

        if ($this->post_type === $post->post_type) {
            if ($gutenberg && $post->post_status === 'auto-draft') {
                update_post_meta($post->ID, 'isGutenberg', true);
                return true;
            } else if ($isGutenberg) {
                return true;
            } else {
                remove_post_type_support($this->post_type, 'editor');
                return false;
            }
        }

        return $use;
    }

    public function changeUpdateMessage($messages)
    {
        $messages[$this->post_type][1] = __('Model Updated', 'model-viewer');
        return $messages;
    }

    public function addShortcodeColumnContent($defaults)
    {
        unset($defaults['date']);
        $defaults['shortcode'] = 'ShortCode';
        $defaults['date'] = 'Date';
        return $defaults;
    }

    public function addShortcodeColumn($column_name, $post_ID)
    {
        if ($column_name === 'shortcode') {
            echo "<div onclick='this.select()' class='b3dviewer_front_shortcode'><input value='[3d_viewer id=" . esc_attr($post_ID) . "]' ><span class='htooltip'>" . esc_html__("Copy To Clipboard", "model-viewer") . "</span></div>";
        }
    }

    public function registerPostType()
    {
        register_post_type(
            $this->post_type,
            array(
                'labels' => array(
                    'name'           => __('3D Viewer', 'model-viewer'),
                    'menu_name'      => __('3D Viewer', 'model-viewer'),
                    'name_admin_bar' => __('3D Viewer', 'model-viewer'),
                    'add_new'        => __('Add New', 'model-viewer'),
                    'add_new_item'   => __('Add New ', 'model-viewer'),
                    'new_item'       => __('New 3D Viewer ', 'model-viewer'),
                    'edit_item'      => __('Edit 3D Viewer ', 'model-viewer'),
                    'view_item'      => __('View 3D Viewer ', 'model-viewer'),
                    'all_items'      => __('3D Viewers', 'model-viewer'),
                    'not_found'      => __('Sorry, we couldn\'t find the Feed you are looking for.', 'model-viewer'),
                ),
                'description'     => __('3D Viewer Options.', 'model-viewer'),
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => '3d-viewer',
                'menu_icon'       => 'dashicons-format-image',
                'query_var'       => true,
                'rewrite'         => array('slug' => 'model-viewer'),
                'capability_type' => 'post',
                'has_archive'     => false,
                'hierarchical'    => false,
                'menu_position'   => 1,
                'supports'        => array('title', 'editor'),
                'show_in_rest'    => true,
                'template'        => [
                    ['b3dviewer/modelviewer']
                ],
                'template_lock' => 'all',
            )
        );
    }

    // HIDE everything in PUBLISH metabox except Move to Trash & PUBLISH button
    public function bp3d_hide_publishing_actions()
    {
        global  $post;
        if ($post->post_type == $this->post_type) {
            echo  ' <style type="text/css">
                    #misc-publishing-actions,
                    #minor-publishing-actions{
                        display:none;
                    } </style> ';
        }
    }

    public function bp3d_change_publish_button($translation, $text)
    {
        if ($this->post_type == get_post_type()) {
            if ($text == 'Publish') {
                return 'Save';
            }
        }
        return $translation;
    }

    /*-------------------------------------------------------------------------------*/
    /* Shortcode Generator area  .
       /*-------------------------------------------------------------------------------*/

    public function bp3d_shortcode_area()
    {
        global  $post;

        if ($post->post_type == 'bp3d-model-viewer') { ?>
            <div class="bp3d_shortcode">
                <div class="shortcode-heading">
                    <div class="icon"><span class="dashicons dashicons-shortcode"></span> <?php
                                                                                            echo esc_html(__("SHORTCODE", "model-viewer")); ?></div>
                    <div class="text"> <a href="https://bplugins.com/support/" target="_blank"><?php
                                                                                                echo esc_html(__("Supports", "model-viewer")); ?></a></div>
                </div>
                <div class="shortcode-left">
                    <h3><?php echo esc_html(__("Shortcode", "model-viewer")); ?></h3>
                    <p><?php echo esc_html(__("Copy and paste this shortcode into your posts, pages and widget:", "model-viewer")); ?></p>
                    <div class="shortcode" selectable>[3d_viewer id="<?php echo  esc_attr($post->ID); ?>"]</div>
                </div>
                <div class="shortcode-right">
                    <h3><?php echo esc_html(__("Template Include", "model-viewer")); ?></h3>
                    <p><?php echo esc_html(__("Copy and paste the PHP code into your template file:", "model-viewer")); ?></p>
                    <div class="shortcode">&lt;?php echo do_shortcode('[3d_viewer id="<?php echo esc_html($post->ID); ?>"]'); ?&gt;</div>
                </div>
            </div>
<?php
        }
    }

    // Hide & Disabled View, Quick Edit and Preview Button
    public function bp3d_remove_row_actions($idtions)
    {
        global  $post;
        if ($post->post_type == 'bp3d-model-viewer') {
            unset($idtions['view']);
            unset($idtions['inline hide-if-no-js']);
        }
        return $idtions;
    }

    function remove_metabox($metaboxs)
    {
        global $post;
        $screen = get_current_screen();

        if ($screen->post_type === $this->post_type) {
            return false;
        }
        return $metaboxs;
    }
}
