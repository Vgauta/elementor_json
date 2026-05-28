<?php

namespace ElementorVisionCore\Orchestration;

class VisionAgent extends BaseAgent { public function name(): string { return 'Vision Agent'; }
    public function run(array $task, SharedMemory $memory, ProviderClientInterface $client, array $config = []): array {
        return $this->ask_model('You extract visual structure.', 'Return JSON with sections and style signals from: ' . wp_json_encode($task['input'] ?? []), $client, $config);
    }}
class LayoutAgent extends BaseAgent { public function name(): string { return 'Layout Agent'; }
    public function run(array $task, SharedMemory $memory, ProviderClientInterface $client, array $config = []): array {
        return $this->ask_model('You map layout into preset-assemblable blocks.', 'Use vision output and return layout_plan JSON: ' . wp_json_encode($memory->get('vision_output', [])), $client, $config);
    }}
class ResponsiveAgent extends BaseAgent { public function name(): string { return 'Responsive Agent'; }
    public function run(array $task, SharedMemory $memory, ProviderClientInterface $client, array $config = []): array {
        return $this->ask_model('You add responsive breakpoints rules.', 'Return responsive adjustments JSON for plan: ' . wp_json_encode($memory->get('layout_plan', [])), $client, $config);
    }}
class ElementorSchemaAgent extends BaseAgent { public function name(): string { return 'Elementor Schema Agent'; }
    public function run(array $task, SharedMemory $memory, ProviderClientInterface $client, array $config = []): array {
        return $this->ask_model('You enforce latest Elementor container schema.', 'Return schema corrections JSON for assembly payload: ' . wp_json_encode($memory->get('assembly_payload', [])), $client, $config);
    }}
class ValidationAgent extends BaseAgent { public function name(): string { return 'Validation Agent'; }
    public function run(array $task, SharedMemory $memory, ProviderClientInterface $client, array $config = []): array {
        return $this->ask_model('You validate page reconstruction quality.', 'Return validation findings JSON from state: ' . wp_json_encode($memory->all()), $client, $config);
    }}
class VisualDiffAgent extends BaseAgent { public function name(): string { return 'Visual Diff Agent'; }
    public function run(array $task, SharedMemory $memory, ProviderClientInterface $client, array $config = []): array {
        return $this->ask_model('You propose visual-diff corrections.', 'Return correction JSON based on diff: ' . wp_json_encode($memory->get('visual_diff', [])), $client, $config);
    }}
