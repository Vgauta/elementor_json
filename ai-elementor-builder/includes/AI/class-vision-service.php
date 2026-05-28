<?php

namespace AIEB\AI;

if (! defined('ABSPATH')) {
    exit;
}

class Vision_Service
{
    public function analyze_image(string $image_path): array
    {
        $api_key = (string) get_option('aieb_openai_api_key', '');
        if ($api_key === '' || ! file_exists($image_path)) {
            return $this->fallback_structure();
        }

        $binary = file_get_contents($image_path);
        if ($binary === false) {
            return $this->fallback_structure();
        }

        $image_data = base64_encode($binary);
        $mime = wp_check_filetype($image_path)['type'] ?? 'image/png';

        $payload = [
            'model' => 'gpt-4.1-mini',
            'input' => [[
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => 'Analyze website screenshot into structured JSON with sections[]. Each section requires type, layout, style(background_color optional), elements[]. Keep semantics only, no Elementor raw schema.',
                    ],
                    ['type' => 'input_image', 'image_url' => 'data:' . $mime . ';base64,' . $image_data],
                ],
            ]],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'layout_analysis',
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'page_title' => ['type' => 'string'],
                            'sections' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'type' => ['type' => 'string'],
                                        'layout' => ['type' => 'string'],
                                        'style' => ['type' => 'object'],
                                        'elements' => ['type' => 'array'],
                                    ],
                                    'required' => ['type', 'layout', 'elements'],
                                ],
                            ],
                        ],
                        'required' => ['sections'],
                    ],
                ],
            ],
        ];

        $response = wp_remote_post('https://api.openai.com/v1/responses', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($payload),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            return $this->fallback_structure();
        }

        $status = wp_remote_retrieve_response_code($response);
        if ($status < 200 || $status >= 300) {
            return $this->fallback_structure();
        }

        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        $text = $body['output'][0]['content'][0]['text'] ?? '';
        $parsed = json_decode((string) $text, true);

        return is_array($parsed) && isset($parsed['sections']) ? $parsed : $this->fallback_structure();
    }

    private function fallback_structure(): array
    {
        return [
            'page_title' => 'AI Generated Page',
            'sections' => [
                ['type' => 'hero', 'layout' => '2-column', 'style' => ['background_color' => '#111827'], 'elements' => ['heading', 'text', 'button', 'image']],
                ['type' => 'services', 'layout' => 'grid', 'style' => ['background_color' => '#FFFFFF'], 'elements' => ['cards']],
                ['type' => 'stats', 'layout' => 'inline', 'style' => ['background_color' => '#F8FAFC'], 'elements' => ['counter', 'counter', 'counter']],
                ['type' => 'footer', 'layout' => 'stack', 'style' => ['background_color' => '#0B1020'], 'elements' => ['links', 'copyright']],
            ],
        ];
    }
}
