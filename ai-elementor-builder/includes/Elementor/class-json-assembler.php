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
            if (empty($preset)) {
                continue;
            }
            $content[] = $this->normalize_container_tree($this->apply_dynamic_changes($preset, $section), false);
        }

        $doc = [
            'title' => sanitize_text_field((string) ($analysis['page_title'] ?? 'AI Generated Template')),
            'type' => 'page',
            'version' => '0.4',
            'page_settings' => [
                'hide_title' => 'yes',
                'content_width' => 'full',
            ],
            'content' => $content,
        ];

        return $this->validator->validate($doc) ? $doc : [];
    }

    private function apply_dynamic_changes(array $preset, array $section): array
    {
        $preset['id'] = $this->element_id();
        $preset['isInner'] = false;
        $preset['settings']['_title'] = ucfirst((string) ($section['type'] ?? 'Section'));

        if (! empty($section['style']['background_color'])) {
            $preset['settings']['background_background'] = 'classic';
            $preset['settings']['background_color'] = sanitize_hex_color($section['style']['background_color']) ?: $preset['settings']['background_color'] ?? '#FFFFFF';
        }

        return $preset;
    }

    private function normalize_container_tree(array $container, bool $is_inner): array
    {
        $container['id'] = $this->element_id();
        $container['elType'] = 'container';
        $container['isInner'] = $is_inner;
        $container['settings'] = is_array($container['settings'] ?? null) ? $container['settings'] : [];
        $container['elements'] = is_array($container['elements'] ?? null) ? $container['elements'] : [];

        $normalized_children = [];
        foreach ($container['elements'] as $child) {
            if (($child['elType'] ?? '') === 'container') {
                $normalized_children[] = $this->normalize_container_tree($child, true);
                continue;
            }

            if (($child['elType'] ?? '') === 'widget') {
                $child['id'] = $this->element_id();
                $child['elements'] = [];
                $normalized_children[] = $child;
            }
        }
        $container['elements'] = $normalized_children;

        return $container;
    }

    private function element_id(): string
    {
        return substr(str_replace('-', '', wp_generate_uuid4()), 0, 7);
    }
}
