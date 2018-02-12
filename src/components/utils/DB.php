<?php
namespace WBF\components\utils;


class DB {
	/**
	 * Assure existence of $table_name.
	 *
	 * @param string $table_name (eventual prefix will be stripped out)
	 *
	 * @return bool
	 */
	static function table_exists($table_name){
		global $wpdb;
		$wpdb->suppress_errors();
		static $cache;

		//If prefix already exists, strip it
		if(preg_match("/^{$wpdb->prefix}/",$table_name)){
			$table_name = preg_replace("/^$wpdb->prefix/","",$table_name);
		}

		if(isset($cache[$table_name])) return $cache[$table_name];

		$search = $wpdb->query("SHOW TABLES LIKE '".$wpdb->prefix.$table_name."'");
		if($search){
			$cache[$table_name] = true;
			return true;
		}
		$cache[$table_name] = false;
		return false;
	}

	/**
	 * @param $table_name
	 * @param $fields
	 * @param array $primary_keys
	 *
	 * @return bool
	 */
	static function create_table($table_name,$fields,$primary_keys = []){
		global $wpdb;
		$wpdb->suppress_errors();
		$charset_collate = $wpdb->get_charset_collate();
		$sql = 'CREATE TABLE `'.$wpdb->prefix.$table_name.'` (';
		foreach ($fields as $field_name => $field_args){
			$sql .= '`'.$field_name.'` '.$field_args.' ,';
		}
		if(!empty($primary_keys)){
			$sql .= 'PRIMARY KEY (`'.implode('`,`',$primary_keys).'`)';
		}else{
			$sql = rtrim($sql,',');
		}
		$sql .= ') '.$charset_collate.';';
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
		return self::table_exists($table_name);
	}
}