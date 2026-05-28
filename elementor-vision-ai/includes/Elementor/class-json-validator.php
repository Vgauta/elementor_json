<?php
namespace EVAI\Elementor;
if (!defined('ABSPATH')) exit;
class Json_Validator {
  public function validate_and_repair(array $doc):array{
    $errors=[];$repairs=[];
    $doc['type']=$doc['type']??'page'; if($doc['type']!=='page'){$errors[]='type';$doc['type']='page';$repairs[]='fix type';}
    $doc['version']=$doc['version']??'0.4'; $doc['page_settings']=is_array($doc['page_settings']??null)?$doc['page_settings']:[];
    $doc['content']=is_array($doc['content']??null)?$doc['content']:[];
    foreach($doc['content'] as &$c){$c['elType']='container';$c['isInner']=false;$c['elements']=is_array($c['elements']??null)?$c['elements']:[];}
    return ['valid'=>count($doc['content'])>0,'repaired_json'=>$doc,'errors'=>$errors,'repairs'=>$repairs];
  }
}
