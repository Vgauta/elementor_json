<?php

namespace ElementorVisionCore\Refinement;

class DifferenceAnalyzer {
    public function analyze(array $diff_metrics): array {
        $ratio = (float) ($diff_metrics['mismatch_ratio'] ?? 0);

        return [
            'spacing_differences' => $this->severity($ratio, 0.04, 0.10),
            'alignment_issues' => $this->severity($ratio, 0.03, 0.08),
            'color_mismatches' => $this->severity($ratio, 0.02, 0.07),
            'typography_differences' => $this->severity($ratio, 0.025, 0.09),
            'raw_metrics' => $diff_metrics,
        ];
    }

    private function severity(float $ratio, float $medium, float $high): string {
        if ($ratio >= $high) {
            return 'high';
        }
        if ($ratio >= $medium) {
            return 'medium';
        }
        return 'low';
    }
}
