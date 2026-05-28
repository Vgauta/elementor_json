<?php

namespace AIEB\Elementor;

if (! defined('ABSPATH')) {
    exit;
}

class Json_Validator
{
    public function validate(array $json): bool
    {
        if (! isset($json['title'], $json['type'], $json['version'], $json['content']) || ! is_array($json['content'])) {
            return false;
        }

        if ($json['type'] !== 'page') {
            return false;
        }

        foreach ($json['content'] as $element) {
            if (! $this->validate_element($element, false)) {
                return false;
            }
        }

        return true;
    }

    private function validate_element(array $element, bool $is_inner): bool
    {
        if (($element['elType'] ?? '') !== 'container') {
            return false;
        }

        if (! isset($element['id'], $element['settings'], $element['elements']) || ! is_array($element['elements'])) {
            return false;
        }

        if ((bool) ($element['isInner'] ?? false) !== $is_inner) {
            return false;
        }

        foreach ($element['elements'] as $child) {
            $child_type = $child['elType'] ?? '';
            if ($child_type === 'container') {
                if (! $this->validate_element($child, true)) {
                    return false;
                }
                continue;
            }

            if (! $this->validate_widget($child)) {
                return false;
            }
        }

        return true;
    }

    private function validate_widget(array $widget): bool
    {
        if (($widget['elType'] ?? '') !== 'widget') {
            return false;
        }

        if (empty($widget['widgetType']) || ! isset($widget['id'], $widget['settings'])) {
            return false;
        }

        return true;
    }
}
