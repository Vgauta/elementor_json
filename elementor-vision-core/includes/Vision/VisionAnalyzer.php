<?php

namespace ElementorVisionCore\Vision;

class VisionAnalyzer {
    private AiProviderInterface $provider;
    private LayoutExtractor $extractor;

    public function __construct(AiProviderInterface $provider, ?LayoutExtractor $extractor = null) {
        $this->provider = $provider;
        $this->extractor = $extractor ?: new LayoutExtractor();
    }

    public function analyze(string $image_data_url): array {
        $prompt = $this->build_prompt();
        $result = $this->provider->analyze_image($image_data_url, $prompt);

        if (! ($result['ok'] ?? false)) {
            return [
                'ok' => false,
                'error' => $result['error'] ?? 'Unknown provider error.',
                'provider_details' => $result['details'] ?? null,
            ];
        }

        $layout_map = $this->extractor->extract($result['data']);

        return [
            'ok' => true,
            'layout_map' => $layout_map,
            'analysis' => $result['data'],
        ];
    }

    private function build_prompt(): string {
        return 'Analyze this website screenshot and return JSON only with keys: sections, containers, typography_hierarchy, buttons, cards, grids, spacing, colors, visual_hierarchy. '
            . 'For each section include: type, layout, style, confidence. '
            . 'Detect hero/services/stats/cta/footer/testimonials where present.';
    }
}
