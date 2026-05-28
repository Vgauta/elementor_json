<?php

namespace ElementorVisionCore\Presets;

class PresetMatcher {
    public function by_category(array $presets, string $category): array {
        return array_filter($presets, static function ($preset) use ($category) {
            return ($preset['category'] ?? '') === $category;
        });
    }

    public function by_tags(array $presets, array $tags): array {
        return array_filter($presets, static function ($preset) use ($tags) {
            $preset_tags = $preset['metadata']['tags'] ?? [];
            return count(array_intersect($tags, $preset_tags)) > 0;
        });
    }
}
