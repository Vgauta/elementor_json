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

            register_rest_route('aieb/v1', '/learned-presets', [
                'methods' => 'GET',
                'permission_callback' => fn() => current_user_can('manage_options'),
                'callback' => fn() => new WP_REST_Response(['presets' => $this->registry->list_learned()]),
            ]);

            register_rest_route('aieb/v1', '/save-preset', [
                'methods' => 'POST',
                'permission_callback' => fn() => current_user_can('manage_options'),
                'callback' => [$this, 'save_preset'],
            ]);

        });
    }

    public function generate(WP_REST_Request $request): WP_REST_Response
    {
        $nonce = (string) $request->get_header('X-WP-Nonce');
        if (! wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_REST_Response(['message' => 'Invalid nonce'], 403);
        }

        if (empty($_FILES['image'])) {
            return new WP_REST_Response(['message' => 'No image uploaded'], 422);
        }

        $file = $_FILES['image'];
        if (! is_array($file) || ! empty($file['error'])) {
            return new WP_REST_Response(['message' => 'Invalid upload'], 422);
        }

        if ((int) ($file['size'] ?? 0) > 8 * 1024 * 1024) {
            return new WP_REST_Response(['message' => 'Image exceeds 8MB limit'], 422);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        $uploaded = wp_handle_upload($file, [
            'test_form' => false,
            'mimes' => [
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
            ],
        ]);

        if (! empty($uploaded['error'])) {
            return new WP_REST_Response(['message' => sanitize_text_field((string) $uploaded['error'])], 422);
        }

        $analysis = $this->vision->analyze_image((string) $uploaded['file']);
        $assembly = $this->assembler->build($analysis);
        $json = $assembly['json'] ?? [];

        if (empty($json)) {
            return new WP_REST_Response([
                'message' => 'Failed to build Elementor JSON',
                'validation' => [
                    'valid' => false,
                    'errors' => $assembly['errors'] ?? ['Unknown assembly error'],
                    'repairs' => $assembly['repairs'] ?? [],
                ],
                'compatibility' => $assembly['compatibility'] ?? [],
            ], 500);
        }

        return new WP_REST_Response([
            'analysis' => $analysis,
            'elementor_json' => $json,
            'validation' => [
                'valid' => (bool) ($assembly['valid'] ?? false),
                'errors' => $assembly['errors'] ?? [],
                'repairs' => $assembly['repairs'] ?? [],
            ],
            'assembly_mode' => $assembly['mode'] ?? 'unknown',
            'compatibility' => $assembly['compatibility'] ?? [],
            'download_name' => 'ai-elementor-template.json',
        ]);
    }


    public function save_preset(WP_REST_Request $request): WP_REST_Response
    {
        $nonce = (string) $request->get_header('X-WP-Nonce');
        if (! wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_REST_Response(['message' => 'Invalid nonce'], 403);
        }

        $page_id = (int) $request->get_param('page_id');
        $category = sanitize_text_field((string) $request->get_param('category'));
        $tags = (array) $request->get_param('tags');
        $pattern = (array) $request->get_param('pattern');

        if ($page_id <= 0) {
            return new WP_REST_Response(['message' => 'Invalid page_id'], 422);
        }

        $raw = get_post_meta($page_id, '_elementor_data', true);
        if (empty($raw)) {
            return new WP_REST_Response(['message' => 'No Elementor data found for page'], 404);
        }

        $template = is_string($raw) ? json_decode($raw, true) : $raw;
        if (! is_array($template)) {
            return new WP_REST_Response(['message' => 'Elementor data is invalid'], 422);
        }

        $saved = $this->registry->save_learned_preset([
            'id' => 'page-' . $page_id . '-' . time(),
            'title' => get_the_title($page_id),
            'category' => $category ?: 'custom',
            'tags' => $tags,
            'pattern' => $pattern,
            'template' => [
                'elType' => 'container',
                'isInner' => false,
                'settings' => ['_title' => get_the_title($page_id)],
                'elements' => $template,
            ],
        ]);

        return new WP_REST_Response(['preset' => $saved], 201);
    }

}
