<?php

namespace ElementorVisionCore\Builders;

class JsonRepairEngine {
    private array $logs = [];
    private array $seen_ids = [];

    public function repair(array $json): array {
        $this->logs = [];
        $this->seen_ids = [];

        $json = $this->repair_root($json);

        if (is_array($json['content'])) {
            foreach ($json['content'] as $i => $node) {
                $json['content'][$i] = $this->repair_node($node, false);
            }
        }

        return ['json' => $json, 'logs' => $this->logs];
    }

    private function repair_root(array $json): array {
        $defaults = [
            'version' => '0.4',
            'title' => 'Repaired Elementor Template',
            'type' => 'page',
            'content' => [],
            'page_settings' => [],
        ];

        foreach ($defaults as $k => $v) {
            if (! array_key_exists($k, $json)) {
                $json[$k] = $v;
                $this->logs[] = "Added missing root key: {$k}";
            }
        }

        if (! is_array($json['content'])) {
            $json['content'] = [];
            $this->logs[] = 'Reset invalid content to empty array.';
        }

        return $json;
    }

    private function repair_node($node, bool $inside_container): array {
        if (! is_array($node)) {
            $node = [];
            $this->logs[] = 'Converted invalid node to array.';
        }

        $elType = $node['elType'] ?? 'container';
        if (! in_array($elType, ['container', 'widget'], true)) {
            $elType = 'container';
            $this->logs[] = 'Repaired invalid elType to container.';
        }
        $node['elType'] = $elType;

        $node['id'] = $this->unique_id($node['id'] ?? '');

        if (! isset($node['settings']) || ! is_array($node['settings'])) {
            $node['settings'] = [];
            $this->logs[] = "Reset corrupted settings for {$node['id']}.";
        }

        if (! isset($node['elements']) || ! is_array($node['elements'])) {
            $node['elements'] = [];
            $this->logs[] = "Reset invalid elements for {$node['id']}.";
        }

        if ($node['elType'] === 'container') {
            if (! isset($node['settings']['flex_direction']) || ! in_array($node['settings']['flex_direction'], ['row', 'column'], true)) {
                $node['settings']['flex_direction'] = 'column';
                $this->logs[] = "Set default flex_direction for container {$node['id']}.";
            }
            $node['isInner'] = $inside_container;
        } else {
            if (! isset($node['widgetType']) || ! in_array($node['widgetType'], ElementorSchemaChecker::SUPPORTED_WIDGETS, true)) {
                $node['widgetType'] = 'text-editor';
                $this->logs[] = "Set fallback widgetType for widget {$node['id']}.";
            }
            if (! $inside_container) {
                $container = [
                    'id' => $this->unique_id(''),
                    'elType' => 'container',
                    'isInner' => false,
                    'settings' => ['flex_direction' => 'column'],
                    'elements' => [$node],
                ];
                $this->logs[] = "Wrapped root widget {$node['id']} in container {$container['id']}.";
                return $container;
            }
        }

        foreach ($node['elements'] as $idx => $child) {
            $node['elements'][$idx] = $this->repair_node($child, $node['elType'] === 'container');
        }

        return $node;
    }

    private function unique_id(string $id): string {
        if ($id === '' || isset($this->seen_ids[$id])) {
            $id = substr(md5(uniqid((string) wp_rand(), true)), 0, 8);
            $this->logs[] = "Generated new unique ID: {$id}";
        }
        $this->seen_ids[$id] = true;
        return $id;
    }
}
