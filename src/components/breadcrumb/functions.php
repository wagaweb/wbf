<?php
/*
 * This file is part of WBF Framework: https://github.com/wagaweb/wbf
 *
 * @author WAGA Team <dev@waga.it>
 */

namespace WBF\components\breadcrumb;

require_once( dirname(__FILE__).'/vendor/breadcrumb-trail.php');

/**
 * Shows a breadcrumb for all types of pages.  This is a wrapper function for the Breadcrumb_Trail class,
 * which should be used in theme templates.
 *
 * It uses the Wordpress permalink structures to build the trails.
 *
 * @since  0.1.0
 * @access public
 * @param  array $args Arguments to pass to Breadcrumb_Trail.
 *                     The available options are the default ones for Breadcrumb_Trail (https://github.com/justintadlock/breadcrumb-trail#parameters), plus:
 *                     - wrapper_start: a wrapper open tag (it wraps all the content of the container)
 *                     - wrapper_end: the wrapper close tag
 *                     - additional_classes: a string (space separated) of classes to add to breadcrumb container (since 0.3.10)
 * @return void
 */
function trail($args = []){
	if(function_exists('is_bbpress') && is_bbpress()){
		$breadcrumb = new \bbPress_Breadcrumb_Trail( $args );
	}
	else{
		$breadcrumb = new WBF_Breadcrumb_Trail( $args );	
	}
	$breadcrumb->trail();
}