<?php
namespace EVAI\Elementor;
use EVAI\Templates\Preset_Registry;
if (!defined('ABSPATH')) exit;
class Json_Builder {
  public function __construct(private Preset_Registry $registry, private Json_Validator $validator){}
  public function assemble(array $analysis,array $refinement=[]):array{
    $content=[];
    foreach(($analysis['sections']??[]) as $s){$m=$this->registry->match($s);$p=$this->registry->load($m['path']); if(!$p) continue; $p['id']=substr(md5(wp_generate_uuid4()),0,7); if(isset($s['style']['background_color']))$p['settings']['background_color']=sanitize_hex_color($s['style']['background_color'])?:($p['settings']['background_color']??'#fff'); $content[]=$p;}
    $doc=['title'=>sanitize_text_field($analysis['page_title']??'EVAI Output'),'type'=>'page','version'=>'0.4','page_settings'=>[],'content'=>$content];
    return $this->validator->validate_and_repair($doc);
  }
}
