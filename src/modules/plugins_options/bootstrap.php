<?php

namespace WBF\modules\plugins_options;

use WBF\components\mvc\HTMLView;

add_action('wbf_admin_submenu', function(){
	if(has_filter('wbf/modules/plugins_options/tabs')){
		$screen = WBF()->add_submenu_page( __('Plugins Options','wbf'), __('Plugins Options','wbf'), 'manage_options', 'wbf-plugins-options', function(){
			if(isset($_POST['save-wbf-plugins-settings'])){
				if(wp_verify_nonce($_POST['_wpnonce'],'save-wbf-plugins-settings')){
					$current_saved_tab = $_POST['save-wbf-plugins-settings'];
					unset($_POST['save-wbf-plugins-settings']);
					do_action('wbf/modules/plugin_options/save_settings',$current_saved_tab,$_POST);
				}
			}
			$tabs = apply_filters('wbf/modules/plugins_options/tabs',[]);
			$tabs = array_filter($tabs,function($tab){
				return $tab instanceof OptionsTab;
			});
			$active_tab = false;
			$active_section = false;
			if(is_array($tabs) && count($tabs) > 0){
				$active_tab_slug = isset($_GET['tab']) ? $_GET['tab'] : $tabs[0]->get_slug();
				$active_tab = pluck_tab($active_tab_slug,$tabs);
				if(isset($active_tab) && $active_tab instanceof OptionsTab){
					if($active_tab->has_sections()){
						$active_section_slug = isset($_GET['section']) ? $_GET['section'] : $active_tab->get_sections()[0];
						$active_tab = pluck_tab($active_section_slug,$active_tab->get_sections());
					}
				}else {
					$active_tab = false;
				}
			}
			$v = new HTMLView('src/modules/plugins_options/views/options.php','wbf');
			$v->for_dashboard()->display([
				'page_title' => __('WBF Plugins Options','wbf'),
				'tabs' => $tabs,
				'active_tab' => $active_tab,
				'active_section' => $active_section,
				'current_uri' => isset($active_section) && $active_section ? $active_section->get_href() : isset($active_tab) && $active_tab ? $active_tab->get_href() : ""
			]);
		});
	}
},12);

/**
 * @param string $slug
 * @param array $tabs
 *
 * @return bool|mixed
 */
function pluck_tab($slug,$tabs){
	foreach ($tabs as $tab){
		if($tab->get_slug() === $slug){
			return $tab;
		}
	}
	return false;
}