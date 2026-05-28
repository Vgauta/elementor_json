<?php

namespace ElementorVisionCore\Vision;

class LayoutExtractor {
    private SectionDetector $section_detector;
    private StyleDetector $style_detector;

    public function __construct(?SectionDetector $section_detector = null, ?StyleDetector $style_detector = null) {
        $this->section_detector = $section_detector ?: new SectionDetector();
        $this->style_detector = $style_detector ?: new StyleDetector();
    }

    public function extract(array $analysis): array {
        $sections = $this->section_detector->detect($analysis);
        $styles = $this->style_detector->detect($analysis);

        return [
            'sections' => $sections,
            'containers' => $analysis['containers'] ?? [],
            'buttons' => $analysis['buttons'] ?? [],
            'cards' => $analysis['cards'] ?? [],
            'grids' => $analysis['grids'] ?? [],
            'styles' => $styles,
        ];
    }
}
