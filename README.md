# AI Elementor Builder (MVP)

## 1) Folder Structure

```text
ai-elementor-builder/
  ai-elementor-builder.php
  includes/
    class-plugin.php
    Admin/class-admin-page.php
    API/class-rest-controller.php
    AI/class-vision-service.php
    Elementor/class-json-assembler.php
    Elementor/class-json-validator.php
    Templates/class-template-registry.php
  assets/
    css/admin.css
    js/admin.js
  templates/presets/
    saas/hero-saas-01.json
    agency/services-grid-03.json
    logistics/
    corporate/footer-premium-01.json
    dark-premium/hero-dark-01.json
    minimal-modern/stats-inline-02.json
  storage/logs/
```

## 2) Architecture Overview

- Admin UI (WordPress menu + React-like wp.element app)
- REST API endpoints for template generation and registry listing
- Vision service for AI screenshot analysis
- Schema-aware template registry and preset matcher
- JSON assembler that merges matched presets into a final Elementor document
- JSON validator guard before export

## 3) Database / Storage

- `wp_options.aieb_openai_api_key`: OpenAI API key.
- WordPress uploads directory: temporary image uploads via `wp_handle_upload`.
- `templates/presets/**`: immutable template preset source library.
- `storage/logs/`: reserved for future structured logs.

## 4) Elementor JSON Schema Strategy

- Never generate raw Elementor trees from AI output directly.
- AI returns section-level semantic structure only.
- Template registry maps semantic sections to tested preset JSON blocks.
- Assembler mutates safe settings only (IDs, titles, style variables, content variables).
- Final document envelope:
  - `title`
  - `type: container`
  - `version`
  - `page_settings`
  - `content[]` from known presets

## 5) First Working MVP Implementation

- Upload screenshot in admin panel.
- Run `/aieb/v1/generate`.
- AI analysis (or fallback) produces normalized structure.
- Preset matcher selects section JSON templates.
- Assembler merges and emits valid Elementor-style JSON envelope.
- UI previews structure and allows JSON download.
