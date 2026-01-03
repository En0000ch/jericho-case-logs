<?php

namespace BP3D\Base;

class ChoosePreferredEditor
{
    public function register()
    {
        add_action('admin_menu', [$this, 'adminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_bp3d_save_preferred_editor', [$this, 'bp3d_save_preferred_editor']);
    }

    public function adminMenu()
    {
        add_submenu_page('hide', 'Choose Preferred Editor', 'Choose Preferred Editor', 'manage_options', 'bp3d-choose-preferred-editor', array($this, 'choose_preferred_editor'));
    }



    public function choose_preferred_editor()
    {
?>
        <div class="" id="bp3d-choose-preferred-editor"></div>
<?php
    }


    public function enqueue_scripts($hook)
    {
        if ($hook === 'admin_page_bp3d-choose-preferred-editor') {
            wp_enqueue_script('bp3d-choose-preferred-editor', BP3D_DIR . 'build/choose-preferred-editor/index.js', array('react', 'react-dom', 'wp-util'), BP3D_VERSION, true);
            wp_enqueue_script('bp3d-tailwind', 'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4', array(), BP3D_VERSION, true);
            wp_enqueue_style('bp3d-choose-preferred-editor', BP3D_DIR . 'build/choose-preferred-editor/index.css', array(), BP3D_VERSION, 'all');

            wp_localize_script('bp3d-choose-preferred-editor', 'bp3d_choose_preferred_editor', [
                'nonce' => wp_create_nonce('bp3d_security_key'),
            ]);
        }
    }

    public function bp3d_save_preferred_editor()
    {
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_security_code'])), 'bp3d_security_key')) {
            wp_send_json_error('invalid request');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Invalid Authorization');
        }

        $editor = sanitize_text_field(wp_unslash($_POST['editor']));

        $options = get_option('_bp3d_settings_', []);
        $options['gutenberg_enabled'] = $editor === 'gutenberg' ? '1' : '0';
        update_option('_bp3d_settings_', $options);

        wp_send_json_success(['status' => 'success']);
    }
}
