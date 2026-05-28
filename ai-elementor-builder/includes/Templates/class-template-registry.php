<?php

namespace AIEB\Templates;

if (! defined('ABSPATH')) {
    exit;
}

class Template_Registry
{
    public function __construct(private string $presets_dir)
    {
    }

    public function list(): array
    {
        return array_keys($this->preset_map());
    }

    public function list_learned(): array
    {
        $file = $this->learned_index_file();
        if (! file_exists($file)) {
            return [];
        }

        $raw = file_get_contents($file);
        $json = json_decode((string) $raw, true);
        return is_array($json) ? $json : [];
    }

    public function save_learned_preset(array $payload): array
    {
        $uploads = wp_upload_dir();
        $base = trailingslashit($uploads['basedir']) . 'aieb-presets';
        wp_mkdir_p($base);

        $id = sanitize_key((string) ($payload['id'] ?? 'preset-' . time()));
        $file = $base . '/' . $id . '.json';

        $record = [
            'id' => $id,
            'title' => sanitize_text_field((string) ($payload['title'] ?? $id)),
            'category' => sanitize_text_field((string) ($payload['category'] ?? 'custom')),
            'tags' => array_values(array_filter(array_map('sanitize_text_field', (array) ($payload['tags'] ?? [])))),
            'pattern' => (array) ($payload['pattern'] ?? []),
            'source' => 'learned',
            'path' => $file,
            'created_at' => gmdate('c'),
        ];

        file_put_contents($file, wp_json_encode((array) ($payload['template'] ?? []), JSON_PRETTY_PRINT));

        $index = $this->list_learned();
        $index[$id] = $record;
        file_put_contents($this->learned_index_file(), wp_json_encode($index, JSON_PRETTY_PRINT));

        return $record;
    }

    public function match_section(array $section): array
    {
        $type = strtolower((string) ($section['type'] ?? ''));
        $layout = strtolower((string) ($section['layout'] ?? ''));
        $elements = array_map('strval', (array) ($section['elements'] ?? []));

        $pool = $this->preset_map();
        foreach ($this->list_learned() as $id => $record) {
            $pool[$id] = [
                'path' => (string) ($record['path'] ?? ''),
                'pattern' => (array) ($record['pattern'] ?? []),
                'source' => 'learned',
                'category' => (string) ($record['category'] ?? 'custom'),
                'tags' => (array) ($record['tags'] ?? []),
            ];
        }

        $best_key = 'hero-saas-01';
        $best_score = -1;

        foreach ($pool as $key => $meta) {
            $score = 0;
            if (($meta['pattern']['type'] ?? '') === $type) {
                $score += 3;
            }
            if (($meta['pattern']['layout'] ?? '') === $layout) {
                $score += 2;
            }
            $supported = (array) ($meta['pattern']['elements'] ?? []);
            foreach ($elements as $element) {
                if (in_array($element, $supported, true)) {
                    $score += 1;
                }
            }
            if ($score > $best_score) {
                $best_score = $score;
                $best_key = $key;
            }
        }

        $selected = $pool[$best_key] ?? $this->preset_map()['hero-saas-01'];
        return [
            'key' => $best_key,
            'path' => str_contains((string) ($selected['path'] ?? ''), $this->presets_dir) ? $selected['path'] : $this->presets_dir . '/' . $selected['path'],
            'score' => $best_score,
            'source' => (string) ($selected['source'] ?? 'core'),
            'category' => (string) ($selected['category'] ?? 'core'),
            'tags' => (array) ($selected['tags'] ?? []),
        ];
    }

    public function get_template(string $path): array
    {
        if (! file_exists($path)) {
            return [];
        }
        $raw = file_get_contents($path);
        $json = json_decode((string) $raw, true);
        return is_array($json) ? $json : [];
    }

    private function learned_index_file(): string
    {
        $uploads = wp_upload_dir();
        $base = trailingslashit($uploads['basedir']) . 'aieb-presets';
        wp_mkdir_p($base);
        return $base . '/index.json';
    }

    private function preset_map(): array
    {
        return [
            'hero-dark-01' => ['path' => 'dark-premium/hero-dark-01.json', 'pattern' => ['type' => 'hero', 'layout' => '2-column', 'elements' => ['heading', 'text', 'button', 'image']], 'source' => 'core', 'category' => 'dark-premium', 'tags' => ['hero']],
            'stats-inline-02' => ['path' => 'minimal-modern/stats-inline-02.json', 'pattern' => ['type' => 'stats', 'layout' => 'inline', 'elements' => ['counter']], 'source' => 'core', 'category' => 'minimal-modern', 'tags' => ['stats']],
            'services-grid-03' => ['path' => 'agency/services-grid-03.json', 'pattern' => ['type' => 'services', 'layout' => 'grid', 'elements' => ['cards', 'icon-box']], 'source' => 'core', 'category' => 'agency', 'tags' => ['services']],
            'footer-premium-01' => ['path' => 'corporate/footer-premium-01.json', 'pattern' => ['type' => 'footer', 'layout' => 'stack', 'elements' => ['links', 'copyright']], 'source' => 'core', 'category' => 'corporate', 'tags' => ['footer']],
            'hero-saas-01' => ['path' => 'saas/hero-saas-01.json', 'pattern' => ['type' => 'hero', 'layout' => 'single', 'elements' => ['heading', 'text', 'button']], 'source' => 'core', 'category' => 'saas', 'tags' => ['hero']],
        ];
    }
}
