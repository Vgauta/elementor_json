<?php

namespace ElementorVisionCore\Refinement;

class ScreenshotRenderer {
    public function render(string $url, string $generated_path, array $viewport = ['width' => 1440, 'height' => 2200]): array {
        if ($url === '' || $generated_path === '') {
            return ['ok' => false, 'error' => 'Missing render parameters.'];
        }

        return [
            'ok' => true,
            'generated_url' => $url,
            'generated_screenshot_path' => $generated_path,
            'viewport' => $viewport,
        ];
    }
}
