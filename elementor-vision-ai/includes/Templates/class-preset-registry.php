<?php
namespace EVAI\Templates;
if (!defined('ABSPATH')) exit;
class Preset_Registry {
  public function __construct(private string $dir){}
  public function catalog():array { return [
    'hero-dark-enterprise-01'=>['path'=>'enterprise/hero-dark-enterprise-01.json','type'=>'hero','layout'=>'2-column'],
    'stats-inline-02'=>['path'=>'minimal/stats-inline-02.json','type'=>'stats','layout'=>'inline'],
    'services-grid-03'=>['path'=>'agency/services-grid-03.json','type'=>'services','layout'=>'grid'],
    'cta-banner-01'=>['path'=>'agency/cta-banner-01.json','type'=>'cta','layout'=>'single'],
    'footer-corporate-02'=>['path'=>'corporate/footer-corporate-02.json','type'=>'footer','layout'=>'stack'],
  ];}
  public function match(array $section):array {
    $best=['key'=>'hero-dark-enterprise-01','score'=>-1,'path'=>$this->dir.'/enterprise/hero-dark-enterprise-01.json'];
    foreach($this->catalog() as $k=>$m){$s=(($m['type']??'')===($section['type']??''))*3 + (($m['layout']??'')===($section['layout']??''))*2; if($s>$best['score'])$best=['key'=>$k,'score'=>$s,'path'=>$this->dir.'/'.$m['path']];}
    return $best;
  }
  public function load(string $path):array { $d=@file_get_contents($path); $j=json_decode((string)$d,true); return is_array($j)?$j:[]; }
}
