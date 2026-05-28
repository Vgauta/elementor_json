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

        if ($api_key === '') {
            return $this->fallback_structure();
        }

        $image_data = base64_encode((string) file_get_contents($image_path));
        $mime = wp_check_filetype($image_path)['type'] ?? 'image/png';

        $payload = [
            'model' => 'gpt-4.1-mini',
            'input' => [[
                'role' => 'user',
                'content' => [
                    ['type' => 'input_text', 'text' => 'Analyze this website screenshot into sections, layout patterns, styles and components. Return strict JSON with key: sections.'],
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
                            'sections' => ['type' => 'array'],
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

        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        $text = $body['output'][0]['content'][0]['text'] ?? '';
        $parsed = json_decode($text, true);

        return is_array($parsed) ? $parsed : $this->fallback_structure();
    }

    private function fallback_structure(): array
    {
        return [
            'sections' => [
                ['type' => 'hero', 'layout' => '2-column', 'style' => 'dark-premium', 'elements' => ['heading', 'text', 'button', 'image']],
                ['type' => 'stats', 'layout' => 'inline', 'style' => 'minimal-modern', 'elements' => ['counter', 'counter', 'counter']],
                ['type' => 'footer', 'layout' => '3-column', 'style' => 'corporate', 'elements' => ['links', 'contact', 'social']],
            ],
        ];
    }
}
