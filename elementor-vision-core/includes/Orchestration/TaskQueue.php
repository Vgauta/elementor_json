<?php

namespace ElementorVisionCore\Orchestration;

class TaskQueue {
    private array $queue = [];
    public function push(array $task): void { $this->queue[] = $task; }
    public function pop(): ?array { return array_shift($this->queue); }
    public function has_items(): bool { return ! empty($this->queue); }
}
