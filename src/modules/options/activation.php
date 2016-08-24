<?php
/**
 * @package   Options Module
 * @author    Riccardo D'Angelo <riccardo@waga.it>, WAGA <dev@waga.it>
 * @license   GPL-2.0+
 * @link      http://www.waboot.com
 * @copyright WAGA.it
 * 
 * Based on Devin Price' Options_Framework
 */

namespace WBF\modules\options;

add_action("wbf_activated",'\WBF\modules\options\set_theme_option_root_id');

function set_theme_option_root_id(){
	Framework::set_theme_option_default_root_id();
}