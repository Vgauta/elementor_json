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

use ElementorVisionCore\Builders\JsonRepairEngine;
use ElementorVisionCore\Builders\JsonValidator;
use ElementorVisionCore\Builders\TemplateExporter;
use ElementorVisionCore\Presets\SampleTemplatePreset;

class Elementor_Vision_Core_Plugin {
    public function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('admin_post_evc_generate_template', [$this, 'handle_generate_template']);
        add_action('admin_post_evc_validate_template', [$this, 'handle_validate_template']);
        add_action('admin_post_evc_repair_template', [$this, 'handle_repair_template']);
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
