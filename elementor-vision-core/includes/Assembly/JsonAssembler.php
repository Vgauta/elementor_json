<?php

namespace ElementorVisionCore\Assembly;

use ElementorVisionCore\Builders\JsonRepairEngine;
use ElementorVisionCore\Builders\JsonValidator;

class JsonAssembler {
    private PresetMerger $merger;
    private StyleInjector $style_injector;
    private ContentInjector $content_injector;
    private JsonRepairEngine $repair_engine;
    private JsonValidator $validator;

    public function __construct(
        ?PresetMerger $merger = null,
        ?StyleInjector $style_injector = null,
        ?ContentInjector $content_injector = null,
        ?JsonRepairEngine $repair_engine = null,
        ?JsonValidator $validator = null
    ) {
        $this->merger = $merger ?: new PresetMerger();
        $this->style_injector = $style_injector ?: new StyleInjector();
        $this->content_injector = $content_injector ?: new ContentInjector();
        $this->repair_engine = $repair_engine ?: new JsonRepairEngine();
        $this->validator = $validator ?: new JsonValidator();
    }

    public function assemble(array $preset_payloads, array $style_overrides = [], array $content_overrides = []): array {
        $merged = $this->merger->merge($preset_payloads);

        $merged = $this->style_injector->inject($merged, $style_overrides);
        $merged = $this->content_injector->inject($merged, $content_overrides);

        $repaired = $this->repair_engine->repair($merged);
        $final = $repaired['json'];

        $validation = $this->validator->validate($final);

        return [
            'json' => $final,
            'repair_logs' => $repaired['logs'],
            'validation' => $validation,
        ];
    }
}
