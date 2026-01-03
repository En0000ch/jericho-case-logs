<?php

namespace BP3D\Base;

class PostTypePreset
{

    protected $post_type = 'bp3d-preset';
    protected $import_ver = '1.0.0';

    public function register()
    {
        add_action('init', [$this, 'registerPostType'], 1);

        if (is_admin()) {
            // add_action('manage_' . $this->post_type . '_posts_custom_column', [$this, 'addShortcodeColumn'], 10, 2);
            // add_action('manage_' . $this->post_type . '_posts_columns', [$this, 'addShortcodeColumnContent'], 10, 2);
            add_filter('post_updated_messages', [$this, 'changeUpdateMessage']);
            add_action('admin_head-post.php', [$this, 'bp3d_hide_publishing_actions']);
            add_action('admin_head-post-new.php', [$this, 'bp3d_hide_publishing_actions']);
            add_filter('gettext', [$this, 'bp3d_change_publish_button'], 10, 2);
            add_filter('post_row_actions', [$this, 'bp3d_remove_row_actions'], 10, 2);

            // force gutenberg here
            // add_action('use_block_editor_for_post', [$this, 'use_block_editor_for_post'], 999, 2);
            add_filter('filter_block_editor_meta_boxes', [$this, 'remove_metabox']);
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
        $messages[$this->post_type][1] = __('Preset Updated', 'model-viewer');
        return $messages;
    }


    public function registerPostType()
    {
        register_post_type(
            $this->post_type,
            array(
                'labels' => array(
                    'name'           => __('Presets', 'model-viewer'),
                    'menu_name'      => __('Preset', 'model-viewer'),
                    'name_admin_bar' => __('Preset', 'model-viewer'),
                    'add_new'        => __('Add New', 'model-viewer'),
                    'add_new_item'   => __('Add New ', 'model-viewer'),
                    'new_item'       => __('New Preset ', 'model-viewer'),
                    'edit_item'      => __('Edit Preset ', 'model-viewer'),
                    'view_item'      => __('View Preset ', 'model-viewer'),
                    'all_items'      => __('Presets', 'model-viewer'),
                    'not_found'      => __('Sorry, we couldn\'t find the Feed you are looking for.', 'model-viewer'),
                ),
                'description'     => __('Preset Options.', 'model-viewer'),
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => '3d-viewer',
                'menu_icon'       => 'dashicons-format-image',
                'query_var'       => true,
                'rewrite'         => array('slug' => '3d-viewer-template'),
                'capability_type' => 'post',
                'has_archive'     => false,
                'hierarchical'    => false,
                'menu_position'   => 2,
                'supports'        => array('title', 'editor'),
                'show_in_rest'    => true,
                'template'        => [
                    ['b3dviewer/preset']
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
