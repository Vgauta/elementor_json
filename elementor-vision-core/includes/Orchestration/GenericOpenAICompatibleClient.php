<?php

namespace ElementorVisionCore\Orchestration;

class GenericOpenAICompatibleClient implements ProviderClientInterface {
    private string $api_key; private string $base_url;
    public function __construct(string $api_key, string $base_url) { $this->api_key = $api_key; $this->base_url = rtrim($base_url, '/'); }
    public function generate(array $messages, array $config = []): array {
        $body = ['model' => $config['model'] ?? 'default', 'messages' => $messages, 'temperature' => $config['temperature'] ?? 0.2];
        return HttpProviderClient::post_json($this->base_url . '/chat/completions', $this->api_key, $body, 'choices.0.message.content');
    }
}
