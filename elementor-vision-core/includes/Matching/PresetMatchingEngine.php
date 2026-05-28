<?php

namespace ElementorVisionCore\Matching;

class PresetMatchingEngine {
    private LayoutComparator $comparator;
    private SimilarityScorer $scorer;

    public function __construct(?LayoutComparator $comparator = null, ?SimilarityScorer $scorer = null) {
        $this->comparator = $comparator ?: new LayoutComparator();
        $this->scorer = $scorer ?: new SimilarityScorer();
    }

    public function match(array $layout_map, array $presets, int $limit = 3): array {
        $sections = $layout_map['sections'] ?? [];
        $assembled = [];

        foreach ($sections as $index => $section) {
            $candidates = [];
            foreach ($presets as $preset_id => $preset) {
                $cmp = $this->comparator->compare($section, $preset);
                $score = $this->scorer->score($cmp, $section, $preset);
                $candidates[] = [
                    'preset_id' => $preset_id,
                    'preset_name' => $preset['name'] ?? $preset_id,
                    'category' => $preset['category'] ?? '',
                    'metadata' => $preset['metadata'] ?? [],
                    'confidence' => $score['confidence'],
                    'breakdown' => $score['breakdown'],
                ];
            }

            usort($candidates, static function ($a, $b) {
                return $b['confidence'] <=> $a['confidence'];
            });

            $top = array_slice($candidates, 0, $limit);
            $assembled[] = [
                'section_index' => $index,
                'detected_section' => $section,
                'best_matches' => $top,
                'selected_preset' => $top[0] ?? null,
            ];
        }

        return [
            'sections_matched' => $assembled,
            'strategy' => 'Assemble final layout using selected_preset per detected section instead of raw structure generation.',
        ];
    }
}
