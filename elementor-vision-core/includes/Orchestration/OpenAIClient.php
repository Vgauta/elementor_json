<?php

namespace ElementorVisionCore\Orchestration;

class OpenAIClient implements ProviderClientInterface {
    private string $api_key;
    public function __construct(string $api_key) { $this->api_key = $api_key; }
    public function generate(array $messages, array $config = []): array {
        $body = ['model' => $config['model'] ?? 'gpt-4.1-mini', 'input' => $messages];
        return HttpProviderClient::post_json('https://api.openai.com/v1/responses', $this->api_key, $body, 'output.0.content.0.text');
    }
}
