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
			return new WBF_Product_Simple($product->get_id());
		}elseif($product instanceof \WC_Product_Variation){
			return new WBF_Product_Variation($product->get_id());
		}elseif($product instanceof \WC_Product_Variable){
			return new WBF_Product_Variable($product->get_id());
		}

		return false;
	}

	/**
	 * Get the product attributes
	 *
	 * @param int|object $product
	 *
	 * @return array
	 */
	public static function get_product_attributes($product){

		if(is_numeric($product)){
			$product = self::get_product($product);
		}

		$is_variation = $product instanceof \WC_Product_Variation || $product instanceof WBF_Product_Variation;

		$main_attributes = call_user_func(function() use($product){
			$taxs = wc_get_attribute_taxonomies();
			$terms = [];
			foreach ($taxs as $att_tax){
				$real_tax_name = 'pa_'.$att_tax->attribute_name;
				if(taxonomy_exists($real_tax_name)){
					$term_list = wp_get_post_terms($product->get_id(),$real_tax_name);
					if(is_array($term_list) && !empty($term_list)){
						$terms[$real_tax_name][] = $term_list;
					}
				}
			}
			return $terms;
		});

		if($is_variation){
			$variation_attributes_raw_list = wc_get_product_variation_attributes($product->variation_id);
			foreach ($variation_attributes_raw_list as $attribute_name => $attribute_value){
				$real_tax_name = str_replace('attribute_','',$attribute_name);
				$term = get_term_by('slug',$attribute_value,$real_tax_name);
				$variation_attributes[$real_tax_name] = $term;
			}
		}

		if($is_variation){
			$attributes = isset($variation_attributes) ? $variation_attributes : [];
			$attributes['_parent'] = $main_attributes;
		}else{
			$attributes = $main_attributes;
		}

		return $attributes;
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
			if($post instanceof WBF_Product_Variation || $post instanceof \WC_Product_Variation){
				$post_id = $post->variation_id;
			}else{
				if(isset($post->id)){
					$post_id = $post->id;
				}else{
					$post_id = $post->ID;
				}
			}
		}else{
			$post_id = $post;
		}

		if(!$post_id) return [];

		static $cache;
		if(isset($cache[$post_id])) return $cache[$post_id];

		/**
		 * Get and unserialize post meta
		 *
		 * @param $post_id
		 *
		 * @return array
		 */
		$get_metas = function($post_id){
			$metas = array_map(function($value){
				if(is_array($value) && isset($value[0])){
					return maybe_unserialize($value[0]);
				}else{
					return $value;
				}
			},get_post_meta($post_id));
			return $metas;
		};

		$metas = $get_metas($post_id);

		if($post instanceof \WC_Product_Variation || $post instanceof WBF_Product_Variation){
			$parent_metas = $get_metas($post->parent->id);
			$metas['__parent'] = $parent_metas;
		}

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

	/**
	 * Update a stock quantity of a product
	 *
	 * @param int $product_id
	 * @param int $new_stock
	 * @param bool $dry_run
	 *
	 * @return bool|int
	 */
	public static function update_product_stock($product_id,$new_stock,$dry_run = false){
		global $wpdb;

		//Get product
		$product = self::get_product($product_id);

		//Get the metas
		$metas = self::get_post_metas($product);

		//Get parent and parent metas
		$parent = isset($product->parent) ? $product->parent : false;
		$parent_metas = $parent ? self::get_post_metas($parent) : false;

		/*
		 * UPDATING STOCK QUANTITY
		 */
		if(!$dry_run){
			$update_result = update_post_meta($product_id,"_stock",$new_stock);
		}else{
			$update_result = 1;
		}
		if($new_stock === 0 || $new_stock === "0"){
			//Product is out-of-stock
			if(!$dry_run){
				update_post_meta($product_id,"_stock_status","outofstock");
			}
			if($parent && ( $parent instanceof \WC_Product_Variable || $parent instanceof WBF_Product_Variable ) ){
				if(self::db_variable_product_maybe_set_out_of_stock($parent->get_id(),true)){
					//All product variations are out-of-stock;
					if(!$dry_run) {
						update_post_meta( $parent->get_id(), "_stock_status", "outofstock" ); //Set the parent out of stock if needed
					}
				}
			}
		}
		if($parent && ( $parent instanceof \WC_Product_Variable || $parent instanceof WBF_Product_Variable ) ){
			//Sync variations;
			if(!$dry_run){
				$parent->variable_product_sync();
			}
		}

		return $update_result;
	}

	/**
	 * @param $product
	 *
	 * @return bool
	 */
	public static function is_simple_product($product){
		return $product instanceof \WC_Product_Simple || $product instanceof WBF_Product_Simple;
	}

	/**
	 * @param $product
	 *
	 * @return bool
	 */
	public static function is_variable_product($product){
		return $product instanceof \WC_Product_Variable || $product instanceof WBF_Product_Variable;
	}

	/**
	 * @param $product
	 *
	 * @return bool
	 */
	public static function is_variation($product){
		return $product instanceof \WC_Product_Variation || $product instanceof WBF_Product_Variation;
	}
}