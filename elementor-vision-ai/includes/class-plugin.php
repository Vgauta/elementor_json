<?php
namespace EVAI;
if (!defined('ABSPATH')) exit;
require_once EVAI_DIR.'includes/Admin/class-admin-page.php';
require_once EVAI_DIR.'includes/API/class-rest-controller.php';
require_once EVAI_DIR.'includes/AI/class-orchestrator-client.php';
require_once EVAI_DIR.'includes/Elementor/class-json-builder.php';
require_once EVAI_DIR.'includes/Elementor/class-json-validator.php';
require_once EVAI_DIR.'includes/Templates/class-preset-registry.php';
final class Plugin{
  public static function boot():void{ add_action('plugins_loaded',[self::class,'init']); }
  public static function init():void{
    $registry=new Templates\Preset_Registry(EVAI_DIR.'templates/presets');
    $validator=new Elementor\Json_Validator();
    $builder=new Elementor\Json_Builder($registry,$validator);
    $ai=new AI\Orchestrator_Client();
    (new Admin\Admin_Page())->register();
    (new API\Rest_Controller($ai,$builder,$registry))->register();
  }
}
