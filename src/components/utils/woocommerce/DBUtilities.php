<?php

namespace WBF\components\utils\woocommerce;

use WBF\components\utils\Utilities;

trait DBUtilities {
	/**
	 * Set a variable product 'out-of-stock' directly from the DB if needed.
	 *
	 * This function can help when you have products with 0 as stock quantity, but that are not properly set out-of-stock.
	 *
	 * @param $post_id
	 *
	 * @param bool $pretend
	 *
	 * @return bool|false|int
	 */
	public static function db_variable_product_maybe_set_out_of_stock($post_id,$pretend = false){
		global $wpdb;
		$posts_table = $wpdb->prefix."posts";
		$metas_table = $wpdb->prefix."postmeta";
		$variations = $wpdb->get_results("SELECT ID FROM {$posts_table} WHERE post_parent = {$post_id}");
		$is_out_of_stock = true;
		foreach($variations as $v){
			$stock_qnt = $wpdb->get_var("SELECT meta_value FROM {$metas_table} WHERE meta_key = '_stock' AND post_id = {$v->ID}");
			if(is_int($stock_qnt) && $stock_qnt > 0){
				$is_out_of_stock = false;
				break;
			}elseif(is_null($stock_qnt)){
				$is_out_of_stock = false; //I don't know why sometime this is null... so don't know if should or shouldn't do the update.
			}
		}
		if($is_out_of_stock){
			if(!$pretend)
				$q = $wpdb->query("UPDATE {$metas_table} SET meta_value = 'outofstock' WHERE meta_key = '_stock_status' AND post_id = {$post_id}");
			else
				$q = true;
		}else{
			$q = false;
		}

		return $q;
	}

	/**
	 * Get a variation parent directly from the DB. Useful to save some resource.
	 *
	 * @param $variation_id
	 *
	 * @return null|string
	 */
	public static function db_get_product_variation_parent($variation_id){
		global $wpdb;
		$posts_table = $wpdb->prefix."posts";
		$parent = $wpdb->get_var("SELECT post_parent FROM {$posts_table} WHERE ID = {$variation_id}");
		return $parent;
	}

	/**
	 * Get metas of a Order directly from the DB. Useful to save some resources.
	 *
	 * @param $order_id
	 * @return array|null|object
	 */
	public static function db_get_order_meta($order_id,$lang = null){
		global $wpdb;

		$meta = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE post_id = $order_id");
		$results = [];

		$parse_db_meta = function($metas){
			$results = [];
			foreach($metas as $oMeta){
				$key = preg_replace("/^_/","",$oMeta->meta_key);
				$value = call_user_func(function() use($oMeta){
					if(is_serialized($oMeta->meta_value)){
						$value = unserialize($oMeta->meta_value);
					}elseif(Utilities::isJson($oMeta->meta_value)){
						$value = json_decode($oMeta->meta_value);
					}else{
						$value = $oMeta->meta_value;
					}
					return $value;
				});
				$results[$key] = $value;
			}
			return $results;
		};

		if(is_array($meta) && !empty($meta)){
			$results = $parse_db_meta($meta);
		}

		//Get the items ( @see Abstract_WC_Order get_items() )
		$type = array( 'line_item' );
		$get_items_sql  = $wpdb->prepare( "SELECT order_item_id, order_item_name, order_item_type FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d ", $order_id );
		$get_items_sql .= "AND order_item_type IN ( '" . implode( "','", array_map( 'esc_sql', $type ) ) . "' ) ORDER BY order_item_id;";
		$line_items     = $wpdb->get_results( $get_items_sql );

		foreach($line_items as $k => $item){
			$item_meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value, meta_id FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = %d ORDER BY meta_id", absint( $item->order_item_id ) ) );
			$item_meta_result = [];
			if(is_array($item_meta) && !empty($item_meta)){
				$item_meta_result = $parse_db_meta($item_meta);
			}
			$line_items[$k]->meta = $item_meta_result;
		}

		$results['items'] = $line_items;

		return $results;
	}
}