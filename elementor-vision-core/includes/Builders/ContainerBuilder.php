<?php

namespace ElementorVisionCore\Builders;

class ContainerBuilder {
    public static function make(array $children = [], array $settings = []): array {
        return [
            'id' => substr(md5(uniqid((string) wp_rand(), true)), 0, 8),
            'elType' => 'container',
            'isInner' => false,
            'settings' => $settings,
            'elements' => $children,
        ];
    }
}
