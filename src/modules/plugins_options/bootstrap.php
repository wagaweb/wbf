<?php

namespace WBF\modules\plugins_options;

add_action('wbf_admin_submenu', function(){
	if(has_filter('wbf/modules/plugins_options/tabs')){
		$tabs = apply_filters('wbf/modules/plugins_options/tabs',[]);

	}
});