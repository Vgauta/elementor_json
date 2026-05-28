<?php

namespace AIEB\Templates;

if (! defined('ABSPATH')) {
    exit;
}

class Template_Registry
{
    public function __construct(private string $presets_dir)
    {
    }

    public function list(): array
    {
        $files = glob($this->presets_dir . '/*/*.json') ?: [];
        return array_map(static fn($file) => basename($file), $files);
    }

    public function match_section(array $section): string
    {
        $map = [
            'hero' => 'dark-premium/hero-dark-01.json',
            'stats' => 'minimal-modern/stats-inline-02.json',
            'services' => 'agency/services-grid-03.json',
            'footer' => 'corporate/footer-premium-01.json',
        ];

        return $this->presets_dir . '/' . ($map[$section['type'] ?? ''] ?? 'saas/hero-saas-01.json');
    }

    public function get_template(string $path): array
    {
        $raw = file_get_contents($path);
        $json = json_decode((string) $raw, true);
        return is_array($json) ? $json : [];
    }
}
