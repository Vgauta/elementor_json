<?php

namespace ElementorVisionCore\Assembly;

class StyleInjector {
    public function inject(array $template, array $style_overrides): array {
        if (! isset($template['content']) || ! is_array($template['content'])) {
            return $template;
        }

        foreach ($template['content'] as $i => $node) {
            $template['content'][$i] = $this->apply_to_node($node, $style_overrides);
        }
        return $template;
    }

    private function apply_to_node(array $node, array $overrides): array {
        if (! isset($node['settings']) || ! is_array($node['settings'])) {
            $node['settings'] = [];
        }

        if (isset($overrides['colors']) && is_array($overrides['colors'])) {
            foreach ($overrides['colors'] as $k => $v) {
                if (is_string($v) && $v !== '') {
                    $node['settings'][$k] = $v;
                }
            }
        }

        if (isset($overrides['spacing']) && is_array($overrides['spacing'])) {
            foreach ($overrides['spacing'] as $k => $v) {
                if (is_array($v)) {
                    $node['settings'][$k] = $v;
                }
            }
        }

        if (isset($overrides['typography']) && is_array($overrides['typography']) && ($node['elType'] ?? '') === 'widget') {
            foreach ($overrides['typography'] as $k => $v) {
                $node['settings'][$k] = $v;
            }
        }

        if (isset($node['elements']) && is_array($node['elements'])) {
            foreach ($node['elements'] as $idx => $child) {
                if (is_array($child)) {
                    $node['elements'][$idx] = $this->apply_to_node($child, $overrides);
                }
            }
        }

        return $node;
    }
}
