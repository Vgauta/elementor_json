<?php

namespace ElementorVisionCore\Assembly;

class ContentInjector {
    public function inject(array $template, array $content_overrides): array {
        if (! isset($template['content']) || ! is_array($template['content'])) {
            return $template;
        }

        foreach ($template['content'] as $i => $node) {
            $template['content'][$i] = $this->apply_to_node($node, $content_overrides);
        }

        return $template;
    }

    private function apply_to_node(array $node, array $overrides): array {
        if (($node['elType'] ?? '') === 'widget') {
            $w = $node['widgetType'] ?? '';
            if ($w === 'heading' && isset($overrides['heading_text'])) {
                $node['settings']['title'] = (string) $overrides['heading_text'];
            }
            if ($w === 'text-editor' && isset($overrides['body_text'])) {
                $node['settings']['editor'] = (string) $overrides['body_text'];
            }
            if ($w === 'button' && isset($overrides['button_text'])) {
                $node['settings']['text'] = (string) $overrides['button_text'];
            }
            if ($w === 'image' && isset($overrides['image_url'])) {
                $node['settings']['image'] = ['url' => (string) $overrides['image_url'], 'id' => 0];
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
