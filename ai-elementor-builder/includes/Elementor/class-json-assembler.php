<?php

namespace AIEB\Elementor;

use AIEB\Templates\Template_Registry;

if (! defined('ABSPATH')) {
    exit;
}

class Json_Assembler
{
    public function __construct(
        private Template_Registry $registry,
        private Json_Validator $validator,
        private Compatibility_Service $compatibility
    ) {
    }

    public function build(array $analysis): array
    {
        $compat = $this->compatibility->detect();
        if (empty($compat['supports']['flexbox_container'])) {
            return [
                'json' => [],
                'valid' => false,
                'errors' => ['Flexbox container support is required but appears inactive in Elementor experiments.'],
                'repairs' => [],
                'mode' => 'preset_registry_assembly',
                'compatibility' => $compat,
            ];
        }

        $content = [];

        foreach (($analysis['sections'] ?? []) as $section) {
            $match = $this->registry->match_section($section);
            $preset = $this->registry->get_template((string) ($match['path'] ?? ''));
            if (empty($preset)) {
                continue;
            }

            $node = $this->apply_dynamic_changes($preset, $section, $compat);
            $content[] = $this->normalize_container_tree($node, false, $compat);
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

        $validation = $this->validator->validate_and_repair($doc);

        return [
            'json' => $validation['repaired_json'],
            'valid' => (bool) $validation['valid'],
            'errors' => $validation['errors'],
            'repairs' => $validation['repairs'],
            'mode' => 'preset_registry_assembly',
            'compatibility' => $compat,
        ];
    }

    private function apply_dynamic_changes(array $preset, array $section, array $compat): array
    {
        $preset['id'] = $this->element_id();
        $preset['isInner'] = false;
        $preset['settings']['_title'] = ucfirst((string) ($section['type'] ?? 'Section'));

        if (! empty($section['style']['background_color'])) {
            $preset['settings']['background_background'] = 'classic';
            $preset['settings']['background_color'] = sanitize_hex_color($section['style']['background_color']) ?: $preset['settings']['background_color'] ?? '#FFFFFF';
        }

        if (! empty($compat['supports']['grid_container']) && (($section['layout'] ?? '') === 'grid')) {
            $preset['settings']['display'] = 'grid';
        } else {
            $preset['settings']['display'] = 'flex';
        }

        return $preset;
    }

    private function normalize_container_tree(array $container, bool $is_inner, array $compat): array
    {
        $container['id'] = $this->element_id();
        $container['elType'] = 'container';
        $container['isInner'] = $is_inner;
        $container['settings'] = is_array($container['settings'] ?? null) ? $container['settings'] : [];
        $container['settings']['display'] = $container['settings']['display'] ?? 'flex';
        if (empty($compat['supports']['grid_container']) && $container['settings']['display'] === 'grid') {
            $container['settings']['display'] = 'flex';
        }
        $container['elements'] = is_array($container['elements'] ?? null) ? $container['elements'] : [];

        $normalized_children = [];
        foreach ($container['elements'] as $child) {
            if (($child['elType'] ?? '') === 'container') {
                $normalized_children[] = $this->normalize_container_tree($child, true, $compat);
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
