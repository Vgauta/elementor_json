<?php

namespace ElementorVisionCore\Presets;

class PresetRegistry {
    private const OPTION_KEY = 'evc_presets';

    public function all(): array {
        $saved = get_option(self::OPTION_KEY, []);
        return is_array($saved) ? $saved : [];
    }

    public function seed_defaults(array $defaults): void {
        $current = $this->all();
        if (empty($current)) {
            update_option(self::OPTION_KEY, $defaults, false);
        }
    }

    public function get(string $id): ?array {
        $all = $this->all();
        return $all[$id] ?? null;
    }

    public function save(array $preset): string {
        $all = $this->all();
        $id = $preset['id'] ?? sanitize_key('preset_' . wp_generate_password(8, false, false));
        $preset['id'] = $id;
        $all[$id] = $preset;
        update_option(self::OPTION_KEY, $all, false);
        return $id;
    }

    public function duplicate(string $id): ?string {
        $preset = $this->get($id);
        if (! $preset) {
            return null;
        }
        $new_id = sanitize_key($id . '_copy_' . wp_generate_password(4, false, false));
        $preset['id'] = $new_id;
        $preset['name'] = ($preset['name'] ?? 'Preset') . ' Copy';
        return $this->save($preset);
    }
}
