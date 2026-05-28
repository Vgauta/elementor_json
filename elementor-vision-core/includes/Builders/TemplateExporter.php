<?php

namespace ElementorVisionCore\Builders;

class TemplateExporter {
    public function export(array $content, string $title): string {
        $payload = [
            'version' => '0.4',
            'title' => $title,
            'type' => 'page',
            'content' => $content,
            'page_settings' => [],
        ];

        return wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
