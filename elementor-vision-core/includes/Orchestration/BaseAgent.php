<?php

namespace ElementorVisionCore\Orchestration;

abstract class BaseAgent implements AgentInterface {
    protected function ask_model(string $system, string $user, ProviderClientInterface $client, array $config = []): array {
        $messages = [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => $user],
        ];
        $res = $client->generate($messages, $config);
        if (! ($res['ok'] ?? false)) { return ['ok' => false, 'error' => $res['error'] ?? 'Model error']; }
        $json = json_decode((string) ($res['text'] ?? ''), true);
        if (! is_array($json)) { return ['ok' => false, 'error' => 'Agent response not JSON', 'raw' => $res['text'] ?? '']; }
        return ['ok' => true, 'data' => $json];
    }
}
