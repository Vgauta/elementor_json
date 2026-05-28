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

use ElementorVisionCore\Builders\JsonRepairEngine;
use ElementorVisionCore\Builders\JsonValidator;
use ElementorVisionCore\Builders\TemplateExporter;
use ElementorVisionCore\Presets\PresetLibrary;
use ElementorVisionCore\Presets\PresetLoader;
use ElementorVisionCore\Presets\PresetRegistry;
use ElementorVisionCore\Presets\SampleTemplatePreset;
use ElementorVisionCore\Vision\OpenAIVisionProvider;
use ElementorVisionCore\Vision\VisionAnalyzer;

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

            <form method="post" action="<?php echo $action; ?>" style="margin: 8px 0; display:flex; gap:8px; align-items:center;">
                <?php wp_nonce_field('evc_save_preset'); ?>
                <input type="hidden" name="action" value="evc_save_preset" />
                <input type="text" name="preset_name" placeholder="Preset name" required />
                <select name="preset_category"><option>hero</option><option>services</option><option>stats</option><option>CTA</option><option>footer</option><option>testimonials</option></select>
                <?php submit_button('Save Preset', 'secondary', 'submit', false); ?>
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
