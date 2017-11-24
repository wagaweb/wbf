<?php

namespace WBF\components\utils;

class Crons{
	/**
	 * Check if the $hook was scheduled by wp_schedule_single_event()
	 *
	 * @param $hook
	 * @return bool
	 */
	public static function is_single_event_scheduled($hook){
		$crons = _get_cron_array();
		foreach ($crons as $timestamp => $events){
			if(\is_array($events) && isset($events[$hook])){
				return true;
			}
		}
		return false;
	}
}