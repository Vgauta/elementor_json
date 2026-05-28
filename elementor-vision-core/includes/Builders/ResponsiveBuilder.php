<?php

namespace ElementorVisionCore\Builders;

class ResponsiveBuilder {
    public static function with_mobile_overrides(array $settings, array $mobile): array {
        foreach ($mobile as $key => $value) {
            $settings[$key . '_mobile'] = $value;
        }
        return $settings;
    }
}
