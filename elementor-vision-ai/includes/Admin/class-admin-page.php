<?php
namespace EVAI\Admin;
if(!defined('ABSPATH')) exit;
class Admin_Page{
  public function register():void{ add_action('admin_menu',[$this,'menu']); add_action('admin_enqueue_scripts',[$this,'assets']); }
  public function menu():void{ add_menu_page('Elementor Vision AI','Elementor Vision AI','manage_options','elementor-vision-ai',[$this,'render'],'dashicons-visibility',61); }
  public function assets(string $hook):void{ if($hook!=='toplevel_page_elementor-vision-ai') return; wp_enqueue_script('evai-admin',EVAI_URL.'assets/js/admin.js',['wp-element'],null,true); wp_enqueue_style('evai-admin',EVAI_URL.'assets/css/admin.css',[],null); wp_localize_script('evai-admin','EVAI_CONFIG',['restUrl'=>rest_url('evai/v1'),'nonce'=>wp_create_nonce('wp_rest')]); }
  public function render():void{ echo '<div class="wrap"><div id="evai-root"></div></div>'; }
}
