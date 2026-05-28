<?php

namespace ElementorVisionCore\Presets;

use ElementorVisionCore\Builders\ContainerBuilder;
use ElementorVisionCore\Builders\ResponsiveBuilder;
use ElementorVisionCore\Builders\StyleBuilder;
use ElementorVisionCore\Builders\WidgetBuilder;

class SampleTemplatePreset {
    public function build(): array {
        $hero = $this->hero_section();
        $services = $this->services_grid();
        $cta = $this->cta_section();

        return [
            ContainerBuilder::make([$hero, $services, $cta], [
                'content_width' => 'full',
                'flex_direction' => 'column',
                'gap' => ['unit' => 'px', 'size' => 0, 'sizes' => []],
            ]),
        ];
    }

    private function hero_section(): array {
        $heading = WidgetBuilder::make('heading', array_merge([
            'title' => 'Build Faster with Elementor Vision Core',
            'header_size' => 'h1',
            'align' => 'center',
            'text_color' => '#111827',
        ], StyleBuilder::typography('Inter', 54, 700, 1.1)));

        $text = WidgetBuilder::make('text-editor', array_merge([
            'editor' => 'Programmatically generated Elementor templates using modern flexbox containers only.',
            'align' => 'center',
            'text_color' => '#4B5563',
        ], StyleBuilder::typography('Inter', 18, 400, 1.6)));

        $button = WidgetBuilder::make('button', [
            'text' => 'Get Started',
            'link' => ['url' => '#'],
            'align' => 'center',
            'button_text_color' => '#FFFFFF',
            'background_color' => '#2563EB',
            'border_radius' => StyleBuilder::spacing(10, 10, 10, 10),
        ]);

        return ContainerBuilder::make([$heading, $text, $button], ResponsiveBuilder::with_mobile_overrides([
            'flex_direction' => 'column',
            'justify_content' => 'center',
            'align_items' => 'center',
            'min_height' => ['unit' => 'vh', 'size' => 80, 'sizes' => []],
            'padding' => StyleBuilder::spacing(120, 30, 120, 30),
            'background_background' => 'classic',
            'background_color' => '#F9FAFB',
            'gap' => ['unit' => 'px', 'size' => 22, 'sizes' => []],
        ], [
            'padding' => StyleBuilder::spacing(90, 20, 90, 20),
            'min_height' => ['unit' => 'vh', 'size' => 60, 'sizes' => []],
        ]));
    }

    private function services_grid(): array {
        $items = [];
        $services = [
            ['icon' => 'fas fa-bolt', 'title' => 'Speed', 'desc' => 'Generate accurate Elementor JSON instantly.'],
            ['icon' => 'fas fa-layer-group', 'title' => 'Flexbox', 'desc' => 'Built for modern container layouts.'],
            ['icon' => 'fas fa-mobile-alt', 'title' => 'Responsive', 'desc' => 'Device-level responsive settings included.'],
        ];

        foreach ($services as $service) {
            $items[] = ContainerBuilder::make([
                WidgetBuilder::make('icon-box', [
                    'selected_icon' => ['value' => $service['icon'], 'library' => 'fa-solid'],
                    'title_text' => $service['title'],
                    'description_text' => $service['desc'],
                    'title_color' => '#111827',
                    'description_color' => '#6B7280',
                ]),
            ], [
                'flex_direction' => 'column',
                'padding' => StyleBuilder::spacing(26, 26, 26, 26),
                'border_border' => 'solid',
                'border_width' => StyleBuilder::spacing(1, 1, 1, 1),
                'border_color' => '#E5E7EB',
                'border_radius' => StyleBuilder::spacing(12, 12, 12, 12),
                'background_background' => 'classic',
                'background_color' => '#FFFFFF',
            ]);
        }

        return ContainerBuilder::make($items, ResponsiveBuilder::with_mobile_overrides([
            'content_width' => 'boxed',
            'boxed_width' => ['unit' => 'px', 'size' => 1140, 'sizes' => []],
            'padding' => StyleBuilder::spacing(70, 20, 70, 20),
            'display' => 'flex',
            'flex_direction' => 'row',
            'flex_wrap' => 'wrap',
            'gap' => ['unit' => 'px', 'size' => 24, 'sizes' => []],
        ], [
            'flex_direction' => 'column',
        ]));
    }

    private function cta_section(): array {
        $heading = WidgetBuilder::make('heading', [
            'title' => 'Ready to export your next page template?',
            'header_size' => 'h2',
            'align' => 'center',
            'text_color' => '#FFFFFF',
        ]);

        $spacer = WidgetBuilder::make('spacer', [
            'space' => ['unit' => 'px', 'size' => 12, 'sizes' => []],
        ]);

        $button = WidgetBuilder::make('button', [
            'text' => 'Download Template JSON',
            'align' => 'center',
            'button_text_color' => '#1E3A8A',
            'background_color' => '#FFFFFF',
            'border_radius' => StyleBuilder::spacing(8, 8, 8, 8),
        ]);

        $image = WidgetBuilder::make('image', [
            'image' => ['url' => 'https://via.placeholder.com/640x360.png?text=Elementor+Vision+Core', 'id' => 0],
            'caption_source' => 'none',
            'align' => 'center',
        ]);

        return ContainerBuilder::make([$heading, $spacer, $button, $image], [
            'flex_direction' => 'column',
            'align_items' => 'center',
            'justify_content' => 'center',
            'padding' => StyleBuilder::spacing(80, 20, 80, 20),
            'gap' => ['unit' => 'px', 'size' => 14, 'sizes' => []],
            'background_background' => 'classic',
            'background_color' => '#1D4ED8',
        ]);
    }
}
