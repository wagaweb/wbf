<?php

namespace WBF\modules\update_channels;

add_action('wbf/admins/status_page/before_render', function(){
	if(isset($_POST['wbf_update_versions_channels'])){
		$nonce_verified = wp_verify_nonce($_POST['_wpnonce'],'wbf_update_versions_channels');
		if($nonce_verified){
			$update_channels = get_option('wbf_registered_update_channels',[]);
			$update_channels = array_merge($update_channels,$_POST['channels']);
			update_option('wbf_registered_update_channels',$update_channels);
		}
	}
});

add_action("wbf/admins/status_page/sections", function(){
	$v = new \WBF\components\mvc\HTMLView("src/modules/update_channels/views/update-channels-table.php","wbf");

	$update_channels = apply_filters('wbf/update_channels/available',[]);
	$update_channels_values = get_option('wbf_registered_update_channels',[]);

	$v->display([
		'registered_updates_channels' => $update_channels,
		'registered_updates_channels_values' => $update_channels_values
	]);
});

add_filter('wbf/update_channels/available',function($channels){
	$channels['wbf'] = [
		'name' => 'Waboot Framework',
		'slug' => 'wbf',
		'channels' => [
			'Stable' => 'stable',
			'Beta' => 'beta',
			'Dev' => 'dev'
		]
	];
	return $channels;
});

/**
 * @param $name
 *
 * @return bool
 */
function get_update_channel($name){
	$channels = get_option('wbf_registered_update_channels',[]);
	if(isset($channels[$name])) return $channels[$name];
	return false;
}