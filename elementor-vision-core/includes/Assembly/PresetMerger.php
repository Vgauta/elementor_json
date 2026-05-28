<?php

namespace ElementorVisionCore\Assembly;

class PresetMerger {
    public function merge(array $preset_payloads): array {
        $content = [];
        foreach ($preset_payloads as $payload) {
            if (! is_array($payload) || ! isset($payload['content']) || ! is_array($payload['content'])) {
                continue;
            }
            foreach ($payload['content'] as $node) {
                $content[] = $node;
            }
        }

        return [
            'version' => '0.4',
            'title' => 'Elementor Vision Assembled Template',
            'type' => 'page',
            'content' => $content,
            'page_settings' => [],
        ];
    }
}
