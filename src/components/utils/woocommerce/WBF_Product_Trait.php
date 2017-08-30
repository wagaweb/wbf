<?php

namespace WBF\components\utils\woocommerce;

use WBF\components\utils\WooCommerce;

trait WBF_Product_Trait{
	/**
	 * Checks if the product is on sale (for real... :') )
	 *
	 * @return bool
	 */
	public function is_on_sale_for_real(){
		$meta = WooCommerce::get_post_metas($this->id);
		$stock_status = isset($meta['_stock_status']) ? $meta['_stock_status'] : false;
		if(!$stock_status) return false;

		$sale_price = isset($meta['_sale_price'])? $meta['_sale_price'] : 0;

		if($sale_price > 0){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Get the actual percentage of discount
	 *
	 * @return int
	 */
	public function get_discount_percentage(){
		if(!$this->is_on_sale_for_real()) return 0;

		$regular_price = $this->regular_price;
		$sale_price = $this->sale_price;
		$percentage = round((( ( $regular_price - $sale_price ) / $regular_price ) * 100),0) ;
		return $percentage;
	}

	/**
	 * Returns the product attributes
	 *
	 * @return array
	 */
	public function get_attributes(){
		return WooCommerce::get_product_attributes($this);
	}
}