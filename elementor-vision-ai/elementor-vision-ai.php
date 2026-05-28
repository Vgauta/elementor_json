<?php
/**
 * Plugin Name: Elementor Vision AI
 * Description: Multi-agent screenshot-to-Elementor reconstruction engine with iterative visual diff refinement.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 */
if (!defined('ABSPATH')) exit;
define('EVAI_DIR', plugin_dir_path(__FILE__));
define('EVAI_URL', plugin_dir_url(__FILE__));
require_once EVAI_DIR.'includes/class-plugin.php';
\EVAI\Plugin::boot();
