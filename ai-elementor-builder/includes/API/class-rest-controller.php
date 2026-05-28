<?php

namespace AIEB\API;

use AIEB\AI\Vision_Service;
use AIEB\Elementor\Json_Assembler;
use AIEB\Templates\Template_Registry;
use WP_REST_Request;
use WP_REST_Response;

if (! defined('ABSPATH')) {
    exit;
}

class Rest_Controller
{
    public function __construct(
        private Vision_Service $vision,
        private Json_Assembler $assembler,
        private Template_Registry $registry
    ) {
    }

    public function register(): void
    {
        add_action('rest_api_init', function (): void {
            register_rest_route('aieb/v1', '/generate', [
                'methods' => 'POST',
                'permission_callback' => fn() => current_user_can('manage_options'),
                'callback' => [$this, 'generate'],
            ]);

            register_rest_route('aieb/v1', '/templates', [
                'methods' => 'GET',
                'permission_callback' => fn() => current_user_can('manage_options'),
                'callback' => fn() => new WP_REST_Response(['templates' => $this->registry->list()]),
            ]);
        });
    }

    public function generate(WP_REST_Request $request): WP_REST_Response
    {
        $nonce = $request->get_header('X-WP-Nonce');
        if (! wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_REST_Response(['message' => 'Invalid nonce'], 403);
        }

        if (empty($_FILES['image'])) {
            return new WP_REST_Response(['message' => 'No image uploaded'], 422);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        $uploaded = wp_handle_upload($_FILES['image'], ['test_form' => false]);

        if (! empty($uploaded['error'])) {
            return new WP_REST_Response(['message' => $uploaded['error']], 422);
        }

        $analysis = $this->vision->analyze_image($uploaded['file']);
        $json = $this->assembler->build($analysis);

        return new WP_REST_Response([
            'analysis' => $analysis,
            'elementor_json' => $json,
            'download_name' => 'ai-elementor-template.json',
        ]);
    }
}
