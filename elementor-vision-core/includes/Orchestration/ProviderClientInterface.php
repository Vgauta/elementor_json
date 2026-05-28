<?php

namespace ElementorVisionCore\Orchestration;

interface ProviderClientInterface {
    public function generate(array $messages, array $config = []): array;
}
