<?php

namespace ElementorVisionCore\Orchestration;

class ProviderFactory {
    public static function make(string $provider, string $api_key, array $config = []): ProviderClientInterface {
        $provider = strtolower($provider);
        if ($provider === 'openai') { return new OpenAIClient($api_key); }
        $map = [
            'claude' => $config['claude_base_url'] ?? 'https://api.anthropic.com/v1',
            'gemini' => $config['gemini_base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta/openai',
            'deepseek' => $config['deepseek_base_url'] ?? 'https://api.deepseek.com',
            'qwen' => $config['qwen_base_url'] ?? 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1',
            'openrouter' => $config['openrouter_base_url'] ?? 'https://openrouter.ai/api/v1',
        ];
        $base = $map[$provider] ?? ($config['base_url'] ?? 'https://openrouter.ai/api/v1');
        return new GenericOpenAICompatibleClient($api_key, $base);
    }
}
