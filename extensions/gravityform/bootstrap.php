<?php

namespace WBF\extensions\gravityform;

if(is_plugin_active('wp-better-emails/wpbe.php') && is_plugin_active("gravityform")):
	/**
	 * Add WP Better email support for gravity form
	 * @param $notification
	 * @param $form
	 * @param $entry
	 * @return mixed
	 */
	function wbf_gravityform_support_for_betteremail( $notification, $form, $entry ) {
		// is_plugin_active is not availble on front end
		if(!is_admin()){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );	
		}
		// does WP Better Emails exists and activated ?
		if(!is_plugin_active('wp-better-emails/wpbe.php') ){
			return $notification;	
		}
		// change notification format to text from the default html
		$notification['message_format'] = "text";
		// disable auto formatting so you don't get double line breaks
		$notification['disableAutoformat'] = true;
		return $notification;
	}
	add_filter('gform_notification', 'wbf_gravityform_support_for_betteremail', 10, 3);
endif;