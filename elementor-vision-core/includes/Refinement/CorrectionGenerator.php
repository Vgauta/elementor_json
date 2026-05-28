<?php

namespace ElementorVisionCore\Refinement;

class CorrectionGenerator {
    public function generate(array $analysis): array {
        $suggestions = [];

        if (($analysis['spacing_differences'] ?? 'low') !== 'low') {
            $suggestions[] = 'Adjust container padding/margins and widget spacing scales (desktop/tablet/mobile).';
        }
        if (($analysis['alignment_issues'] ?? 'low') !== 'low') {
            $suggestions[] = 'Correct flex alignment/justification and verify column widths across breakpoints.';
        }
        if (($analysis['color_mismatches'] ?? 'low') !== 'low') {
            $suggestions[] = 'Update background/text/accent color tokens to match sampled screenshot palette.';
        }
        if (($analysis['typography_differences'] ?? 'low') !== 'low') {
            $suggestions[] = 'Tune font family, font size, line height, and weight for heading/body hierarchy.';
        }

        return [
            'suggestions' => $suggestions,
            'next_iteration_prompt' => $this->build_iteration_prompt($analysis, $suggestions),
        ];
    }

    private function build_iteration_prompt(array $analysis, array $suggestions): string {
        return 'Refinement loop input: Diff analysis=' . wp_json_encode($analysis) . '; apply corrections=' . wp_json_encode($suggestions);
    }
}
