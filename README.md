# AI Elementor Builder (Container-Only MVP)

## Architecture Highlights
- Container-only Elementor JSON generation (`elType: container` roots + valid widget children).
- No deprecated section/column output.
- Schema-aware template assembly from preset library only.
- WordPress REST endpoint with nonce, capability, upload type/size guards.

## Storage
- Option key: `aieb_openai_api_key`.
- Presets: `templates/presets/*/*.json`.
- Uploads: WordPress-managed temporary files via `wp_handle_upload`.

## Elementor Compatibility Strategy
- Top-level document: `type: page`, `version: 0.4`, `page_settings`, `content[]`.
- Every content node validated as:
  - container: `{id, elType: container, isInner, settings, elements[]}`
  - widget: `{id, elType: widget, widgetType, settings, elements: []}`
- Nested containers force `isInner: true`; root containers force `isInner: false`.
- Generated IDs are short Elementor-style alphanumerics.

## Security and Performance
- `manage_options` + `X-WP-Nonce` enforced.
- MIME allow-list: JPG/PNG/WEBP.
- 8MB upload limit.
- AI call fallback path avoids hard failures.

## MVP Flow
1. Upload screenshot from plugin admin page.
2. Vision model returns semantic section map only.
3. Registry selects closest preset per section.
4. Assembler normalizes container tree and injects dynamic styles/titles.
5. Validator blocks invalid Elementor shape.
6. User previews and downloads importable JSON.
