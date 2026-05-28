<?php

namespace ElementorVisionCore\Builders;

class StyleBuilder {
    public static function color(string $value): array {
        return ['color' => $value];
    }

    public static function typography(string $family = 'Inter', int $size = 16, int $weight = 400, float $line_height = 1.5): array {
        return [
            'typography_typography' => 'custom',
            'typography_font_family' => $family,
            'typography_font_size' => ['unit' => 'px', 'size' => $size, 'sizes' => []],
            'typography_font_weight' => (string) $weight,
            'typography_line_height' => ['unit' => 'em', 'size' => $line_height, 'sizes' => []],
        ];
    }

    public static function spacing(int $top, int $right, int $bottom, int $left, string $unit = 'px'): array {
        return [
            'unit' => $unit,
            'top' => (string) $top,
            'right' => (string) $right,
            'bottom' => (string) $bottom,
            'left' => (string) $left,
            'isLinked' => false,
        ];
    }
}
