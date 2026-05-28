<?php

namespace ElementorVisionCore\Orchestration;

class MultiAgentOrchestrator {
    private SharedMemory $memory;
    private TaskQueue $queue;
    private AgentCommunicator $comm;
    private ProviderClientInterface $client;
    private array $agents;

    public function __construct(ProviderClientInterface $client) {
        $this->memory = new SharedMemory();
        $this->queue = new TaskQueue();
        $this->comm = new AgentCommunicator();
        $this->client = $client;
        $this->agents = [
            'vision' => new VisionAgent(),
            'layout' => new LayoutAgent(),
            'responsive' => new ResponsiveAgent(),
            'schema' => new ElementorSchemaAgent(),
            'validation' => new ValidationAgent(),
            'visual_diff' => new VisualDiffAgent(),
        ];
    }

    public function run(array $input, array $config = []): array {
        $iterations = max(1, (int) ($config['iterations'] ?? 2));
        $this->memory->set('input', $input);
        for ($i = 1; $i <= $iterations; $i++) {
            $this->memory->append_log("Iteration {$i} started");
            $this->seed_iteration_tasks($input);
            while ($this->queue->has_items()) {
                $task = $this->queue->pop();
                if (! is_array($task)) { continue; }
                $agent_key = (string) ($task['agent'] ?? '');
                $agent = $this->agents[$agent_key] ?? null;
                if (! $agent) { continue; }

                $result = $agent->run($task, $this->memory, $this->client, $config);
                $this->memory->set($task['output_key'], $result);
                $this->comm->send('orchestrator', $agent->name(), 'task_result', $result, $this->memory);
            }
            $this->memory->append_log("Iteration {$i} completed");
        }

        return [
            'ok' => true,
            'memory' => $this->memory->all(),
            'messages' => $this->memory->get('messages', []),
            'logs' => $this->memory->get('logs', []),
        ];
    }

    private function seed_iteration_tasks(array $input): void {
        $this->queue->push(['agent' => 'vision', 'input' => $input, 'output_key' => 'vision_output']);
        $this->queue->push(['agent' => 'layout', 'input' => [], 'output_key' => 'layout_plan']);
        $this->queue->push(['agent' => 'responsive', 'input' => [], 'output_key' => 'responsive_plan']);
        $this->queue->push(['agent' => 'schema', 'input' => [], 'output_key' => 'schema_feedback']);
        $this->queue->push(['agent' => 'validation', 'input' => [], 'output_key' => 'validation_feedback']);
        $this->queue->push(['agent' => 'visual_diff', 'input' => [], 'output_key' => 'visual_diff_feedback']);
    }
}
