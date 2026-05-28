<?php

namespace AIEB\Elementor;

use AIEB\Templates\Template_Registry;

if (! defined('ABSPATH')) {
    exit;
}

class Json_Assembler
{
    public function __construct(private Template_Registry $registry, private Json_Validator $validator)
    {
    }

    public function build(array $analysis): array
    {
        $content = [];

        foreach (($analysis['sections'] ?? []) as $section) {
            $template_path = $this->registry->match_section($section);
            $preset = $this->registry->get_template($template_path);
            $content[] = $this->apply_dynamic_changes($preset, $section);
        }

        $doc = [
            'title' => 'AI Generated Template',
            'type' => 'container',
            'version' => '0.4',
            'page_settings' => [],
            'content' => $content,
        ];

        if (! $this->validator->validate($doc)) {
            return [];
        }

        return $doc;
    }

    private function apply_dynamic_changes(array $preset, array $section): array
    {
        $preset['id'] = wp_generate_uuid4();
        $preset['settings']['_title'] = ucfirst((string) ($section['type'] ?? 'Section'));
        return $preset;
    }
}
