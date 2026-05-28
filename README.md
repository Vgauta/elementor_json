# AI Elementor Builder

## Template Learning System
- Admin can save existing Elementor pages as reusable AI presets (`POST /aieb/v1/save-preset`).
- Learned presets are categorized and tagged (`category`, `tags`).
- Learned template metadata is persisted in uploads: `wp-content/uploads/aieb-presets/index.json`.
- Preset JSON files are stored in `wp-content/uploads/aieb-presets/*.json`.
- AI performs semantic screenshot analysis only; matching improves over time by scoring against both core presets and learned presets.
- Final export always assembles from preset templates (core + learned), never raw AI-generated Elementor structures.
