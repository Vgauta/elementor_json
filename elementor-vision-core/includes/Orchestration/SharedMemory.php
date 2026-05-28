<?php

namespace ElementorVisionCore\Orchestration;

class SharedMemory {
    private array $state = [];
    public function set(string $key, $value): void { $this->state[$key] = $value; }
    public function get(string $key, $default = null) { return $this->state[$key] ?? $default; }
    public function all(): array { return $this->state; }
    public function append_log(string $line): void {
        $logs = $this->get('logs', []);
        $logs[] = $line;
        $this->set('logs', $logs);
    }
}
