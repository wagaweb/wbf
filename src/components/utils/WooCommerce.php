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

	/**
	 * Alias of get_post_meta() that returns the meta unserialized and cache the results.
	 *
	 * @param int|object $post if object id provided, it checks
	 *
	 * @return array
	 */
	static function get_post_metas($post){
		$post_id = false;

		if(!is_numeric($post)){
			if($post instanceof \WP_Post){
				if(isset($post->id)){
					$post_id = $post->id;
				}else{
					$post_id = $post->ID;
				}
			}elseif($post instanceof WBF_Product_Variation || $post instanceof \WC_Product_Variation){
				$post_id = $post->variation_id;
			}
		}else{
			$post_id = $post;
		}

		if(!$post_id) return [];

		static $cache;
		if(isset($cache[$post_id])) return $cache[$post_id];

		$metas = array_map(function($value){
			if(is_array($value) && isset($value[0])){
				return maybe_unserialize($value[0]);
			}else{
				return $value;
			}
		},get_post_meta($post_id));

		$cache[$post_id] = $metas;

		return $metas;
	}

	/**
	 * Alias of get_post_meta($post_id,$key,true) that cache the result.
	 *
	 * @param int|object $post
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function get_post_meta($post,$key){
		$post_id = false;

		if(!is_numeric($post)){
			if($post instanceof \WP_Post){
				if(isset($post->id)){
					$post_id = $post->id;
				}else{
					$post_id = $post->ID;
				}
			}elseif($post instanceof WBF_Product_Variation || $post instanceof \WC_Product_Variation){
				$post_id = $post->variation_id;
			}
		}else{
			$post_id = $post;
		}

		if(!$post_id) return [];

		static $cache = [];

		if(isset($cache[$post_id][$key])) return $cache[$post_id][$key];

		$meta = get_post_meta($post_id,$key,true);

		if($meta){
			$cache[$post_id][$key] = $meta;
		}

		return $meta;
	}
}