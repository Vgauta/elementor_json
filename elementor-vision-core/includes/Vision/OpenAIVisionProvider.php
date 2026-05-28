<?php

namespace ElementorVisionCore\Vision;

class OpenAIVisionProvider implements AiProviderInterface {
    private string $api_key;
    private string $model;

    public function __construct(string $api_key, string $model = 'gpt-4.1-mini') {
        $this->api_key = $api_key;
        $this->model = $model;
    }

    public function analyze_image(string $image_data_url, string $prompt): array {
        if ($this->api_key === '') {
            return ['ok' => false, 'error' => 'Missing OpenAI API key.'];
        }

        $body = [
            'model' => $this->model,
            'input' => [[
                'role' => 'user',
                'content' => [
                    ['type' => 'input_text', 'text' => $prompt],
                    ['type' => 'input_image', 'image_url' => $image_data_url],
                ],
            ]],
            'text' => ['format' => ['type' => 'json_object']],
        ];

        $response = wp_remote_post('https://api.openai.com/v1/responses', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($body),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            return ['ok' => false, 'error' => $response->get_error_message()];
        }

        $status = wp_remote_retrieve_response_code($response);
        $raw = wp_remote_retrieve_body($response);
        $json = json_decode($raw, true);

        if ($status < 200 || $status >= 300) {
            return ['ok' => false, 'error' => 'OpenAI API error', 'details' => $json ?: $raw];
        }

        $text = $json['output'][0]['content'][0]['text'] ?? '';
        $parsed = json_decode($text, true);
        if (! is_array($parsed)) {
            return ['ok' => false, 'error' => 'Invalid JSON returned by vision model.', 'raw_text' => $text];
        }

        return ['ok' => true, 'data' => $parsed, 'raw' => $json];
    }
}
