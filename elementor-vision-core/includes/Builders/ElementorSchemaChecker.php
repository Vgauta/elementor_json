<?php

namespace ElementorVisionCore\Builders;

class ElementorSchemaChecker {
    public const REQUIRED_ROOT_KEYS = ['version', 'title', 'type', 'content', 'page_settings'];
    public const CONTAINER_REQUIRED_KEYS = ['id', 'elType', 'settings', 'elements'];
    public const WIDGET_REQUIRED_KEYS = ['id', 'elType', 'widgetType', 'settings', 'elements'];
    public const SUPPORTED_WIDGETS = ['heading', 'text-editor', 'button', 'image', 'icon-box', 'spacer'];

    public function check_root(array $json): array {
        $errors = [];
        foreach (self::REQUIRED_ROOT_KEYS as $key) {
            if (! array_key_exists($key, $json)) {
                $errors[] = "Missing root key: {$key}";
            }
        }
        if (isset($json['content']) && ! is_array($json['content'])) {
            $errors[] = 'Root key content must be an array.';
        }
        return $errors;
    }

    public function check_node(array $node, bool $inside_container): array {
        $errors = [];
        $type = $node['elType'] ?? '';

        if ($type === 'container') {
            foreach (self::CONTAINER_REQUIRED_KEYS as $key) {
                if (! array_key_exists($key, $node)) {
                    $errors[] = "Container missing key: {$key}";
                }
            }
            if (! $inside_container && ($node['isInner'] ?? false) === true) {
                $errors[] = 'Root container cannot be marked as inner.';
            }
            if (! isset($node['settings']) || ! is_array($node['settings'])) {
                $errors[] = 'Container settings must be an array.';
            }
        } elseif ($type === 'widget') {
            foreach (self::WIDGET_REQUIRED_KEYS as $key) {
                if (! array_key_exists($key, $node)) {
                    $errors[] = "Widget missing key: {$key}";
                }
            }
            if (($node['widgetType'] ?? '') && ! in_array($node['widgetType'], self::SUPPORTED_WIDGETS, true)) {
                $errors[] = 'Unsupported widget type: ' . $node['widgetType'];
            }
            if (! $inside_container) {
                $errors[] = 'Widget found outside of container.';
            }
        } else {
            $errors[] = 'Invalid node elType. Must be container or widget.';
        }

        return $errors;
    }
}
