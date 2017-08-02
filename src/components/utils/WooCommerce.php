<?php

namespace WBF\components\utils;

use WBF\components\utils\woocommerce\DBUtilities;
use WBF\components\utils\woocommerce\WBF_Product_Simple;
use WBF\components\utils\woocommerce\WBF_Product_Variable;
use WBF\components\utils\woocommerce\WBF_Product_Variation;

class WooCommerce{

	use DBUtilities;

	/**
	 * Wrapper for wc_get_product that caches the results
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function get_product($id){
		static $ids;
		if(!isset($ids[$id])){
			$ids[$id] = wc_get_product($id);
		}
		return $ids[$id];
	}

	/**
	 * Adds an hook to "woocommerce_product_class" that replace the vanilla WC classes with WBF ones
	 */
	public static function replace_wc_product_classes(){
		add_filter("woocommerce_product_class", function($classname, $product_type, $post_type, $product_id){
			$prefix = '\WBF\components\utils\woocommerce';

			switch($classname){
				case 'WC_Product_Simple':
					$classname = $prefix.'\WBF_Product_Simple';
					break;
				case 'WC_Product_Variable':
					$classname = $prefix.'\WBF_Product_Variable';
					break;
				case 'WC_Product_Variation':
					$classname = $prefix.'\WBF_Product_Variation';
					break;
			}
			return $classname;
		},10,4);
	}

	/**
	 * Create a new WBF_Product_* instance starting from a vanilla $product
	 *
	 * @param \WC_Product|int $product
	 *
	 * @return FALSE|WBF_Product_Simple|WBF_Product_Variable|WBF_Product_Variation
	 */
	public static function get_wbf_product($product){
		if(is_numeric($product)){
			$product = self::get_product( $product );
		}

		if($product instanceof \WC_Product_Simple){
			return new WBF_Product_Simple($product->id);
		}elseif($product instanceof \WC_Product_Variation){
			return new WBF_Product_Variation($product->id);
		}elseif($product instanceof \WC_Product_Variable){
			return new WBF_Product_Variable($product->id);
		}

		return false;
	}
}