<?php

namespace AIEB\Admin;

if (! defined('ABSPATH')) {
    exit;
}

class Admin_Page
{
    public function register(): void
    {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function add_menu(): void
    {
        add_menu_page(
            __('AI Elementor Builder', 'ai-elementor-builder'),
            __('AI Elementor Builder', 'ai-elementor-builder'),
            'manage_options',
            'ai-elementor-builder',
            [$this, 'render'],
            'dashicons-layout',
            60
        );
    }

    public function enqueue(string $hook): void
    {
        if ($hook !== 'toplevel_page_ai-elementor-builder') {
            return;
        }

        wp_enqueue_style('aieb-admin', AIEB_PLUGIN_URL . 'assets/css/admin.css', [], AIEB_VERSION);
        wp_enqueue_script('aieb-admin', AIEB_PLUGIN_URL . 'assets/js/admin.js', ['wp-element'], AIEB_VERSION, true);

        wp_localize_script('aieb-admin', 'AIEB_CONFIG', [
            'restUrl' => esc_url_raw(rest_url('aieb/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    public function render(): void
    {
        echo '<div class="wrap"><div id="aieb-admin-root"></div></div>';
    }
}
