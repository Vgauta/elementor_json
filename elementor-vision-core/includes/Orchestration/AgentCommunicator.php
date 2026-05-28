<?php

namespace ElementorVisionCore\Orchestration;

class AgentCommunicator {
    public function send(string $from, string $to, string $topic, array $payload, SharedMemory $memory): void {
        $messages = $memory->get('messages', []);
        $messages[] = ['from' => $from, 'to' => $to, 'topic' => $topic, 'payload' => $payload, 'ts' => gmdate('c')];
        $memory->set('messages', $messages);
    }
}
