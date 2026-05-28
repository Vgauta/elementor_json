<?php

namespace AIEB;

use AIEB\Admin\Admin_Page;
use AIEB\API\Rest_Controller;
use AIEB\AI\Vision_Service;
use AIEB\Elementor\Compatibility_Service;
use AIEB\Elementor\Json_Assembler;
use AIEB\Elementor\Json_Validator;
use AIEB\Templates\Template_Registry;

if (! defined('ABSPATH')) {
    exit;
}

require_once AIEB_PLUGIN_DIR . 'includes/Admin/class-admin-page.php';
require_once AIEB_PLUGIN_DIR . 'includes/API/class-rest-controller.php';
require_once AIEB_PLUGIN_DIR . 'includes/AI/class-vision-service.php';
require_once AIEB_PLUGIN_DIR . 'includes/Elementor/class-compatibility-service.php';
require_once AIEB_PLUGIN_DIR . 'includes/Elementor/class-json-assembler.php';
require_once AIEB_PLUGIN_DIR . 'includes/Elementor/class-json-validator.php';
require_once AIEB_PLUGIN_DIR . 'includes/Templates/class-template-registry.php';

final class Plugin
{
    public static function boot(): void
    {
        add_action('plugins_loaded', [self::class, 'init']);
    }

    public static function init(): void
    {
        $registry = new Template_Registry(AIEB_PLUGIN_DIR . 'templates/presets');
        $vision = new Vision_Service();
        $validator = new Json_Validator();
        $compatibility = new Compatibility_Service();
        $assembler = new Json_Assembler($registry, $validator, $compatibility);

        (new Admin_Page())->register();
        (new Rest_Controller($vision, $assembler, $registry))->register();
    }
}
