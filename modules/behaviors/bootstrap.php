<?php
/*
 * Behaviors Framework.
 *
 * As all other modules, keep in mind that this piece of code will be executed during "after_setup_theme"
 *
 * @package   Behaviors Framework
 * @author    Riccardo D'Angelo <me@riccardodangelo.com>
 * @license   copyrighted
 * @link      http://www.waga.it
 * @copyright Riccardo D'Angelo and WAGA.it
 */

namespace WBF\modules\behaviors;

require_once "functions.php";

//locate_template('/inc/behaviors.php', true); //todo: questo sarebbe meglio toglierlo, mi pare superfluo, valutare se crea qlc danno

add_action("wbf/theme_options/register", '\WBF\modules\behaviors\register_behaviors_as_theme_options',11,1);

add_action( 'add_meta_boxes', '\WBF\modules\behaviors\create_metabox' );

add_action( 'save_post', '\WBF\modules\behaviors\save_metabox' );
add_action( 'pre_post_update', '\WBF\modules\behaviors\save_metabox' );
add_action( 'edit_post', '\WBF\modules\behaviors\save_metabox' );
add_action( 'publish_post', '\WBF\modules\behaviors\save_metabox' );
add_action( 'edit_page_form', '\WBF\modules\behaviors\save_metabox' );

//add_action( 'optionsframework_after_validate','waboot_reset_defaults' );

add_action("wbf_init",'\WBF\modules\behaviors\module_init');
function module_init(){
	$behaviors_file = apply_filters("wbf/modules/behaviors/include_file","/inc/behaviors");
	if(!is_file($behaviors_file)){
		$r = locate_template('/inc/behaviors.php', true);
	}else{
		require_once $behaviors_file;
	}
}