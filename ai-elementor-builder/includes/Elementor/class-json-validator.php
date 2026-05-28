<?php

namespace AIEB\Elementor;

if (! defined('ABSPATH')) {
    exit;
}

class Json_Validator
{
    public function validate_and_repair(array $json): array
    {
        $errors = [];
        $repairs = [];

        if (($json['type'] ?? '') !== 'page') {
            $errors[] = 'Document type must be page.';
            $json['type'] = 'page';
            $repairs[] = 'Set document type to page.';
        }

        if (! isset($json['title']) || ! is_string($json['title'])) {
            $errors[] = 'Missing or invalid title.';
            $json['title'] = 'AI Generated Template';
            $repairs[] = 'Injected default title.';
        }

        if (! isset($json['version']) || ! is_string($json['version'])) {
            $errors[] = 'Missing or invalid version.';
            $json['version'] = '0.4';
            $repairs[] = 'Injected default version 0.4.';
        }

        if (! isset($json['page_settings']) || ! is_array($json['page_settings'])) {
            $errors[] = 'Missing page_settings.';
            $json['page_settings'] = [];
            $repairs[] = 'Injected empty page_settings.';
        }

        if (! isset($json['content']) || ! is_array($json['content'])) {
            $errors[] = 'Missing content array.';
            $json['content'] = [];
            $repairs[] = 'Injected empty content array.';
        }

        $normalized_content = [];
        foreach ($json['content'] as $index => $element) {
            if (! is_array($element)) {
                $errors[] = "Content index {$index} is not an object.";
                continue;
            }

            $result = $this->repair_container($element, false, "content[$index]");
            $errors = array_merge($errors, $result['errors']);
            $repairs = array_merge($repairs, $result['repairs']);

            if ($result['valid']) {
                $normalized_content[] = $result['node'];
            }
        }

        $json['content'] = $normalized_content;

        $valid = empty($errors) || ! empty($normalized_content);

        return [
            'valid' => $valid,
            'repaired_json' => $json,
            'errors' => array_values(array_unique($errors)),
            'repairs' => array_values(array_unique($repairs)),
        ];
    }

    private function repair_container(array $node, bool $is_inner, string $path): array
    {
        $errors = [];
        $repairs = [];

        if (($node['elType'] ?? '') !== 'container') {
            $errors[] = "{$path}: elType must be container.";
            $node['elType'] = 'container';
            $repairs[] = "{$path}: converted elType to container.";
        }

        if (empty($node['id']) || ! is_string($node['id'])) {
            $node['id'] = $this->element_id();
            $errors[] = "{$path}: missing id.";
            $repairs[] = "{$path}: generated container id.";
        }

        if (($node['isInner'] ?? null) !== $is_inner) {
            $node['isInner'] = $is_inner;
            $errors[] = "{$path}: invalid isInner flag.";
            $repairs[] = "{$path}: normalized isInner to " . ($is_inner ? 'true' : 'false') . '.';
        }

        if (! isset($node['settings']) || ! is_array($node['settings'])) {
            $node['settings'] = [];
            $errors[] = "{$path}: missing settings object.";
            $repairs[] = "{$path}: injected empty settings.";
        }

        $node['settings'] = $this->normalize_responsive_settings($node['settings'], $path, $errors, $repairs);

        if (! isset($node['elements']) || ! is_array($node['elements'])) {
            $node['elements'] = [];
            $errors[] = "{$path}: missing elements array.";
            $repairs[] = "{$path}: injected empty elements array.";
        }

        $normalized_children = [];
        foreach ($node['elements'] as $child_index => $child) {
            $child_path = "{$path}.elements[$child_index]";
            if (! is_array($child)) {
                $errors[] = "{$child_path}: child is not object.";
                continue;
            }

            if (($child['elType'] ?? '') === 'container') {
                $child_result = $this->repair_container($child, true, $child_path);
                $errors = array_merge($errors, $child_result['errors']);
                $repairs = array_merge($repairs, $child_result['repairs']);
                if ($child_result['valid']) {
                    $normalized_children[] = $child_result['node'];
                }
                continue;
            }

            $widget_result = $this->repair_widget($child, $child_path);
            $errors = array_merge($errors, $widget_result['errors']);
            $repairs = array_merge($repairs, $widget_result['repairs']);
            if ($widget_result['valid']) {
                $normalized_children[] = $widget_result['node'];
            }
        }

        $node['elements'] = $normalized_children;

        return ['valid' => true, 'node' => $node, 'errors' => $errors, 'repairs' => $repairs];
    }

    private function repair_widget(array $node, string $path): array
    {
        $errors = [];
        $repairs = [];

        if (($node['elType'] ?? '') !== 'widget') {
            $errors[] = "{$path}: elType must be widget.";
            return ['valid' => false, 'node' => [], 'errors' => $errors, 'repairs' => $repairs];
        }

        if (empty($node['widgetType']) || ! is_string($node['widgetType'])) {
            $errors[] = "{$path}: missing widgetType.";
            return ['valid' => false, 'node' => [], 'errors' => $errors, 'repairs' => $repairs];
        }

        if (empty($node['id']) || ! is_string($node['id'])) {
            $node['id'] = $this->element_id();
            $errors[] = "{$path}: missing widget id.";
            $repairs[] = "{$path}: generated widget id.";
        }

        if (! isset($node['settings']) || ! is_array($node['settings'])) {
            $node['settings'] = [];
            $errors[] = "{$path}: missing widget settings.";
            $repairs[] = "{$path}: injected empty widget settings.";
        }

        $node['settings'] = $this->normalize_responsive_settings($node['settings'], $path, $errors, $repairs);
        $node['elements'] = [];

        return ['valid' => true, 'node' => $node, 'errors' => $errors, 'repairs' => $repairs];
    }

    private function normalize_responsive_settings(array $settings, string $path, array &$errors, array &$repairs): array
    {
        foreach (['mobile_default', 'tablet_default'] as $key) {
            if (! isset($settings[$key])) {
                $settings[$key] = 'inherit';
                $errors[] = "{$path}: missing {$key} responsive marker.";
                $repairs[] = "{$path}: added {$key}=inherit.";
            }
        }

        return $settings;
    }

    private function element_id(): string
    {
        return substr(str_replace('-', '', wp_generate_uuid4()), 0, 7);
    }
}
