<?php
/**
 * Plugin Name: AI Elementor Builder
 * Description: Upload a screenshot and generate schema-aware Elementor container JSON templates via AI-guided template assembly.
 * Version: 0.1.0
 * Author: AI Elementor Builder
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Text Domain: ai-elementor-builder
 */

if (! defined('ABSPATH')) {
    exit;
}

define('AIEB_VERSION', '0.1.0');
define('AIEB_PLUGIN_FILE', __FILE__);
define('AIEB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AIEB_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once AIEB_PLUGIN_DIR . 'includes/class-plugin.php';

\AIEB\Plugin::boot();
