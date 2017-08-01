<?php

namespace WBF\components\utils;

use WBF\components\utils\woocommerce\DBUtilities;

class WooCommerce{

	use DBUtilities;

	/**
	 * Wrapper for wc_get_product that caches the results
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function wc_get_product($id){
		static $ids;
		if(!isset($ids[$id])){
			$ids[$id] = wc_get_product($id);
		}
		return $ids[$id];
	}

	public static function replace_wc_product_classes(){
		add_filter("woocommerce_product_class", function(){

		});
	}
}