<?php
namespace EVAI\AI;
if (!defined('ABSPATH')) exit;
class Orchestrator_Client {
  public function run_pipeline(string $image_url,array $options=[]):array{
    $endpoint=(string)get_option('evai_orchestrator_url','http://127.0.0.1:8787');
    $res=wp_remote_post($endpoint.'/pipeline/run',['headers'=>['Content-Type'=>'application/json'],'body'=>wp_json_encode(['image_url'=>$image_url,'options'=>$options]),'timeout'=>120]);
    if(is_wp_error($res)) return ['analysis'=>$this->fallback(),'iterations'=>[],'similarity'=>0];
    $body=json_decode((string)wp_remote_retrieve_body($res),true);
    return is_array($body)?$body:['analysis'=>$this->fallback(),'iterations'=>[],'similarity'=>0];
  }
  private function fallback():array{return ['page_title'=>'Fallback','sections'=>[['type'=>'hero','layout'=>'2-column','style'=>['background_color'=>'#111827'],'elements'=>['heading','text','button']]]];}
}
