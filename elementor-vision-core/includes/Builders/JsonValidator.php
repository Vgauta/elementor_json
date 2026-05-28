<?php

namespace ElementorVisionCore\Builders;

class JsonValidator {
    private ElementorSchemaChecker $schema_checker;
    private array $errors = [];
    private array $logs = [];
    private array $seen_ids = [];

    public function __construct(?ElementorSchemaChecker $schema_checker = null) {
        $this->schema_checker = $schema_checker ?: new ElementorSchemaChecker();
    }

    public function validate(array $json): array {
        $this->errors = [];
        $this->logs = [];
        $this->seen_ids = [];

        foreach ($this->schema_checker->check_root($json) as $error) {
            $this->add_error($error);
        }

        if (! isset($json['content']) || ! is_array($json['content'])) {
            return $this->result();
        }

        foreach ($json['content'] as $index => $node) {
            $this->validate_node($node, "content.{$index}", false);
        }

        return $this->result();
    }

    private function validate_node($node, string $path, bool $inside_container): void {
        if (! is_array($node)) {
            $this->add_error("{$path}: Node must be array.");
            return;
        }

        foreach ($this->schema_checker->check_node($node, $inside_container) as $error) {
            $this->add_error("{$path}: {$error}");
        }

        $this->check_id($node, $path);
        $this->check_responsive($node, $path);
        $this->check_flexbox_container($node, $path);

        if (! isset($node['elements']) || ! is_array($node['elements'])) {
            return;
        }

        $is_container = ($node['elType'] ?? '') === 'container';
        foreach ($node['elements'] as $idx => $child) {
            $this->validate_node($child, "{$path}.elements.{$idx}", $is_container);
        }
    }

    private function check_id(array $node, string $path): void {
        $id = $node['id'] ?? '';
        if (! is_string($id) || $id === '') {
            $this->add_error("{$path}: Missing or invalid ID.");
            return;
        }
        if (isset($this->seen_ids[$id])) {
            $this->add_error("{$path}: Duplicate ID {$id}.");
        }
        $this->seen_ids[$id] = true;
    }

    private function check_responsive(array $node, string $path): void {
        if (! isset($node['settings']) || ! is_array($node['settings'])) {
            return;
        }
        foreach ($node['settings'] as $key => $value) {
            if (strpos($key, '_mobile') !== false && ! is_array($value)) {
                $this->add_error("{$path}: Responsive key {$key} must be array.");
            }
        }
    }

    private function check_flexbox_container(array $node, string $path): void {
        if (($node['elType'] ?? '') !== 'container') {
            return;
        }
        $settings = $node['settings'] ?? [];
        $direction = $settings['flex_direction'] ?? null;
        if (! in_array($direction, ['row', 'column'], true)) {
            $this->add_error("{$path}: Container missing valid flex_direction.");
        }
    }

    private function add_error(string $message): void {
        $this->errors[] = $message;
        $this->logs[] = '[ERROR] ' . $message;
    }

    private function result(): array {
        if (empty($this->errors)) {
            $this->logs[] = '[OK] Validation passed.';
        }
        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'logs' => $this->logs,
        ];
    }
}
