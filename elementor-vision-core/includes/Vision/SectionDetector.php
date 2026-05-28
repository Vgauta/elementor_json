<?php

namespace ElementorVisionCore\Vision;

class SectionDetector {
    public function detect(array $analysis): array {
        $sections = $analysis['sections'] ?? [];
        return is_array($sections) ? $sections : [];
    }
}
