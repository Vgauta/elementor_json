<?php
namespace EVAI\API;
use EVAI\AI\Orchestrator_Client; use EVAI\Elementor\Json_Builder; use EVAI\Templates\Preset_Registry; use WP_REST_Request; use WP_REST_Response;
if (!defined('ABSPATH')) exit;
class Rest_Controller{
  public function __construct(private Orchestrator_Client $ai, private Json_Builder $builder, private Preset_Registry $registry){}
  public function register():void{ add_action('rest_api_init',function(){
    register_rest_route('evai/v1','/generate',['methods'=>'POST','permission_callback'=>fn()=>current_user_can('manage_options'),'callback'=>[$this,'generate']]);
    register_rest_route('evai/v1','/presets',['methods'=>'GET','permission_callback'=>fn()=>current_user_can('manage_options'),'callback'=>fn()=>new WP_REST_Response(['presets'=>$this->registry->catalog()])]);
  });}
  public function generate(WP_REST_Request $r):WP_REST_Response{
    if(!wp_verify_nonce((string)$r->get_header('X-WP-Nonce'),'wp_rest')) return new WP_REST_Response(['message'=>'Invalid nonce'],403);
    if(empty($_FILES['image'])) return new WP_REST_Response(['message'=>'No image'],422);
    require_once ABSPATH.'wp-admin/includes/file.php'; $u=wp_handle_upload($_FILES['image'],['test_form'=>false]); if(!empty($u['error'])) return new WP_REST_Response(['message'=>$u['error']],422);
    $pipe=$this->ai->run_pipeline((string)$u['url'],['max_iterations'=>3,'target_similarity'=>0.92]);
    $assembled=$this->builder->assemble((array)($pipe['analysis']??[]),(array)$pipe);
    return new WP_REST_Response(['analysis'=>$pipe['analysis']??[],'similarity'=>$pipe['similarity']??0,'iterations'=>$pipe['iterations']??[],'elementor_json'=>$assembled['repaired_json']??[],'validation'=>$assembled]);
  }
}
