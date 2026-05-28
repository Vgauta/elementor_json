<?php

namespace ElementorVisionCore\Presets;

use ElementorVisionCore\Builders\ContainerBuilder;
use ElementorVisionCore\Builders\StyleBuilder;
use ElementorVisionCore\Builders\WidgetBuilder;

class PresetLibrary {
    public function defaults(): array {
        return [
            'dark-enterprise-hero' => $this->preset('dark-enterprise-hero', 'Dark Enterprise Hero', 'hero', ['style' => 'dark', 'layout_type' => 'centered', 'color_scheme' => 'dark-blue', 'industry' => 'enterprise', 'tags' => ['hero', 'enterprise', 'dark']], [
                ContainerBuilder::make([
                    WidgetBuilder::make('heading', ['title' => 'Enterprise-grade Platform', 'header_size' => 'h1', 'text_color' => '#FFFFFF']),
                    WidgetBuilder::make('text-editor', ['editor' => 'Secure and scalable systems for global teams.', 'text_color' => '#CBD5E1']),
                    WidgetBuilder::make('button', ['text' => 'Request Demo', 'button_text_color' => '#0F172A', 'background_color' => '#F8FAFC']),
                ], ['flex_direction' => 'column', 'align_items' => 'center', 'background_background' => 'classic', 'background_color' => '#0B1120', 'padding' => StyleBuilder::spacing(120, 20, 120, 20)]),
            ]),
            'saas-hero' => $this->preset('saas-hero', 'SaaS Hero', 'hero', ['style' => 'modern', 'layout_type' => 'split', 'color_scheme' => 'indigo', 'industry' => 'saas', 'tags' => ['hero', 'saas']], [
                ContainerBuilder::make([
                    WidgetBuilder::make('heading', ['title' => 'Scale your SaaS onboarding', 'header_size' => 'h1', 'text_color' => '#111827']),
                    WidgetBuilder::make('text-editor', ['editor' => 'Reduce churn with better activation journeys.', 'text_color' => '#4B5563']),
                    WidgetBuilder::make('image', ['image' => ['url' => 'https://via.placeholder.com/600x360.png?text=SaaS+Dashboard', 'id' => 0]]),
                ], ['flex_direction' => 'column', 'padding' => StyleBuilder::spacing(90, 20, 90, 20), 'background_background' => 'classic', 'background_color' => '#EEF2FF']),
            ]),
            'agency-services-grid' => $this->preset('agency-services-grid', 'Agency Services Grid', 'services', ['style' => 'clean', 'layout_type' => 'grid', 'color_scheme' => 'light', 'industry' => 'agency', 'tags' => ['services', 'agency', 'grid']], [
                ContainerBuilder::make([
                    ContainerBuilder::make([WidgetBuilder::make('icon-box', ['selected_icon' => ['value' => 'fas fa-pen-ruler', 'library' => 'fa-solid'], 'title_text' => 'Branding', 'description_text' => 'Identity systems that convert.'])], ['flex_direction' => 'column', 'padding' => StyleBuilder::spacing(24, 24, 24, 24)]),
                    ContainerBuilder::make([WidgetBuilder::make('icon-box', ['selected_icon' => ['value' => 'fas fa-code', 'library' => 'fa-solid'], 'title_text' => 'Development', 'description_text' => 'Fast, maintainable site builds.'])], ['flex_direction' => 'column', 'padding' => StyleBuilder::spacing(24, 24, 24, 24)]),
                ], ['flex_direction' => 'row', 'gap' => ['unit' => 'px', 'size' => 20, 'sizes' => []], 'padding' => StyleBuilder::spacing(60, 20, 60, 20)]),
            ]),
            'premium-cta' => $this->preset('premium-cta', 'Premium CTA', 'cta', ['style' => 'premium', 'layout_type' => 'centered', 'color_scheme' => 'gold-dark', 'industry' => 'multi', 'tags' => ['cta', 'premium']], [
                ContainerBuilder::make([
                    WidgetBuilder::make('heading', ['title' => 'Start your premium rollout', 'header_size' => 'h2', 'text_color' => '#FFFFFF']),
                    WidgetBuilder::make('button', ['text' => 'Book Strategy Call', 'button_text_color' => '#111827', 'background_color' => '#FDE68A']),
                ], ['flex_direction' => 'column', 'align_items' => 'center', 'background_background' => 'classic', 'background_color' => '#111827', 'padding' => StyleBuilder::spacing(70, 20, 70, 20)]),
            ]),
            'corporate-footer' => $this->preset('corporate-footer', 'Corporate Footer', 'footer', ['style' => 'corporate', 'layout_type' => 'columns', 'color_scheme' => 'slate', 'industry' => 'corporate', 'tags' => ['footer', 'corporate']], [
                ContainerBuilder::make([
                    WidgetBuilder::make('heading', ['title' => 'Acme Corp', 'header_size' => 'h4', 'text_color' => '#E5E7EB']),
                    WidgetBuilder::make('text-editor', ['editor' => '© 2026 Acme Corp. All rights reserved.', 'text_color' => '#9CA3AF']),
                ], ['flex_direction' => 'column', 'background_background' => 'classic', 'background_color' => '#1F2937', 'padding' => StyleBuilder::spacing(40, 20, 40, 20)]),
            ]),
        ];
    }

    private function preset(string $id, string $name, string $category, array $metadata, array $content): array {
        return [
            'id' => $id,
            'name' => $name,
            'category' => $category,
            'metadata' => $metadata,
            'content' => $content,
        ];
    }
}
