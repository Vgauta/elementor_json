<?php

namespace AIEB\Elementor;

if (! defined('ABSPATH')) {
    exit;
}

class Compatibility_Service
{
    public function detect(): array
    {
        $version = defined('ELEMENTOR_VERSION') ? (string) ELEMENTOR_VERSION : 'unknown';
        $experiments = $this->get_experiments();

        $flexbox_enabled = $this->is_experiment_active($experiments, 'container') || $this->is_experiment_active($experiments, 'flexbox_container');
        $grid_enabled = $this->is_experiment_active($experiments, 'grid_container');

        return [
            'elementor_version' => $version,
            'experiments' => $experiments,
            'supports' => [
                'flexbox_container' => $flexbox_enabled,
                'grid_container' => $grid_enabled,
            ],
        ];
    }

    private function get_experiments(): array
    {
        if (! class_exists('\Elementor\Plugin')) {
            return [];
        }

        $instance = \Elementor\Plugin::$instance ?? null;
        $manager = $instance->experiments ?? null;
        if (! $manager || ! method_exists($manager, 'get_features')) {
            return [];
        }

        $features = (array) $manager->get_features();
        $out = [];
        foreach ($features as $name => $feature) {
            $state = 'inactive';
            if (is_array($feature) && isset($feature['default'])) {
                $state = (string) $feature['default'];
            }
            if (method_exists($manager, 'is_feature_active')) {
                $state = $manager->is_feature_active((string) $name) ? 'active' : $state;
            }
            $out[(string) $name] = $state;
        }

        return $out;
    }

    private function is_experiment_active(array $experiments, string $key): bool
    {
        if (! isset($experiments[$key])) {
            return false;
        }

        return in_array((string) $experiments[$key], ['active', 'default', 'yes', 'on', '1'], true);
    }
}
