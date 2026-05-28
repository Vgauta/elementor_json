<?php

namespace ElementorVisionCore\Vision;

class StyleDetector {
    public function detect(array $analysis): array {
        return [
            'typography_hierarchy' => $analysis['typography_hierarchy'] ?? [],
            'spacing' => $analysis['spacing'] ?? [],
            'colors' => $analysis['colors'] ?? [],
            'visual_hierarchy' => $analysis['visual_hierarchy'] ?? [],
        ];
    }
}
