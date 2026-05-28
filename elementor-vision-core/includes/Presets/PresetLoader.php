<?php

namespace ElementorVisionCore\Presets;

class PresetLoader {
    public function payload(array $preset): array {
        return [
            'version' => '0.4',
            'title' => $preset['name'] ?? 'Elementor Vision Preset',
            'type' => 'page',
            'content' => $preset['content'] ?? [],
            'page_settings' => [],
        ];
    }
}
