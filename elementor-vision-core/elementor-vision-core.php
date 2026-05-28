<?php
/**
 * Plugin Name: Elementor Vision Core
 * Description: Generates real importable Elementor JSON templates using flexbox containers.
 * Version: 1.1.0
 * Author: Elementor Vision
 * Requires at least: 6.4
 * Requires PHP: 7.4
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/Builders/StyleBuilder.php';
require_once __DIR__ . '/includes/Builders/ResponsiveBuilder.php';
require_once __DIR__ . '/includes/Builders/WidgetBuilder.php';
require_once __DIR__ . '/includes/Builders/ContainerBuilder.php';
require_once __DIR__ . '/includes/Builders/TemplateExporter.php';
require_once __DIR__ . '/includes/Builders/ElementorSchemaChecker.php';
require_once __DIR__ . '/includes/Builders/JsonValidator.php';
require_once __DIR__ . '/includes/Builders/JsonRepairEngine.php';
require_once __DIR__ . '/includes/Presets/SampleTemplatePreset.php';
require_once __DIR__ . '/includes/Presets/PresetRegistry.php';
require_once __DIR__ . '/includes/Presets/PresetLoader.php';
require_once __DIR__ . '/includes/Presets/PresetMatcher.php';
require_once __DIR__ . '/includes/Presets/PresetLibrary.php';
require_once __DIR__ . '/includes/Vision/AiProviderInterface.php';
require_once __DIR__ . '/includes/Vision/OpenAIVisionProvider.php';
require_once __DIR__ . '/includes/Vision/SectionDetector.php';
require_once __DIR__ . '/includes/Vision/StyleDetector.php';
require_once __DIR__ . '/includes/Vision/LayoutExtractor.php';
require_once __DIR__ . '/includes/Vision/VisionAnalyzer.php';
require_once __DIR__ . '/includes/Matching/LayoutComparator.php';
require_once __DIR__ . '/includes/Matching/SimilarityScorer.php';
require_once __DIR__ . '/includes/Matching/PresetMatchingEngine.php';
require_once __DIR__ . '/includes/Assembly/PresetMerger.php';
require_once __DIR__ . '/includes/Assembly/StyleInjector.php';
require_once __DIR__ . '/includes/Assembly/ContentInjector.php';
require_once __DIR__ . '/includes/Assembly/JsonAssembler.php';
require_once __DIR__ . '/includes/Refinement/ScreenshotRenderer.php';
require_once __DIR__ . '/includes/Refinement/DifferenceAnalyzer.php';
require_once __DIR__ . '/includes/Refinement/CorrectionGenerator.php';
require_once __DIR__ . '/includes/Refinement/VisualDiffEngine.php';
require_once __DIR__ . '/includes/Orchestration/ProviderClientInterface.php';
require_once __DIR__ . '/includes/Orchestration/HttpProviderClient.php';
require_once __DIR__ . '/includes/Orchestration/OpenAIClient.php';
require_once __DIR__ . '/includes/Orchestration/GenericOpenAICompatibleClient.php';
require_once __DIR__ . '/includes/Orchestration/ProviderFactory.php';
require_once __DIR__ . '/includes/Orchestration/SharedMemory.php';
require_once __DIR__ . '/includes/Orchestration/TaskQueue.php';
require_once __DIR__ . '/includes/Orchestration/AgentInterface.php';
require_once __DIR__ . '/includes/Orchestration/BaseAgent.php';
require_once __DIR__ . '/includes/Orchestration/Agents.php';
require_once __DIR__ . '/includes/Orchestration/AgentCommunicator.php';
require_once __DIR__ . '/includes/Orchestration/MultiAgentOrchestrator.php';

use ElementorVisionCore\Builders\JsonRepairEngine;
use ElementorVisionCore\Builders\JsonValidator;
use ElementorVisionCore\Builders\TemplateExporter;
use ElementorVisionCore\Presets\PresetLibrary;
use ElementorVisionCore\Presets\PresetLoader;
use ElementorVisionCore\Presets\PresetRegistry;
use ElementorVisionCore\Presets\SampleTemplatePreset;
use ElementorVisionCore\Vision\OpenAIVisionProvider;
use ElementorVisionCore\Vision\VisionAnalyzer;
use ElementorVisionCore\Matching\PresetMatchingEngine;
use ElementorVisionCore\Assembly\JsonAssembler;
use ElementorVisionCore\Refinement\VisualDiffEngine;
use ElementorVisionCore\Orchestration\MultiAgentOrchestrator;
use ElementorVisionCore\Orchestration\ProviderFactory;

class Elementor_Vision_Core_Plugin {
    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_post_evc_generate_template', [$this, 'handle_generate_template']);
        add_action('admin_post_evc_validate_template', [$this, 'handle_validate_template']);
        add_action('admin_post_evc_repair_template', [$this, 'handle_repair_template']);
        add_action('admin_post_evc_load_preset', [$this, 'handle_load_preset']);
        add_action('admin_post_evc_save_preset', [$this, 'handle_save_preset']);
        add_action('admin_post_evc_duplicate_preset', [$this, 'handle_duplicate_preset']);
        add_action('admin_post_evc_analyze_screenshot', [$this, 'handle_analyze_screenshot']);
        add_action('admin_post_evc_match_presets', [$this, 'handle_match_presets']);
        add_action('admin_post_evc_assemble_template', [$this, 'handle_assemble_template']);
        add_action('admin_post_evc_visual_refine', [$this, 'handle_visual_refine']);
        add_action('admin_post_evc_run_orchestration', [$this, 'handle_run_orchestration']);
    }

    public function register_admin_menu() {
        add_menu_page('Elementor Vision Core', 'Elementor Vision Core', 'manage_options', 'elementor-vision-core', [$this, 'render_admin_page'], 'dashicons-layout', 58);
    }

    public function render_admin_page() {
        if (! current_user_can('manage_options')) {
            return;
        }
        $action = esc_url(admin_url('admin-post.php'));
        $report = get_transient('evc_last_report');
        $registry = new PresetRegistry();
        $registry->seed_defaults((new PresetLibrary())->defaults());
        $presets = $registry->all();
        ?>
        <div class="wrap">
            <h1>Elementor Vision Core</h1>
            <p>Generate, validate, and repair Elementor container templates.</p>

            <form method="post" action="<?php echo $action; ?>" style="margin-bottom:12px;">
                <?php wp_nonce_field('evc_generate_template'); ?>
                <input type="hidden" name="action" value="evc_generate_template" />
                <?php submit_button('Generate Sample Template', 'primary', 'submit', false); ?>
            </form>

            <form method="post" action="<?php echo $action; ?>" style="margin-bottom:12px;">
                <?php wp_nonce_field('evc_validate_template'); ?>
                <input type="hidden" name="action" value="evc_validate_template" />
                <?php submit_button('Validate Template', 'secondary', 'submit', false); ?>
            </form>

            <form method="post" action="<?php echo $action; ?>">
                <?php wp_nonce_field('evc_repair_template'); ?>
                <input type="hidden" name="action" value="evc_repair_template" />
                <?php submit_button('Repair Template', 'secondary', 'submit', false); ?>
            </form>


            <h2>Preset Registry</h2>
            <form method="post" action="<?php echo $action; ?>" style="margin: 8px 0; display:flex; gap:8px; align-items:center;">
                <?php wp_nonce_field('evc_load_preset'); ?>
                <input type="hidden" name="action" value="evc_load_preset" />
                <select name="preset_id"><?php foreach ($presets as $preset_id => $preset) : ?><option value="<?php echo esc_attr($preset_id); ?>"><?php echo esc_html($preset['name'] . ' [' . $preset['category'] . ']'); ?></option><?php endforeach; ?></select>
                <?php submit_button('Load Preset', 'secondary', 'submit', false); ?>
            </form>
            <form method="post" action="<?php echo $action; ?>" style="margin: 8px 0; display:flex; gap:8px; align-items:center;">
                <?php wp_nonce_field('evc_duplicate_preset'); ?>
                <input type="hidden" name="action" value="evc_duplicate_preset" />
                <select name="preset_id"><?php foreach ($presets as $preset_id => $preset) : ?><option value="<?php echo esc_attr($preset_id); ?>"><?php echo esc_html($preset['name']); ?></option><?php endforeach; ?></select>
                <?php submit_button('Duplicate Preset', 'secondary', 'submit', false); ?>
            </form>

            <h2>Screenshot Analysis</h2>
            <form method="post" action="<?php echo $action; ?>" enctype="multipart/form-data" style="margin-bottom:12px;display:flex;gap:8px;align-items:center;">
                <?php wp_nonce_field('evc_analyze_screenshot'); ?>
                <input type="hidden" name="action" value="evc_analyze_screenshot" />
                <input type="file" name="screenshot" accept="image/*" required />
                <input type="text" name="openai_api_key" placeholder="OpenAI API Key" style="min-width:280px;" required />
                <?php submit_button('Analyze Screenshot', 'primary', 'submit', false); ?>
            </form>

            <form method="post" action="<?php echo $action; ?>" style="margin-bottom:12px;">
                <?php wp_nonce_field('evc_match_presets'); ?>
                <input type="hidden" name="action" value="evc_match_presets" />
                <?php submit_button('Match Presets from Analysis', 'secondary', 'submit', false); ?>
            </form>

            <form method="post" action="<?php echo $action; ?>" style="margin-bottom:12px;">
                <?php wp_nonce_field('evc_assemble_template'); ?>
                <input type="hidden" name="action" value="evc_assemble_template" />
                <?php submit_button('Assemble Template from Matches', 'primary', 'submit', false); ?>
            </form>

            <form method="post" action="<?php echo $action; ?>" style="margin: 8px 0; display:flex; gap:8px; align-items:center;">
                <?php wp_nonce_field('evc_save_preset'); ?>
                <input type="hidden" name="action" value="evc_save_preset" />
                <input type="text" name="preset_name" placeholder="Preset name" required />
                <select name="preset_category"><option>hero</option><option>services</option><option>stats</option><option>CTA</option><option>footer</option><option>testimonials</option></select>
                <?php submit_button('Save Preset', 'secondary', 'submit', false); ?>
            </form>


            <h2>Visual Comparison Refinement</h2>
            <form method="post" action="<?php echo $action; ?>" style="margin-bottom:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <?php wp_nonce_field('evc_visual_refine'); ?>
                <input type="hidden" name="action" value="evc_visual_refine" />
                <input type="url" name="generated_url" placeholder="Generated page URL" required style="min-width:280px;" />
                <input type="text" name="original_screenshot_path" placeholder="Original screenshot path" required style="min-width:260px;" />
                <input type="text" name="generated_screenshot_path" placeholder="Generated screenshot output path" required style="min-width:260px;" />
                <input type="text" name="diff_output_path" placeholder="Diff output path" required style="min-width:220px;" />
                <?php submit_button('Run Visual Diff', 'secondary', 'submit', false); ?>
            </form>


            <h2>Multi-Agent Orchestration</h2>
            <form method="post" action="<?php echo $action; ?>" style="margin-bottom:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <?php wp_nonce_field('evc_run_orchestration'); ?>
                <input type="hidden" name="action" value="evc_run_orchestration" />
                <select name="provider"><option value="openai">OpenAI</option><option value="claude">Claude</option><option value="gemini">Gemini</option><option value="deepseek">DeepSeek</option><option value="qwen">Qwen</option><option value="openrouter">OpenRouter</option></select>
                <input type="text" name="api_key" placeholder="Provider API Key" required style="min-width:280px;" />
                <input type="number" name="iterations" min="1" max="5" value="2" />
                <?php submit_button('Run Orchestration', 'primary', 'submit', false); ?>
            </form>

            <?php if (is_array($report)) : ?>
                <h2>Latest Report</h2>
                <pre style="background:#fff;padding:12px;border:1px solid #dcdcde;max-height:300px;overflow:auto;"><?php echo esc_html(wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
            <?php endif; ?>
        </div>
        <?php
        delete_transient('evc_last_report');
    }

    private function build_sample_json_array(): array {
        $preset = new SampleTemplatePreset();
        return [
            'version' => '0.4',
            'title' => 'Elementor Vision Sample Template',
            'type' => 'page',
            'content' => $preset->build(),
            'page_settings' => [],
        ];
    }

    public function handle_generate_template() {
        if (! current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('evc_generate_template');

        $exporter = new TemplateExporter();
        $json = $exporter->export($this->build_sample_json_array()['content'], 'Elementor Vision Sample Template');
        $this->download_json($json, 'elementor-vision-sample-template');
    }

    public function handle_validate_template() {
        if (! current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('evc_validate_template');

        $validator = new JsonValidator();
        $result = $validator->validate($this->build_sample_json_array());

        set_transient('evc_last_report', ['action' => 'validate', 'result' => $result], 120);
        wp_safe_redirect(admin_url('admin.php?page=elementor-vision-core'));
        exit;
    }

    public function handle_repair_template() {
        if (! current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        check_admin_referer('evc_repair_template');

        $json = $this->build_sample_json_array();
        $json['content'][0]['id'] = 'dup-id';
        $json['content'][0]['elements'][0]['id'] = 'dup-id';

        $repair = new JsonRepairEngine();
        $repair_result = $repair->repair($json);

        $validator = new JsonValidator();
        $validation = $validator->validate($repair_result['json']);

        set_transient('evc_last_report', [
            'action' => 'repair',
            'repair_logs' => $repair_result['logs'],
            'validation_after_repair' => $validation,
        ], 120);

        $exporter = new TemplateExporter();
        $fixed = $exporter->export($repair_result['json']['content'], $repair_result['json']['title']);
        $this->download_json($fixed, 'elementor-vision-repaired-template');
    }


    public function handle_load_preset() {
        if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }
        check_admin_referer('evc_load_preset');
        $id = sanitize_key($_POST['preset_id'] ?? '');
        $registry = new PresetRegistry();
        $preset = $registry->get($id);
        if (! $preset) { wp_die('Preset not found'); }
        $payload = (new PresetLoader())->payload($preset);
        $json = wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->download_json((string) $json, 'elementor-vision-preset-' . $id);
    }

    public function handle_save_preset() {
        if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }
        check_admin_referer('evc_save_preset');
        $name = sanitize_text_field($_POST['preset_name'] ?? 'Custom Preset');
        $category = sanitize_text_field($_POST['preset_category'] ?? 'hero');
        $allowed = ['hero','services','stats','CTA','footer','testimonials'];
        if (! in_array($category, $allowed, true)) { $category = 'hero'; }
        $preset = [
            'id' => sanitize_key(strtolower(str_replace(' ', '-', $name)) . '-' . wp_generate_password(4, false, false)),
            'name' => $name,
            'category' => $category,
            'metadata' => ['style' => 'custom', 'layout_type' => 'custom', 'color_scheme' => 'custom', 'industry' => 'general', 'tags' => ['custom']],
            'content' => (new SampleTemplatePreset())->build(),
        ];
        (new PresetRegistry())->save($preset);
        set_transient('evc_last_report', ['action' => 'save_preset', 'preset' => $preset['id']], 120);
        wp_safe_redirect(admin_url('admin.php?page=elementor-vision-core')); exit;
    }

    public function handle_duplicate_preset() {
        if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }
        check_admin_referer('evc_duplicate_preset');
        $id = sanitize_key($_POST['preset_id'] ?? '');
        $new_id = (new PresetRegistry())->duplicate($id);
        set_transient('evc_last_report', ['action' => 'duplicate_preset', 'source' => $id, 'duplicate' => $new_id], 120);
        wp_safe_redirect(admin_url('admin.php?page=elementor-vision-core')); exit;
    }


    public function handle_analyze_screenshot() {
        if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }
        check_admin_referer('evc_analyze_screenshot');

        if (! isset($_FILES['screenshot']) || empty($_FILES['screenshot']['tmp_name'])) {
            wp_die('Screenshot file is required.');
        }

        $tmp = $_FILES['screenshot']['tmp_name'];
        $mime = mime_content_type($tmp);
        if (! is_string($mime) || strpos($mime, 'image/') !== 0) {
            wp_die('Uploaded file must be an image.');
        }

        $bytes = file_get_contents($tmp);
        if ($bytes === false) {
            wp_die('Unable to read uploaded file.');
        }

        $api_key = sanitize_text_field($_POST['openai_api_key'] ?? '');
        $data_url = 'data:' . $mime . ';base64,' . base64_encode($bytes);

        $provider = new OpenAIVisionProvider($api_key);
        $analyzer = new VisionAnalyzer($provider);
        $result = $analyzer->analyze($data_url);

        set_transient('evc_last_report', ['action' => 'analyze_screenshot', 'result' => $result], 300);
        wp_safe_redirect(admin_url('admin.php?page=elementor-vision-core'));
        exit;
    }


    public function handle_match_presets() {
        if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }
        check_admin_referer('evc_match_presets');

        $report = get_transient('evc_last_report');
        $layout_map = $report['result']['layout_map'] ?? null;
        if (! is_array($layout_map)) {
            set_transient('evc_last_report', ['action' => 'match_presets', 'error' => 'Run screenshot analysis first.'], 180);
            wp_safe_redirect(admin_url('admin.php?page=elementor-vision-core')); exit;
        }

        $registry = new PresetRegistry();
        $registry->seed_defaults((new PresetLibrary())->defaults());
        $presets = $registry->all();

        $engine = new PresetMatchingEngine();
        $matches = $engine->match($layout_map, $presets, 3);

        set_transient('evc_last_report', [
            'action' => 'match_presets',
            'layout_map' => $layout_map,
            'matches' => $matches,
        ], 300);

        wp_safe_redirect(admin_url('admin.php?page=elementor-vision-core')); exit;
    }


    public function handle_assemble_template() {
        if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }
        check_admin_referer('evc_assemble_template');

        $report = get_transient('evc_last_report');
        $matches = $report['matches']['sections_matched'] ?? [];
        if (! is_array($matches) || empty($matches)) {
            set_transient('evc_last_report', ['action' => 'assemble_template', 'error' => 'Run preset matching first.'], 180);
            wp_safe_redirect(admin_url('admin.php?page=elementor-vision-core')); exit;
        }

        $registry = new PresetRegistry();
        $registry->seed_defaults((new PresetLibrary())->defaults());

        $payloads = [];
        foreach ($matches as $match) {
            $selected = $match['selected_preset']['preset_id'] ?? '';
            if (! is_string($selected) || $selected === '') { continue; }
            $preset = $registry->get($selected);
            if (! $preset) { continue; }
            $payloads[] = (new PresetLoader())->payload($preset);
        }

        if (empty($payloads)) {
            set_transient('evc_last_report', ['action' => 'assemble_template', 'error' => 'No preset payloads resolved.'], 180);
            wp_safe_redirect(admin_url('admin.php?page=elementor-vision-core')); exit;
        }

        $style_overrides = [
            'colors' => ['background_color' => '#FFFFFF'],
            'spacing' => ['padding_mobile' => ['unit' => 'px', 'top' => '24', 'right' => '16', 'bottom' => '24', 'left' => '16', 'isLinked' => false]],
            'typography' => ['typography_font_family' => 'Inter'],
        ];
        $content_overrides = ['button_text' => 'Get Started', 'heading_text' => 'Assembled Layout', 'body_text' => 'Template assembled from best preset matches.'];

        $assembler = new JsonAssembler();
        $assembled = $assembler->assemble($payloads, $style_overrides, $content_overrides);

        set_transient('evc_last_report', ['action' => 'assemble_template', 'assembly' => $assembled], 300);

        $json = wp_json_encode($assembled['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->download_json((string) $json, 'elementor-vision-assembled-template');
    }


    public function handle_visual_refine() {
        if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }
        check_admin_referer('evc_visual_refine');

        $engine = new VisualDiffEngine();
        $result = $engine->run([
            'generated_url' => esc_url_raw($_POST['generated_url'] ?? ''),
            'original_screenshot_path' => sanitize_text_field($_POST['original_screenshot_path'] ?? ''),
            'generated_screenshot_path' => sanitize_text_field($_POST['generated_screenshot_path'] ?? ''),
            'diff_output_path' => sanitize_text_field($_POST['diff_output_path'] ?? ''),
            'viewport' => ['width' => 1440, 'height' => 2200],
            'threshold' => 0.1,
        ]);

        set_transient('evc_last_report', ['action' => 'visual_refine', 'result' => $result], 300);
        wp_safe_redirect(admin_url('admin.php?page=elementor-vision-core'));
        exit;
    }


    public function handle_run_orchestration() {
        if (! current_user_can('manage_options')) { wp_die('Unauthorized'); }
        check_admin_referer('evc_run_orchestration');

        $provider = sanitize_text_field($_POST['provider'] ?? 'openai');
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        $iterations = (int) ($_POST['iterations'] ?? 2);

        if ($api_key === '') {
            set_transient('evc_last_report', ['action' => 'orchestration', 'error' => 'API key required'], 180);
            wp_safe_redirect(admin_url('admin.php?page=elementor-vision-core')); exit;
        }

        $client = ProviderFactory::make($provider, $api_key, []);
        $orchestrator = new MultiAgentOrchestrator($client);

        $input = [
            'layout_map' => ((array) get_transient('evc_last_report'))['result']['layout_map'] ?? [],
            'match_report' => ((array) get_transient('evc_last_report'))['matches'] ?? [],
            'assembly_report' => ((array) get_transient('evc_last_report'))['assembly'] ?? [],
            'visual_diff' => ((array) get_transient('evc_last_report'))['result'] ?? [],
        ];

        $result = $orchestrator->run($input, ['iterations' => $iterations]);

        set_transient('evc_last_report', ['action' => 'orchestration', 'provider' => $provider, 'result' => $result], 300);
        wp_safe_redirect(admin_url('admin.php?page=elementor-vision-core')); exit;
    }

    private function download_json(string $json, string $prefix): void {
        $filename = $prefix . '-' . gmdate('Y-m-d-H-i-s') . '.json';
        nocache_headers();
        header('Content-Description: File Transfer');
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Transfer-Encoding: binary');
        echo $json;
        exit;
    }
}

new Elementor_Vision_Core_Plugin();
