<?php

namespace ElementorVisionCore\Orchestration;

interface AgentInterface {
    public function name(): string;
    public function run(array $task, SharedMemory $memory, ProviderClientInterface $client, array $config = []): array;
}
