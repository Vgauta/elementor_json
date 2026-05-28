<?php

namespace ElementorVisionCore\Matching;

class SimilarityScorer {
    public function score(array $comparison, array $analyzed_section, array $preset): array {
        $spacing_match = $this->spacing_match($analyzed_section, $preset);

        $score =
            ($comparison['category_match'] * 0.35) +
            ($comparison['layout_match'] * 0.30) +
            ($comparison['style_match'] * 0.20) +
            ($spacing_match * 0.15);

        $confidence = round(max(0, min(1, $score)), 4);

        return [
            'confidence' => $confidence,
            'breakdown' => [
                'category' => round($comparison['category_match'], 4),
                'layout' => round($comparison['layout_match'], 4),
                'style' => round($comparison['style_match'], 4),
                'spacing' => round($spacing_match, 4),
            ],
        ];
    }

    private function spacing_match(array $analyzed_section, array $preset): float {
        $a = strtolower((string) ($analyzed_section['spacing'] ?? ''));
        $tags = array_map('strtolower', $preset['metadata']['tags'] ?? []);
        if ($a === '') {
            return 0.0;
        }
        foreach ($tags as $tag) {
            if (strpos($tag, $a) !== false || strpos($a, $tag) !== false) {
                return 1.0;
            }
        }
        return 0.25;
    }
}
