<?php

namespace WBF\modules\license_manager;

use WBF\components\license\License_Exception;
use WBF\components\license\License_Manager;
use WBF\components\mvc\HTMLView;
use WBF\components\utils\Utilities;

class License_GUI{

	static function init(){
		add_action( 'current_screen', __NAMESPACE__.'\\License_GUI::perform_page_actions', 10 );
		add_action( 'wbf_admin_submenu', __NAMESPACE__.'\\License_GUI::admin_license_menu_item', 30 );
	}

	static function perform_page_actions(){
		if(!preg_match("/wbf_licenses/",get_current_screen()->base)){
			return;
		}
		$wbf_notice_manager = Utilities::get_wbf_notice_manager();
		if(isset($_POST['update-license'])){
			try{
				if(!isset($_POST['slug'])) throw new License_Exception(__("License slug was not set","wbf"));
				if(!isset($_POST['type'])) throw new License_Exception(__("License type was not set","wbf"));
				if(!isset($_POST['code']) || empty($_POST['code'])) throw new License_Exception(__("License code was not set","wbf"));
				$slug = $_POST['slug'];
				$type = $_POST['type'];
				$code = trim($_POST['code']);
				License_Manager::update($slug,$type,$code);
				$wbf_notice_manager->add_notice("license_updated",_x("License updated!","License","wbf"),"updated","_flash_");
			}catch(License_Exception $e){
				$wbf_notice_manager->add_notice("license_not_updated",$e->getMessage(),"error","_flash_");
			}
		}elseif(isset($_POST['delete-license'])){
			try{
				if(!isset($_POST['slug'])) throw new License_Exception(__("License slug was not set","wbf"));
				if(!isset($_POST['type'])) throw new License_Exception(__("License type was not set","wbf"));
				$slug = $_POST['slug'];
				$type = $_POST['type'];
				License_Manager::delete($slug,$type);
				$wbf_notice_manager->add_notice("license_deleted",_x("License deleted!","License","wbf"),"updated","_flash_");
			}catch(License_Exception $e){
				$wbf_notice_manager->add_notice("license_not_deleted",$e->getMessage(),"error","_flash_");
			}
		}
	}

	static function admin_license_menu_item($parent_slug){
		$licenses = License_Manager::get_all();
		if(is_array($licenses) && !empty($licenses)){
			WBF()->add_submenu_page(__( "Licenses", "wbf" ), __( "Licenses", "wbf" ), "edit_theme_options", "wbf_licenses", __NAMESPACE__ . "\\License_GUI::license_page" );
		}
	}

	/**
	 * Callback for displaying the licenses page
	 */
	static function license_page(){
		$v = new HTMLView("src/modules/license_manager/views/license-manager.php","wbf");
		$vars = [
			'has_theme_licenses' => License_Manager::has_theme_licenses(),
			'has_plugin_licenses' => License_Manager::has_plugin_licenses(),
			'theme_licenses' => License_Manager::has_theme_licenses() ? License_Manager::get_theme_licenses() : [],
			'plugin_licenses' => License_Manager::has_plugin_licenses() ? License_Manager::get_plugin_licenses() : [],
		];
		$v->clean()->display($vars);
	}
}