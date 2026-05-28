<?php

namespace ElementorVisionCore\Matching;

class LayoutComparator {
    public function compare(array $analyzed_section, array $preset): array {
        $preset_category = strtolower((string) ($preset['category'] ?? ''));
        $analyzed_type = strtolower((string) ($analyzed_section['type'] ?? ''));

        $layout_a = strtolower((string) ($analyzed_section['layout'] ?? ''));
        $layout_b = strtolower((string) ($preset['metadata']['layout_type'] ?? ''));

        $style_a = strtolower((string) ($analyzed_section['style'] ?? ''));
        $style_b = strtolower((string) ($preset['metadata']['style'] ?? ''));

        return [
            'category_match' => $analyzed_type !== '' && $preset_category === $analyzed_type ? 1.0 : 0.0,
            'layout_match' => $this->string_similarity($layout_a, $layout_b),
            'style_match' => $this->string_similarity($style_a, $style_b),
        ];
    }

    private function string_similarity(string $a, string $b): float {
        if ($a === '' || $b === '') {
            return 0.0;
        }
        if ($a === $b) {
            return 1.0;
        }
        similar_text($a, $b, $percent);
        return max(0.0, min(1.0, $percent / 100));
    }
}
