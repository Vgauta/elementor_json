<?php

namespace ElementorVisionCore\Builders;

class WidgetBuilder {
    public static function make(string $type, array $settings = []): array {
        return [
            'id' => substr(md5(uniqid((string) wp_rand(), true)), 0, 8),
            'elType' => 'widget',
            'widgetType' => $type,
            'settings' => $settings,
            'elements' => [],
        ];
    }
}
