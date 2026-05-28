<?php

namespace ElementorVisionCore\Refinement;

class VisualDiffEngine {
    private ScreenshotRenderer $renderer;
    private DifferenceAnalyzer $analyzer;
    private CorrectionGenerator $generator;

    public function __construct(
        ?ScreenshotRenderer $renderer = null,
        ?DifferenceAnalyzer $analyzer = null,
        ?CorrectionGenerator $generator = null
    ) {
        $this->renderer = $renderer ?: new ScreenshotRenderer();
        $this->analyzer = $analyzer ?: new DifferenceAnalyzer();
        $this->generator = $generator ?: new CorrectionGenerator();
    }

    public function run(array $input): array {
        $render = $this->renderer->render(
            (string) ($input['generated_url'] ?? ''),
            (string) ($input['generated_screenshot_path'] ?? ''),
            $input['viewport'] ?? ['width' => 1440, 'height' => 2200]
        );

        if (! ($render['ok'] ?? false)) {
            return $render;
        }

        $cmd_input = [
            'generated_url' => $render['generated_url'],
            'generated_screenshot_path' => $render['generated_screenshot_path'],
            'original_screenshot_path' => (string) ($input['original_screenshot_path'] ?? ''),
            'diff_output_path' => (string) ($input['diff_output_path'] ?? ''),
            'viewport' => $render['viewport'],
            'threshold' => (float) ($input['threshold'] ?? 0.1),
        ];

        $script = escapeshellarg(__DIR__ . '/../../bin/visual_diff.js');
        $json = escapeshellarg(wp_json_encode($cmd_input));
        $output = shell_exec("echo {$json} | node {$script}");
        $metrics = json_decode((string) $output, true);

        if (! is_array($metrics) || ! ($metrics['ok'] ?? false)) {
            return ['ok' => false, 'error' => 'Visual diff script failed.', 'output' => $output];
        }

        $analysis = $this->analyzer->analyze($metrics);
        $corrections = $this->generator->generate($analysis);

        return [
            'ok' => true,
            'metrics' => $metrics,
            'analysis' => $analysis,
            'corrections' => $corrections,
            'iterative_refinement_ready' => true,
        ];
    }
}
