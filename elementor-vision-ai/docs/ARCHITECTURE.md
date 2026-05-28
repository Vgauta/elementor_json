# Elementor Vision AI MVP Architecture

## Multi-Agent pipeline
1. Vision Analyzer
2. Design Pattern Matcher
3. Elementor JSON Engineer
4. Responsive Layout Engineer
5. Validation & Repair Agent
6. Visual Diff Comparison Agent (iterative loop)

## Flow
Upload screenshot -> Node orchestrator loop -> semantic analysis -> preset matching -> JSON assembly from presets -> validation -> similarity report -> export.

## Folder structure
- `elementor-vision-ai/` WordPress plugin
- `node-orchestrator/` multi-agent orchestration server with iterative refinement loop
